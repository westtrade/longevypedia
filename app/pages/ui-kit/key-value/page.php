<?php

declare(strict_types=1);

/**
 * UI-набор: Поля и значения.
 *
 * Паспорт объекта: идентификаторы, параметры исследования, свойства.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Поля и значения — UI-набор',
	'description' => 'Паспорт объекта: идентификаторы, параметры исследования, свойства.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'key-value',
	'title' => 'Поля и значения',
	'lead' => 'Паспорт объекта: идентификаторы, параметры исследования, свойства.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'значение — готовая разметка, ссылки на внешние базы внутри',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/key-value', ['title' => 'Идентификаторы', 'items' => [['label' => 'HGNC', 'value' => 'HGNC:3942'], ['label' => 'UniProt', 'value' => 'P42345'], ['label' => 'MeSH', 'value' => 'D020123', 'hint' => 'Sirolimus']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/key-value', ['title' => 'Идентификаторы', 'items' => [['label' => 'HGNC', 'value' => 'HGNC:3942'], ['label' => 'UniProt', 'value' => 'P42345'], ['label' => 'MeSH', 'value' => 'D020123', 'hint' => 'Sirolimus']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
