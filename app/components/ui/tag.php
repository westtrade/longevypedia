<?php

declare(strict_types=1);

/**
 * Тег — тематика, к которой относится документ.
 *
 * Проектный компонент: `components::ui/tag`. От бейджа отличается ролью: бейдж
 * описывает состояние документа (статус, уверенность), тег — его место в
 * рубрикаторе. Поэтому тег всегда ведёт в каталог, а бейдж — нет.
 *
 * @param string      $label
 * @param string|null $href  ссылка в каталог по тематике
 * @param int|null    $count сколько документов в тематике
 * @param bool        $active текущая тематика фильтра
 * @param array       $attrs
 */

$label ??= '';
$href ??= null;
$count ??= null;
$active ??= false;
$attrs ??= [];

$tag = $href !== null ? 'a' : 'span';
?>
<<?= $tag ?><?= attrs($attrs, ['class' => clsx('ui-tag', ['ui-tag--active' => (bool) $active]), 'href' => $href]) ?>>
	<?= $this->e((string) $label) ?>
	<?php if ($count !== null): ?>
		<span class="ui-tag__count"><?= $this->e((string) $count) ?></span>
	<?php endif; ?>
</<?= $tag ?>>

<style lang="scss">
	.ui-tag {
		display: inline-flex;
		align-items: center;
		gap: var(--space-2);
		padding: 0.2rem var(--space-3);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-full);
		background: var(--color-surface);
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		line-height: var(--leading-table);
		text-decoration: none;
		white-space: nowrap;
		transition:
			border-color 120ms ease,
			color 120ms ease,
			background-color 120ms ease;
	}

	a.ui-tag:hover {
		border-color: var(--color-brand);
		background: var(--color-brand-subtle);
		color: var(--color-brand-text);
	}

	.ui-tag:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}

	.ui-tag--active {
		border-color: var(--color-brand);
		background: var(--color-brand-subtle);
		color: var(--color-brand-text);
		font-weight: var(--weight-medium);
	}

	.ui-tag__count {
		color: var(--color-text-subtle);
		font-variant-numeric: tabular-nums;
	}

	.ui-tag--active .ui-tag__count {
		color: inherit;
	}
</style>
