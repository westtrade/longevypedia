<?php

declare(strict_types=1);

/**
 * UI-набор: Постраничная навигация.
 *
 * Номера страниц ссылками; окно вокруг текущей и разрывы.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Постраничная навигация — UI-набор',
	'description' => 'Номера страниц ссылками; окно вокруг текущей и разрывы.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'pagination',
	'title' => 'Постраничная навигация',
	'lead' => 'Номера страниц ссылками; окно вокруг текущей и разрывы.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'адрес страницы собирает вызывающий шаблон',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/pagination', ['current' => 3, 'total' => 12, 'href' => '/catalog?page=']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/pagination', ['current' => 3, 'total' => 12, 'href' => '/catalog?page=']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
