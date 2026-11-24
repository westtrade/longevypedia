<?php

declare(strict_types=1);

/**
 * UI-набор: Статус утверждения.
 *
 * Уверенность сообщества и направление доказательств — разные шкалы.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Статус утверждения — UI-набор',
	'description' => 'Уверенность сообщества и направление доказательств — разные шкалы.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'claim-status',
	'title' => 'Статус утверждения',
	'lead' => 'Уверенность сообщества и направление доказательств — разные шкалы.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Уровни',
			'note' => 'статус приходит из frontmatter утверждения',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'established', 'direction' => 'supports', 'asOf' => '2026-11-20']) ?>
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'likely', 'direction' => 'supports', 'asOf' => '2026-11-20']) ?>
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'contested', 'direction' => 'mixed']) ?>
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'speculative', 'direction' => 'insufficient']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'established', 'direction' => 'supports', 'asOf' => '2026-11-20']) ?>
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'likely', 'direction' => 'supports', 'asOf' => '2026-11-20']) ?>
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'contested', 'direction' => 'mixed']) ?>
				<?= $this->insert('components::ui/claim-status', ['confidence' => 'speculative', 'direction' => 'insufficient']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
