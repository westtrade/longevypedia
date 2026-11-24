<?php

declare(strict_types=1);

/**
 * UI-набор: Аккордеон.
 *
 * Раскрывающиеся блоки на details: клавиатура и поиск работают сами.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Аккордеон — UI-набор',
	'description' => 'Раскрывающиеся блоки на details: клавиатура и поиск работают сами.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'accordion',
	'title' => 'Аккордеон',
	'lead' => 'Раскрывающиеся блоки на details: клавиатура и поиск работают сами.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'для длинных пояснений, которые не нужны сразу',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/accordion', ['items' => [['title' => 'Что считается первичным источником', 'content' => '<p>Оригинальная публикация с описанием метода, а не пресс-релиз и не обзор.</p>', 'open' => true], ['title' => 'Как указать DOI', 'content' => '<p>Только проверенный идентификатор: doi.org должен открывать ту же публикацию.</p>']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/accordion', ['items' => [['title' => 'Что считается первичным источником', 'content' => '<p>Оригинальная публикация с описанием метода, а не пресс-релиз и не обзор.</p>', 'open' => true], ['title' => 'Как указать DOI', 'content' => '<p>Только проверенный идентификатор: doi.org должен открывать ту же публикацию.</p>']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
