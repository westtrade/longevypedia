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
		<?= $this->insert('components::ui/empty-state', [
			'title' => 'No studies indexed yet',
			'text' => 'Every claim on this site should trace back to a study record. This index fills as records are added.',
		]) ?>
	<?php else: ?>
		<ul class="research-index__list">
			<?php foreach ($studies as $study): ?>
				<li class="research-index__item">
					<?= $this->insert('components::ui/research-card', [
						'document' => $study,
						'headingLevel' => 2,
					]) ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>

<style lang="scss">
	.research-index {
		padding-block: var(--space-8) 0;
		max-width: var(--layout-page);
	}

	.research-index__header {
		margin-bottom: var(--space-8);
	}

	.research-index__title {
		margin: 0 0 var(--space-2);
		font-family: var(--font-serif);
		font-size: clamp(1.8rem, 1.3rem + 1.8vw, var(--text-4xl));
	}

	.research-index__lead {
		margin: 0;
		max-width: var(--layout-measure);
		color: var(--color-text-muted);
		line-height: var(--leading-normal);
	}

	/* Карточка исследования — компонент набора, поэтому список задаёт только
	   сетку и расстояние: вид карточки правится в одном месте. */
	.research-index__list {
		margin: 0;
		padding: 0;
		list-style: none;
		display: grid;
		grid-template-columns: minmax(0, 1fr);
		gap: var(--space-4);
	}

	.research-index__item {
		display: flex;
	}

	.research-index__item > * {
		flex: 1 1 auto;
	}
</style>
<?php $this->stop('page'); ?>
