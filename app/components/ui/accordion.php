<?php

declare(strict_types=1);

/**
 * Аккордеон на `<details>`: раскрытие, клавиатура и поиск по странице
 * работают без JavaScript.
 *
 * @var array<int, array{title: string, content: string, open?: bool}> $items
 * @var string $name имя группы: открытие одного пункта закрывает соседние
 */

$items ??= [];
$name ??= 'accordion';

if ($items === []) {
	return;
}
?>
<div class="ui-accordion">
	<?php foreach ($items as $index => $item): ?>
		<details class="ui-accordion__item" <?= (bool) ($item['open'] ?? false) ? 'open' : '' ?>>
			<summary class="ui-accordion__summary"><?= e((string) ($item['title'] ?? '')) ?></summary>
			<div class="ui-accordion__content"><?= $item['content'] ?? '' ?></div>
		</details>
	<?php endforeach ?>
</div>

<style lang="scss">
	.ui-accordion {
		border-top: 1px solid var(--color-border);
	}

	.ui-accordion__item {
		border-bottom: 1px solid var(--color-border);
	}

	.ui-accordion__summary {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: var(--space-4);
		padding: var(--space-3) 0;
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		font-weight: var(--weight-medium);
		cursor: pointer;
		list-style: none;
	}

	.ui-accordion__summary::-webkit-details-marker {
		display: none;
	}

	.ui-accordion__summary::after {
		content: '+';
		flex: none;
		color: var(--color-text-subtle);
		font-family: var(--font-mono);
		font-size: var(--text-base);
	}

	.ui-accordion__item[open] > .ui-accordion__summary::after {
		content: '−';
	}

	.ui-accordion__summary:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
		border-radius: var(--radius-sm);
	}

	.ui-accordion__content {
		padding: 0 0 var(--space-4);
		max-width: var(--layout-measure);
		color: var(--color-text-muted);
		font-family: var(--font-serif);
		line-height: var(--leading-relaxed);
	}
</style>
