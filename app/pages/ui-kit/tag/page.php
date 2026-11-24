<?php

declare(strict_types=1);

/**
 * UI-набор: Тег.
 *
 * Тематика материала: всегда ссылка в каталог, не украшение.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Тег — UI-набор',
	'description' => 'Тематика материала: всегда ссылка в каталог, не украшение.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'tag',
	'title' => 'Тег',
	'lead' => 'Тематика материала: всегда ссылка в каталог, не украшение.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'счётчик — сколько документов в теме',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/tag', ['label' => 'Механизмы старения', 'href' => '/catalog?topic=aging-mechanisms', 'count' => 4]) ?>
				<?= $this->insert('components::ui/tag', ['label' => 'Вмешательства', 'href' => '/catalog?topic=interventions', 'count' => 2, 'active' => true]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/tag', ['label' => 'Механизмы старения', 'href' => '/catalog?topic=aging-mechanisms', 'count' => 4]) ?>
				<?= $this->insert('components::ui/tag', ['label' => 'Вмешательства', 'href' => '/catalog?topic=interventions', 'count' => 2, 'active' => true]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
