<?php

declare(strict_types=1);

/**
 * UI-набор: Переключатель темы.
 *
 * Кнопка-значок открывает список из трёх состояний: как в системе, светлая, тёмная. Текущее состояние видно по значку и по галочке в списке.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Переключатель темы — UI-набор',
	'description' => 'Кнопка-значок открывает список из трёх состояний: как в системе, светлая, тёмная. Текущее состояние видно по значку и по галочке в списке.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'theme-toggle',
	'title' => 'Переключатель темы',
	'lead' => 'Кнопка-значок открывает список из трёх состояний: как в системе, светлая, тёмная. Текущее состояние видно по значку и по галочке в списке.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'значок показывает выбранное состояние: половина круга — «как в системе», солнце — светлая, луна — тёмная',
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
				<?= $this->insert('components::ui/button', ['label' => 'Скачать', 'variant' => 'ghost', 'icon' => 'download', 'iconOnly' => true]) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Оформить подписку']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/theme-toggle') ?>
				<?= $this->insert('components::ui/button', ['label' => 'Скачать', 'variant' => 'ghost', 'icon' => 'download', 'iconOnly' => true]) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Оформить подписку']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
