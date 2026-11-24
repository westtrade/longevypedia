<?php

declare(strict_types=1);

/**
 * Вкладки-ссылки: переключение разделов адресом, без JavaScript.
 *
 * Это навигация, а не форма: состояние живёт в URL, поэтому вкладку можно
 * открыть в новой вкладке браузера и дать на неё ссылку.
 *
 * @var array<int, array{label: string, url: string, active?: bool, count?: int|null}> $items
 * @var string $label доступное имя списка
 */

$items ??= [];
$label ??= 'Sections';

if ($items === []) {
	return;
}
?>
<nav class="ui-tabs" aria-label="<?= e($label) ?>">
	<ul class="ui-tabs__list">
		<?php foreach ($items as $item): ?>
			<?php $isActive = (bool) ($item['active'] ?? false); ?>
			<li class="ui-tabs__item">
				<a class="ui-tabs__link<?= $isActive ? ' ui-tabs__link--active' : '' ?>"
					href="<?= e((string) ($item['url'] ?? '#')) ?>"
					<?= $isActive ? 'aria-current="page"' : '' ?>>
					<?= e((string) ($item['label'] ?? '')) ?>
					<?php if (isset($item['count']) && $item['count'] !== null): ?>
						<span class="ui-tabs__count"><?= e((string) $item['count']) ?></span>
					<?php endif ?>
				</a>
			</li>
		<?php endforeach ?>
	</ul>
</nav>

<style lang="scss">
	.ui-tabs {
		border-bottom: 1px solid var(--color-border);
		overflow-x: auto;
	}

	.ui-tabs__list {
		display: flex;
		gap: var(--space-1);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.ui-tabs__item {
		flex: none;
	}

	.ui-tabs__link {
		display: inline-flex;
		align-items: center;
		gap: var(--space-2);
		padding: var(--space-2) var(--space-3);
		border-bottom: 2px solid transparent;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		font-weight: var(--weight-medium);
		text-decoration: none;
		white-space: nowrap;
	}

	.ui-tabs__link:hover {
		border-bottom-color: var(--color-border-strong);
		color: var(--color-text);
	}

	.ui-tabs__link--active {
		border-bottom-color: var(--color-brand);
		color: var(--color-brand-text);
	}

	.ui-tabs__link:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring-inset);
	}

	.ui-tabs__count {
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
		font-variant-numeric: tabular-nums;
	}
</style>
