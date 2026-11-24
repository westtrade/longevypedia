<?php

declare(strict_types=1);

/**
 * UI-набор: Доказательность.
 *
 * Два независимых параметра: что говорят данные и насколько им можно верить.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Доказательность — UI-набор',
	'description' => 'Два независимых параметра: что говорят данные и насколько им можно верить.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'evidence',
	'title' => 'Доказательность',
	'lead' => 'Два независимых параметра: что говорят данные и насколько им можно верить.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Направление и качество',
			'note' => 'сильный эффект в малой выборке — это «подтверждает» + «низкое», а не «умеренно»',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/evidence', ['direction' => 'supports', 'quality' => 'high', 'basis' => 'Рандомизированное исследование, 190 участников, воспроизведено дважды.']) ?>
				<?= $this->insert('components::ui/evidence', ['direction' => 'contradicts', 'quality' => 'low', 'basis' => 'Одна группа, 12 животных, эффект не воспроизведён.']) ?>
				<?= $this->insert('components::ui/evidence', ['direction' => 'mixed', 'quality' => 'moderate']) ?>
				<?= $this->insert('components::ui/evidence', ['direction' => 'insufficient', 'quality' => 'unassessed', 'basis' => 'Качество не оценивалось: нет разметки метода.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/evidence', ['direction' => 'supports', 'quality' => 'high', 'basis' => 'Рандомизированное исследование, 190 участников, воспроизведено дважды.']) ?>
				<?= $this->insert('components::ui/evidence', ['direction' => 'contradicts', 'quality' => 'low', 'basis' => 'Одна группа, 12 животных, эффект не воспроизведён.']) ?>
				<?= $this->insert('components::ui/evidence', ['direction' => 'mixed', 'quality' => 'moderate']) ?>
				<?= $this->insert('components::ui/evidence', ['direction' => 'insufficient', 'quality' => 'unassessed', 'basis' => 'Качество не оценивалось: нет разметки метода.']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
