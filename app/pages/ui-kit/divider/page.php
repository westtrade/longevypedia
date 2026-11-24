<?php

declare(strict_types=1);

/**
 * UI-набор: Разделитель.
 *
 * Граница секций; с подписью — когда нужно назвать, что начинается ниже.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Разделитель — UI-набор',
	'description' => 'Граница секций; с подписью — когда нужно назвать, что начинается ниже.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'divider',
	'title' => 'Разделитель',
	'lead' => 'Граница секций; с подписью — когда нужно назвать, что начинается ниже.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'без подписи — просто линия',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/divider', []) ?>
				<?= $this->insert('components::ui/divider', ['label' => 'Источники']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/divider', []) ?>
				<?= $this->insert('components::ui/divider', ['label' => 'Источники']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
