<?php

declare(strict_types=1);

/**
 * UI-набор: Карточка автора.
 *
 * Автор или рецензент: имя, роль, организация, внешняя ссылка.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Карточка автора — UI-набор',
	'description' => 'Автор или рецензент: имя, роль, организация, внешняя ссылка.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'author-card',
	'title' => 'Карточка автора',
	'lead' => 'Автор или рецензент: имя, роль, организация, внешняя ссылка.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'адрес профиля печатается текстом — заголовок уже ссылка',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/author-card', ['name' => 'Harrison D. E.', 'role' => 'Corresponding author', 'affiliation' => 'The Jackson Laboratory', 'href' => 'https://orcid.org/0000-0002-1825-0097']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/author-card', ['name' => 'Harrison D. E.', 'role' => 'Corresponding author', 'affiliation' => 'The Jackson Laboratory', 'href' => 'https://orcid.org/0000-0002-1825-0097']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Без ссылки',
			'note' => 'для рецензента без публичного профиля',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/author-card', ['name' => 'Рецензент 2', 'role' => 'Reviewer', 'affiliation' => 'Независимый рецензент']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/author-card', ['name' => 'Рецензент 2', 'role' => 'Reviewer', 'affiliation' => 'Независимый рецензент']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
