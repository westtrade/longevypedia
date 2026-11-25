<?php

declare(strict_types=1);

/**
 * Переключатель темы.
 *
 * Проектный компонент: `components::ui/theme-toggle`.
 *
 * Кнопка собрана из `components::ui/button` (ghost, только значок), поэтому
 * размер, фокус-кольцо и состояния наведения у неё те же, что у остальных
 * кнопок набора. Своей вёрстки у компонента нет — только два значка и правило,
 * какой из них виден.
 *
 * Тема живёт тремя состояниями (`auto`, `light`, `dark`), но значок показывает
 * не состояние, а следующий шаг: в светлой теме — луна, в тёмной — солнце.
 * Пока тема следует за системной (`auto`), решает `prefers-color-scheme`.
 * Скрипт `app/client/theme.ts` переключает `data-theme` на `<html>` и пишет
 * `data-theme-state` на кнопку; цвета выбирает CSS, поэтому здесь нет ни
 * одного литерального цвета.
 *
 * @param string $label доступное имя кнопки; скрипт уточняет его текущим состоянием
 * @param array  $attrs дополнительные атрибуты кнопки
 */

$label ??= 'Change colour theme';
$attrs ??= [];

$iconSize = 18;

$attributes = $attrs;
$attributes['class'] = clsx('ui-theme-toggle', $attrs['class'] ?? null);
$attributes['data-theme-toggle'] = true;
?>
<?= $this->insert('components::ui/button', [
	'label' => $label,
	'variant' => 'ghost',
	'size' => 'md',
	'iconOnly' => true,
	'attrs' => $attributes,
	'children' => function () use ($iconSize): void {
		?>
		<span class="ui-theme-toggle__icon ui-theme-toggle__icon--sun"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'sun', 'size' => $iconSize]) ?></span>
		<span class="ui-theme-toggle__icon ui-theme-toggle__icon--moon"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'moon', 'size' => $iconSize]) ?></span>
		<?php
	},
]) ?>

<style lang="scss">
	/* Значок — часть кнопки, поэтому размеры и цвет наследуются от неё:
	   кнопка сама задаёт `color`, а значки рисуются `currentColor`. */
	.ui-theme-toggle__icon {
		display: inline-flex;
	}

	/* По умолчанию видна луна: следующий шаг из светлой темы — тёмная. */
	.ui-theme-toggle__icon--sun {
		display: none;
	}

	/* Системная тёмная тема, пока человек не выбрал вручную. */
	@media (prefers-color-scheme: dark) {
		:root:not([data-theme]) .ui-theme-toggle__icon--sun {
			display: inline-flex;
		}

		:root:not([data-theme]) .ui-theme-toggle__icon--moon {
			display: none;
		}
	}

	:root[data-theme='dark'] .ui-theme-toggle__icon--sun {
		display: inline-flex;
	}

	:root[data-theme='dark'] .ui-theme-toggle__icon--moon {
		display: none;
	}
</style>
