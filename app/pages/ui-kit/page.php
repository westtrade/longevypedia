<?php

declare(strict_types=1);

/**
 * UI-набор: индекс.
 *
 * Пункты читаются из `data/ui-kit.php` — того же каталога, из которого строятся
 * меню и навигация по страницам компонентов. Отдельного списка здесь нет,
 * иначе он разошёлся бы с каталогом при первом же добавлении компонента.
 */

$kit = require dirname(__DIR__, 3) . '/data/ui-kit.php';

$this->layout('components::layout/site-layout', [
	'title' => (string) ($kit['title'] ?? 'UI-набор') . ' — Longevypedia',
	'description' => (string) ($kit['lead'] ?? ''),
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<div class="container ui-kit-index">
	<header class="ui-kit-index__header">
		<p class="ui-kit-index__eyebrow">Дизайн-система</p>
		<h1 class="ui-kit-index__title"><?= e((string) ($kit['title'] ?? 'UI-набор')) ?></h1>
		<p class="ui-kit-index__lead"><?= e((string) ($kit['lead'] ?? '')) ?></p>
	</header>

	<?php foreach (($kit['groups'] ?? []) as $group): ?>
		<section class="ui-kit-index__group">
			<h2 class="ui-kit-index__group-title"><?= e((string) ($group['title'] ?? '')) ?></h2>
			<ul class="ui-kit-index__list">
				<?php foreach (($group['items'] ?? []) as $item): ?>
					<?php
					// Плитка индекса — та же карточка набора: рамка, наведение,
					// фокус и растянутая ссылка уже есть в `ui/card`, дублировать
					// их здесь значило бы держать вторую копию стилей.
					?>
					<li class="ui-kit-index__item">
						<?= $this->insert('components::ui/card', [
							'title' => (string) ($item['title'] ?? ''),
							'meta' => (string) ($item['note'] ?? ''),
							'href' => '/ui-kit/' . (string) ($item['slug'] ?? ''),
							'variant' => 'default',
							'titleTag' => 'h3',
							'attrs' => ['class' => 'ui-kit-index__card'],
						]) ?>
					</li>
				<?php endforeach ?>
			</ul>
		</section>
	<?php endforeach ?>
</div>

<style lang="scss">
	.ui-kit-index {
		display: flex;
		flex-direction: column;
		gap: var(--space-8);
		padding-block: var(--space-8);
	}

	.ui-kit-index__header {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		max-width: var(--layout-measure);
	}

	.ui-kit-index__eyebrow {
		margin: 0;
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-kit-index__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-4xl);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-tight);
		line-height: var(--leading-tight);
	}

	.ui-kit-index__lead {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		line-height: var(--leading-relaxed);
	}

	.ui-kit-index__group {
		display: flex;
		flex-direction: column;
		gap: var(--space-3);
	}

	.ui-kit-index__group-title {
		margin: 0;
		padding-bottom: var(--space-2);
		border-bottom: 1px solid var(--color-border);
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-kit-index__list {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(min(100%, 20rem), 1fr));
		gap: var(--space-3);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	/* Карточка тянется на высоту ряда, чтобы плитки в строке были одной высоты. */
	.ui-kit-index__card {
		height: 100%;
	}
</style>
<?php $this->end(); ?>
