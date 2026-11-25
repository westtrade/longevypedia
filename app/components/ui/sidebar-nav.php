<?php

declare(strict_types=1);

/**
 * Боковая навигация: содержание статьи, разделы страницы, список разделов.
 *
 * Плоский режим (`items`) — содержание статьи и якоря. Режим групп (`groups`) —
 * каталог разделов, где у каждой группы своя подпись: так устроено меню
 * UI-набора, и отдельного компонента для него не нужно.
 *
 * @var string|null $title заголовок блока в плоском режиме
 * @var array<int, array{label: string, url: string, active?: bool, level?: int}> $items
 * @var array<int, array{title?: string|null, items: array<int, array{label: string, url: string, active?: bool}>}> $groups
 * @var bool $sticky прилипает при прокрутке
 */

$title ??= null;
$items ??= [];
$groups ??= [];
$sticky ??= false;

if ($groups !== []) {
	$sections = array_values(array_filter(
		array_map(static function (array $group): array {
			return [
				'title' => $group['title'] ?? null,
				'items' => (array) ($group['items'] ?? []),
			];
		}, $groups),
		static fn (array $group): bool => $group['items'] !== [],
	));
	$label = $title;
} elseif ($items !== []) {
	$sections = [['title' => $title, 'items' => $items]];
	$label = $title;
} else {
	return;
}

if ($sections === []) {
	return;
}

$isGrouped = $groups !== [];
?>
<nav class="ui-sidebar-nav<?= $sticky ? ' ui-sidebar-nav--sticky' : '' ?><?= $isGrouped ? ' ui-sidebar-nav--grouped' : '' ?>"<?= $label !== null ? ' aria-label="' . e($label) . '"' : '' ?>>
	<?php foreach ($sections as $section): ?>
		<div class="ui-sidebar-nav__group">
			<?php if (($section['title'] ?? null) !== null): ?>
				<p class="ui-sidebar-nav__title"><?= e((string) $section['title']) ?></p>
			<?php endif ?>
			<ul class="ui-sidebar-nav__list">
				<?php foreach ($section['items'] as $item): ?>
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
		</div>
	<?php endforeach ?>
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

	/* Группы разделов: подпись + список, промежуток между группами больше,
	   чем между пунктами внутри. */
	.ui-sidebar-nav--grouped {
		display: flex;
		flex-direction: column;
		gap: var(--space-5);
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__list {
		border-left: 0;
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__link {
		margin-left: 0;
		border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__link--active {
		background: var(--color-brand-subtle);
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__link:hover {
		background: var(--color-surface-alt);
		border-left-color: transparent;
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
