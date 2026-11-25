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
			'title' => 'Закрытое',
			'note' => 'на узком экране стоит в шапке рядом с логотипом; здесь показано на широком через `preview`',
			'stack' => true,
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav', 'label' => 'Основная навигация', 'current' => 'catalog', 'preview' => true, 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav', 'label' => 'Основная навигация', 'current' => 'catalog', 'preview' => true, 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Открытое',
			'note' => 'панель раскрывается без JavaScript; текущий раздел помечен aria-current',
			'stack' => true,
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav-open', 'label' => 'Основная навигация', 'current' => 'catalog', 'open' => true, 'preview' => true, 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav-open', 'label' => 'Основная навигация', 'current' => 'catalog', 'open' => true, 'preview' => true, 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
