<?php

declare(strict_types=1);

/**
 * UI-набор: Поле ввода.
 *
 * Текст, поиск, выбор, дата, файл, диапазон: подпись, описание, ошибка.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Поле ввода — UI-набор',
	'description' => 'Текст, поиск, выбор, дата, файл, диапазон: подпись, описание, ошибка.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'field',
	'title' => 'Поле ввода',
	'lead' => 'Текст, поиск, выбор, дата, файл, диапазон: подпись, описание, ошибка.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'подпись связана с полем, id строится из имени',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/field', ['name' => 'query', 'label' => 'Поиск по энциклопедии', 'type' => 'search', 'placeholder' => 'сенолитики, mTOR, саркопения']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/field', ['name' => 'query', 'label' => 'Поиск по энциклопедии', 'type' => 'search', 'placeholder' => 'сенолитики, mTOR, саркопения']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Описание и ошибка',
			'note' => 'ошибка не заменяет описание: скринридер читает оба',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/field', ['name' => 'email', 'label' => 'Почта', 'type' => 'email', 'description' => 'Нужна для ответа на правку.', 'error' => 'Адрес не похож на почтовый.']) ?>
				<?= $this->insert('components::ui/field', ['name' => 'doi', 'label' => 'DOI', 'description' => 'Только если идентификатор проверен.', 'required' => true]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/field', ['name' => 'email', 'label' => 'Почта', 'type' => 'email', 'description' => 'Нужна для ответа на правку.', 'error' => 'Адрес не похож на почтовый.']) ?>
				<?= $this->insert('components::ui/field', ['name' => 'doi', 'label' => 'DOI', 'description' => 'Только если идентификатор проверен.', 'required' => true]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Выбор и текст',
			'note' => 'select и textarea — те же состояния, что у input',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/field', ['name' => 'type', 'label' => 'Тип документа', 'type' => 'select', 'options' => ['concept' => 'Понятие', 'mechanism' => 'Механизм', 'research' => 'Исследование']]) ?>
				<?= $this->insert('components::ui/field', ['name' => 'note', 'label' => 'Комментарий к правке', 'type' => 'textarea', 'rows' => 4]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/field', ['name' => 'type', 'label' => 'Тип документа', 'type' => 'select', 'options' => ['concept' => 'Понятие', 'mechanism' => 'Механизм', 'research' => 'Исследование']]) ?>
				<?= $this->insert('components::ui/field', ['name' => 'note', 'label' => 'Комментарий к правке', 'type' => 'textarea', 'rows' => 4]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Отключённое',
			'note' => 'состояние видно и объяснено, а не только серое',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/field', ['name' => 'slug', 'label' => 'Адрес', 'value' => 'mechanisms/autophagy', 'disabled' => true, 'description' => 'Адрес меняется только через перенос документа.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/field', ['name' => 'slug', 'label' => 'Адрес', 'value' => 'mechanisms/autophagy', 'disabled' => true, 'description' => 'Адрес меняется только через перенос документа.']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
