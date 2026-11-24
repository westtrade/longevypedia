<?php

declare(strict_types=1);

/**
 * Сообщение: результат действия, предупреждение, ошибка, пояснение.
 *
 * Проектный компонент: `components::ui/alert`. В ui-kit Bolt есть
 * `form-message`, но он про форму: принимает список ошибок валидации и не умеет
 * информационный тип и произвольные действия. Здесь — общий блок для страницы.
 *
 * @param string      $type     success | warning | danger | info
 * @param string|null $title
 * @param string|null $text
 * @param mixed       $children тело вместо text
 * @param mixed       $actions  кнопки или ссылки
 * @param bool        $dismissible показать кнопку закрытия (нужен JS)
 * @param string      $live     role: status | alert | null
 * @param array       $attrs
 */

$type ??= 'info';
$title ??= null;
$text ??= null;
$children ??= null;
$actions ??= null;
$dismissible ??= false;
$live ??= null;
$attrs ??= [];

$icons = [
	'success' => 'check',
	'warning' => 'alert',
	'danger' => 'alert',
	'info' => 'document',
];

$role = $live !== null ? 'role="' . $this->e((string) $live) . '"' : '';
?>
<div<?= attrs($attrs, ['class' => 'ui-alert ui-alert--' . $type, 'role' => $live]) ?>>
	<span class="ui-alert__icon" aria-hidden="true">
		<?= $this->insert('bolt://ui-kit/icon', ['name' => $icons[$type] ?? 'alert', 'size' => 18]) ?>
	</span>

	<div class="ui-alert__body">
		<?php if ($title !== null): ?>
			<p class="ui-alert__title"><?= $this->e((string) $title) ?></p>
		<?php endif; ?>

		<?php if ($children !== null): ?>
			<div class="ui-alert__text"><?php render($children); ?></div>
		<?php elseif ($text !== null): ?>
			<p class="ui-alert__text"><?= $this->e((string) $text) ?></p>
		<?php endif; ?>

		<?php if ($actions !== null): ?>
			<div class="ui-alert__actions"><?php render($actions); ?></div>
		<?php endif; ?>
	</div>

	<?php if ($dismissible): ?>
		<button class="ui-alert__close" type="button" aria-label="Dismiss" data-dismiss-alert>
			<?= $this->insert('bolt://ui-kit/icon', ['name' => 'close', 'size' => 16]) ?>
		</button>
	<?php endif; ?>
</div>

<style lang="scss">
	.ui-alert {
		display: flex;
		gap: var(--space-3);
		padding: var(--space-4);
		border: 1px solid var(--color-info-border);
		border-inline-start-width: 3px;
		border-radius: var(--radius-md);
		background: var(--color-info-soft);
		color: var(--color-info-text);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.ui-alert__icon {
		display: flex;
		flex: none;
		padding-top: 0.1em;
	}

	.ui-alert__body {
		display: grid;
		gap: var(--space-1);
		min-width: 0;
	}

	.ui-alert__title {
		font-weight: var(--weight-semibold);
	}

	.ui-alert__text {
		color: inherit;
	}

	.ui-alert__text a {
		color: inherit;
	}

	.ui-alert__actions {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		margin-top: var(--space-1);
	}

	.ui-alert__close {
		align-self: flex-start;
		padding: var(--space-1);
		border: 0;
		border-radius: var(--radius-sm);
		background: transparent;
		color: inherit;
		cursor: pointer;
	}

	.ui-alert__close:hover {
		background: color-mix(in oklch, currentColor 12%, transparent);
	}

	.ui-alert--success {
		border-color: var(--color-success-border);
		background: var(--color-success-soft);
		color: var(--color-success-text);
	}

	.ui-alert--warning {
		border-color: var(--color-warning-border);
		background: var(--color-warning-soft);
		color: var(--color-warning-text);
	}

	.ui-alert--danger {
		border-color: var(--color-danger-border);
		background: var(--color-danger-soft);
		color: var(--color-danger-text);
	}
</style>
