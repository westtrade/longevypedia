<?php

declare(strict_types=1);

/**
 * UI-набор: Пустое состояние.
 *
 * Объясняет, почему пусто, и что делать дальше.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Пустое состояние — UI-набор',
	'description' => 'Объясняет, почему пусто, и что делать дальше.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'empty-state',
	'title' => 'Пустое состояние',
	'lead' => 'Объясняет, почему пусто, и что делать дальше.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'без объяснения пустой экран читается как ошибка',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/empty-state', ['icon' => 'search', 'title' => 'Ничего не найдено', 'text' => 'Попробуйте другое написание или более общий термин: «стар», «аутофаг», «mTOR».', 'action' => $this->insert('components::ui/button', ['label' => 'Открыть каталог', 'variant' => 'secondary', 'href' => '/catalog'])]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/empty-state', ['icon' => 'search', 'title' => 'Ничего не найдено', 'text' => 'Попробуйте другое написание или более общий термин: «стар», «аутофаг», «mTOR».', 'action' => $this->insert('components::ui/button', ['label' => 'Открыть каталог', 'variant' => 'secondary', 'href' => '/catalog'])]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
