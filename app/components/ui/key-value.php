<?php

declare(strict_types=1);

/**
 * Таблица «поле — значение»: карточка препарата, паспорт гена, параметры
 * исследования. Это не данные для сравнения по строкам, поэтому список
 * определений, а не `<table>`.
 *
 * @var string|null $title заголовок блока
 * @var array<int, array{label: string, value: string, hint?: string|null}> $items значения — готовая разметка
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
?>
<section class="ui-kv<?= $density === 'compact' ? ' ui-kv--compact' : '' ?><?= $dividers ? '' : ' ui-kv--plain' ?>">
	<?php if ($title !== null): ?>
		<h2 class="ui-kv__title"><?= e($title) ?></h2>
	<?php endif ?>
	<dl class="ui-kv__list">
		<?php foreach ($items as $item): ?>
			<div class="ui-kv__row">
				<dt class="ui-kv__label"><?= e((string) ($item['label'] ?? '')) ?></dt>
				<dd class="ui-kv__value">
					<?= (string) ($item['value'] ?? '') ?>
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
		gap: var(--space-3);
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
		grid-template-columns: minmax(8rem, 14rem) minmax(0, 1fr);
		gap: var(--space-4);
		padding: var(--space-2) 0;
		border-bottom: 1px solid var(--color-border);
	}

	.ui-kv__label {
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
	}

	.ui-kv__value {
		margin: 0;
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		font-variant-numeric: tabular-nums;
	}

	.ui-kv__hint {
		display: block;
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
	}

	.ui-kv--compact .ui-kv__row {
		padding: var(--space-1) 0;
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
		padding-block: var(--space-1);
	}

	@media (max-width: $bp-sm) {
		.ui-kv__row {
			grid-template-columns: minmax(0, 1fr);
			gap: 2px;
		}
	}
</style>
