<?php

declare(strict_types=1);

/**
 * UI-набор: Показатель.
 *
 * Число с подписью; значение форматирует вызывающий шаблон.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Показатель — UI-набор',
	'description' => 'Число с подписью; значение форматирует вызывающий шаблон.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'stat',
	'title' => 'Показатель',
	'lead' => 'Число с подписью; значение форматирует вызывающий шаблон.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'ряд показателей на первом экране',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/stat', ['label' => 'Документов', 'value' => '10']) ?>
				<?= $this->insert('components::ui/stat', ['label' => 'Тематик', 'value' => '7']) ?>
				<?= $this->insert('components::ui/stat', ['label' => 'Исследований', 'value' => '1', 'hint' => 'с проверенными источниками']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/stat', ['label' => 'Документов', 'value' => '10']) ?>
				<?= $this->insert('components::ui/stat', ['label' => 'Тематик', 'value' => '7']) ?>
				<?= $this->insert('components::ui/stat', ['label' => 'Исследований', 'value' => '1', 'hint' => 'с проверенными источниками']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
