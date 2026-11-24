<?php

declare(strict_types=1);

/**
 * Редакционная врезка: определение, методическое замечание, предупреждение.
 *
 * Отличие от `ui/alert`: alert — реакция системы (ошибка формы, результат
 * действия), callout — часть текста, которая остаётся на странице всегда.
 *
 * @var string $kind note | definition | method | caution
 * @var string|null $title заголовок врезки
 * @var mixed $children содержимое
 */

$kind ??= 'note';
$title ??= null;
$children ??= null;

$kinds = [
	'note' => ['label' => 'Note'],
	'definition' => ['label' => 'Definition'],
	'method' => ['label' => 'Method'],
	'caution' => ['label' => 'Caution'],
];

$meta = $kinds[$kind] ?? $kinds['note'];
?>
<aside class="ui-callout ui-callout--<?= e($kind) ?>">
	<p class="ui-callout__title"><?= e($title ?? $meta['label']) ?></p>
	<div class="ui-callout__content"><?php render($children); ?></div>
</aside>

<style lang="scss">
	.ui-callout {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		padding: var(--space-4);
		border: 1px solid var(--color-border);
		border-left-width: 3px;
		border-radius: 0 var(--radius-md) var(--radius-md) 0;
		background: var(--color-surface-alt);
	}

	.ui-callout--note {
		border-left-color: var(--color-info);
	}

	.ui-callout--definition {
		border-left-color: var(--color-brand);
	}

	.ui-callout--method {
		border-left-color: var(--color-accent);
	}

	.ui-callout--caution {
		border-left-color: var(--color-warning);
	}

	.ui-callout__title {
		margin: 0;
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-callout__content {
		max-width: var(--layout-measure);
		color: var(--color-text-muted);
		font-family: var(--font-serif);
		font-size: var(--text-base);
		line-height: var(--leading-relaxed);
	}

	.ui-callout__content > :first-child {
		margin-top: 0;
	}

	.ui-callout__content > :last-child {
		margin-bottom: 0;
	}
</style>
