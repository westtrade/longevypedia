<?php

declare(strict_types=1);

/**
 * Всплывающее уведомление.
 *
 * Это регион, а не модальное окно: он живёт в углу и не перехватывает фокус.
 * `role="status"` объявляет смену содержимого, поэтому текст обязателен —
 * уведомление без текста скринридер не прочитает.
 *
 * @var string $type success | warning | danger | info
 * @var string $title заголовок
 * @var string|null $text уточнение
 * @var string|null $actionUrl ссылка-действие
 * @var string|null $actionLabel подпись ссылки-действия
 */

$type ??= 'info';
$title ??= '';
$text ??= null;
$actionUrl ??= null;
$actionLabel ??= null;

$roles = ['success' => 'status', 'warning' => 'status', 'danger' => 'alert', 'info' => 'status'];
?>
<div class="ui-toast ui-toast--<?= e($type) ?>" role="<?= e($roles[$type] ?? 'status') ?>">
	<p class="ui-toast__title"><?= e($title) ?></p>
	<?php if ($text !== null): ?>
		<p class="ui-toast__text"><?= e($text) ?></p>
	<?php endif ?>
	<?php if ($actionUrl !== null && $actionLabel !== null): ?>
		<a class="ui-toast__action" href="<?= e($actionUrl) ?>"><?= e($actionLabel) ?></a>
	<?php endif ?>
</div>

<style lang="scss">
	.ui-toast {
		display: flex;
		flex-direction: column;
		gap: var(--space-1);
		max-width: 22rem;
		padding: var(--space-3) var(--space-4);
		border: 1px solid var(--color-border);
		border-left-width: 3px;
		border-radius: var(--radius-md);
		background: var(--color-surface);
		box-shadow: var(--shadow-md);
		font-family: var(--font-ui);
	}

	.ui-toast--success {
		border-left-color: var(--color-success);
	}

	.ui-toast--warning {
		border-left-color: var(--color-warning);
	}

	.ui-toast--danger {
		border-left-color: var(--color-danger);
	}

	.ui-toast--info {
		border-left-color: var(--color-info);
	}

	.ui-toast__title {
		margin: 0;
		color: var(--color-text);
		font-size: var(--text-sm);
		font-weight: var(--weight-semibold);
	}

	.ui-toast__text {
		margin: 0;
		color: var(--color-text-muted);
		font-size: var(--text-xs);
		line-height: var(--leading-normal);
	}

	.ui-toast__action {
		color: var(--color-brand-text);
		font-size: var(--text-xs);
		text-decoration: underline;
		text-underline-offset: 0.18em;
	}
</style>
