<?php

declare(strict_types=1);

/**
 * Рамка графика.
 *
 * Компонент не рисует данные и не придумывает серии: он даёт оформление
 * (заголовок, область, подпись, легенда, пустое состояние), а сам график
 * вставляется снаружи готовым SVG. Так демонстрационные страницы не выдают
 * вымышленные числа за измерения.
 *
 * @var string $title заголовок графика
 * @var string|null $caption подпись: что показано и в каких единицах
 * @var mixed $chart сам график (SVG или замыкание)
 * @var array<int, array{label: string, color?: string|null}> $legend
 * @var mixed $empty состояние «данных нет»
 */

$title ??= '';
$caption ??= null;
$chart ??= null;
$legend ??= [];
$empty ??= null;
?>
<figure class="ui-chart">
	<figcaption class="ui-chart__head">
		<span class="ui-chart__title"><?= e($title) ?></span>
		<?php if ($caption !== null): ?>
			<span class="ui-chart__caption"><?= e($caption) ?></span>
		<?php endif ?>
	</figcaption>

	<div class="ui-chart__plot">
		<?php if ($chart === null): ?>
			<div class="ui-chart__empty"><?php render($empty); ?></div>
		<?php else: ?>
			<?php render($chart); ?>
		<?php endif ?>
	</div>

	<?php if ($legend !== []): ?>
		<ul class="ui-chart__legend">
			<?php foreach ($legend as $item): ?>
				<li class="ui-chart__legend-item">
					<span class="ui-chart__swatch" style="background: <?= e((string) ($item['color'] ?? 'var(--color-brand)')) ?>"></span>
					<?= e((string) ($item['label'] ?? '')) ?>
				</li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>
</figure>

<style lang="scss">
	.ui-chart {
		display: flex;
		flex-direction: column;
		gap: var(--space-3);
		margin: 0;
		padding: var(--space-4);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.ui-chart__head {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	.ui-chart__title {
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		font-weight: var(--weight-semibold);
	}

	.ui-chart__caption {
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-chart__plot {
		display: grid;
		place-items: center;
		min-height: 10rem;
		border: 1px dashed var(--color-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface-alt);
	}

	.ui-chart__plot svg {
		max-width: 100%;
		height: auto;
	}

	.ui-chart__empty {
		padding: var(--space-6);
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		text-align: center;
	}

	.ui-chart__legend {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-4);
		margin: 0;
		padding: 0;
		list-style: none;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-chart__legend-item {
		display: inline-flex;
		align-items: center;
		gap: var(--space-2);
	}

	.ui-chart__swatch {
		width: 0.75rem;
		height: 0.75rem;
		border-radius: 2px;
	}
</style>
