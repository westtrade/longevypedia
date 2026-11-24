<?php

declare(strict_types=1);

/**
 * UI-набор: Карточка.
 *
 * Контейнер материала: надзаголовок, заголовок, метаданные, действия.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Карточка — UI-набор',
	'description' => 'Контейнер материала: надзаголовок, заголовок, метаданные, действия.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'card',
	'title' => 'Карточка',
	'lead' => 'Контейнер материала: надзаголовок, заголовок, метаданные, действия.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'карточка-ссылка: вся площадь кликабельна, фокус на ссылке',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/card', ['eyebrow' => 'Механизм', 'title' => 'Клеточное старение', 'meta' => 'Обновлено 20 ноября 2026', 'href' => '/mechanisms/cellular-senescence', 'children' => 'Клетки перестают делиться, но остаются в ткани и выделяют сигнальные молекулы.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/card', ['eyebrow' => 'Механизм', 'title' => 'Клеточное старение', 'meta' => 'Обновлено 20 ноября 2026', 'href' => '/mechanisms/cellular-senescence', 'children' => 'Клетки перестают делиться, но остаются в ткани и выделяют сигнальные молекулы.']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Виды',
			'note' => 'outlined — на белом фоне, flat — внутри другой карточки',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/card', ['title' => 'Определение', 'variant' => 'outlined', 'children' => 'Старение — постепенное снижение функции организма со временем.']) ?>
				<?= $this->insert('components::ui/card', ['title' => 'Заметка', 'variant' => 'flat', 'children' => 'Вложенная карточка не добавляет вторую рамку.']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/card', ['title' => 'Определение', 'variant' => 'outlined', 'children' => 'Старение — постепенное снижение функции организма со временем.']) ?>
				<?= $this->insert('components::ui/card', ['title' => 'Заметка', 'variant' => 'flat', 'children' => 'Вложенная карточка не добавляет вторую рамку.']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'С действиями',
			'note' => 'кнопки в подвале — не ссылки на всю карточку',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/card', ['title' => 'Исследование', 'meta' => 'Harrison et al., 2009', 'children' => 'Рапамицин продлевает медианную и максимальную продолжительность жизни мышей.', 'footer' => 'Nature, 2009']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/card', ['title' => 'Исследование', 'meta' => 'Harrison et al., 2009', 'children' => 'Рапамицин продлевает медианную и максимальную продолжительность жизни мышей.', 'footer' => 'Nature, 2009']) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
