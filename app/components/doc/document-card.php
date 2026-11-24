<?php

declare(strict_types=1);

/**
 * Карточка документа в списках: каталог, главная, связанные материалы.
 *
 * @var \Longevypedia\Content\Document $document
 * @var bool                           $showType
 * @var bool                           $showSummary
 * @var bool                           $showTopics
 */

$showType ??= true;
$showSummary ??= true;
$showTopics ??= false;

$updated = $document->updated();
?>
<article class="doc-card">
	<?php if ($showType || $updated !== null): ?>
		<p class="doc-card__meta">
			<?php if ($showType): ?>
				<span class="doc-card__type"><?= $this->e($document->typeLabel()) ?></span>
			<?php endif; ?>
			<?php if ($updated !== null): ?>
				<time class="doc-card__date" datetime="<?= $this->e($updated) ?>"><?= $this->e(longevypedia_date($updated)) ?></time>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<h3 class="doc-card__title">
		<a class="doc-card__link" href="<?= $this->e($document->url) ?>"><?= $this->e($document->title()) ?></a>
	</h3>

	<?php if ($showSummary && $document->summary() !== ''): ?>
		<p class="doc-card__summary"><?= $this->e($document->summary()) ?></p>
	<?php endif; ?>

	<?php if ($showTopics && $document->topics() !== []): ?>
		<ul class="doc-card__topics">
			<?php foreach ($document->topics() as $topic): ?>
				<li class="doc-card__topic">
					<a href="/catalog?topic=<?= rawurlencode($topic) ?>"><?= $this->e($topic) ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</article>

<style lang="scss">
	.doc-card {
		position: relative;
		display: flex;
		flex-direction: column;
		gap: 0.35rem;
		padding: 1rem 1.1rem;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);

		&:hover {
			border-color: var(--color-accent);
		}
	}

	.doc-card__meta {
		display: flex;
		align-items: baseline;
		gap: 0.6rem;
		margin: 0;
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.doc-card__type {
		text-transform: uppercase;
		letter-spacing: 0.07em;
		font-weight: 600;
		color: var(--color-accent);
	}

	.doc-card__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		font-weight: 600;
		line-height: 1.25;
	}

	.doc-card__link {
		color: var(--color-text);
		text-decoration: none;

		&:hover {
			color: var(--color-accent);
		}

		/* Ссылка растягивается на карточку: кликабельна вся площадь,
		   других интерактивных элементов внутри нет. */
		&::after {
			content: '';
			position: absolute;
			inset: 0;
		}
	}

	.doc-card__summary {
		margin: 0;
		font-size: var(--text-sm);
		color: var(--color-text-muted);
		line-height: 1.55;
	}

	.doc-card__topics {
		display: flex;
		flex-wrap: wrap;
		gap: 0.35rem;
		margin: 0.25rem 0 0;
		padding: 0;
		list-style: none;
	}

	.doc-card__topic a {
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
		text-decoration: none;
		border-bottom: 1px dotted var(--color-border-strong);

		&:hover {
			color: var(--color-accent);
		}
	}
</style>
