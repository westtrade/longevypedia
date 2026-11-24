<?php

declare(strict_types=1);

/**
 * Хлебные крошки страницы.
 *
 * @var list<array{label: string, url?: ?string}> $items
 */

$items ??= [];
?>
<?php if ($items !== []): ?>
	<nav class="breadcrumbs" aria-label="Breadcrumb">
		<ol class="breadcrumbs__list">
			<?php foreach ($items as $index => $item): ?>
				<?php $isLast = $index === array_key_last($items); ?>
				<li class="breadcrumbs__item">
					<?php if (!$isLast && !empty($item['url'])): ?>
						<a class="breadcrumbs__link" href="<?= $this->e($item['url']) ?>"><?= $this->e($item['label']) ?></a>
					<?php else: ?>
						<span class="breadcrumbs__current" aria-current="page"><?= $this->e($item['label']) ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
<?php endif; ?>

<style lang="scss">
	.breadcrumbs {
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.breadcrumbs__list {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.4rem;
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.breadcrumbs__item {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;

		& + &::before {
			content: '/';
			color: var(--color-border-strong);
		}
	}

	.breadcrumbs__link {
		color: var(--color-text-muted);
		text-decoration: none;

		&:hover {
			color: var(--color-accent);
		}
	}

	.breadcrumbs__current {
		color: var(--color-text);
	}
</style>
