<?php

declare(strict_types=1);

/**
 * Форма поиска.
 *
 * Без JavaScript это обычный GET на `/search`. С HTMX тот же адрес с
 * `_fragments=search-results` отдаёт только список подсказок: ядро BOLT
 * распознаёт HTMX-запрос и не рендерит страницу целиком.
 *
 * `hx-get` и `action` указывают на один и тот же адрес — это требование
 * контракта фрагментов BOLT: без JavaScript и с ним сервер получает один путь.
 *
 * Поле и кнопка берутся из набора (`components::ui/field`, `components::ui/button`),
 * а не рисуются заново: рамка, фокусное кольцо и высота контрола живут в одном
 * месте. Здесь остаётся только то, чего у набора нет, — HTMX-подсказки,
 * живой регион для скринридера и панель результатов.
 *
 * @var string $id      id поля ввода (уникальный на странице)
 * @var string $value   текущий запрос
 * @var bool   $compact компактный вариант для шапки
 */

$id ??= 'search';
$value ??= '';
$compact ??= false;

$suggestId = $id . '-suggest';
$statusId = $suggestId . '-status';
$template = $this;
?>
<form class="search<?= $compact ? ' search--compact' : '' ?>" action="/search" method="get" role="search">
	<?= $template->insert('components::ui/field', [
		'name' => 'q',
		'id' => $id,
		'label' => 'Search Longevypedia',
		'labelHidden' => true,
		'type' => 'search',
		'value' => $value,
		'placeholder' => 'Search genes, mechanisms, studies…',
		'describedBy' => [$statusId],
		'attrs' => [
			'autocomplete' => 'off',
			'data-search-input' => true,
			// Тот же адрес, что в action: контракт фрагментов BOLT.
			'hx-get' => '/search?_fragments=search-results',
			'hx-trigger' => 'input changed delay:250ms',
			'hx-target' => '#' . $suggestId,
			'hx-swap' => 'innerHTML',
		],
		// Контрол и кнопка стоят в одной строке, поэтому кнопку рисуем внутри
		// поля: иначе высоты контролов разъезжаются на пиксель.
		'element' => static function (array $params) use ($template, $value): void {
			// Ветку element поле рисует само, поэтому value передаём здесь.
			$control = ['type' => 'search'];
			if ((string) $value !== '') {
				$control['value'] = (string) $value;
			}
			?>
			<input<?= attrs($params['attrs'], $control) ?>>
			<?= $template->insert('components::ui/button', [
				'label' => 'Search',
				'icon' => 'search',
				'iconOnly' => true,
				'size' => 'sm',
				'variant' => 'ghost',
				'type' => 'submit',
				'attrs' => ['class' => 'search__submit'],
			]) ?>
			<?php
		},
	]) ?>

	<p class="visually-hidden" id="<?= e($statusId) ?>" aria-live="polite">Suggestions update as you type.</p>
	<div class="search__suggest" id="<?= e($suggestId) ?>" data-search-suggest></div>
</form>

<style lang="scss">
	.search {
		position: relative;
		width: 100%;
		max-width: 34rem;
	}

	/* Кнопка живёт внутри рамки поля: рамку рисует обёртка контрола, а сам
	   input остаётся без границы. Иначе кнопка висит снаружи и выглядит
	   отдельным элементом. Без min-width: 0 контрол с width: 100%
	   выталкивает кнопку за рамку. */
	.search .ui-field__control {
		gap: var(--space-1);
		padding-inline-end: var(--space-1);
		border: 1px solid var(--color-control-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface);
	}

	.search .ui-field__control:focus-within {
		border-color: var(--color-brand);
		box-shadow: var(--focus-ring);
	}

	.search .ui-field__input {
		flex: 1 1 auto;
		min-width: 0;
		border: 0;
		background: none;
		box-shadow: none;
	}

	/* Кольцо фокуса рисует обёртка: два кольца подряд читаются как ошибка. */
	.search .ui-field__input:focus-visible {
		box-shadow: none;
	}

	.search__submit {
		flex: none;
	}

	.search--compact {
		max-width: 20rem;
		/* Компактная высота — переменной: структура поля от неё не зависит. */
		--ui-field-input-height: 2.25rem;
	}

	.search__suggest {
		position: absolute;
		inset-inline: 0;
		top: calc(100% + 0.35rem);
		z-index: 30;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
		box-shadow: var(--shadow-md);
		overflow: hidden;

		/* Пустой контейнер не должен ловить клики и висеть рамкой. */
		&:empty {
			display: none;
		}
	}

	@media (max-width: $bp-2xl) {
		.search--compact {
			max-width: 12rem;
		}
	}

	@media (max-width: $bp-md) {
		.search--compact {
			max-width: none;
		}
	}
</style>
