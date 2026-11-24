<?php

declare(strict_types=1);

/**
 * Результаты каталога: документы, сгруппированные по типу.
 *
 * Компонент один на страницу и на будущий фрагмент: разметка результата живёт
 * в одном месте, поэтому фильтр и полная загрузка не могут разойтись.
 *
 * @var list<\Longevypedia\Content\Document>                        $documents
 * @var array<string, array{label: string, plural: string}>        $types
 * @var string                                                     $query
 */

$documents ??= [];
$types ??= [];
$query ??= '';

$grouped = [];

foreach ($documents as $document) {
	$grouped[$document->type][] = $document;
}
?>
<div class="catalog-results" id="catalog-results">
	<?php if ($documents === []): ?>
		<p class="catalog-results__empty">
			<?php if ($query !== ''): ?>
				Nothing matches “<?= $this->e($query) ?>” with these filters.
			<?php else: ?>
				No documents match these filters yet.
			<?php endif; ?>
			<a href="/catalog">Reset filters</a>
		</p>
	<?php else: ?>
		<p class="catalog-results__count">
			<?= count($documents) ?> <?= count($documents) === 1 ? 'document' : 'documents' ?>
		</p>

		<?php foreach ($grouped as $type => $items): ?>
			<section class="catalog-results__group">
				<h2 class="catalog-results__heading">
					<?= $this->e((string) ($types[$type]['plural'] ?? $type)) ?>
					<span class="catalog-results__badge"><?= count($items) ?></span>
				</h2>
				<div class="catalog-results__grid">
					<?php foreach ($items as $document): ?>
						<?= $this->insert('components::doc/document-card', ['document' => $document, 'showTopics' => true]) ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<style lang="scss">
	.catalog-results__count {
		margin: 0 0 1rem;
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.07em;
		color: var(--color-text-subtle);
	}

	.catalog-results__group + .catalog-results__group {
		margin-top: 2rem;
	}

	.catalog-results__heading {
		display: flex;
		align-items: baseline;
		gap: 0.5rem;
		margin: 0 0 0.75rem;
		font-family: var(--font-text);
		font-size: var(--text-xl);
	}

	.catalog-results__badge {
		font-size: var(--text-xs);
		font-family: var(--font-ui);
		color: var(--color-text-subtle);
		border: 1px solid var(--color-border);
		border-radius: 999px;
		padding: 0.05rem 0.45rem;
	}

	.catalog-results__grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(min(100%, 19rem), 1fr));
		gap: 0.85rem;
	}

	.catalog-results__empty {
		padding: 2rem 1rem;
		border: 1px dashed var(--color-border-strong);
		border-radius: var(--radius-md);
		text-align: center;
		color: var(--color-text-muted);
		font-size: var(--text-sm);

		a {
			display: block;
			margin-top: 0.5rem;
		}
	}
</style>
