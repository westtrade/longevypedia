<?php

declare(strict_types=1);

/**
 * UI-набор: Выбор.
 *
 * Флажок, переключатель, тумблер: нативное поле под стилизованной меткой.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Выбор — UI-набор',
	'description' => 'Флажок, переключатель, тумблер: нативное поле под стилизованной меткой.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'choice',
	'title' => 'Выбор',
	'lead' => 'Флажок, переключатель, тумблер: нативное поле под стилизованной меткой.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Флажок',
			'note' => 'для независимых параметров',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/choice', ['name' => 'published', 'type' => 'checkbox', 'label' => 'Опубликовано', 'description' => 'Видно в каталоге и поиске.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/choice', ['name' => 'published', 'type' => 'checkbox', 'label' => 'Опубликовано', 'description' => 'Видно в каталоге и поиске.']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Переключатель',
			'note' => 'для взаимоисключающего выбора',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'Черновик']) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'На проверке']) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'Опубликовано']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'Черновик']) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'На проверке']) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'Опубликовано']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Тумблер',
			'note' => 'мгновенное переключение настройки',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/choice', ['name' => 'theme', 'type' => 'switch', 'label' => 'Тёмная тема', 'description' => 'Следует за системной, пока не выбран вручную.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/choice', ['name' => 'theme', 'type' => 'switch', 'label' => 'Тёмная тема', 'description' => 'Следует за системной, пока не выбран вручную.']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Отмеченные',
			'note' => 'знак по центру контрола: галочка, точка и кружок тумблера',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/choice', ['name' => 'published-checked', 'type' => 'checkbox', 'label' => 'Опубликовано', 'checked' => true]) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'status-checked', 'type' => 'radio', 'label' => 'Опубликовано', 'checked' => true]) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'theme-checked', 'type' => 'switch', 'label' => 'Тёмная тема', 'checked' => true]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/choice', ['name' => 'published-checked', 'type' => 'checkbox', 'label' => 'Опубликовано', 'checked' => true]) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'status-checked', 'type' => 'radio', 'label' => 'Опубликовано', 'checked' => true]) ?>
				<?= $this->insert('components::ui/choice', ['name' => 'theme-checked', 'type' => 'switch', 'label' => 'Тёмная тема', 'checked' => true]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Ошибка',
			'note' => 'ошибка относится к группе, а не к одной метке',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/choice', ['name' => 'license', 'type' => 'checkbox', 'label' => 'Согласен с лицензией CC BY-SA 4.0', 'error' => 'Без согласия правку нельзя опубликовать.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/choice', ['name' => 'license', 'type' => 'checkbox', 'label' => 'Согласен с лицензией CC BY-SA 4.0', 'error' => 'Без согласия правку нельзя опубликовать.']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
