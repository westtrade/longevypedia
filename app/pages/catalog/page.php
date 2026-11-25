<?php

declare(strict_types=1);

/**
 * Каталог: все опубликованные документы с фильтрами по типу и тематике.
 *
 * Один шаблон обслуживает и общий каталог `/catalog`, и индекс типа
 * `/concepts/`: тип приходит либо из маршрута (`$route['type']`), либо из
 * строки запроса. Фильтры работают без JavaScript обычной GET-формой; с HTMX
 * обновляется только список результатов (`#catalog-results`), адрес меняется
 * через `hx-push-url`, поэтому ссылку можно скопировать.
 *
 * @var array  $route
 * @var \Bolt\Core\Http\Request $request
 */

$request ??= \Bolt\Core\Http\Request::fromGlobals();

$repository = longevypedia_content();
$locale = (string) ($route['locale'] ?? $repository->defaultLocale());

$typeFromRoute = (string) ($route['type'] ?? '');
$type = $typeFromRoute !== '' ? $typeFromRoute : trim((string) $request->input('type', ''));
$topic = trim((string) $request->input('topic', ''));
$sort = (string) $request->input('sort', 'recent');
$query = trim((string) $request->input('q', ''));

if (!in_array($sort, ['recent', 'title'], true)) {
	$sort = 'recent';
}

$filters = ['locale' => $locale];

if ($type !== '') {
	$filters['type'] = $type;
}

if ($topic !== '') {
	$filters['topic'] = $topic;
}

$documents = $query !== ''
	? array_map(static fn ($hit) => $hit->document, $repository->search($query, $filters))
	: $repository->all($locale);

if ($query === '') {
	$documents = array_values(array_filter(
		$documents,
		static fn ($document): bool => ($type === '' || $document->type === $type)
			&& ($topic === '' || in_array($topic, $document->topics(), true)),
	));
}

usort($documents, static function ($a, $b) use ($sort): int {
	if ($sort === 'title') {
		return strcasecmp($a->title(), $b->title());
	}

	return strcmp((string) $b->updated(), (string) $a->updated())
		?: strcasecmp($a->title(), $b->title());
});

$stats = $repository->stats($locale);
$topics = $repository->topics($locale);

// Варианты для `ui/field` type=select: подпись — человеку, слаг — значением.
$topicOptions = ['' => 'All topics'];

foreach ($topics as $slug => $definition) {
	$topicOptions[$slug] = $definition['label'] . ' (' . $definition['count'] . ')';
}
$title = $type !== '' ? \Longevypedia\Content\DocumentTypes::plural($type) : 'Knowledge base';

// Ссылка фильтра сохраняет остальные параметры: чипы типов не должны сбрасывать
// выбранную тематику или поисковый запрос.
$urlWith = static function (array $overrides) use ($type, $topic, $sort, $query): string {
	$params = array_filter(array_merge([
		'type' => $type,
		'topic' => $topic,
		'sort' => $sort,
		'q' => $query,
	], $overrides), static fn (string $value): bool => $value !== '');

	return '/catalog' . ($params === [] ? '' : '?' . http_build_query($params));
};

$this->layout('components::layout/site-layout', [
	'title' => $title,
	'description' => 'Browse every document in Longevypedia by type, topic and update date.',
	'canonical' => $urlWith([]),
	'current' => 'catalog',
]);

$this->start('page');
?>
<div class="container catalog">
	<header class="catalog__header">
		<h1 class="catalog__title"><?= $this->e($title) ?></h1>
		<p class="catalog__lead">
			<?= $stats['total'] ?> published documents across <?= $stats['topics'] ?> topics.
			Every entry links to its sources and to the Markdown file it came from.
		</p>
	</header>

	<nav class="catalog__chips" aria-label="Filter by document type">
		<?= $this->insert('components::ui/tag', [
			'label' => 'All',
			'href' => $urlWith(['type' => '']),
			'count' => $stats['total'],
			'active' => $type === '',
		]) ?>
		<?php foreach ($stats['byType'] as $chipType => $count): ?>
			<?= $this->insert('components::ui/tag', [
				'label' => \Longevypedia\Content\DocumentTypes::plural($chipType),
				'href' => $urlWith(['type' => $chipType]),
				'count' => $count,
				'active' => $type === $chipType,
			]) ?>
		<?php endforeach ?>
	</nav>

	<form
		class="catalog__filters"
		method="get"
		action="/catalog"
		hx-get="/catalog"
		hx-target="#catalog-results"
		hx-select="#catalog-results"
		hx-swap="outerHTML"
		hx-push-url="true"
		hx-indicator="#catalog-pending"
		hx-trigger="change, submit, input changed delay:400ms from:find input[type='search']"
	>
		<?php if ($type !== ''): ?>
			<input type="hidden" name="type" value="<?= $this->e($type) ?>">
		<?php endif ?>

		<?= $this->insert('components::ui/field', [
			'name' => 'topic',
			'id' => 'catalog-topic',
			'label' => 'Topic',
			'type' => 'select',
			'value' => $topic,
			'options' => $topicOptions,
			'attrs' => ['class' => 'catalog__filter'],
		]) ?>

		<?= $this->insert('components::ui/field', [
			'name' => 'sort',
			'id' => 'catalog-sort',
			'label' => 'Sort',
			'type' => 'select',
			'value' => $sort,
			'options' => ['recent' => 'Recently updated', 'title' => 'Title A–Z'],
			'attrs' => ['class' => 'catalog__filter'],
		]) ?>

		<?= $this->insert('components::ui/field', [
			'name' => 'q',
			'id' => 'catalog-q',
			'label' => 'Search within',
			'type' => 'search',
			'value' => $query,
			'placeholder' => 'e.g. senescence',
			'attrs' => ['class' => 'catalog__filter catalog__filter--search'],
		]) ?>

		<?= $this->insert('components::ui/button', [
			'label' => 'Apply',
			'type' => 'submit',
			'variant' => 'secondary',
			'attrs' => ['class' => 'catalog__submit'],
		]) ?>
	</form>

	<p class="catalog__pending" id="catalog-pending" role="status" aria-live="polite">
		<?= $this->insert('bolt://ui-kit/icon', ['name' => 'spinner', 'size' => 16, 'class' => 'catalog__pending-spinner']) ?>
		Updating results…
	</p>

	<p class="visually-hidden" role="status" aria-live="polite" data-catalog-status></p>

	<?= $this->insert('components::catalog/results', [
		'documents' => $documents,
		'types' => \Longevypedia\Content\DocumentTypes::TYPES,
		'query' => $query,
	]) ?>
</div>

<style lang="scss">
	.catalog {
		padding-block: var(--space-8) 0;
	}

	.catalog__header {
		max-width: var(--layout-measure);
		margin-bottom: var(--space-5);
	}

	.catalog__title {
		margin: 0 0 var(--space-2);
		font-family: var(--font-serif);
		font-size: clamp(1.8rem, 1.3rem + 1.8vw, var(--text-4xl));
		line-height: var(--leading-tight);
	}

	.catalog__lead {
		margin: 0;
		color: var(--color-text-muted);
		line-height: var(--leading-normal);
	}

	/* Чипы — теги набора: страница задаёт только раскладку, вид тега живёт в
	   компоненте, поэтому фильтр и карточка документа выглядят одинаково. */
	.catalog__chips {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		margin-bottom: var(--space-5);
	}

	.catalog__filters {
		display: flex;
		flex-wrap: wrap;
		align-items: end;
		gap: var(--space-3);
		padding: var(--space-4);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
		margin-bottom: var(--space-6);
	}

	.catalog__filter {
		min-width: 9rem;
	}

	.catalog__filter--search {
		flex: 1 1 12rem;
	}

	.catalog__submit {
		align-self: end;
	}

	/* Индикатор запроса: htmx добавляет класс htmx-request, пока идёт обмен.
	   Без него фильтр «замирает» без обратной связи. */
	.catalog__pending {
		display: none;
		align-items: center;
		gap: var(--space-2);
		margin: -1rem 0 var(--space-4);
		color: var(--color-text-muted);
		font-size: var(--text-sm);
	}

	.catalog__pending.htmx-request {
		display: flex;
	}

	/* Значок тот же, что у кнопки в состоянии загрузки: рисовать второй
	   кружок рядом с набором незачем. */
	.catalog__pending-spinner {
		flex: none;
		animation: catalog-spin 800ms linear infinite;
	}

	@keyframes catalog-spin {
		to {
			transform: rotate(360deg);
		}
	}

	@media (max-width: $bp-md) {
		.catalog__filter,
		.catalog__filter--search {
			flex: 1 1 100%;
			min-width: 0;
		}
	}
</style>
<?php $this->stop('page'); ?>
