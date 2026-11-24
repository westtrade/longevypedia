<?php

declare(strict_types=1);

/**
 * UI-набор: Вкладки.
 *
 * Разделы страницы ссылками: состояние в адресе, а не в скрипте.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Вкладки — UI-набор',
	'description' => 'Разделы страницы ссылками: состояние в адресе, а не в скрипте.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'tabs',
	'title' => 'Вкладки',
	'lead' => 'Разделы страницы ссылками: состояние в адресе, а не в скрипте.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'счётчик показывает размер раздела',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/tabs', ['items' => [['label' => 'Все документы', 'url' => '/catalog', 'active' => true, 'count' => 10], ['label' => 'Исследования', 'url' => '/research', 'count' => 1], ['label' => 'Утверждения', 'url' => '/claims', 'count' => 1]]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/tabs', ['items' => [['label' => 'Все документы', 'url' => '/catalog', 'active' => true, 'count' => 10], ['label' => 'Исследования', 'url' => '/research', 'count' => 1], ['label' => 'Утверждения', 'url' => '/claims', 'count' => 1]]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
