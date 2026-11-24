<?php

declare(strict_types=1);

/**
 * UI-набор: Параметры исследования.
 *
 * Дизайн, выборка, вмешательство и исходы: поля и таблица из набора.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Параметры исследования — UI-набор',
	'description' => 'Дизайн, выборка, вмешательство и исходы: поля и таблица из набора.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'research-metadata',
	'title' => 'Параметры исследования',
	'lead' => 'Дизайн, выборка, вмешательство и исходы: поля и таблица из набора.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'машинный код дизайна остаётся в data-code рядом с подписью',
			'code' => <<<'HTML'
				<?php $doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>
				<?= $doc === null ? '' : $this->insert('components::ui/research-metadata', ['study' => (array) $doc->metaValue('study', []), 'id' => 'kit-study']) ?>
				HTML,
			'body' => function (): void { ?>
				<?php $doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>
				<?= $doc === null ? '' : $this->insert('components::ui/research-metadata', ['study' => (array) $doc->metaValue('study', []), 'id' => 'kit-study']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Пустые поля',
			'note' => 'незаполненные поля не печатаются: прочерк выглядит как данные',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/research-metadata', ['study' => ['design' => 'meta_analysis', 'sample_size' => '12 исследований'], 'title' => 'Обзор', 'id' => 'kit-study-2']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/research-metadata', ['study' => ['design' => 'meta_analysis', 'sample_size' => '12 исследований'], 'title' => 'Обзор', 'id' => 'kit-study-2']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
