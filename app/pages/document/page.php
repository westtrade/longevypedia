<?php

declare(strict_types=1);

/**
 * Страница документа: статья, исследование, утверждение, сущность.
 *
 * Один шаблон на все типы: общий каркас (заголовок, содержание, источники,
 * связи) одинаков, а различия вынесены в компоненты `ui/research-metadata` и
 * `doc/claim-fields`. Дублировать страницу на каждый тип пришлось бы потому,
 * что у исследования и утверждения разные поля, — но не разную вёрстку.
 *
 * @var array  $route   метаданные маршрута: type, locale
 * @var string $slug
 * @var \Bolt\Core\Http\Request $request
 */

$request ??= \Bolt\Core\Http\Request::fromGlobals();

$repository = longevypedia_content();
$locale = (string) ($route['locale'] ?? $repository->defaultLocale());
$type = (string) ($route['type'] ?? '');
$slug = (string) ($slug ?? '');

$document = $repository->find($type, $slug, $locale);

if ($document === null) {
	throw new \Bolt\Core\Exception\PageNotFoundException($request->path());
}

$repositoryUrl = (string) ($config['app']['repository'] ?? 'https://github.com/westtrade/longevypedia');
$branch = (string) ($config['app']['branch'] ?? 'main');
$sourceUrl = $repositoryUrl . '/blob/' . $branch . '/' . $document->repositoryPath();
$historyUrl = $repositoryUrl . '/commits/' . $branch . '/' . $document->repositoryPath();

$claimMeta = (array) $document->metaValue('claim', []);
$studyMeta = (array) $document->metaValue('study', []);

$relations = $repository->related($document);
$backlinks = $repository->backlinks($document);
$groups = array_merge($relations, $backlinks);

// Связанные утверждения раскладываются по направлению доказательств: это
// отдельный блок, потому что «подтверждает» и «противоречит» — не ярлык связи,
// а собственное поле утверждения.
$evidence = $repository->evidenceGroups($document);

$schema = match ($document->type) {
	'claim' => [
		'@context' => 'https://schema.org',
		'@type' => 'Claim',
		'name' => $document->title(),
		'text' => (string) ($claimMeta['statement'] ?? $document->summary()),
		'url' => $document->url,
	],
	'research' => [
		'@context' => 'https://schema.org',
		'@type' => 'ScholarlyArticle',
		'name' => $document->title(),
		'description' => $document->summary(),
		'url' => $document->url,
		'datePublished' => (string) ($studyMeta['year'] ?? ''),
		'identifier' => (string) ($studyMeta['doi'] ?? ''),
	],
	default => [
		'@context' => 'https://schema.org',
		'@type' => 'Article',
		'name' => $document->title(),
		'description' => $document->summary(),
		'url' => $document->url,
		'dateModified' => (string) $document->updated(),
	],
};

$this->layout('components::layout/site-layout', [
	'title' => $document->title(),
	'description' => $document->summary(),
	'canonical' => $document->url,
	'ogType' => 'article',
	'schema' => $schema,
	'current' => 'catalog',
]);

$this->start('page');
?>
<div class="container doc-page">
	<?= $this->insert('components::ui/breadcrumbs', [
		'items' => [
			['label' => 'Knowledge', 'url' => '/catalog'],
			['label' => $document->typeLabel(), 'url' => '/' . \Longevypedia\Content\DocumentTypes::dir($document->type) . '/'],
			['label' => $document->title()],
		],
		'label' => 'Breadcrumb',
	]) ?>

	<div class="doc-page__grid">
		<article class="doc">
			<header class="doc__header">
				<?php
				$metaItems = [];

				if ($document->updated() !== null) {
					$metaItems[] = [
						'label' => 'Updated ' . longevypedia_date($document->updated()),
						'datetime' => (string) $document->updated(),
					];
				}

				$metaItems[] = ['label' => $document->readingMinutes() . ' min read'];
				?>
				<?= $this->insert('components::ui/meta', [
					'eyebrow' => $document->typeLabel(),
					'items' => $metaItems,
					'attrs' => ['class' => 'doc__meta'],
				]) ?>

				<h1 class="doc__title"><?= $this->e($document->title()) ?></h1>

				<?php if ($document->summary() !== ''): ?>
					<p class="doc__summary"><?= $this->e($document->summary()) ?></p>
				<?php endif; ?>

				<?php if ($document->topics() !== []): ?>
					<ul class="doc__topics">
						<?php foreach ($document->topics() as $topic): ?>
							<li>
								<?= $this->insert('components::ui/tag', [
									'label' => $repository->topics($locale)[$topic]['label'] ?? $topic,
									'href' => '/catalog?topic=' . rawurlencode($topic),
								]) ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</header>

			<div class="doc__body">
				<?php if ($document->type === 'claim'): ?>
					<?= $this->insert('components::doc/claim-fields', ['claim' => $claimMeta]) ?>
				<?php endif; ?>

				<?php if ($document->type === 'research'): ?>
					<?= $this->insert('components::ui/research-metadata', [
						'study' => $studyMeta,
						'isExample' => (bool) ($studyMeta['example'] ?? false),
						'id' => 'study',
					]) ?>
				<?php endif; ?>

				<div class="prose">
					<?= $document->html ?>
				</div>

				<?= $this->insert('components::ui/source-list', [
					'sources' => $document->sources(),
					'title' => 'Sources',
				]) ?>

				<?= $this->insert('components::ui/evidence-summary', [
					'groups' => $evidence,
					'title' => 'Related claims',
					'id' => 'related-claims',
					'headingLevel' => 2,
				]) ?>

				<?= $this->insert('components::doc/relation-groups', [
					'groups' => $groups,
					'title' => 'Connections',
				]) ?>

				<footer class="doc__footer">
					<p>
						<a class="doc__source-link" href="<?= $this->e($sourceUrl) ?>" rel="noopener">
							View the source Markdown on GitHub
						</a>
					</p>
					<p>
						<a class="doc__source-link" href="<?= $this->e($historyUrl) ?>" rel="noopener">
							Change history
						</a>
					</p>
					<?= $this->insert('components::ui/meta', [
						'items' => [
							['label' => (string) $document->license()],
							['label' => $document->wordCount() . ' words'],
						],
					]) ?>
				</footer>
			</div>
		</article>

		<aside class="doc-page__aside">
			<?php
			// Содержание — тот же боковой список, что и в наборе: отдельного
			// компонента для оглавления не нужно, различается только источник.
			$tocItems = array_map(static fn (array $entry): array => [
				'label' => $entry['text'],
				'url' => '#' . $entry['id'],
				'level' => max(0, $entry['level'] - 2),
			], $document->toc);
			?>
			<?= $this->insert('components::ui/sidebar-nav', [
				'title' => 'On this page',
				'items' => $tocItems,
				'sticky' => true,
			]) ?>

			<?php if ($document->aliases() !== []): ?>
				<div class="doc-page__aliases">
					<p class="doc-page__aliases-title">Also known as</p>
					<p><?= $this->e(implode(', ', $document->aliases())) ?></p>
				</div>
			<?php endif; ?>
		</aside>
	</div>
</div>

<style lang="scss">
	.doc-page {
		padding-block: 1.75rem 0;
	}

	.doc-page__grid {
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 16rem);
		gap: 2.5rem;
		align-items: start;
		margin-top: 1rem;
	}

	/* Блоки статьи идут друг за другом без собственных внешних отступов:
	   вертикальный ритм задаёт колонка, иначе источники прилипают к последнему
	   абзацу, а связи — к сводке утверждений. */
	.doc__body {
		display: flex;
		flex-direction: column;
		gap: var(--space-8);
	}

	.doc__header {
		margin-bottom: var(--space-8);
	}

	.doc__meta {
		margin: 0 0 var(--space-3);
	}

	.doc__title {
		margin: 0 0 0.6rem;
		font-family: var(--font-serif);
		font-size: clamp(1.9rem, 1.3rem + 2.2vw, 2.7rem);
		line-height: 1.15;
		letter-spacing: -0.015em;
	}

	.doc__summary {
		margin: 0 0 0.9rem;
		max-width: var(--layout-measure);
		font-size: var(--text-lg);
		line-height: 1.5;
		color: var(--color-text-muted);
	}

	.doc__topics {
		display: flex;
		flex-wrap: wrap;
		gap: 0.4rem;
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.doc .claim,
	.doc .study {
		margin-bottom: 1.75rem;
	}

	.doc__footer {
		margin-top: 2.5rem;
		padding-top: 1rem;
		border-top: 1px solid var(--color-border);
		font-size: var(--text-xs);
		color: var(--color-text-subtle);

		p {
			display: flex;
			flex-wrap: wrap;
			gap: 0.4rem;
			margin: 0.3rem 0;
		}
	}

	.doc__source-link {
		color: var(--color-text-muted);
	}

	.doc-page__aside {
		position: sticky;
		top: 5rem;
		display: grid;
		gap: 1.5rem;
		font-size: var(--text-sm);
	}

	.doc-page__aliases-title {
		margin: 0 0 0.2rem;
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.07em;
		color: var(--color-text-subtle);
	}

	.doc-page__aliases p:last-child {
		margin: 0;
		color: var(--color-text-muted);
	}

	@media (max-width: $bp-xl) {
		.doc-page__grid {
			grid-template-columns: minmax(0, 1fr);
			gap: 1.5rem;
		}

		/* На узком экране оглавление уходит под статью: над заголовком оно
		   читается как «страница началась не с названия». */
		.doc-page__aside {
			position: static;
			order: 1;
			border-top: 1px solid var(--color-border);
			padding-top: 1rem;
		}
	}
</style>
<?php $this->stop('page'); ?>
