<?php

declare(strict_types=1);

/**
 * Таблица данных.
 *
 * Значения ячеек приходят уже готовой разметкой: внутри ячейки может стоять
 * ссылка, показатель или бейдж, а экранирование делает вызывающий шаблон.
 * Поэтому `cells` выводятся как есть — это осознанный контракт, а не пропуск
 * экранирования.
 *
 * Сортировка серверная: заголовок становится ссылкой с `sort` и `direction`,
 * состояние приходит из адреса, а не хранится в компоненте.
 *
 * @var string $caption подпись таблицы, читается скринридером
 * @var array<int, array{label: string, key: string, align?: string, numeric?: bool, sortable?: bool, rowHeader?: bool}> $columns
 * @var array<int, array{cells: array<string, string>, href?: string|null, highlight?: bool}> $rows
 * @var string $density normal | compact
 * @var bool $sticky закрепить строку заголовков
 * @var array{key?: string, direction?: string}|null $sort текущая сортировка
 * @var callable(string, string): string|null $sortUrl адрес сортировки по колонке
 * @var mixed $empty состояние «нет строк»
 */

$caption ??= '';
$columns ??= [];
$rows ??= [];
$density ??= 'normal';
$sticky ??= false;
$sort ??= null;
$sortUrl ??= null;
$empty ??= null;
?>
<div class="ui-table<?= $density === 'compact' ? ' ui-table--compact' : '' ?><?= $sticky ? ' ui-table--sticky' : '' ?>">
	<table class="ui-table__table">
		<?php if ($caption !== ''): ?>
			<caption class="ui-table__caption"><?= e($caption) ?></caption>
		<?php endif ?>
		<thead class="ui-table__head">
			<tr>
				<?php foreach ($columns as $column): ?>
					<?php
					$key = (string) ($column['key'] ?? '');
					$align = (string) ($column['align'] ?? 'start');
					$numeric = (bool) ($column['numeric'] ?? false);
					$sortable = (bool) ($column['sortable'] ?? false) && is_callable($sortUrl);
					$isSorted = ($sort['key'] ?? null) === $key;
					$direction = $isSorted ? (string) ($sort['direction'] ?? 'asc') : 'asc';
					$next = $isSorted && $direction === 'asc' ? 'desc' : 'asc';
					?>
					<th scope="col"
						class="ui-table__th ui-table__th--<?= e($align) ?><?= $numeric ? ' ui-table__th--numeric' : '' ?>"
						<?= $sortable ? 'aria-sort="' . ($isSorted ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none') . '"' : '' ?>>
						<?php if ($sortable): ?>
							<a class="ui-table__sort" href="<?= e((string) $sortUrl($key, $next)) ?>">
								<?= e((string) ($column['label'] ?? $key)) ?>
								<span class="ui-table__sort-icon" aria-hidden="true"><?= $isSorted ? ($direction === 'asc' ? '↑' : '↓') : '↕' ?></span>
							</a>
						<?php else: ?>
							<?= e((string) ($column['label'] ?? $key)) ?>
						<?php endif ?>
					</th>
				<?php endforeach ?>
			</tr>
		</thead>
		<tbody class="ui-table__body">
			<?php if ($rows === []): ?>
				<tr>
					<td class="ui-table__empty" colspan="<?= max(1, count($columns)) ?>">
						<?php render($empty); ?>
					</td>
				</tr>
			<?php else: ?>
				<?php foreach ($rows as $row): ?>
					<tr class="ui-table__row<?= (bool) ($row['highlight'] ?? false) ? ' ui-table__row--highlight' : '' ?>">
						<?php foreach ($columns as $column): ?>
							<?php
							$key = (string) ($column['key'] ?? '');
							$align = (string) ($column['align'] ?? 'start');
							$numeric = (bool) ($column['numeric'] ?? false);
							// Колонка-заголовок строки: у таблицы «уровень → значение»
							// первая ячейка называет строку, и скринридер должен читать
							// её как заголовок, а не как ещё одно значение.
							$isRowHeader = (bool) ($column['rowHeader'] ?? false);
							$cell = $isRowHeader ? 'th' : 'td';
							$cellClass = clsx(
								'ui-table__td',
								'ui-table__td--' . $align,
								['ui-table__td--row-header' => $isRowHeader, 'ui-table__td--numeric' => $numeric],
							);
							?>
							<<?= $cell ?><?= $isRowHeader ? ' scope="row"' : '' ?> class="<?= e($cellClass) ?>">
								<?= (string) ($row['cells'][$key] ?? '') ?>
							</<?= $cell ?>>
						<?php endforeach ?>
					</tr>
				<?php endforeach ?>
			<?php endif ?>
		</tbody>
	</table>
</div>

<style lang="scss">
	.ui-table {
		max-width: 100%;
		overflow-x: auto;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.ui-table__table {
		width: 100%;
		border-collapse: collapse;
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		line-height: var(--leading-table);
	}

	.ui-table__caption {
		padding: var(--space-3) var(--space-4);
		border-bottom: 1px solid var(--color-border);
		color: var(--color-text-muted);
		font-size: var(--text-xs);
		text-align: left;
	}

	.ui-table__th {
		padding: var(--space-2) var(--space-4);
		border-bottom: 1px solid var(--color-border-strong);
		background: var(--color-surface-alt);
		color: var(--color-text-muted);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-align: left;
		text-transform: uppercase;
		white-space: nowrap;
	}

	.ui-table--sticky .ui-table__th {
		position: sticky;
		top: 0;
		z-index: 1;
	}

	/* Прилипание работает только внутри своей прокручиваемой области:
	   `overflow-x: auto` у обёртки уже делает её scroll-контейнером, и
	   относительно окна заголовок в ней не прилипает. Поэтому у варианта
	   `sticky` ограничена высота — таблица прокручивается сама, а заголовок
	   остаётся на виду. `border-collapse: separate` нужен из-за Chrome: с
	   `collapse` нижняя граница прилипшей ячейки уезжает вместе со строкой. */
	.ui-table--sticky {
		max-height: min(80vh, 44rem);
		overflow: auto;
		overscroll-behavior: contain;
	}

	.ui-table--sticky .ui-table__table {
		border-collapse: separate;
		border-spacing: 0;
	}

	.ui-table__td {
		padding: var(--space-3) var(--space-4);
		border-bottom: 1px solid var(--color-border);
		color: var(--color-text);
		vertical-align: top;
	}

	/* Ячейка-заголовок строки: вес обычного текста, а не жирный — иначе
	   таблица «уровень → значение» читается как две колонки равного веса. */
	.ui-table__td--row-header {
		font-weight: var(--weight-medium);
		color: var(--color-text);
	}

	.ui-table__row:last-child .ui-table__td {
		border-bottom: 0;
	}

	.ui-table__row:hover .ui-table__td {
		background: var(--color-surface-alt);
	}

	.ui-table__row--highlight .ui-table__td {
		background: var(--color-brand-subtle);
	}

	.ui-table--compact .ui-table__th,
	.ui-table--compact .ui-table__td {
		padding: var(--space-1) var(--space-3);
	}

	/* Ячейка-заголовок строки — это `<th>`, а у него браузерное выравнивание по
	   центру: без явного `start` колонка «уровень» уезжает к середине. */
	.ui-table__th--start,
	.ui-table__td--start {
		text-align: start;
	}

	.ui-table__th--numeric,
	.ui-table__td--numeric {
		text-align: right;
		font-variant-numeric: tabular-nums;
	}

	.ui-table__th--center,
	.ui-table__td--center {
		text-align: center;
	}

	.ui-table__sort {
		display: inline-flex;
		align-items: center;
		gap: var(--space-1);
		color: inherit;
		text-decoration: none;
	}

	.ui-table__sort:hover {
		color: var(--color-brand-text);
		text-decoration: underline;
	}

	.ui-table__sort:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}

	.ui-table__sort-icon {
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
	}

	.ui-table__empty {
		padding: var(--space-8) var(--space-4);
		color: var(--color-text-muted);
		text-align: center;
	}
</style>
