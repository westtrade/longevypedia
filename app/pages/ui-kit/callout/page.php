<?php

declare(strict_types=1);

/**
 * UI-набор: Врезка.
 *
 * Часть текста: определение, методика, предупреждение.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Врезка — UI-набор',
	'description' => 'Часть текста: определение, методика, предупреждение.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'callout',
	'title' => 'Врезка',
	'lead' => 'Часть текста: определение, методика, предупреждение.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Определение',
			'note' => 'остаётся в статье всегда, в отличие от alert',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/callout', ['kind' => 'definition', 'children' => '<p>Сенолитики — препараты, избирательно удаляющие сенесцентные клетки.</p>']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/callout', ['kind' => 'definition', 'children' => '<p>Сенолитики — препараты, избирательно удаляющие сенесцентные клетки.</p>']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Методика',
			'note' => 'объясняет, как получена оценка',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/callout', ['kind' => 'method', 'title' => 'Как мы оцениваем доказательства', 'children' => '<p>Качество данных присваивается по типу исследования, размеру выборки и воспроизведению, а не по величине эффекта.</p>']) ?>
				<?= $this->insert('components::ui/callout', ['kind' => 'caution', 'children' => '<p>Данные на животных не переносятся на человека автоматически.</p>']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/callout', ['kind' => 'method', 'title' => 'Как мы оцениваем доказательства', 'children' => '<p>Качество данных присваивается по типу исследования, размеру выборки и воспроизведению, а не по величине эффекта.</p>']) ?>
				<?= $this->insert('components::ui/callout', ['kind' => 'caution', 'children' => '<p>Данные на животных не переносятся на человека автоматически.</p>']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
