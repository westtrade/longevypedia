<?php

declare(strict_types=1);

/**
 * UI-набор: Хлебные крошки.
 *
 * Путь от раздела к текущей странице; последний пункт — не ссылка.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Хлебные крошки — UI-набор',
	'description' => 'Путь от раздела к текущей странице; последний пункт — не ссылка.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'breadcrumbs',
	'title' => 'Хлебные крошки',
	'lead' => 'Путь от раздела к текущей странице; последний пункт — не ссылка.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'разделитель рисует CSS, в разметке его нет',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/breadcrumbs', ['items' => [['label' => 'Знания', 'url' => '/catalog'], ['label' => 'Механизмы', 'url' => '/mechanisms'], ['label' => 'Аутофагия']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/breadcrumbs', ['items' => [['label' => 'Знания', 'url' => '/catalog'], ['label' => 'Механизмы', 'url' => '/mechanisms'], ['label' => 'Аутофагия']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
