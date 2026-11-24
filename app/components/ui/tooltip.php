<?php

declare(strict_types=1);

/**
 * Подсказка к сокращению или незнакомому термину.
 *
 * Текст подсказки — часть содержимого, а не украшение: он читается
 * скринридером и доступен по фокусу, а не только по наведению.
 *
 * @var string $text текст подсказки
 * @var string|null $label видимый текст; если не задан — текст подсказки
 */

$text ??= '';
$label ??= null;

if ($text === '') {
	return;
}
?>
<span class="ui-tooltip" tabindex="0" data-tooltip="<?= e($text) ?>"><?= e($label ?? $text) ?></span>

<style lang="scss">
	.ui-tooltip {
		position: relative;
		border-bottom: 1px dotted var(--color-text-subtle);
		cursor: help;
	}

	.ui-tooltip::after {
		content: attr(data-tooltip);
		position: absolute;
		z-index: 30;
		bottom: calc(100% + var(--space-2));
		left: 50%;
		width: max-content;
		max-width: 18rem;
		padding: var(--space-2) var(--space-3);
		border-radius: var(--radius-sm);
		background: var(--color-text);
		color: var(--color-bg);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-style: normal;
		font-weight: var(--weight-regular);
		line-height: var(--leading-normal);
		text-align: left;
		transform: translateX(-50%);
		opacity: 0;
		visibility: hidden;
		transition: opacity 120ms ease;
	}

	.ui-tooltip:hover::after,
	.ui-tooltip:focus-visible::after {
		opacity: 1;
		visibility: visible;
	}

	.ui-tooltip:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
		border-radius: var(--radius-sm);
	}

	@media (prefers-reduced-motion: reduce) {
		.ui-tooltip::after {
			transition: none;
		}
	}
</style>
