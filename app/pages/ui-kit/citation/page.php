<?php

declare(strict_types=1);

/**
 * UI-набор: Цитата источника.
 *
 * Отсылка внутри текста; идентификаторы живут в списке источников.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Цитата источника — UI-набор',
	'description' => 'Отсылка внутри текста; идентификаторы живут в списке источников.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'citation',
	'title' => 'Цитата источника',
	'lead' => 'Отсылка внутри текста; идентификаторы живут в списке источников.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'номер и подпись, ссылка на источник',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/citation', ['number' => 1, 'text' => 'Harrison et al., 2009', 'href' => 'https://doi.org/10.1038/nature08221']) ?>
				<?= $this->insert('components::ui/citation', ['number' => 2, 'text' => 'Saxton & Sabatini, 2017', 'href' => 'https://doi.org/10.1016/j.cell.2017.02.004', 'note' => 'обзор']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/citation', ['number' => 1, 'text' => 'Harrison et al., 2009', 'href' => 'https://doi.org/10.1038/nature08221']) ?>
				<?= $this->insert('components::ui/citation', ['number' => 2, 'text' => 'Saxton & Sabatini, 2017', 'href' => 'https://doi.org/10.1016/j.cell.2017.02.004', 'note' => 'обзор']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Без ссылки',
			'note' => 'источник упомянут, но адреса нет — cite без ссылки',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/citation', ['text' => 'личное сообщение, 2026']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/citation', ['text' => 'личное сообщение, 2026']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
