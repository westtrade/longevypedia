<?php

declare(strict_types=1);

/**
 * UI-набор: Бейдж.
 *
 * Короткий статус объекта: тип, состояние проверки, уверенность.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Бейдж — UI-набор',
	'description' => 'Короткий статус объекта: тип, состояние проверки, уверенность.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'badge',
	'title' => 'Бейдж',
	'lead' => 'Короткий статус объекта: тип, состояние проверки, уверенность.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Виды',
			'note' => 'цвет не единственный носитель смысла — рядом всегда текст',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/badge', ['label' => 'Опубликовано', 'variant' => 'success']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'На проверке', 'variant' => 'warning']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Черновик', 'variant' => 'neutral']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Оспаривается', 'variant' => 'danger']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Исследование', 'variant' => 'brand']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Пример структуры', 'variant' => 'outline']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Опубликовано', 'variant' => 'success']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'На проверке', 'variant' => 'warning']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Черновик', 'variant' => 'neutral']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Оспаривается', 'variant' => 'danger']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Исследование', 'variant' => 'brand']) ?>
				<?= $this->insert('components::ui/badge', ['label' => 'Пример структуры', 'variant' => 'outline']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
