<?php

declare(strict_types=1);

/**
 * UI-набор: Логотип.
 *
 * Знак и текстовый вордмарк; знак заменяется, разметка шапки — нет.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Логотип — UI-набор',
	'description' => 'Знак и текстовый вордмарк; знак заменяется, разметка шапки — нет.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'wordmark',
	'title' => 'Логотип',
	'lead' => 'Знак и текстовый вордмарк; знак заменяется, разметка шапки — нет.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'знак — зарезервированное место, а не эмблема',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/wordmark', ['href' => '/', 'label' => 'Longevypedia', 'tagline' => 'Энциклопедия долголетия']) ?>
				<?= $this->insert('components::ui/wordmark', ['href' => null, 'label' => 'Longevypedia', 'size' => 'lg']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/wordmark', ['href' => '/', 'label' => 'Longevypedia', 'tagline' => 'Энциклопедия долголетия']) ?>
				<?= $this->insert('components::ui/wordmark', ['href' => null, 'label' => 'Longevypedia', 'size' => 'lg']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
