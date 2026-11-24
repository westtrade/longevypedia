<?php

declare(strict_types=1);

/**
 * UI-набор: График.
 *
 * Рамка графика: данные рисует вызывающий шаблон, компонент их не выдумывает.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'График — UI-набор',
	'description' => 'Рамка графика: данные рисует вызывающий шаблон, компонент их не выдумывает.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'chart',
	'title' => 'График',
	'lead' => 'Рамка графика: данные рисует вызывающий шаблон, компонент их не выдумывает.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Пустое состояние',
			'note' => 'пока данных нет — так и написано',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/chart', ['title' => 'Продолжительность жизни', 'caption' => 'Медианная, дни', 'empty' => 'Данные появятся, когда будет размечен первый набор.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/chart', ['title' => 'Продолжительность жизни', 'caption' => 'Медианная, дни', 'empty' => 'Данные появятся, когда будет размечен первый набор.']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
