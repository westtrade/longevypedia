<?php

declare(strict_types=1);

/**
 * UI-набор: Мобильная навигация.
 *
 * Меню для узкого экрана: раскрывается без JavaScript, закрывается повторным нажатием.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Мобильная навигация — UI-набор',
	'description' => 'Меню для узкого экрана: раскрывается без JavaScript, закрывается повторным нажатием.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'mobile-nav',
	'title' => 'Мобильная навигация',
	'lead' => 'Меню для узкого экрана: раскрывается без JavaScript, закрывается повторным нажатием.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'видно только на узком экране; в документации показано всегда',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav', 'label' => 'Основная навигация', 'current' => 'catalog', 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav', 'label' => 'Основная навигация', 'current' => 'catalog', 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
