<?php

declare(strict_types=1);

/**
 * Разделитель секций. С подписью — когда нужно назвать, что начинается ниже.
 *
 * @var string|null $label подпись по центру
 */

$label ??= null;
?>
<?php if ($label === null): ?>
	<hr class="ui-divider">
<?php else: ?>
	<div class="ui-divider ui-divider--labeled" role="separator">
		<span class="ui-divider__label"><?= e($label) ?></span>
	</div>
<?php endif ?>

<style lang="scss">
	.ui-divider {
		margin: var(--space-6) 0;
		border: 0;
		border-top: 1px solid var(--color-border);
	}

	.ui-divider--labeled {
		display: flex;
		align-items: center;
		gap: var(--space-3);
		margin: var(--space-6) 0;
	}

	.ui-divider--labeled::before,
	.ui-divider--labeled::after {
		content: '';
		flex: 1;
		border-top: 1px solid var(--color-border);
	}

	.ui-divider__label {
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}
</style>
