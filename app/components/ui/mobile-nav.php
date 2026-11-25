<?php

declare(strict_types=1);

/**
 * Мобильная навигация: раскрывающаяся панель под шапкой.
 *
 * На широком экране компонент не выводится вовсе (`display: none`), на узком
 * заменяет основное меню, которое шапка скрывает. Раскрытие — `<details>`,
 * поэтому клавиатура и скринридеры работают без JavaScript; пункт текущего
 * раздела помечен `aria-current="page"`, как и в обычном меню.
 *
 * @param string $label подпись кнопки для скринридера
 * @param array<int, array{label: string, url: string, key?: string}> $items пункты меню
 * @param string $current ключ текущего раздела
 * @param string $id идентификатор `<details>`, чтобы панель можно было связать с кнопкой
 * @param bool $open панель раскрыта сразу
 * @param bool $preview показать на широком экране (только для UI-набора)
 */

$label ??= 'Menu';
$items ??= [];
$current ??= '';
$id ??= 'mobile-nav';
$open ??= false;
$preview ??= false;

if ($items === []) {
	return;
}
?>
<details class="ui-mobile-nav<?= $preview ? ' ui-mobile-nav--preview' : '' ?>" id="<?= e($id) ?>"<?= $open ? ' open' : '' ?>>
	<summary class="ui-mobile-nav__toggle">
		<?= $this->insert('bolt://ui-kit/icon', ['name' => 'burger', 'size' => 20]) ?>
		<span class="visually-hidden"><?= e($label) ?></span>
	</summary>
	<nav class="ui-mobile-nav__panel" aria-label="<?= e($label) ?>">
		<ul class="ui-mobile-nav__list">
			<?php foreach ($items as $item): ?>
				<li class="ui-mobile-nav__item">
					<a class="ui-mobile-nav__link" href="<?= e((string) $item['url']) ?>"<?= ($item['key'] ?? '') === $current && $current !== '' ? ' aria-current="page"' : '' ?>>
						<?= e((string) $item['label']) ?>
					</a>
				</li>
			<?php endforeach ?>
		</ul>
	</nav>
</details>

<style lang="scss">
	/* Выше точки перехода меню в шапке видно целиком — дублировать его
	   кнопкой незачем. */
	.ui-mobile-nav {
		display: none;
		position: static;
	}

	.ui-mobile-nav__toggle {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 2.25rem;
		height: 2.25rem;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface);
		color: var(--color-text-muted);
		cursor: pointer;
		list-style: none;
	}

	.ui-mobile-nav__toggle::-webkit-details-marker {
		display: none;
	}

	.ui-mobile-nav__toggle:hover {
		color: var(--color-text);
		border-color: var(--color-border-strong);
	}

	.ui-mobile-nav[open] > .ui-mobile-nav__toggle {
		color: var(--color-brand-text);
		border-color: var(--color-brand);
	}

	/* Панель позиционируется от шапки (она `sticky`, то есть служит
	   содержащим блоком), а не от контейнера: так она занимает всю ширину
	   экрана и не обрезается внутренними отступами. */
	.ui-mobile-nav__panel {
		position: absolute;
		inset-inline: 0;
		top: 100%;
		border-bottom: 1px solid var(--color-border);
		background: var(--color-surface);
		box-shadow: var(--shadow-md);
	}

	.ui-mobile-nav__list {
		display: flex;
		flex-direction: column;
		margin: 0;
		padding: var(--space-2) var(--layout-gutter);
		list-style: none;
	}

	.ui-mobile-nav__link {
		display: block;
		padding: var(--space-3) 0;
		border-bottom: 1px solid var(--color-border);
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-base);
		font-weight: var(--weight-medium);
		text-decoration: none;
	}

	.ui-mobile-nav__item:last-child .ui-mobile-nav__link {
		border-bottom: 0;
	}

	.ui-mobile-nav__link:hover {
		color: var(--color-brand-text);
	}

	.ui-mobile-nav__link[aria-current='page'] {
		color: var(--color-brand-text);
	}

	@media (max-width: $bp-md) {
		.ui-mobile-nav {
			display: block;
			flex: none;
		}
	}

	/* Образец в UI-наборе: на широком экране компонент скрыт по назначению, и
	   без этого правила страница компонента показывала пустое место вместо
	   примера. Панель в образце встаёт в поток — иначе она накрыла бы
	   исходник под примером. */
	.ui-mobile-nav--preview {
		display: block;
		/* Ширина телефона: на всю ширину образца кнопка-бургер выглядела бы
		   одинокой в пустом поле, а панель — растянутой на 900px. */
		max-width: 22rem;
	}

	.ui-mobile-nav--preview .ui-mobile-nav__panel {
		position: static;
		box-shadow: none;
		border-top: 1px solid var(--color-border);
	}

	.ui-mobile-nav--preview .ui-mobile-nav__list {
		padding-inline: 0;
	}
</style>
