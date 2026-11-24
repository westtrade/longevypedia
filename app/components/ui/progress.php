<?php

declare(strict_types=1);

/**
 * Полоса выполнения или заполненности шкалы.
 *
 * Это индикатор, а не оценка: смысл числа задаёт вызывающий шаблон через
 * `label`, компонент не решает, «хорошо» это или «плохо».
 *
 * @var string $label что показывает шкала
 * @var float|int $value текущее значение
 * @var float|int $max максимум шкалы
 * @var string|null $hint уточнение справа
 * @var string $tone brand | success | warning | danger
 */

$label ??= '';
$value ??= 0;
$max ??= 100;
$hint ??= null;
$tone ??= 'brand';

$max = (float) $max > 0 ? (float) $max : 100.0;
$value = max(0.0, min((float) $value, $max));
$percent = (int) round($value / $max * 100);
?>
<div class="ui-progress ui-progress--<?= e($tone) ?>">
	<div class="ui-progress__head">
		<span class="ui-progress__label"><?= e($label) ?></span>
		<?php if ($hint !== null): ?>
			<span class="ui-progress__hint"><?= e($hint) ?></span>
		<?php endif ?>
	</div>
	<div class="ui-progress__track"
		role="progressbar"
		aria-valuemin="0"
		aria-valuemax="100"
		aria-valuenow="<?= e((string) $percent) ?>"
		aria-label="<?= e($label) ?>">
		<span class="ui-progress__bar" style="width: <?= e((string) $percent) ?>%"></span>
	</div>
</div>

<style lang="scss">
	.ui-progress {
		display: flex;
		flex-direction: column;
		gap: var(--space-1);
	}

	.ui-progress__head {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: var(--space-3);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-progress__label {
		color: var(--color-text-muted);
	}

	.ui-progress__hint {
		color: var(--color-text-subtle);
		font-variant-numeric: tabular-nums;
	}

	.ui-progress__track {
		height: 0.5rem;
		border-radius: var(--radius-full);
		background: var(--color-surface-inset);
		overflow: hidden;
	}

	.ui-progress__bar {
		display: block;
		height: 100%;
		border-radius: inherit;
		background: var(--color-brand);
	}

	.ui-progress--success .ui-progress__bar {
		background: var(--color-success);
	}

	.ui-progress--warning .ui-progress__bar {
		background: var(--color-warning);
	}

	.ui-progress--danger .ui-progress__bar {
		background: var(--color-danger);
	}
</style>
