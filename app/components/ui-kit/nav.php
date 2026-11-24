<?php

declare(strict_types=1);

/**
 * Боковое меню UI-набора.
 *
 * Единственный источник пунктов — `data/ui-kit.php`: компонент не перечисляет
 * страницы сам, иначе список разошёлся бы с каталогом.
 *
 * @var array<int, array{title?: string, items?: array<int, array<string, mixed>>}> $groups
 * @var string|null $active slug текущей страницы — для aria-current и подсветки
 */

$groups ??= [];
$active ??= null;

if ($groups === []) {
	return;
}
?>
<nav class="ui-kit-nav" aria-label="Разделы UI-набора">
	<?php foreach ($groups as $group): ?>
		<?php $items = (array) ($group['items'] ?? []); ?>
		<?php if ($items === []) {
			continue;
		} ?>
		<p class="ui-kit-nav__title"><?= e((string) ($group['title'] ?? '')) ?></p>
		<ul class="ui-kit-nav__list">
			<?php foreach ($items as $item): ?>
				<?php $slug = (string) ($item['slug'] ?? ''); ?>
				<?php if ($slug === '') {
					continue;
				} ?>
				<?php $isActive = $slug === $active; ?>
				<li>
					<a class="ui-kit-nav__link<?= $isActive ? ' ui-kit-nav__link--active' : '' ?>"
						href="/ui-kit/<?= e($slug) ?>"
						<?= $isActive ? 'aria-current="page"' : '' ?>><?= e((string) ($item['title'] ?? $slug)) ?></a>
				</li>
			<?php endforeach ?>
		</ul>
	<?php endforeach ?>
</nav>

<style lang="scss">
	.ui-kit-nav {
		display: flex;
		flex-direction: column;
		gap: var(--space-5);
		font-size: var(--text-sm);
	}

	.ui-kit-nav__title {
		margin: 0 0 var(--space-2);
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-kit-nav__list {
		display: flex;
		flex-direction: column;
		gap: 2px;
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.ui-kit-nav__link {
		display: block;
		padding: var(--space-1) var(--space-3);
		border-left: 2px solid transparent;
		border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
		color: var(--color-text-muted);
		text-decoration: none;
	}

	.ui-kit-nav__link:hover {
		background: var(--color-surface-alt);
		color: var(--color-text);
	}

	.ui-kit-nav__link--active {
		border-left-color: var(--color-brand);
		background: var(--color-brand-subtle);
		color: var(--color-brand-text);
		font-weight: var(--weight-medium);
	}

	.ui-kit-nav__link:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}
</style>
