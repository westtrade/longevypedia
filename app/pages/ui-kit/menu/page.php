<?php

declare(strict_types=1);

/**
 * UI-набор: Меню.
 *
 * Раскрывающаяся панель на details: работает без JavaScript.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Меню — UI-набор',
	'description' => 'Раскрывающаяся панель на details: работает без JavaScript.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'menu',
	'title' => 'Меню',
	'lead' => 'Раскрывающаяся панель на details: работает без JavaScript.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'для действий над документом и выбора раздела',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/menu', ['label' => 'Действия', 'items' => [['label' => 'Скачать Markdown', 'url' => '#', 'description' => 'Исходный файл документа'], ['label' => 'История изменений', 'url' => '#', 'description' => 'Коммиты в GitHub'], ['label' => 'Предложить правку', 'url' => '#']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/menu', ['label' => 'Действия', 'items' => [['label' => 'Скачать Markdown', 'url' => '#', 'description' => 'Исходный файл документа'], ['label' => 'История изменений', 'url' => '#', 'description' => 'Коммиты в GitHub'], ['label' => 'Предложить правку', 'url' => '#']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
