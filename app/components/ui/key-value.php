<?php

declare(strict_types=1);

/**
 * Таблица «поле — значение»: карточка препарата, паспорт гена, параметры
 * исследования. Это не данные для сравнения по строкам, поэтому список
 * определений, а не `<table>`.
 *
 * Подпись называет поле, а не спорит с содержимым: она идёт капителью, мелким
 * кеглем и в узкой колонке. Прежняя колонка `minmax(8rem, 14rem)` раздувалась
 * до 224px, хотя самая длинная подпись занимает около 90px, — между подписью и
 * значением оставалась пустая полоса, и значение начиналось на трети карточки.
 *
 * `code` печатает машинный код рядом с переводом (`.ui-kv__code`): он нужен
 * тому, кто сверяет данные с API или фильтром, и не должен читаться как часть
 * названия — поэтому моноширинный, мелкий и приглушённый.
 *
 * `wide` выводит поле на всю ширину в двухколоночном режиме. Короткие поля
 * («Species», «Duration») встают парами, прозаические («Population»,
 * «Intervention») занимают строку целиком: иначе значение сжимается до трети
 * карточки и рвётся на пять строк.
 *
 * `groups` делит паспорт на смысловые части — «кто и как», «что делали».
 * Заголовок группы печатается `<h3>`, поэтому группы используют внутри
 * раздела, у которого уже есть свой `<h2>`.
 *
 * @var string|null $title заголовок блока
 * @var array<int, array{label: string, value: string, hint?: string|null, code?: string|null, wide?: bool}> $items значения — готовая разметка
 * @var array<int, array{title?: string|null, items: array<int, array{label: string, value: string, hint?: string|null, code?: string|null, wide?: bool}>}> $groups смысловые группы полей
 * @var int $columns сколько колонок на широком экране: 1 или 2
 * @var string $density normal | compact
 * @var bool $dividers рисовать линии между строками
 */

$title ??= null;
$items ??= [];
$groups ??= [];
$columns ??= 1;
$density ??= 'normal';
$dividers ??= true;

$sections = $groups !== []
	? $groups
	: ($items !== [] ? [['title' => null, 'items' => $items]] : []);

$sections = array_values(array_filter(
	$sections,
	static fn (array $section): bool => (array) ($section['items'] ?? []) !== [],
));

if ($sections === []) {
	return;
}

$rootClass = clsx('ui-kv', [
	'ui-kv--compact' => $density === 'compact',
	'ui-kv--plain' => !$dividers,
	'ui-kv--columns' => (int) $columns > 1,
]);
?>
<section class="<?= e($rootClass) ?>">
	<?php if ($title !== null): ?>
		<h2 class="ui-kv__title"><?= e($title) ?></h2>
	<?php endif ?>
	<?php foreach ($sections as $section): ?>
		<div class="ui-kv__group">
			<?php if (($section['title'] ?? null) !== null): ?>
				<h3 class="ui-kv__group-title"><?= e((string) $section['title']) ?></h3>
			<?php endif ?>
			<dl class="ui-kv__list">
				<?php foreach ((array) $section['items'] as $item): ?>
					<?php
					$code = (string) ($item['code'] ?? '');
					$wide = (bool) ($item['wide'] ?? false);
					?>
					<div class="ui-kv__row<?= $wide ? ' ui-kv__row--wide' : '' ?>">
						<dt class="ui-kv__label"><?= e((string) ($item['label'] ?? '')) ?></dt>
						<dd class="ui-kv__value">
							<?= (string) ($item['value'] ?? '') ?>
							<?php if ($code !== ''): ?>
								<code class="ui-kv__code" data-code="<?= e($code) ?>"><?= e($code) ?></code>
							<?php endif ?>
							<?php if (($item['hint'] ?? null) !== null): ?>
								<span class="ui-kv__hint"><?= e((string) $item['hint']) ?></span>
							<?php endif ?>
						</dd>
					</div>
				<?php endforeach ?>
			</dl>
		</div>
	<?php endforeach ?>
</section>

<style lang="scss">
	.ui-kv {
		display: flex;
		flex-direction: column;
		gap: var(--space-4);
	}

	.ui-kv__title {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-kv__group {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
	}

	/* Заголовок группы — та же капитель, что у подписей полей: он делит
	   паспорт на части, не споря с заголовком блока. */
	.ui-kv__group-title {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-kv__list {
		margin: 0;
		border-top: 1px solid var(--color-border);
	}

	.ui-kv__row {
		display: grid;
		grid-template-columns: minmax(6rem, 8rem) minmax(0, 1fr);
		gap: var(--space-5);
		align-items: baseline;
		padding-block: var(--space-2);
		border-bottom: 1px solid var(--color-border);
	}

	.ui-kv__label {
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-medium);
		letter-spacing: 0.07em;
		text-transform: uppercase;
	}

	.ui-kv__value {
		margin: 0;
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		font-variant-numeric: tabular-nums;
		line-height: var(--leading-relaxed);
	}

	/* Машинный код — не отдельный элемент, а сноска к значению: глобальный
	   стиль `code` рисует плашку с рамкой, и рядом с тегами страницы такая
	   плашка читалась бы как ещё один бейдж. Здесь плашка снимается. */
	.ui-kv__code {
		margin-inline-start: var(--space-2);
		padding: 0;
		border: 0;
		background: none;
		color: var(--color-text-subtle);
		font-family: var(--font-mono);
		font-size: var(--text-xs);
		white-space: nowrap;
	}

	.ui-kv__hint {
		display: block;
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
	}

	.ui-kv--compact .ui-kv__row {
		padding-block: var(--space-1);
	}

	/* Две колонки: короткие поля встают парами, `wide` занимает строку целиком.
	   Ряды тянутся по высоте ряда сетки, поэтому линии под соседями совпадают
	   даже когда одно из значений переносится на вторую строку. */
	.ui-kv--columns .ui-kv__list {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		column-gap: var(--space-8);
	}

	.ui-kv--columns .ui-kv__row--wide {
		grid-column: 1 / -1;
	}

	/* Без линий: блок стоит внутри карточки, у которой уже есть рамка. Две
	   горизонтальные линии сверху и снизу читались как вторая рамка вокруг
	   четырёх строк — вместо «поле: значение» получалась таблица в таблице.
	   Ряды разделяет отступ, а не граница. */
	.ui-kv--plain .ui-kv__list {
		border-top: 0;
	}

	.ui-kv--plain .ui-kv__row {
		border-bottom: 0;
	}

	/* Колонки разваливаются раньше, чем карточка: на планшете пара «подпись —
	   значение» уже не влезает в половину ширины. */
	@media (max-width: $bp-md) {
		.ui-kv--columns .ui-kv__list {
			grid-template-columns: minmax(0, 1fr);
		}

		.ui-kv--columns .ui-kv__row--wide {
			grid-column: auto;
		}
	}

	@media (max-width: $bp-sm) {
		.ui-kv__row {
			grid-template-columns: minmax(0, 1fr);
			gap: 2px;
		}
	}
</style>
