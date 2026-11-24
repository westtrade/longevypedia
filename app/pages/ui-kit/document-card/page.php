<?php

declare(strict_types=1);

/**
 * UI-набор: Карточка документа.
 *
 * Документ в списке: тип, дата, заголовок, краткое описание, тематики.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Карточка документа — UI-набор',
	'description' => 'Документ в списке: тип, дата, заголовок, краткое описание, тематики.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'document-card',
	'title' => 'Карточка документа',
	'lead' => 'Документ в списке: тип, дата, заголовок, краткое описание, тематики.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'тип и дата — метаданные, заголовок — ссылка на документ',
			'code' => <<<'HTML'
				<?php $doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>
				<?= $doc === null ? '' : $this->insert('components::ui/document-card', ['document' => $doc]) ?>
				HTML,
			'body' => function (): void { ?>
				<?php $doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>
				<?= $doc === null ? '' : $this->insert('components::ui/document-card', ['document' => $doc]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'С тематиками',
			'note' => 'showTopics добавляет ссылки в каталог; summary можно убрать в плотном списке',
			'code' => <<<'HTML'
				<?php $doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>
				<?= $doc === null ? '' : $this->insert('components::ui/document-card', ['document' => $doc, 'showTopics' => true, 'showSummary' => false]) ?>
				HTML,
			'body' => function (): void { ?>
				<?php $doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>
				<?= $doc === null ? '' : $this->insert('components::ui/document-card', ['document' => $doc, 'showTopics' => true, 'showSummary' => false]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Без типа',
			'note' => 'showType выключается там, где список уже отфильтрован по типу',
			'code' => <<<'HTML'
				<?php $doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>
				<?= $doc === null ? '' : $this->insert('components::ui/document-card', ['document' => $doc, 'showType' => false]) ?>
				HTML,
			'body' => function (): void { ?>
				<?php $doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>
				<?= $doc === null ? '' : $this->insert('components::ui/document-card', ['document' => $doc, 'showType' => false]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
