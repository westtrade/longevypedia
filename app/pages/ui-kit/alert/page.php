<?php

declare(strict_types=1);

/**
 * UI-набор: Сообщение.
 *
 * Реакция системы: результат действия, ошибка формы, предупреждение.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Сообщение — UI-набор',
	'description' => 'Реакция системы: результат действия, ошибка формы, предупреждение.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'alert',
	'title' => 'Сообщение',
	'lead' => 'Реакция системы: результат действия, ошибка формы, предупреждение.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Виды',
			'note' => 'danger объявляется как alert, остальные — как status',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/alert', ['type' => 'success', 'title' => 'Правка сохранена', 'text' => 'Документ уйдёт на проверку после ревью.']) ?>
				<?= $this->insert('components::ui/alert', ['type' => 'warning', 'title' => 'Источник не проверен', 'text' => 'DOI не найден в Crossref — ссылка не опубликована.']) ?>
				<?= $this->insert('components::ui/alert', ['type' => 'danger', 'title' => 'Не удалось сохранить', 'text' => 'Проверьте frontmatter: поле updated обязательно.']) ?>
				<?= $this->insert('components::ui/alert', ['type' => 'info', 'title' => 'Черновик виден только редакторам']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/alert', ['type' => 'success', 'title' => 'Правка сохранена', 'text' => 'Документ уйдёт на проверку после ревью.']) ?>
				<?= $this->insert('components::ui/alert', ['type' => 'warning', 'title' => 'Источник не проверен', 'text' => 'DOI не найден в Crossref — ссылка не опубликована.']) ?>
				<?= $this->insert('components::ui/alert', ['type' => 'danger', 'title' => 'Не удалось сохранить', 'text' => 'Проверьте frontmatter: поле updated обязательно.']) ?>
				<?= $this->insert('components::ui/alert', ['type' => 'info', 'title' => 'Черновик виден только редакторам']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
