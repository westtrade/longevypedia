<?php

declare(strict_types=1);

/**
 * UI-набор: Строка метаданных.
 *
 * Рубрика капителью и то, что о документе известно до чтения: дата, время чтения.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Строка метаданных — UI-набор',
	'description' => 'Рубрика капителью и то, что о документе известно до чтения: дата, время чтения.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'meta',
	'title' => 'Строка метаданных',
	'lead' => 'Рубрика капителью и то, что о документе известно до чтения: дата, время чтения.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'разделитель рисует компонент — точку в разметку не пишут',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/meta', ['eyebrow' => 'Соединение', 'items' => [['label' => 'Обновлено 20 ноября 2026', 'datetime' => '2026-11-20'], ['label' => '6 минут чтения']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/meta', ['eyebrow' => 'Соединение', 'items' => [['label' => 'Обновлено 20 ноября 2026', 'datetime' => '2026-11-20'], ['label' => '6 минут чтения']]]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Только рубрика',
			'note' => 'когда даты ещё нет — новый документ',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/meta', ['eyebrow' => 'Гипотеза']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/meta', ['eyebrow' => 'Гипотеза']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
