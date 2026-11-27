<?php

declare(strict_types=1);

/**
 * UI-набор: Поля и значения.
 *
 * Паспорт объекта: идентификаторы, параметры исследования, свойства. Подпись идёт капителью в узкой колонке, значение — обычным текстом.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Поля и значения — UI-набор',
	'description' => 'Паспорт объекта: идентификаторы, параметры исследования, свойства. Подпись идёт капителью в узкой колонке, значение — обычным текстом.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'key-value',
	'title' => 'Поля и значения',
	'lead' => 'Паспорт объекта: идентификаторы, параметры исследования, свойства. Подпись идёт капителью в узкой колонке, значение — обычным текстом.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'значение — готовая разметка, ссылки на внешние базы внутри; `code` печатает машинный код рядом с переводом',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/key-value', ['title' => 'Идентификаторы', 'items' => [['label' => 'HGNC', 'value' => 'HGNC:3942'], ['label' => 'UniProt', 'value' => 'P42345'], ['label' => 'Тип', 'value' => 'Ингибитор', 'code' => 'inhibitor'], ['label' => 'MeSH', 'value' => 'D020123', 'hint' => 'Sirolimus']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/key-value', ['title' => 'Идентификаторы', 'items' => [['label' => 'HGNC', 'value' => 'HGNC:3942'], ['label' => 'UniProt', 'value' => 'P42345'], ['label' => 'Тип', 'value' => 'Ингибитор', 'code' => 'inhibitor'], ['label' => 'MeSH', 'value' => 'D020123', 'hint' => 'Sirolimus']]]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Группы и две колонки',
			'note' => '`groups` делит паспорт на смысловые части, `wide` отдаёт прозаическое поле на всю ширину',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/key-value', ['columns' => 2, 'groups' => [['title' => 'Кто и как', 'items' => [['label' => 'Design', 'value' => 'Animal experiment', 'code' => 'animal_experiment'], ['label' => 'Species', 'value' => 'mouse (Mus musculus)'], ['label' => 'Population', 'value' => 'Genetically heterogeneous UM-HET3 mice of both sexes, housed at three independent test sites.', 'wide' => true]]], ['title' => 'Что делали', 'items' => [['label' => 'Intervention', 'value' => 'Encapsulated rapamycin in the diet at 14 mg per kg of food.', 'wide' => true], ['label' => 'Duration', 'value' => 'Lifelong']]]]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/key-value', ['columns' => 2, 'groups' => [['title' => 'Кто и как', 'items' => [['label' => 'Design', 'value' => 'Animal experiment', 'code' => 'animal_experiment'], ['label' => 'Species', 'value' => 'mouse (Mus musculus)'], ['label' => 'Population', 'value' => 'Genetically heterogeneous UM-HET3 mice of both sexes, housed at three independent test sites.', 'wide' => true]]], ['title' => 'Что делали', 'items' => [['label' => 'Intervention', 'value' => 'Encapsulated rapamycin in the diet at 14 mg per kg of food.', 'wide' => true], ['label' => 'Duration', 'value' => 'Lifelong']]]]]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'В карточке',
			'note' => '`dividers => false` — карточка уже даёт рамку, вложенному блоку своя не нужна',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/key-value', ['items' => [['label' => 'HGNC', 'value' => 'HGNC:3942'], ['label' => 'UniProt', 'value' => 'P42345']], 'dividers' => false, 'density' => 'compact']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/key-value', ['items' => [['label' => 'HGNC', 'value' => 'HGNC:3942'], ['label' => 'UniProt', 'value' => 'P42345']], 'dividers' => false, 'density' => 'compact']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
