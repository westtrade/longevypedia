<?php

declare(strict_types=1);

/**
 * UI-набор: Заглушка загрузки.
 *
 * Показывается, пока HTMX ждёт фрагмент; скрыта от скринридера.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Заглушка загрузки — UI-набор',
	'description' => 'Показывается, пока HTMX ждёт фрагмент; скрыта от скринридера.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'skeleton',
	'title' => 'Заглушка загрузки',
	'lead' => 'Показывается, пока HTMX ждёт фрагмент; скрыта от скринридера.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Текст и карточка',
			'note' => 'высота повторяет будущий контент, чтобы не было скачка',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/skeleton', ['lines' => 4]) ?>
				<?= $this->insert('components::ui/skeleton', ['variant' => 'card']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/skeleton', ['lines' => 4]) ?>
				<?= $this->insert('components::ui/skeleton', ['variant' => 'card']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
