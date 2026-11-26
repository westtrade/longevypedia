<?php

declare(strict_types=1);

/**
 * Раскрывающееся меню на `<details>`: без JavaScript и без потери клавиатуры.
 *
 * Раскрывается при наведении (`app/client/hover-menu.ts`), закрывается выбором
 * пункта, Escape или кликом мимо. Без скрипта остаётся обычный `<details>`:
 * клик по подписи открывает и закрывает.
 *
 * @var string $label подпись кнопки
 * @var array<int, array{label: string, url: string, description?: string|null}> $items
 * @var string $align выравнивание панели: start | end
 */

$label ??= 'Menu';
$items ??= [];
$align ??= 'start';

if ($items === []) {
	return;
}
?>
<details class="ui-menu ui-menu--<?= e($align) ?>" data-hover-menu>
	<summary class="ui-menu__summary"><?= e($label) ?></summary>
	<ul class="ui-menu__list">
		<?php foreach ($items as $item): ?>
			<li class="ui-menu__item">
				<a class="ui-menu__link" href="<?= e((string) ($item['url'] ?? '#')) ?>">
					<span class="ui-menu__label"><?= e((string) ($item['label'] ?? '')) ?></span>
					<?php if (($item['description'] ?? null) !== null): ?>
						<span class="ui-menu__description"><?= e((string) $item['description']) ?></span>
					<?php endif ?>
				</a>
			</li>
		<?php endforeach ?>
	</ul>
</details>

<style lang="scss">
	.ui-menu {
		position: relative;
		font-family: var(--font-ui);
	}

	.ui-menu__summary {
		display: inline-flex;
		align-items: center;
		gap: var(--space-2);
		padding: var(--space-2) var(--space-3);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
		color: var(--color-text);
		font-size: var(--text-sm);
		cursor: pointer;
		list-style: none;
	}

	.ui-menu__summary::-webkit-details-marker {
		display: none;
	}

	.ui-menu__summary::after {
		content: '';
		width: 0.45em;
		height: 0.45em;
		border-right: 1.5px solid currentColor;
		border-bottom: 1.5px solid currentColor;
		transform: translateY(-0.1em) rotate(45deg);
	}

	.ui-menu[open] > .ui-menu__summary {
		border-color: var(--color-brand);
		color: var(--color-brand-text);
	}

	.ui-menu__summary:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}

	.ui-menu__list {
		position: absolute;
		z-index: 20;
		top: calc(100% + var(--space-2));
		min-width: 15rem;
		margin: 0;
		padding: var(--space-2);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
		box-shadow: var(--shadow-md);
		list-style: none;
	}

	.ui-menu--start .ui-menu__list {
		left: 0;
	}

	.ui-menu--end .ui-menu__list {
		right: 0;
	}

	.ui-menu__link {
		display: flex;
		flex-direction: column;
		gap: 2px;
		padding: var(--space-2) var(--space-3);
		border-radius: var(--radius-sm);
		color: var(--color-text);
		font-size: var(--text-sm);
		text-decoration: none;
	}

	.ui-menu__link:hover {
		background: var(--color-surface-alt);
	}

	.ui-menu__link:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring-inset);
	}

	.ui-menu__description {
		color: var(--color-text-muted);
		font-size: var(--text-xs);
	}
</style>
