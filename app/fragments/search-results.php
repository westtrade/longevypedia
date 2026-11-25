<?php

declare(strict_types=1);

/**
 * Подсказки поиска — HTMX-фрагмент.
 *
 * Рендерится без каркаса страницы: ядро BOLT при `?_fragments=search-results`
 * отдаёт только этот шаблон. Параметры приходят из строки запроса, потому что
 * у фрагмента нет данных страницы; если его вставляют из шаблона, `$q` можно
 * передать параметром.
 *
 * Пустой запрос даёт пустую строку: контейнер в форме скрыт через `:empty`,
 * так что «ничего не найдено» показывается только для непустого запроса.
 *
 * @var string $q поисковый запрос
 */

$q ??= trim((string) \Bolt\Core\Http\Request::fromGlobals()->input('q', ''));

if ($q === '') {
	return;
}

$repository = longevypedia_content();
$hits = array_slice($repository->search($q, ['locale' => $repository->defaultLocale()]), 0, 6);
$total = count($hits) === 6 ? '6+' : (string) count($hits);
?>
<?php if ($hits === []): ?>
	<?= $this->insert('components::ui/empty-state', [
		'title' => 'Nothing matches “' . $this->e($q) . '”',
		'text' => 'Try a broader term, or browse the catalogue.',
		'icon' => 'search',
		'action' => $this->fetch('components::ui/button', [
			'label' => 'Open the catalogue',
			'variant' => 'secondary',
			'size' => 'sm',
			'href' => '/catalog',
		]),
	]) ?>
<?php else: ?>
	<ul class="search-hits">
		<?php foreach ($hits as $hit): ?>
			<?php $document = $hit->document; ?>
			<li class="search-hits__item">
				<a class="search-hits__link" href="<?= $this->e($document->url) ?>">
					<span class="search-hits__title"><?= $this->e($document->title()) ?></span>
					<span class="search-hits__meta"><?= $this->e($document->typeLabel()) ?></span>
					<?php if ($hit->snippet !== ''): ?>
						<span class="search-hits__snippet"><?= $hit->highlighted() ?></span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<a class="search-hits__all" href="/search?q=<?= rawurlencode($q) ?>">
		See all results <span class="search-hits__count">(<?= $total ?>)</span>
	</a>
<?php endif; ?>

<style lang="scss">
	.search-hits {
		margin: 0;
		padding: 0.35rem;
		list-style: none;
		max-height: min(70vh, 26rem);
		overflow-y: auto;
	}

	.search-hits__item + .search-hits__item {
		border-top: 1px solid var(--color-border);
	}

	.search-hits__link {
		display: grid;
		gap: 0.15rem;
		padding: 0.55rem 0.65rem;
		border-radius: var(--radius-sm);
		text-decoration: none;
		color: var(--color-text);

		&:hover,
		&:focus-visible {
			background: var(--color-surface-alt);
		}
	}

	.search-hits__title {
		font-weight: 600;
		font-size: var(--text-sm);
	}

	.search-hits__meta {
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
		text-transform: uppercase;
		letter-spacing: 0.06em;
	}

	.search-hits__snippet {
		font-size: var(--text-xs);
		color: var(--color-text-muted);
		line-height: 1.45;
	}

	.search-hits mark {
		background: var(--color-accent-subtle);
		color: var(--color-accent-text);
		border-radius: 2px;
		padding-inline: 1px;
	}

	.search-hits__all {
		display: block;
		padding: 0.6rem 0.75rem;
		border-top: 1px solid var(--color-border);
		font-size: var(--text-sm);
		font-weight: 500;
		text-decoration: none;
	}

	.search-hits__count {
		color: var(--color-text-subtle);
	}

</style>
