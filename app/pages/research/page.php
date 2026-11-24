<?php

declare(strict_types=1);

/**
 * Индекс исследований.
 *
 * Отдельная страница, а не каталог с фильтром: у исследований есть поля,
 * которых нет у остальных типов (дизайн, год, журнал), и по ним хочется
 * сортировать. Записи те же, что в `/research/<slug>/` — их отдаёт маршрут
 * `document.research`.
 *
 * @var array $route
 */

$repository = longevypedia_content();
$locale = (string) ($route['locale'] ?? $repository->defaultLocale());

$studies = $repository->ofType('research', $locale);

usort($studies, static function ($a, $b): int {
	$yearA = (int) (($a->metaValue('study') ?? [])['year'] ?? 0);
	$yearB = (int) (($b->metaValue('study') ?? [])['year'] ?? 0);

	return $yearB <=> $yearA ?: strcasecmp($a->title(), $b->title());
});

// Подписи дизайнов — один словарь на весь проект: страница индекса и карточка
// исследования раньше держали по своей копии, и они разошлись со схемой.
$designs = \Longevypedia\Content\StudyDesigns::LABELS;

$this->layout('components::layout/site-layout', [
	'title' => 'Research',
	'description' => 'Primary studies indexed in Longevypedia: design, population, intervention and reported outcomes, each linked to the claims it supports or contradicts.',
	'canonical' => '/research',
	'current' => 'research',
]);

$this->start('page');
?>
<div class="container research-index">
	<header class="research-index__header">
		<h1 class="research-index__title">Research</h1>
		<p class="research-index__lead">
			Primary studies behind the claims in this encyclopedia. Each record states the
			design, the population, the intervention and the outcomes as the authors
			reported them — including null results.
		</p>
	</header>

	<?php if ($studies === []): ?>
		<p class="research-index__empty">No studies have been indexed yet.</p>
	<?php else: ?>
		<ol class="research-index__list">
			<?php foreach ($studies as $study): ?>
				<?php
				$meta = (array) $study->metaValue('study', []);
				$design = (string) ($meta['design'] ?? '');
				$authors = is_array($meta['authors'] ?? null) ? $meta['authors'] : [];
				$journal = (string) ($meta['journal'] ?? '');
				$year = (string) ($meta['year'] ?? '');
				$isExample = (bool) ($meta['example'] ?? false);
				?>
				<li class="research-index__item">
					<div class="research-index__head">
						<h2 class="research-index__name">
							<a href="<?= $this->e($study->url) ?>"><?= $this->e($study->title()) ?></a>
						</h2>
						<?php if ($isExample): ?>
							<span class="research-index__flag">Example record</span>
						<?php endif; ?>
					</div>

					<p class="research-index__meta">
						<?php if ($authors !== []): ?>
							<span><?= $this->e(implode(', ', array_slice($authors, 0, 3))) ?><?= count($authors) > 3 ? ' et al.' : '' ?></span>
							<span class="research-index__dot" aria-hidden="true">·</span>
						<?php endif; ?>
						<?php if ($journal !== ''): ?>
							<span><?= $this->e($journal) ?></span>
							<span class="research-index__dot" aria-hidden="true">·</span>
						<?php endif; ?>
						<?php if ($year !== ''): ?>
							<span><?= $this->e($year) ?></span>
							<span class="research-index__dot" aria-hidden="true">·</span>
						<?php endif; ?>
						<span><?= $this->e($designs[$design] ?? $design) ?></span>
					</p>

					<p class="research-index__summary"><?= $this->e($study->summary()) ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>

<style lang="scss">
	.research-index {
		padding-block: 2rem 0;
		max-width: 56rem;
	}

	.research-index__header {
		margin-bottom: 2rem;
	}

	.research-index__title {
		margin: 0 0 0.5rem;
		font-family: var(--font-text);
		font-size: clamp(1.8rem, 1.3rem + 1.8vw, 2.4rem);
	}

	.research-index__lead {
		margin: 0;
		max-width: var(--layout-measure);
		color: var(--color-text-muted);
		line-height: 1.55;
	}

	.research-index__list {
		margin: 0;
		padding: 0;
		list-style: none;
		display: grid;
		gap: 1.25rem;
	}

	.research-index__item {
		padding-bottom: 1.25rem;
		border-bottom: 1px solid var(--color-border);
	}

	.research-index__head {
		display: flex;
		flex-wrap: wrap;
		align-items: baseline;
		gap: 0.6rem;
	}

	.research-index__name {
		margin: 0;
		font-family: var(--font-text);
		font-size: var(--text-xl);
		line-height: 1.25;

		a {
			color: var(--color-text);
			text-decoration: none;

			&:hover {
				color: var(--color-accent);
			}
		}
	}

	.research-index__flag {
		font-size: var(--text-xs);
		color: var(--color-warning);
		border: 1px solid var(--color-warning);
		border-radius: 999px;
		padding: 0.05rem 0.5rem;
	}

	.research-index__meta {
		display: flex;
		flex-wrap: wrap;
		gap: 0.4rem;
		margin: 0.35rem 0 0.5rem;
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.research-index__dot {
		color: var(--color-border-strong);
	}

	.research-index__summary {
		margin: 0;
		font-size: var(--text-sm);
		line-height: 1.6;
		color: var(--color-text-muted);
	}

	.research-index__empty {
		color: var(--color-text-muted);
	}
</style>
<?php $this->stop('page'); ?>
