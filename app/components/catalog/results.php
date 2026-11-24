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
		<?= $this->insert('components::ui/empty-state', [
			'icon' => 'search',
			'title' => $query !== '' ? 'Nothing matches that query' : 'No documents match these filters',
			'text' => $query !== ''
				? 'Try a shorter term, or clear the filters to see everything in the catalogue.'
				: 'The catalogue is filtered by type, topic and query. Clearing them shows every published document.',
			'action' => function (): void {
				echo '<a class="ui-button ui-button--outline ui-button--md" href="/catalog">Reset filters</a>';
			},
		]) ?>
	<?php else: ?>
		<p class="catalog-results__count">
			<?= count($documents) ?> <?= count($documents) === 1 ? 'document' : 'documents' ?>
		</p>

		<?php foreach ($grouped as $type => $items): ?>
			<section class="catalog-results__group">
				<h2 class="catalog-results__heading">
					<?= $this->e((string) ($types[$type]['plural'] ?? $type)) ?>
					<?= $this->insert('components::ui/badge', [
						'label' => (string) count($items),
						'variant' => 'outline',
						'size' => 'sm',
					]) ?>
				</h2>
				<div class="catalog-results__grid">
					<?php foreach ($items as $document): ?>
						<?= $this->insert('components::ui/document-card', ['document' => $document, 'showTopics' => true]) ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<style lang="scss">
	.catalog-results__count {
		margin: 0 0 var(--space-4);
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.catalog-results__group + .catalog-results__group {
		margin-top: var(--space-8);
	}

	.catalog-results__heading {
		display: flex;
		align-items: baseline;
		gap: var(--space-2);
		margin: 0 0 var(--space-3);
		font-family: var(--font-serif);
		font-size: var(--text-xl);
	}

	.catalog-results__grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(min(100%, 19rem), 1fr));
		gap: var(--space-3);
	}
</style>
