<?php

declare(strict_types=1);

/**
 * Хлебные крошки.
 *
 * Последний пункт — текущая страница: он не ссылка и помечен `aria-current`.
 * Разделитель рисует CSS, поэтому в разметке нет текстовых «/» между пунктами.
 *
 * @var array<int, array{label: string, url?: string|null}> $items путь от корня к текущей странице
 * @var string $label доступное имя навигации
 */

$items ??= [];
$label ??= 'Breadcrumb';

if ($items === []) {
	return;
}

$last = array_key_last($items);
?>
<nav class="ui-breadcrumbs" aria-label="<?= e($label) ?>">
	<ol class="ui-breadcrumbs__list">
		<?php foreach ($items as $index => $item): ?>
			<?php $url = $item['url'] ?? null; ?>
			<li class="ui-breadcrumbs__item">
				<?php if ($url !== null && $index !== $last): ?>
					<a class="ui-breadcrumbs__link" href="<?= e((string) $url) ?>"><?= e((string) ($item['label'] ?? '')) ?></a>
				<?php else: ?>
					<span class="ui-breadcrumbs__current" aria-current="page"><?= e((string) ($item['label'] ?? '')) ?></span>
				<?php endif ?>
			</li>
		<?php endforeach ?>
	</ol>
</nav>

<style lang="scss">
	.ui-breadcrumbs__list {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: var(--space-1);
		margin: 0;
		padding: 0;
		list-style: none;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-breadcrumbs__item {
		display: inline-flex;
		align-items: center;
		gap: var(--space-1);
	}

	.ui-breadcrumbs__item + .ui-breadcrumbs__item::before {
		content: '/';
		color: var(--color-text-subtle);
	}

	.ui-breadcrumbs__link {
		color: var(--color-text-muted);
		text-decoration: none;
	}

	.ui-breadcrumbs__link:hover {
		color: var(--color-brand);
		text-decoration: underline;
		text-underline-offset: 0.18em;
	}

	.ui-breadcrumbs__current {
		color: var(--color-text);
		font-weight: var(--weight-medium);
	}
</style>
