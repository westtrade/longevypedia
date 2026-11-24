<?php

declare(strict_types=1);

/**
 * Бейдж — короткий статус или категория.
 *
 * Проектный компонент: `components::ui/badge`. В ui-kit Bolt бейджа нет.
 *
 * Смысл бейджа — слово, а не цвет: `success` всегда несёт текст («Подтверждено»),
 * цвет только ускоряет чтение. Точка слева — для плотных таблиц, где текст
 * мелкий и цвет фона мешает.
 *
 * @param string      $label
 * @param string      $variant neutral | brand | success | warning | danger | info | outline
 * @param bool        $dot
 * @param string|null $icon  имя иконки слева (без -icon)
 * @param string      $size  sm | md
 * @param array       $attrs
 */

$label ??= '';
$variant ??= 'neutral';
$dot ??= false;
$icon ??= null;
$size ??= 'md';
$attrs ??= [];
?>
<span<?= attrs($attrs, ['class' => clsx('ui-badge', 'ui-badge--' . $variant, 'ui-badge--' . $size)]) ?>>
	<?php if ($dot): ?>
		<span class="ui-badge__dot" aria-hidden="true"></span>
	<?php elseif ($icon !== null): ?>
		<?= $this->insert('bolt://ui-kit/icon', ['name' => $icon, 'size' => 14, 'class' => 'ui-badge__icon']) ?>
	<?php endif; ?>
	<?= $this->e((string) $label) ?>
</span>

<style lang="scss">
	.ui-badge {
		display: inline-flex;
		align-items: center;
		gap: var(--space-1);
		padding: 0.15rem var(--space-2);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface-alt);
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-medium);
		line-height: var(--leading-table);
		white-space: nowrap;
	}

	.ui-badge--sm {
		padding: 0 var(--space-1);
		font-size: 0.6875rem;
	}

	.ui-badge__dot {
		width: 0.4rem;
		height: 0.4rem;
		flex: none;
		border-radius: var(--radius-full);
		background: currentColor;
	}

	.ui-badge__icon {
		flex: none;
	}

	.ui-badge--brand {
		border-color: color-mix(in oklch, var(--color-brand) 30%, transparent);
		background: var(--color-brand-subtle);
		color: var(--color-brand-text);
	}

	.ui-badge--success {
		border-color: var(--color-success-border);
		background: var(--color-success-soft);
		color: var(--color-success-text);
	}

	.ui-badge--warning {
		border-color: var(--color-warning-border);
		background: var(--color-warning-soft);
		color: var(--color-warning-text);
	}

	.ui-badge--danger {
		border-color: var(--color-danger-border);
		background: var(--color-danger-soft);
		color: var(--color-danger-text);
	}

	.ui-badge--info {
		border-color: var(--color-info-border);
		background: var(--color-info-soft);
		color: var(--color-info-text);
	}

	.ui-badge--outline {
		border-color: var(--color-border-strong);
		background: transparent;
		color: var(--color-text);
	}
</style>
