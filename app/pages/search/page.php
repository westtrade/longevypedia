<?php

declare(strict_types=1);

/**
 * Страница результатов поиска.
 *
 * Запрос и тип приходят из строки запроса, поэтому страница живёт без
 * JavaScript: форма в шапке отправляет GET на `/search`. Подсказки в шапке —
 * отдельный фрагмент (`app/fragments/search-results.php`), здесь же полный
 * список с фрагментами текста.
 *
 * @var array $route
 * @var \Bolt\Core\Http\Request $request
 */

$request ??= \Bolt\Core\Http\Request::fromGlobals();

$repository = longevypedia_content();
$locale = (string) ($route['locale'] ?? $repository->defaultLocale());

$query = trim((string) $request->input('q', ''));
$type = trim((string) $request->input('type', ''));

$filters = ['locale' => $locale];

if ($type !== '' && \Longevypedia\Content\DocumentTypes::exists($type)) {
	$filters['type'] = $type;
} else {
	$type = '';
}

$hits = $query !== '' ? $repository->search($query, $filters) : [];
$count = count($hits);

$this->layout('components::layout/site-layout', [
	'title' => $query !== '' ? 'Search: ' . $query : 'Search',
	'description' => 'Search across Longevypedia documents, claims, studies and entities.',
	'canonical' => '/search',
	'current' => '',
]);
?>
<?php $this->start('page'); ?>
<div class="container search-page">
	<header class="search-page__header">
		<h1 class="search-page__title">Search</h1>

		<?= $this->insert('components::site/search-form', ['id' => 'page-search', 'value' => $query]) ?>

		<?php if ($query !== ''): ?>
			<p class="search-page__summary">
				<?= $count === 0 ? 'No results for' : $count . ($count === 1 ? ' result for' : ' results for') ?>
				<strong>“<?= $this->e($query) ?>”</strong><?= $type !== '' ? ' in ' . $this->e(\Longevypedia\Content\DocumentTypes::plural($type)) : '' ?>.
			</p>
		<?php endif; ?>
	</header>

	<?php if ($count > 0): ?>
		<nav class="search-page__types" aria-label="Filter results by type">
			<a class="search-page__type<?= $type === '' ? ' is-active' : '' ?>" href="/search?q=<?= rawurlencode($query) ?>">All</a>
			<?php foreach ($repository->stats($locale)['byType'] as $availableType => $total): ?>
				<a
					class="search-page__type<?= $type === $availableType ? ' is-active' : '' ?>"
					href="/search?q=<?= rawurlencode($query) ?>&type=<?= rawurlencode($availableType) ?>"
				><?= $this->e(\Longevypedia\Content\DocumentTypes::plural($availableType)) ?></a>
			<?php endforeach; ?>
		</nav>

		<ol class="search-page__list">
			<?php foreach ($hits as $hit): ?>
				<?php $document = $hit->document; ?>
				<li class="search-page__item">
					<p class="search-page__meta">
						<span class="search-page__type-label"><?= $this->e($document->typeLabel()) ?></span>
						<?php if ($document->updated() !== null): ?>
							<span class="search-page__dot" aria-hidden="true">·</span>
							<span>updated <?= $this->e(longevypedia_date($document->updated())) ?></span>
						<?php endif; ?>
					</p>
					<h2 class="search-page__name">
						<a href="<?= $this->e($document->url) ?>"><?= $this->e($document->title()) ?></a>
					</h2>
					<?php if ($hit->snippet !== ''): ?>
						<p class="search-page__snippet"><?= $hit->highlighted() ?></p>
					<?php else: ?>
						<p class="search-page__snippet"><?= $this->e($document->summary()) ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php elseif ($query !== ''): ?>
		<?= $this->insert('components::ui/empty-state', [
			'icon' => 'book',
			'title' => 'Nothing in the catalogue matches that query',
			'text' => 'Try a shorter term, or browse the catalogue and the study index.',
			'action' => $this->insert('components::ui/button', [
				'label' => 'Open the catalogue',
				'variant' => 'secondary',
				'href' => '/catalog',
			]),
		]) ?>
	<?php else: ?>
		<p class="search-page__hint">
			Search by title, summary, tags or full text. For example:
			<a href="/search?q=senescence">senescence</a>,
			<a href="/search?q=rapamycin">rapamycin</a>,
			<a href="/search?q=autophagy">autophagy</a>.
		</p>
	<?php endif; ?>
</div>

<style lang="scss">
	.search-page {
		padding-block: 2rem 0;
		max-width: 52rem;
	}

	.search-page__title {
		margin: 0 0 1rem;
		font-family: var(--font-serif);
		font-size: clamp(1.8rem, 1.3rem + 1.8vw, 2.4rem);
	}

	.search-page__summary {
		margin: 1rem 0 0;
		font-size: var(--text-sm);
		color: var(--color-text-muted);
	}

	.search-page__types {
		display: flex;
		flex-wrap: wrap;
		gap: 0.4rem;
		margin: 1.5rem 0 1.25rem;
	}

	.search-page__type {
		padding: 0.25rem 0.6rem;
		border: 1px solid var(--color-border);
		border-radius: 999px;
		font-size: var(--text-xs);
		color: var(--color-text-muted);
		text-decoration: none;

		&:hover {
			border-color: var(--color-accent);
			color: var(--color-accent-text);
		}

		&.is-active {
			background: var(--color-accent-subtle);
			border-color: var(--color-accent);
			color: var(--color-accent-text);
			font-weight: 600;
		}
	}

	.search-page__list {
		margin: 0;
		padding: 0;
		list-style: none;
		display: grid;
		gap: 1.1rem;
	}

	.search-page__item {
		padding-bottom: 1.1rem;
		border-bottom: 1px solid var(--color-border);
	}

	.search-page__meta {
		display: flex;
		flex-wrap: wrap;
		gap: 0.35rem;
		margin: 0 0 0.25rem;
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.search-page__type-label {
		text-transform: uppercase;
		letter-spacing: 0.06em;
		font-weight: 600;
		color: var(--color-accent-text);
	}

	.search-page__dot {
		color: var(--color-border-strong);
	}

	.search-page__name {
		margin: 0 0 0.3rem;
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		line-height: 1.3;

		a {
			color: var(--color-text);
			text-decoration: none;

			&:hover {
				color: var(--color-accent-text);
			}
		}
	}

	.search-page__snippet {
		margin: 0;
		font-size: var(--text-sm);
		line-height: 1.55;
		color: var(--color-text-muted);
	}

	.search-page__snippet mark {
		background: var(--color-accent-subtle);
		color: var(--color-accent-text);
		border-radius: 2px;
		padding-inline: 1px;
	}

	.search-page__hint {
		margin-top: 1.5rem;
		font-size: var(--text-sm);
		color: var(--color-text-muted);
		line-height: 1.6;
	}
</style>
<?php $this->stop('page'); ?>
