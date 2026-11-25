<?php

declare(strict_types=1);

/**
 * Строка метаданных над заголовком документа — рубрика, дата, время чтения.
 *
 * Проектный компонент: `components::ui/meta`. Одна и та же строка стояла на
 * странице документа и на демонстрационной странице двумя копиями, и копии
 * разошлись: `0.45rem` против `var(--space-2)`, `--color-text-subtle` против
 * `--color-text-muted`. Разметка одна и та же, поэтому и стиль должен быть один.
 *
 * @param string|null $eyebrow рубрика капителью: тип документа, «Соединение»
 * @param array       $items   список `['label' => string, 'datetime' => ?string]`;
 *                             разделитель рисует компонент, а не разметка страницы
 * @param array       $attrs
 */

$eyebrow ??= null;
$items ??= [];
$attrs ??= [];

if ($eyebrow === null && $items === []) {
	return;
}
?>
<p<?= attrs($attrs, ['class' => clsx('ui-meta')]) ?>>
	<?php if ($eyebrow !== null && $eyebrow !== ''): ?>
		<span class="ui-meta__eyebrow"><?= $this->e((string) $eyebrow) ?></span>
	<?php endif; ?>
	<?php foreach (array_values($items) as $index => $item): ?>
		<?php if ($index > 0 || ($eyebrow !== null && $eyebrow !== '')): ?>
			<span class="ui-meta__dot" aria-hidden="true">·</span>
		<?php endif; ?>
		<?php if (isset($item['datetime']) && $item['datetime'] !== null): ?>
			<time datetime="<?= $this->e((string) $item['datetime']) ?>"><?= $this->e((string) ($item['label'] ?? '')) ?></time>
		<?php else: ?>
			<span><?= $this->e((string) ($item['label'] ?? '')) ?></span>
		<?php endif; ?>
	<?php endforeach; ?>
</p>

<style lang="scss">
	.ui-meta {
		display: flex;
		flex-wrap: wrap;
		align-items: baseline;
		gap: var(--space-2);
		margin: 0;
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-meta__eyebrow {
		color: var(--color-accent-text);
		font-weight: var(--weight-semibold);
		letter-spacing: 0.08em;
		text-transform: uppercase;
	}

	.ui-meta__dot {
		color: var(--color-border-strong);
	}
</style>
