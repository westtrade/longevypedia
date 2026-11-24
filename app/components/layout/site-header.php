<?php

declare(strict_types=1);

/**
 * Шапка сайта: название, навигация, поиск, переключатель темы.
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
		<a class="site-header__brand" href="/" aria-label="Longevypedia — home">
			<svg class="site-header__mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
				<circle cx="16" cy="16" r="12.5" fill="none" stroke="currentColor" stroke-width="1.6" opacity=".35"/>
				<path d="M16 5.5c5.8 0 10.5 4.7 10.5 10.5S21.8 26.5 16 26.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
				<circle cx="16" cy="16" r="4.2" fill="currentColor"/>
			</svg>
			<span class="site-header__name">Longevypedia</span>
		</a>

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

		<button class="theme-toggle" type="button" data-theme-toggle aria-label="Change colour theme">
			<span class="theme-toggle__icon theme-toggle__icon--sun"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'sun', 'size' => 18]) ?></span>
			<span class="theme-toggle__icon theme-toggle__icon--moon"><?= $this->insert('bolt://ui-kit/icon', ['name' => 'moon', 'size' => 18]) ?></span>
		</button>

		<a class="site-header__github" href="https://github.com/westtrade/longevypedia" rel="noopener">
			<?= $this->insert('bolt://ui-kit/icon', ['name' => 'github', 'size' => 20]) ?>
			<span class="visually-hidden">Source code on GitHub</span>
		</a>
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

	.site-header__brand {
		display: inline-flex;
		align-items: center;
		gap: 0.55rem;
		color: var(--color-text);
		text-decoration: none;
		font-family: var(--font-serif);
		font-size: 1.15rem;
		font-weight: 600;
		letter-spacing: -0.01em;
		margin-inline-end: auto;
	}

	.site-header__mark {
		width: 1.75rem;
		height: 1.75rem;
		color: var(--color-accent);
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

		&[aria-current='page'] {
			color: var(--color-accent);
			background: var(--color-accent-subtle);
		}
	}

	.theme-toggle {
		display: inline-flex;
		flex: none;
		align-items: center;
		justify-content: center;
		width: 2.25rem;
		height: 2.25rem;
		padding: 0;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface);
		color: var(--color-text-muted);
		cursor: pointer;

		&:hover {
			color: var(--color-text);
			border-color: var(--color-border-strong);
		}
	}

	/* Иконка показывает, куда переключимся: в светлой теме — луна, в тёмной —
	   солнце. Тема либо задана атрибутом, либо приходит из системы. */
	.theme-toggle__icon {
		display: inline-flex;
	}

	.theme-toggle__icon--sun {
		display: none;
	}

	@media (prefers-color-scheme: dark) {
		:root:not([data-theme]) .theme-toggle__icon--sun {
			display: inline-flex;
		}

		:root:not([data-theme]) .theme-toggle__icon--moon {
			display: none;
		}
	}

	:root[data-theme='dark'] .theme-toggle__icon--sun {
		display: inline-flex;
	}

	:root[data-theme='dark'] .theme-toggle__icon--moon {
		display: none;
	}

	.site-header__github {
		display: inline-flex;
		flex: none;
		color: var(--color-text-muted);

		&:hover {
			color: var(--color-text);
		}
	}

	/* На узком экране шапка переносится: бренд, тема и GitHub остаются в первой
	   строке, поиск уходит во вторую. Иначе форма поиска (у неё на мобильном
	   `max-width: none`) выталкивает кнопки за край и появляется горизонтальная
	   прокрутка. */
	@media (max-width: 48rem) {
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
	}
</style>
