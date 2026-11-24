<?php

declare(strict_types=1);

/**
 * Показатель: число с подписью.
 *
 * Значение — уже отформатированная строка: компонент не форматирует числа и не
 * считает проценты, иначе округление пришлось бы проверять в каждом месте.
 *
 * @var string $label что измерено
 * @var string $value значение
 * @var string|null $unit единица или знак
 * @var string|null $hint уточнение под числом
 * @var string $size md | lg
 */

$label ??= '';
$value ??= '';
$unit ??= null;
$hint ??= null;
$size ??= 'md';
?>
<div class="ui-stat ui-stat--<?= e($size) ?>">
	<p class="ui-stat__value">
		<?= e($value) ?><?php if ($unit !== null): ?><span class="ui-stat__unit"><?= e($unit) ?></span><?php endif ?>
	</p>
	<p class="ui-stat__label"><?= e($label) ?></p>
	<?php if ($hint !== null): ?>
		<p class="ui-stat__hint"><?= e($hint) ?></p>
	<?php endif ?>
</div>

<style lang="scss">
	.ui-stat {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	.ui-stat__value {
		margin: 0;
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-2xl);
		font-weight: var(--weight-semibold);
		line-height: var(--leading-tight);
		font-variant-numeric: tabular-nums;
	}

	.ui-stat--lg .ui-stat__value {
		font-size: var(--text-4xl);
	}

	.ui-stat__unit {
		margin-left: 0.15em;
		color: var(--color-text-muted);
		font-size: 0.55em;
		font-weight: var(--weight-medium);
	}

	.ui-stat__label {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
	}

	.ui-stat__hint {
		margin: 0;
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}
</style>
