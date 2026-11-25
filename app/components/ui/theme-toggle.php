<?php

declare(strict_types=1);

/**
 * Переключатель темы.
 *
 * Проектный компонент: `components::ui/theme-toggle`.
 *
 * Тема имеет три состояния, как в системных настройках: `auto` (по системе),
 * `light`, `dark`. Кнопка-значок их не показывает: по луне нельзя понять,
 * выбрана тёмная тема или она просто следует за системой. Поэтому здесь
 * раскрывающийся список с тремя пунктами, и текущий отмечен галочкой —
 * состояние видно, а не угадывается.
 *
 * `<details>` вместо кнопки: раскрытие работает без JavaScript, а скрипт
 * `app/client/theme.ts` только меняет `data-theme` на `<html>` и отмечает
 * выбранный пункт. Триггер — `<summary>` с классами `ui-button`, а не
 * `components::ui/button`: кнопка набора рендерит `<button>`, а открыть
 * `<details>` умеет только `<summary>`. Классы те же, поэтому размер, фокус и
 * наведение совпадают с соседними кнопками.
 *
 * @param string $label доступное имя группы состояний
 * @param array  $attrs дополнительные атрибуты контейнера
 */

$label ??= 'Colour theme';
$attrs ??= [];

$options = [
	['value' => 'auto', 'label' => 'System', 'icon' => 'contrast'],
	['value' => 'light', 'label' => 'Light', 'icon' => 'sun'],
	['value' => 'dark', 'label' => 'Dark', 'icon' => 'moon'],
];

$attributes = $attrs;
$attributes['class'] = clsx('ui-theme-toggle', $attrs['class'] ?? null);
$attributes['data-theme-switch'] = true;
?>
<details<?= attrs($attributes) ?>>
	<summary class="ui-button ui-button--ghost ui-button--md ui-button--icon-only ui-theme-toggle__summary"
		aria-label="<?= e($label) ?>">
		<span class="ui-theme-toggle__state" aria-hidden="true">
			<span class="ui-theme-toggle__icon" data-theme-icon="auto"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'contrast', 'size' => 18]) ?></span>
			<span class="ui-theme-toggle__icon" data-theme-icon="light"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'sun', 'size' => 18]) ?></span>
			<span class="ui-theme-toggle__icon" data-theme-icon="dark"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'moon', 'size' => 18]) ?></span>
		</span>
	</summary>

	<div class="ui-theme-toggle__panel" role="group" aria-label="<?= e($label) ?>">
		<?php foreach ($options as $option): ?>
			<button class="ui-theme-toggle__option" type="button"
				data-theme-choice="<?= e($option['value']) ?>"
				<?php /* Без скрипта тема — системная, поэтому `auto` и отмечен. */ ?>
				aria-pressed="<?= $option['value'] === 'auto' ? 'true' : 'false' ?>">
				<span class="ui-theme-toggle__option-icon" aria-hidden="true"><?= $this->insert('bolt://ui-kit/icon', ['name' => $option['icon'], 'size' => 16]) ?></span>
				<span class="ui-theme-toggle__option-label"><?= e($option['label']) ?></span>
				<span class="ui-theme-toggle__option-mark" aria-hidden="true"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'check', 'size' => 16]) ?></span>
			</button>
		<?php endforeach ?>
	</div>
</details>

<style lang="scss">
	.ui-theme-toggle {
		position: relative;
		font-family: var(--font-ui);
	}

	.ui-theme-toggle__summary {
		list-style: none;
		cursor: pointer;
	}

	.ui-theme-toggle__summary::-webkit-details-marker {
		display: none;
	}

	.ui-theme-toggle__state {
		display: inline-flex;
	}

	/* Значок в триггере показывает ТЕКУЩЕЕ состояние, а не следующий шаг:
	   рядом с пунктами списка «следующий шаг» читался бы как ещё одно
	   состояние. Какой из трёх значков виден, решает `data-theme-state`. */
	.ui-theme-toggle__icon {
		display: none;
	}

	.ui-theme-toggle[data-theme-state='auto'] .ui-theme-toggle__icon[data-theme-icon='auto'],
	.ui-theme-toggle[data-theme-state='light'] .ui-theme-toggle__icon[data-theme-icon='light'],
	.ui-theme-toggle[data-theme-state='dark'] .ui-theme-toggle__icon[data-theme-icon='dark'] {
		display: inline-flex;
	}

	.ui-theme-toggle__panel {
		position: absolute;
		inset-block-start: calc(100% + var(--space-2));
		inset-inline-end: 0;
		z-index: 30;
		display: flex;
		flex-direction: column;
		min-width: 11rem;
		padding: var(--space-1);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
		box-shadow: var(--shadow-md);
	}

	.ui-theme-toggle__option {
		display: flex;
		align-items: center;
		gap: var(--space-2);
		width: 100%;
		padding: var(--space-2);
		border: 0;
		border-radius: var(--radius-sm);
		background: none;
		color: var(--color-text);
		font-family: inherit;
		font-size: var(--text-sm);
		text-align: start;
		cursor: pointer;

		&:hover {
			background: var(--color-surface-alt);
		}

		&:focus-visible {
			outline: none;
			box-shadow: var(--focus-ring);
		}
	}

	.ui-theme-toggle__option-icon {
		display: inline-flex;
		flex: none;
		color: var(--color-text-muted);
	}

	.ui-theme-toggle__option-label {
		flex: 1 1 auto;
	}

	/* Галочка стоит у выбранного пункта: цвет и жирность дублируют её, но
	   состояние читается и без цвета. */
	.ui-theme-toggle__option-mark {
		display: none;
		flex: none;
		color: var(--color-brand-text);
	}

	.ui-theme-toggle__option[aria-pressed='true'] {
		color: var(--color-brand-text);
		font-weight: var(--weight-semibold);

		.ui-theme-toggle__option-mark {
			display: inline-flex;
		}

		.ui-theme-toggle__option-icon {
			color: var(--color-brand-text);
		}
	}
</style>
