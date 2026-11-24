<?php

declare(strict_types=1);

/**
 * UI-набор: Таблица данных.
 *
 * Сравнение по строкам: плотность, закреплённая шапка, сортировка, пустое состояние.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Таблица данных — UI-набор',
	'description' => 'Сравнение по строкам: плотность, закреплённая шапка, сортировка, пустое состояние.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'data-table',
	'title' => 'Таблица данных',
	'lead' => 'Сравнение по строкам: плотность, закреплённая шапка, сортировка, пустое состояние.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'числа выровнены вправо и набраны табличными цифрами',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/data-table', ['caption' => 'Сравнение вмешательств', 'columns' => [['label' => 'Вмешательство', 'key' => 'name'], ['label' => 'Цель', 'key' => 'target'], ['label' => 'Статус', 'key' => 'status']], 'rows' => [['cells' => ['name' => 'Рапамицин', 'target' => 'mTOR', 'status' => 'Исследуется']], ['cells' => ['name' => 'Метформин', 'target' => 'AMPK', 'status' => 'Исследуется']]]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/data-table', ['caption' => 'Сравнение вмешательств', 'columns' => [['label' => 'Вмешательство', 'key' => 'name'], ['label' => 'Цель', 'key' => 'target'], ['label' => 'Статус', 'key' => 'status']], 'rows' => [['cells' => ['name' => 'Рапамицин', 'target' => 'mTOR', 'status' => 'Исследуется']], ['cells' => ['name' => 'Метформин', 'target' => 'AMPK', 'status' => 'Исследуется']]]]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Сортировка',
			'note' => 'состояние сортировки приходит из адреса',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/data-table', ['caption' => 'Плотная таблица', 'density' => 'compact', 'sort' => ['key' => 'year', 'direction' => 'desc'], 'sortUrl' => static fn (string $key, string $direction): string => '/research?sort=' . $key . '&direction=' . $direction, 'columns' => [['label' => 'Год', 'key' => 'year', 'numeric' => true, 'sortable' => true], ['label' => 'Исследование', 'key' => 'title', 'sortable' => true]], 'rows' => [['cells' => ['year' => '2009', 'title' => 'Рапамицин у мышей']], ['cells' => ['year' => '2015', 'title' => 'Сенолитики у мышей']]]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/data-table', ['caption' => 'Плотная таблица', 'density' => 'compact', 'sort' => ['key' => 'year', 'direction' => 'desc'], 'sortUrl' => static fn (string $key, string $direction): string => '/research?sort=' . $key . '&direction=' . $direction, 'columns' => [['label' => 'Год', 'key' => 'year', 'numeric' => true, 'sortable' => true], ['label' => 'Исследование', 'key' => 'title', 'sortable' => true]], 'rows' => [['cells' => ['year' => '2009', 'title' => 'Рапамицин у мышей']], ['cells' => ['year' => '2015', 'title' => 'Сенолитики у мышей']]]]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Пустое состояние',
			'note' => 'пустая таблица объясняет причину, а не молчит',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/data-table', ['caption' => 'Результаты фильтра', 'columns' => [['label' => 'Документ', 'key' => 'title']], 'rows' => [], 'empty' => 'Под выбранные фильтры документов нет.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/data-table', ['caption' => 'Результаты фильтра', 'columns' => [['label' => 'Документ', 'key' => 'title']], 'rows' => [], 'empty' => 'Под выбранные фильтры документов нет.']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
