<?php

declare(strict_types=1);

/**
 * UI-набор: Кнопка.
 *
 * Действия: вид, размер, состояние, иконка, ширина.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Кнопка — UI-набор',
	'description' => 'Действия: вид, размер, состояние, иконка, ширина.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'button',
	'title' => 'Кнопка',
	'lead' => 'Действия: вид, размер, состояние, иконка, ширина.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'primary — главное действие на экране; одно на страницу',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/button', ['label' => 'Сохранить', 'variant' => 'primary']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/button', ['label' => 'Сохранить', 'variant' => 'primary']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Виды',
			'note' => 'вид отвечает за вес действия, а не за цвет бренда',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/button', ['label' => 'Основное', 'variant' => 'primary']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Вторичное', 'variant' => 'secondary']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Контур', 'variant' => 'outline']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Тихое', 'variant' => 'ghost']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Удалить', 'variant' => 'danger']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Ссылка', 'variant' => 'link'])?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/button', ['label' => 'Основное', 'variant' => 'primary']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Вторичное', 'variant' => 'secondary']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Контур', 'variant' => 'outline']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Тихое', 'variant' => 'ghost']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Удалить', 'variant' => 'danger']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Ссылка', 'variant' => 'link'])?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Размеры',
			'note' => 'sm — в таблице и тулбаре, md — обычный, lg — первый экран',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/button', ['label' => 'Маленькая', 'size' => 'sm']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Обычная', 'size' => 'md']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Крупная', 'size' => 'lg']) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/button', ['label' => 'Маленькая', 'size' => 'sm']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Обычная', 'size' => 'md']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Крупная', 'size' => 'lg']) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Состояния',
			'note' => 'загрузка блокирует повторное нажатие и объявляется через aria-busy',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/button', ['label' => 'Недоступно', 'disabled' => true]) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Сохраняем', 'loading' => true]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/button', ['label' => 'Недоступно', 'disabled' => true]) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Сохраняем', 'loading' => true]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Иконки',
			'note' => 'у кнопки-иконки нет видимого текста — имя уходит в aria-label',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/button', ['label' => 'Найти', 'icon' => 'search']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Далее', 'iconRight' => 'arrow-right']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Скачать', 'icon' => 'download', 'iconOnly' => true]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/button', ['label' => 'Найти', 'icon' => 'search']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Далее', 'iconRight' => 'arrow-right']) ?>
				<?= $this->insert('components::ui/button', ['label' => 'Скачать', 'icon' => 'download', 'iconOnly' => true]) ?>
			<?php },
		]) ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Ширина',
			'note' => 'block — на всю ширину формы или панели',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/button', ['label' => 'Войти', 'block' => true]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/button', ['label' => 'Войти', 'block' => true]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
