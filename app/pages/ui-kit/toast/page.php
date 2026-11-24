<?php

declare(strict_types=1);

/**
 * UI-набор: Уведомление.
 *
 * Всплывающее сообщение в углу; не перехватывает фокус.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Уведомление — UI-набор',
	'description' => 'Всплывающее сообщение в углу; не перехватывает фокус.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'toast',
	'title' => 'Уведомление',
	'lead' => 'Всплывающее сообщение в углу; не перехватывает фокус.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Виды',
			'note' => 'текст обязателен: уведомление без текста не прочитается',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/toast', ['type' => 'success', 'title' => 'Ссылка скопирована']) ?>
				<?= $this->insert('components::ui/toast', ['type' => 'danger', 'title' => 'Ошибка сети', 'text' => 'Правка не отправлена — попробуйте ещё раз.', 'actionUrl' => '/about', 'actionLabel' => 'Что делать']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/toast', ['type' => 'success', 'title' => 'Ссылка скопирована']) ?>
				<?= $this->insert('components::ui/toast', ['type' => 'danger', 'title' => 'Ошибка сети', 'text' => 'Правка не отправлена — попробуйте ещё раз.', 'actionUrl' => '/about', 'actionLabel' => 'Что делать']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
