<?php

declare(strict_types=1);

/**
 * UI-набор: Переключатель темы.
 *
 * Кнопка-значок из ui/button: те же размеры, фокус и состояния, что у остальных кнопок.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Переключатель темы — UI-набор',
	'description' => 'Кнопка-значок из ui/button: те же размеры, фокус и состояния, что у остальных кнопок.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'theme-toggle',
	'title' => 'Переключатель темы',
	'lead' => 'Кнопка-значок из ui/button: те же размеры, фокус и состояния, что у остальных кнопок.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'значок показывает следующий шаг: в светлой теме — луна, в тёмной — солнце',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/theme-toggle') ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/theme-toggle') ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'В ряду действий',
			'note' => 'высота и поля совпадают с соседними кнопками — отдельной вёрстки у переключателя нет',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/theme-toggle') ?>
				<?= $this->insert('components::ui/button', ['label' => 'Настройки', 'variant' => 'ghost', 'icon' => 'cog', 'iconOnly' => true]) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Оформить подписку']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/theme-toggle') ?>
				<?= $this->insert('components::ui/button', ['label' => 'Настройки', 'variant' => 'ghost', 'icon' => 'cog', 'iconOnly' => true]) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Оформить подписку']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
