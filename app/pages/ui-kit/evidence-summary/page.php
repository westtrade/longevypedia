<?php

declare(strict_types=1);

/**
 * UI-набор: Сводка доказательств.
 *
 * Связанные утверждения по направлению: подтверждает, противоречит, смешанно, мало данных.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Сводка доказательств — UI-набор',
	'description' => 'Связанные утверждения по направлению: подтверждает, противоречит, смешанно, мало данных.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'evidence-summary',
	'title' => 'Сводка доказательств',
	'lead' => 'Связанные утверждения по направлению: подтверждает, противоречит, смешанно, мало данных.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'направление берётся из фронтматтера утверждения, а не из типа связи',
			'code' => <<<'HTML'
				<?php
				$doc = longevypedia_content()->find('compound', 'rapamycin', 'en');
				?>
				<?= $doc === null ? '' : $this->insert('components::ui/evidence-summary', [
					'groups' => longevypedia_content()->evidenceGroups($doc),
					'id' => 'claims-main',
				]) ?>
				HTML,
			'body' => function (): void { ?>
				<?php
				$doc = longevypedia_content()->find('compound', 'rapamycin', 'en');
				?>
				<?= $doc === null ? '' : $this->insert('components::ui/evidence-summary', [
					'groups' => longevypedia_content()->evidenceGroups($doc),
					'id' => 'claims-main',
				]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Свои подписи',
			'note' => 'перевод или другая терминология передаются словарём',
			'code' => <<<'HTML'
				<?php
				$doc = longevypedia_content()->find('compound', 'rapamycin', 'en');
				?>
				<?= $doc === null ? '' : $this->insert('components::ui/evidence-summary', [
					'groups' => longevypedia_content()->evidenceGroups($doc),
					'id' => 'claims-ru',
					'title' => 'Связанные утверждения',
					'labels' => [
						'supports' => 'Подтверждающие данные',
						'contradicts' => 'Противоречащие данные',
					],
				]) ?>
				HTML,
			'body' => function (): void { ?>
				<?php
				$doc = longevypedia_content()->find('compound', 'rapamycin', 'en');
				?>
				<?= $doc === null ? '' : $this->insert('components::ui/evidence-summary', [
					'groups' => longevypedia_content()->evidenceGroups($doc),
					'id' => 'claims-ru',
					'title' => 'Связанные утверждения',
					'labels' => [
						'supports' => 'Подтверждающие данные',
						'contradicts' => 'Противоречащие данные',
					],
				]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
