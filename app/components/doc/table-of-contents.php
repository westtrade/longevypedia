<?php

declare(strict_types=1);

/**
 * Содержание статьи — навигация по заголовкам второго–четвёртого уровня.
 *
 * Список строит `MarkdownRenderer`: он же разрешает дубли id (`results`,
 * `results-2`), поэтому ссылки здесь всегда совпадают с якорями в тексте.
 *
 * @var list<array{level: int, id: string, text: string}> $toc
 * @var string                                            $title
 */

$toc ??= [];
$title ??= 'On this page';
?>
<?php if ($toc !== []): ?>
	<nav class="doc-toc" aria-labelledby="doc-toc-title">
		<p class="doc-toc__title" id="doc-toc-title"><?= $this->e($title) ?></p>
		<ol class="doc-toc__list">
			<?php foreach ($toc as $entry): ?>
				<li class="doc-toc__item" style="--doc-toc-level: <?= max(0, $entry['level'] - 2) ?>">
					<a class="doc-toc__link" href="#<?= $this->e($entry['id']) ?>"><?= $this->e($entry['text']) ?></a>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
<?php endif; ?>

<style lang="scss">
	.doc-toc {
		font-size: var(--text-sm);
	}

	.doc-toc__title {
		margin: 0 0 0.5rem;
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: var(--color-text-subtle);
	}

	.doc-toc__list {
		margin: 0;
		padding: 0;
		list-style: none;
		border-inline-start: 1px solid var(--color-border);
	}

	.doc-toc__item {
		padding-inline-start: calc(0.7rem + var(--doc-toc-level, 0) * 0.85rem);
		line-height: 1.4;
	}

	.doc-toc__link {
		display: block;
		padding-block: 0.22rem;
		color: var(--color-text-muted);
		text-decoration: none;

		&:hover {
			color: var(--color-accent);
		}
	}
</style>
