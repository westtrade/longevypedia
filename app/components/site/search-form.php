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
 * @var string $id      id поля ввода (уникальный на странице)
 * @var string $value   текущий запрос
 * @var bool   $compact компактный вариант для шапки
 */

$id ??= 'search';
$value ??= '';
$compact ??= false;

$suggestId = $id . '-suggest';
?>
<form class="search<?= $compact ? ' search--compact' : '' ?>" action="/search" method="get" role="search">
	<div class="search__field">
		<label class="visually-hidden" for="<?= $this->e($id) ?>">Search Longevypedia</label>
		<input
			class="search__input"
			id="<?= $this->e($id) ?>"
			type="search"
			name="q"
			value="<?= $this->e($value) ?>"
			placeholder="Search genes, mechanisms, studies…"
			autocomplete="off"
			hx-get="/search?_fragments=search-results"
			hx-trigger="input changed delay:250ms"
			hx-target="#<?= $this->e($suggestId) ?>"
			hx-swap="innerHTML"
			aria-describedby="<?= $this->e($suggestId) ?>-status"
			data-search-input
		>
		<button class="search__submit" type="submit" aria-label="Search">
			<?= $this->insert('bolt://ui-kit/icon', ['name' => 'search', 'size' => 18]) ?>
		</button>
	</div>

	<p class="visually-hidden" id="<?= $this->e($suggestId) ?>-status" aria-live="polite">Suggestions update as you type.</p>
	<div class="search__suggest" id="<?= $this->e($suggestId) ?>" data-search-suggest></div>
</form>

<style lang="scss">
	.search {
		position: relative;
		width: 100%;
		max-width: 34rem;
	}

	.search__field {
		display: flex;
		align-items: center;
		gap: 0.25rem;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
		padding-inline: 0.65rem;

		&:focus-within {
			border-color: var(--color-accent);
			box-shadow: 0 0 0 3px var(--color-accent-subtle);
		}
	}

	.search__input {
		flex: 1 1 auto;
		min-width: 0;
		border: 0;
		background: none;
		padding: 0.55rem 0;
		font: inherit;
		color: var(--color-text);
		outline: none;

		&::placeholder {
			color: var(--color-text-subtle);
		}

		&::-webkit-search-cancel-button {
			cursor: pointer;
		}
	}

	.search__submit {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border: 0;
		background: none;
		padding: 0.25rem;
		color: var(--color-text-subtle);
		cursor: pointer;

		&:hover {
			color: var(--color-accent);
		}
	}

	.search--compact {
		max-width: 20rem;
	}

	.search__suggest {
		position: absolute;
		inset-inline: 0;
		top: calc(100% + 0.35rem);
		z-index: 30;
		border-radius: var(--radius-md);
		background: var(--color-surface);
		box-shadow: var(--shadow-md);
		overflow: hidden;

		/* Пустой контейнер не должен ловить клики и висеть рамкой. */
		&:empty {
			display: none;
		}
	}

	@media (max-width: 64rem) {
		.search--compact {
			max-width: 12rem;
		}
	}

	@media (max-width: 48rem) {
		.search--compact {
			max-width: none;
		}
	}
</style>
