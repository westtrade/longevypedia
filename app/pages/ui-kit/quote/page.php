<?php

declare(strict_types=1);

/**
 * UI-набор: Цитата.
 *
 * Цитата с атрибуцией: кто сказал и где опубликовано.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Цитата — UI-набор',
	'description' => 'Цитата с атрибуцией: кто сказал и где опубликовано.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'quote',
	'title' => 'Цитата',
	'lead' => 'Цитата с атрибуцией: кто сказал и где опубликовано.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'без подписи цитата читается как мнение редакции',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/quote', ['text' => 'Старение — не одна болезнь, а набор процессов, которые идут с разной скоростью.', 'cite' => 'Обзор геронтологии', 'source' => 'Longevypedia, раздел «Метод»']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/quote', ['text' => 'Старение — не одна болезнь, а набор процессов, которые идут с разной скоростью.', 'cite' => 'Обзор геронтологии', 'source' => 'Longevypedia, раздел «Метод»']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
