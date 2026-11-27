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
 * Список плоский: заголовки групп пробовались и оказались лишним этажом в
 * маленькой карточке — «Study», затем «Design and population», затем «DESIGN».
 * Порядок полей задаёт вызывающий, он же решает, что читать первым.
 *
 * `code` печатает машинный код рядом с переводом (`.ui-kv__code`): он нужен
 * тому, кто сверяет данные с API или фильтром, и не должен читаться как часть
 * названия — поэтому моноширинный, мелкий и приглушённый.
 *
 * @var string|null $title заголовок блока
 * @var array<int, array{label: string, value: string, hint?: string|null, code?: string|null}> $items значения — готовая разметка
 * @var string $density normal | compact
 * @var bool $dividers рисовать линии между строками
 */

$title ??= null;
$items ??= [];
$density ??= 'normal';
$dividers ??= true;

if ($items === []) {
	return;
}

$rootClass = clsx('ui-kv', [
	'ui-kv--compact' => $density === 'compact',
	'ui-kv--plain' => !$dividers,
]);
?>
<section class="<?= e($rootClass) ?>">
	<?php if ($title !== null): ?>
		<h2 class="ui-kv__title"><?= e($title) ?></h2>
	<?php endif ?>
	<dl class="ui-kv__list">
		<?php foreach ($items as $item): ?>
			<?php $code = (string) ($item['code'] ?? ''); ?>
			<div class="ui-kv__row">
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

	@media (max-width: $bp-sm) {
		.ui-kv__row {
			grid-template-columns: minmax(0, 1fr);
			gap: 2px;
		}
	}
</style>
