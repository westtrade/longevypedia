<?php

declare(strict_types=1);

/**
 * Боковая навигация: содержание статьи, разделы страницы, список разделов.
 *
 * @var string|null $title заголовок блока
 * @var array<int, array{label: string, url: string, active?: bool, level?: int}> $items
 * @var bool $sticky прилипает при прокрутке
 */

$title ??= null;
$items ??= [];
$sticky ??= false;

if ($items === []) {
	return;
}
?>
<nav class="ui-sidebar-nav<?= $sticky ? ' ui-sidebar-nav--sticky' : '' ?>"<?= $title !== null ? ' aria-label="' . e($title) . '"' : '' ?>>
	<?php if ($title !== null): ?>
		<p class="ui-sidebar-nav__title"><?= e($title) ?></p>
	<?php endif ?>
	<ul class="ui-sidebar-nav__list">
		<?php foreach ($items as $item): ?>
			<?php
			$isActive = (bool) ($item['active'] ?? false);
			$level = max(0, (int) ($item['level'] ?? 0));
			?>
			<li class="ui-sidebar-nav__item">
				<a class="ui-sidebar-nav__link<?= $isActive ? ' ui-sidebar-nav__link--active' : '' ?>"
					href="<?= e((string) ($item['url'] ?? '#')) ?>"
					style="--ui-sidebar-nav-level: <?= e((string) $level) ?>"
					<?= $isActive ? 'aria-current="true"' : '' ?>><?= e((string) ($item['label'] ?? '')) ?></a>
			</li>
		<?php endforeach ?>
	</ul>
</nav>

<style lang="scss">
	.ui-sidebar-nav--sticky {
		position: sticky;
		top: calc(var(--layout-header) + var(--space-4));
		align-self: start;
		max-height: calc(100vh - var(--layout-header) - var(--space-8));
		overflow-y: auto;
	}

	.ui-sidebar-nav__title {
		margin: 0 0 var(--space-2);
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-sidebar-nav__list {
		display: flex;
		flex-direction: column;
		gap: 1px;
		margin: 0;
		padding: 0;
		list-style: none;
		border-left: 1px solid var(--color-border);
	}

	.ui-sidebar-nav__link {
		display: block;
		padding: var(--space-1) var(--space-3);
		padding-left: calc(var(--space-3) + var(--ui-sidebar-nav-level, 0) * var(--space-3));
		border-left: 2px solid transparent;
		margin-left: -1px;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
		text-decoration: none;
	}

	.ui-sidebar-nav__link:hover {
		border-left-color: var(--color-border-strong);
		color: var(--color-text);
	}

	.ui-sidebar-nav__link--active {
		border-left-color: var(--color-brand);
		color: var(--color-brand-text);
		font-weight: var(--weight-medium);
	}

	.ui-sidebar-nav__link:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}
</style>
