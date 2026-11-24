<?php

declare(strict_types=1);

/**
 * UI-набор: Боковая навигация.
 *
 * Содержание статьи и разделы страницы; уровень задаётся отступом.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Боковая навигация — UI-набор',
	'description' => 'Содержание статьи и разделы страницы; уровень задаётся отступом.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'sidebar-nav',
	'title' => 'Боковая навигация',
	'lead' => 'Содержание статьи и разделы страницы; уровень задаётся отступом.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Содержание',
			'note' => 'уровень вложенности приходит параметром',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/sidebar-nav', ['title' => 'На этой странице', 'items' => [['label' => 'Определение', 'url' => '#definition', 'active' => true], ['label' => 'Механизм', 'url' => '#mechanism'], ['label' => 'Данные', 'url' => '#evidence', 'level' => 1], ['label' => 'Источники', 'url' => '#sources']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/sidebar-nav', ['title' => 'На этой странице', 'items' => [['label' => 'Определение', 'url' => '#definition', 'active' => true], ['label' => 'Механизм', 'url' => '#mechanism'], ['label' => 'Данные', 'url' => '#evidence', 'level' => 1], ['label' => 'Источники', 'url' => '#sources']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
