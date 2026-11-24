<?php

declare(strict_types=1);

/**
 * UI-набор: Карточка исследования.
 *
 * Публикация в списке: дизайн, авторы, журнал, год, число исходов.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Карточка исследования — UI-набор',
	'description' => 'Публикация в списке: дизайн, авторы, журнал, год, число исходов.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'research-card',
	'title' => 'Карточка исследования',
	'lead' => 'Публикация в списке: дизайн, авторы, журнал, год, число исходов.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'дизайн и библиография берутся из фронтматтера, а не из текста',
			'code' => <<<'HTML'
				<?php $doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>
				<?= $doc === null ? '' : $this->insert('components::ui/research-card', ['document' => $doc]) ?>
				HTML,
			'body' => function (): void { ?>
				<?php $doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>
				<?= $doc === null ? '' : $this->insert('components::ui/research-card', ['document' => $doc]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Уровень заголовка',
			'note' => 'headingLevel держит порядок заголовков страницы',
			'code' => <<<'HTML'
				<?php $doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>
				<?= $doc === null ? '' : $this->insert('components::ui/research-card', ['document' => $doc, 'headingLevel' => 2, 'showSummary' => false]) ?>
				HTML,
			'body' => function (): void { ?>
				<?php $doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>
				<?= $doc === null ? '' : $this->insert('components::ui/research-card', ['document' => $doc, 'headingLevel' => 2, 'showSummary' => false]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
