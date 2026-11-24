<?php

declare(strict_types=1);

/**
 * Постраничная навигация: обычные ссылки, страница живёт в адресе.
 *
 * Номера считаются здесь, а не в шаблоне страницы: окно вокруг текущей
 * страницы и «…» на разрывах — одно правило на весь проект.
 *
 * @var int $current текущая страница, с 1
 * @var int $total всего страниц
 * @var string|callable(int): string $href адрес страницы или функция от номера
 * @var string $label доступное имя навигации
 */

$current ??= 1;
$total ??= 1;
$href ??= '';
$label ??= 'Pagination';

$current = max(1, min($current, max(1, $total)));
$total = max(1, $total);

if ($total < 2) {
	return;
}

$url = static fn (int $page): string => is_callable($href) ? (string) $href($page) : (string) $href . $page;

$pages = [];

for ($page = 1; $page <= $total; $page++) {
	$inWindow = abs($page - $current) <= 2;
	$isEdge = $page === 1 || $page === $total;

	if (!$inWindow && !$isEdge) {
		continue;
	}

	$pages[] = $page;
}

$gaps = [];

foreach ($pages as $index => $page) {
	if ($index > 0 && $page - $pages[$index - 1] > 1) {
		$gaps[] = $page;
	}
}
?>
<nav class="ui-pagination" aria-label="<?= e($label) ?>">
	<?php if ($current > 1): ?>
		<a class="ui-pagination__step" href="<?= e($url($current - 1)) ?>" rel="prev">Previous</a>
	<?php else: ?>
		<span class="ui-pagination__step ui-pagination__step--disabled" aria-disabled="true">Previous</span>
	<?php endif ?>

	<ol class="ui-pagination__list">
		<?php foreach ($pages as $index => $page): ?>
			<?php if ($index > 0 && $page - $pages[$index - 1] > 1): ?>
				<li class="ui-pagination__gap" aria-hidden="true">…</li>
			<?php endif ?>
			<li>
				<?php if ($page === $current): ?>
					<span class="ui-pagination__link ui-pagination__link--active" aria-current="page"><?= e((string) $page) ?></span>
				<?php else: ?>
					<a class="ui-pagination__link" href="<?= e($url($page)) ?>"><?= e((string) $page) ?></a>
				<?php endif ?>
			</li>
		<?php endforeach ?>
	</ol>

	<?php if ($current < $total): ?>
		<a class="ui-pagination__step" href="<?= e($url($current + 1)) ?>" rel="next">Next</a>
	<?php else: ?>
		<span class="ui-pagination__step ui-pagination__step--disabled" aria-disabled="true">Next</span>
	<?php endif ?>
</nav>

<style lang="scss">
	.ui-pagination {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: var(--space-3);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
	}

	.ui-pagination__list {
		display: flex;
		align-items: center;
		gap: 2px;
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.ui-pagination__link,
	.ui-pagination__step {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 2.25rem;
		min-height: 2.25rem;
		padding-inline: var(--space-2);
		border: 1px solid transparent;
		border-radius: var(--radius-sm);
		color: var(--color-text-muted);
		text-decoration: none;
		font-variant-numeric: tabular-nums;
	}

	.ui-pagination__link:hover,
	.ui-pagination__step:hover {
		border-color: var(--color-border);
		background: var(--color-surface-alt);
		color: var(--color-text);
	}

	.ui-pagination__link--active {
		border-color: var(--color-brand);
		background: var(--color-brand-subtle);
		color: var(--color-brand-text);
		font-weight: var(--weight-semibold);
	}

	.ui-pagination__step--disabled {
		color: var(--color-text-subtle);
		cursor: not-allowed;
	}

	.ui-pagination__gap {
		padding-inline: var(--space-1);
		color: var(--color-text-subtle);
	}

	.ui-pagination__link:focus-visible,
	.ui-pagination__step:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}
</style>
