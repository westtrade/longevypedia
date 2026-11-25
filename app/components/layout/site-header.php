<?php

declare(strict_types=1);

/**
 * Шапка сайта: логотип, навигация, поиск, переключатель темы.
 *
 * Логотип приходит из `components::ui/wordmark`, поэтому замена знака не
 * трогает разметку шапки. На узком экране пункты меню прячутся, и вместо них
 * выводится `components::ui/mobile-nav` — та же навигация в `<details>`.
 *
 * Поиск работает без JavaScript (обычный GET на `/search`). С HTMX тот же
 * адрес с `_fragments=search-results` отдаёт только список подсказок —
 * страница при этом не рендерится (см. `app/fragments/search-results.php`).
 *
 * @var string $current раздел навигации для `aria-current`
 */

$current ??= '';

$navigation = [
	['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'],
	['key' => 'research', 'label' => 'Research', 'url' => '/research'],
	['key' => 'about', 'label' => 'About', 'url' => '/about'],
];
?>
<header class="site-header">
	<div class="container site-header__inner">
		<?= $this->insert('components::ui/wordmark', [
			'href' => '/',
			'label' => 'Longevypedia',
			'tagline' => 'The science of longevity',
			'attrs' => ['class' => 'site-header__brand'],
		]) ?>

		<nav class="site-nav" aria-label="Main">
			<ul class="site-nav__list">
				<?php foreach ($navigation as $item): ?>
					<li class="site-nav__item">
						<a class="site-nav__link" href="<?= $this->e($item['url']) ?>"<?= $current === $item['key'] ? ' aria-current="page"' : '' ?>>
							<?= $this->e($item['label']) ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<?= $this->insert('components::site/search-form', ['id' => 'site-search', 'compact' => true]) ?>

		<?= $this->insert('components::ui/theme-toggle', ['attrs' => ['class' => 'site-header__theme']]) ?>

		<?= $this->insert('components::ui/button', [
			'label' => 'Source code on GitHub',
			'variant' => 'ghost',
			'icon' => 'github',
			'iconOnly' => true,
			'href' => 'https://github.com/westtrade/longevypedia',
			'attrs' => ['class' => 'site-header__github', 'rel' => 'noopener'],
		]) ?>

		<?= $this->insert('components::ui/mobile-nav', [
			'id' => 'site-mobile-nav',
			'label' => 'Main navigation',
			'items' => $navigation,
			'current' => $current,
		]) ?>
	</div>
</header>

<style lang="scss">
	.site-header {
		position: sticky;
		top: 0;
		z-index: 20;
		background: color-mix(in oklch, var(--color-bg) 88%, transparent);
		backdrop-filter: blur(8px);
		border-bottom: 1px solid var(--color-border);
	}

	.site-header__inner {
		display: flex;
		align-items: center;
		gap: 1.25rem;
		min-height: 3.75rem;
	}

	/* Логотип — компонент `ui/wordmark`; шапка отвечает только за то, чтобы он
	   прижимался влево и не сжимался. */
	.site-header__brand {
		flex: none;
		margin-inline-end: auto;
	}

	.site-nav__list {
		display: flex;
		gap: 0.25rem;
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.site-nav__link {
		display: inline-block;
		padding: 0.4rem 0.6rem;
		border-radius: var(--radius-sm);
		color: var(--color-text-muted);
		text-decoration: none;
		font-size: var(--text-sm);
		font-weight: 500;

		&:hover {
			color: var(--color-text);
			background: var(--color-surface-alt);
		}

		/* Текущий раздел — бренд-цветом: акцент оставлен вторичным
		   выделениям (надзаголовки, ссылки), иначе шапка спорит с текстом. */
		&[aria-current='page'] {
			color: var(--color-brand-text);
			background: var(--color-brand-subtle);
		}
	}

	/* Кнопку рисует `components::ui/theme-toggle`; шапка задаёт только
	   положение в ряду — сама кнопка живёт в наборе и на своей странице. */
	.site-header__theme {
		flex: none;
	}

	/* Ссылка на исходники — та же кнопка набора, что и переключатель темы:
	   иначе рядом стояли бы значок без полей и кнопка 40×40. */
	.site-header__github {
		flex: none;
	}

	/* На узком экране шапка переносится: бренд, тема и GitHub остаются в первой
	   строке, поиск уходит во вторую. Иначе форма поиска (у неё на мобильном
	   `max-width: none`) выталкивает кнопки за край и появляется горизонтальная
	   прокрутка. */
	@media (max-width: $bp-md) {
		.site-nav {
			display: none;
		}

		.site-header__inner {
			flex-wrap: wrap;
			row-gap: 0;
			padding-block: var(--space-2);
		}

		.site-header__inner > .search {
			order: 3;
			flex: 1 0 100%;
			max-width: none;
			margin-block-end: var(--space-2);
		}

		/* Подпись под именем делает логотип вдвое шире, и на 390px для кнопки
		   меню не остаётся места — она уезжала на вторую строку отдельно от
		   шапки. На узком экране подпись скрывается, знак и имя остаются. */
		.site-header__brand .ui-wordmark__tagline {
			display: none;
		}
	}
</style>
