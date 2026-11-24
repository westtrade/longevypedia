<?php

declare(strict_types=1);

/**
 * UI-набор: Подсказка.
 *
 * Пояснение термина или сокращения; читается и по фокусу.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Подсказка — UI-набор',
	'description' => 'Пояснение термина или сокращения; читается и по фокусу.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'tooltip',
	'title' => 'Подсказка',
	'lead' => 'Пояснение термина или сокращения; читается и по фокусу.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'текст подсказки — часть содержимого',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/tooltip', ['text' => 'Senescence-associated secretory phenotype — набор сигнальных молекул сенесцентной клетки', 'label' => 'SASP']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/tooltip', ['text' => 'Senescence-associated secretory phenotype — набор сигнальных молекул сенесцентной клетки', 'label' => 'SASP']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
