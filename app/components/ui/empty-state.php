<?php

declare(strict_types=1);

/**
 * Пустое состояние: объясняет, почему ничего нет, и что с этим делать.
 *
 * Пустой экран без объяснения читается как ошибка, поэтому `text` описывает
 * причину, а `action` — следующий шаг.
 *
 * @var string $title заголовок
 * @var string|null $text объяснение
 * @var string|null $icon имя иконки
 * @var mixed $action кнопка или ссылка
 */

$title ??= 'Nothing found';
$text ??= null;
$icon ??= null;
$action ??= null;
?>
<div class="ui-empty">
	<?php if ($icon !== null): ?>
		<span class="ui-empty__icon">
			<?= $this->insert('bolt://ui-kit/icon', ['name' => $icon, 'size' => 28]) ?>
		</span>
	<?php endif ?>
	<p class="ui-empty__title"><?= e($title) ?></p>
	<?php if ($text !== null): ?>
		<p class="ui-empty__text"><?= e($text) ?></p>
	<?php endif ?>
	<?php if ($action !== null): ?>
		<div class="ui-empty__action"><?php render($action); ?></div>
	<?php endif ?>
</div>

<style lang="scss">
	.ui-empty {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: var(--space-2);
		padding: var(--space-10) var(--space-6);
		border: 1px dashed var(--color-border);
		border-radius: var(--radius-md);
		text-align: center;
	}

	.ui-empty__icon {
		display: inline-flex;
		color: var(--color-text-subtle);
	}

	.ui-empty__title {
		margin: 0;
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-base);
		font-weight: var(--weight-semibold);
	}

	.ui-empty__text {
		max-width: 32rem;
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.ui-empty__action {
		margin-top: var(--space-2);
	}
</style>
