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
		<a class="catalog__chip<?= $type === '' ? ' is-active' : '' ?>" href="<?= $this->e($urlWith(['type' => ''])) ?>"<?= $type === '' ? ' aria-current="page"' : '' ?>>All</a>
		<?php foreach ($stats['byType'] as $chipType => $count): ?>
			<a class="catalog__chip<?= $type === $chipType ? ' is-active' : '' ?>" href="<?= $this->e($urlWith(['type' => $chipType])) ?>"<?= $type === $chipType ? ' aria-current="page"' : '' ?>>
				<?= $this->e(\Longevypedia\Content\DocumentTypes::plural($chipType)) ?>
				<span class="catalog__chip-count"><?= $count ?></span>
			</a>
		<?php endforeach; ?>
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
		hx-trigger="change, submit, input changed delay:400ms from:find input[type='search']"
	>
		<?php if ($type !== ''): ?>
			<input type="hidden" name="type" value="<?= $this->e($type) ?>">
		<?php endif; ?>

		<div class="catalog__filter">
			<label class="catalog__label" for="catalog-topic">Topic</label>
			<select class="catalog__select" id="catalog-topic" name="topic">
				<option value="">All topics</option>
				<?php foreach ($topics as $slug => $definition): ?>
					<option value="<?= $this->e($slug) ?>"<?= $topic === $slug ? ' selected' : '' ?>>
						<?= $this->e($definition['label']) ?> (<?= $definition['count'] ?>)
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="catalog__filter">
			<label class="catalog__label" for="catalog-sort">Sort</label>
			<select class="catalog__select" id="catalog-sort" name="sort">
				<option value="recent"<?= $sort === 'recent' ? ' selected' : '' ?>>Recently updated</option>
				<option value="title"<?= $sort === 'title' ? ' selected' : '' ?>>Title A–Z</option>
			</select>
		</div>

		<div class="catalog__filter catalog__filter--search">
			<label class="catalog__label" for="catalog-q">Search within</label>
			<input class="catalog__input" id="catalog-q" type="search" name="q" value="<?= $this->e($query) ?>" placeholder="e.g. senescence">
		</div>

		<button class="catalog__submit" type="submit">Apply</button>
	</form>

	<p class="visually-hidden" role="status" aria-live="polite" data-catalog-status></p>

	<?= $this->insert('components::catalog/results', [
		'documents' => $documents,
		'types' => \Longevypedia\Content\DocumentTypes::TYPES,
		'query' => $query,
	]) ?>
</div>

<style lang="scss">
	.catalog {
		padding-block: 2rem 0;
	}

	.catalog__header {
		max-width: var(--layout-measure);
		margin-bottom: 1.5rem;
	}

	.catalog__title {
		margin: 0 0 0.5rem;
		font-family: var(--font-serif);
		font-size: clamp(1.8rem, 1.3rem + 1.8vw, 2.4rem);
		line-height: 1.15;
	}

	.catalog__lead {
		margin: 0;
		color: var(--color-text-muted);
		line-height: 1.55;
	}

	.catalog__chips {
		display: flex;
		flex-wrap: wrap;
		gap: 0.4rem;
		margin-bottom: 1.25rem;
	}

	.catalog__chip {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		padding: 0.3rem 0.7rem;
		border: 1px solid var(--color-border);
		border-radius: 999px;
		font-size: var(--text-sm);
		color: var(--color-text-muted);
		text-decoration: none;
		background: var(--color-surface);

		&:hover {
			border-color: var(--color-accent);
			color: var(--color-accent);
		}

		&.is-active {
			border-color: var(--color-accent);
			background: var(--color-accent-subtle);
			color: var(--color-accent);
			font-weight: 600;
		}
	}

	.catalog__chip-count {
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.catalog__filters {
		display: flex;
		flex-wrap: wrap;
		align-items: end;
		gap: 0.75rem;
		padding: 0.9rem 1rem;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
		margin-bottom: 1.75rem;
	}

	.catalog__filter {
		display: flex;
		flex-direction: column;
		gap: 0.25rem;
		min-width: 9rem;
	}

	.catalog__filter--search {
		flex: 1 1 12rem;
	}

	.catalog__label {
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.06em;
		color: var(--color-text-subtle);
	}

	.catalog__select,
	.catalog__input {
		padding: 0.4rem 0.5rem;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-sm);
		background: var(--color-bg);
		color: var(--color-text);
		font: inherit;
		font-size: var(--text-sm);
	}

	.catalog__submit {
		padding: 0.45rem 1rem;
		border: 1px solid var(--color-accent);
		border-radius: var(--radius-sm);
		background: var(--color-accent-fill);
		color: var(--color-on-accent);
		font: inherit;
		font-size: var(--text-sm);
		font-weight: 600;
		cursor: pointer;

		&:hover {
			background: var(--color-accent-fill-hover);
		}
	}
</style>
<?php $this->stop('page'); ?>
