<?php

declare(strict_types=1);

/**
 * UI-набор: Шкала.
 *
 * Заполненность шкалы; смысл числа задаёт подпись.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Шкала — UI-набор',
	'description' => 'Заполненность шкалы; смысл числа задаёт подпись.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'progress',
	'title' => 'Шкала',
	'lead' => 'Заполненность шкалы; смысл числа задаёт подпись.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'тон не заменяет подпись',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/progress', ['label' => 'Готовность обзора', 'value' => 65, 'hint' => '65%']) ?>
				<?= $this->insert('components::ui/progress', ['label' => 'Проверено источников', 'value' => 3, 'max' => 8, 'tone' => 'warning', 'hint' => '3 из 8']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/progress', ['label' => 'Готовность обзора', 'value' => 65, 'hint' => '65%']) ?>
				<?= $this->insert('components::ui/progress', ['label' => 'Проверено источников', 'value' => 3, 'max' => 8, 'tone' => 'warning', 'hint' => '3 из 8']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
