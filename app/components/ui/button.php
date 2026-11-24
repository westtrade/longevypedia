<?php

declare(strict_types=1);

/**
 * Кнопка.
 *
 * Проектный компонент: `components::ui/button`.
 *
 * Почему не `bolt://ui-kit/button`: встроенная кнопка Bolt знает четыре варианта
 * (`primary|outline|blue|link`), два размера и не имеет состояния загрузки, а
 * список классов в ней закрытый — передать `danger` или `ghost` снаружи нельзя.
 * Расширять BOLT ради этого нельзя, поэтому проект держит свою кнопку с тем же
 * набором ролей и токенов. Всё остальное из ui-kit Bolt (`form`, `form-message`,
 * `icon`, `modal`, `pagination`, `seo`) используется как есть.
 *
 * @param string      $label     текст; для кнопки-иконки — доступное имя
 * @param string      $variant   primary | secondary | outline | ghost | danger | link
 * @param string      $size      sm | md | lg
 * @param string|null $href      если задан — рендерится <a>, иначе <button>
 * @param string      $type      type для <button>
 * @param string|null $icon      имя иконки слева (без суффикса -icon)
 * @param string|null $iconRight имя иконки справа
 * @param bool        $iconOnly  только иконка; текст уходит в aria-label
 * @param bool        $loading   состояние загрузки: спиннер, aria-busy, блокировка
 * @param bool        $disabled
 * @param bool        $block     растянуть на всю ширину контейнера
 * @param mixed       $children  содержимое вместо label
 * @param array       $attrs     дополнительные атрибуты
 */

$label ??= '';
$variant ??= 'primary';
$size ??= 'md';
$href ??= null;
$type ??= 'button';
$icon ??= null;
$iconRight ??= null;
$iconOnly ??= false;
$loading ??= false;
$disabled ??= false;
$block ??= false;
$children ??= null;
$attrs ??= [];

$isLink = $href !== null && !$disabled && !$loading;
$tag = $isLink ? 'a' : 'button';

$classes = clsx('ui-button', 'ui-button--' . $variant, 'ui-button--' . $size, [
	'ui-button--block' => (bool) $block,
	'ui-button--icon-only' => (bool) $iconOnly,
	'is-loading' => (bool) $loading,
	'is-disabled' => (bool) $disabled,
]);

$attributes = $attrs;

if ($isLink) {
	$attributes['href'] = (string) $href;
} else {
	$attributes['type'] = (string) $type;
}

if ($disabled) {
	$attributes['disabled'] = true;

	if ($tag === 'a') {
		$attributes['aria-disabled'] = 'true';
		$attributes['tabindex'] = '-1';
	}
}

if ($loading) {
	$attributes['aria-busy'] = 'true';
	$attributes['disabled'] = true;
}

/* У кнопки-иконки нет видимого текста — имя обязательно, иначе она безымянная. */
if ($iconOnly) {
	$attributes['aria-label'] = (string) $label;
}

$iconSize = $size === 'sm' ? 16 : ($size === 'lg' ? 20 : 18);
?>
<<?= $tag ?><?= attrs($attributes, ['class' => $classes]) ?>>
	<?php if ($loading): ?>
		<?= $this->insert('bolt://ui-kit/icon', ['name' => 'spinner', 'size' => $iconSize, 'class' => 'ui-button__spinner']) ?>
	<?php elseif ($icon !== null): ?>
		<?= $this->insert('bolt://ui-kit/icon', ['name' => $icon, 'size' => $iconSize, 'class' => 'ui-button__icon']) ?>
	<?php endif; ?>

	<?php if (!$iconOnly): ?>
		<?php if ($children !== null): ?>
			<?php render($children); ?>
		<?php else: ?>
			<span class="ui-button__label"><?= $this->e((string) $label) ?></span>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($iconRight !== null && !$iconOnly): ?>
		<?= $this->insert('bolt://ui-kit/icon', ['name' => $iconRight, 'size' => $iconSize, 'class' => 'ui-button__icon']) ?>
	<?php endif; ?>
</<?= $tag ?>>

<style lang="scss">
	.ui-button {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: var(--space-2);
		min-height: 2.5rem;
		padding: var(--space-2) var(--space-4);
		border: 1px solid transparent;
		border-radius: var(--radius-md);
		background: var(--color-brand);
		color: var(--color-on-brand);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		font-weight: var(--weight-medium);
		line-height: var(--leading-snug);
		text-decoration: none;
		cursor: pointer;
		transition:
			background-color 120ms ease,
			border-color 120ms ease,
			color 120ms ease;
	}

	.ui-button:hover {
		background: var(--color-brand-hover);
		color: var(--color-on-brand);
	}

	.ui-button:active {
		transform: translateY(1px);
	}

	.ui-button:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}

	.ui-button--sm {
		min-height: 2rem;
		padding: var(--space-1) var(--space-3);
		font-size: var(--text-xs);
	}

	.ui-button--lg {
		min-height: 3rem;
		padding: var(--space-3) var(--space-6);
		font-size: var(--text-base);
	}

	/* Вторичная: та же форма, тише цвет — для второго действия в паре. */
	.ui-button--secondary {
		border-color: var(--color-border-strong);
		background: var(--color-surface);
		color: var(--color-text);
	}

	.ui-button--secondary:hover {
		border-color: var(--color-brand);
		background: var(--color-brand-subtle);
		color: var(--color-brand-text);
	}

	.ui-button--outline {
		border-color: var(--color-brand);
		background: transparent;
		color: var(--color-brand);
	}

	.ui-button--outline:hover {
		background: var(--color-brand-subtle);
		color: var(--color-brand-text);
	}

	/* Ghost: действие в плотном окружении — таблице, тулбаре, карточке. */
	.ui-button--ghost {
		background: transparent;
		color: var(--color-text-muted);
	}

	.ui-button--ghost:hover {
		background: var(--color-surface-alt);
		color: var(--color-text);
	}

	.ui-button--danger {
		background: var(--color-danger);
		color: #fff;
	}

	.ui-button--danger:hover {
		background: color-mix(in oklch, var(--color-danger) 85%, #000);
		color: #fff;
	}

	.ui-button--link {
		min-height: auto;
		padding: 0;
		background: none;
		color: var(--color-brand);
		text-decoration: underline;
		text-underline-offset: 0.18em;
	}

	.ui-button--link:hover {
		background: none;
		color: var(--color-brand-hover);
		text-decoration-thickness: 2px;
	}

	.ui-button--block {
		width: 100%;
	}

	.ui-button--icon-only {
		padding: var(--space-2);
		aspect-ratio: 1;
	}

	.ui-button__icon,
	.ui-button__spinner {
		flex: none;
	}

	.ui-button__spinner {
		animation: ui-button-spin 900ms linear infinite;
	}

	.ui-button.is-loading {
		cursor: progress;
	}

	.ui-button.is-disabled,
	.ui-button:disabled {
		opacity: 0.55;
		cursor: not-allowed;
		transform: none;
	}

	.ui-button.is-disabled:hover,
	.ui-button:disabled:hover {
		background: var(--color-brand);
		color: var(--color-on-brand);
	}

	.ui-button--secondary.is-disabled:hover,
	.ui-button--secondary:disabled:hover {
		background: var(--color-surface);
		color: var(--color-text);
	}

	@keyframes ui-button-spin {
		to {
			transform: rotate(360deg);
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.ui-button__spinner {
			animation-duration: 3s;
		}
	}
</style>
