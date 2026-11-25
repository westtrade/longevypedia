<?php

declare(strict_types=1);

/**
 * Главная страница.
 *
 * Собирается из того, что реально есть в корпусе: числа, тематики, последние
 * обновления и самые связанные документы. Никаких заранее вписанных счётчиков
 * — при пустом корпусе страница честно покажет нули и подскажет, с чего начать.
 *
 * @var array $route
 */

$repository = longevypedia_content();
$locale = (string) ($route['locale'] ?? $repository->defaultLocale());

$stats = $repository->stats($locale);
$topics = $repository->topics($locale);
$recent = $repository->recent(4, $locale);

// Колонки стоят рядом, поэтому документ, попавший в «обновлённые», не должен
// повторяться в «связанных»: иначе первый экран показывает одно и то же дважды.
$recentKeys = array_map(static fn ($document): string => $document->key(), $recent);
$popular = $repository->popular(4, $locale, $recentKeys);

$repositoryUrl = (string) ($config['app']['repository'] ?? 'https://github.com/westtrade/longevypedia');

$this->layout('components::layout/site-layout', [
	'title' => 'Longevypedia — open knowledge on longevity and aging',
	'description' => 'An open, structured and verifiable knowledge base about longevity, the biology of aging and healthspan. Every claim links to its sources and to the file it came from.',
	'canonical' => '/',
	'current' => '',
	// Класс на <body>: главной нужен hero во всю ширину, примыкающий к шапке,
	// поэтому поля .page__main снимаются только на этой странице.
	'bodyClass' => 'page--home',
]);
?>
<?php $this->start('page'); ?>
<div class="home">
	<section class="home-hero">
		<div class="container home-hero__inner">
			<p class="home-hero__eyebrow">Open scientific encyclopedia</p>
			<h1 class="home-hero__title">Longevypedia</h1>
			<p class="home-hero__lead">
				A structured, verifiable knowledge base about longevity, the biology of aging
				and healthspan — mechanisms, genes, compounds, biomarkers, studies and the
				claims they support.
			</p>

			<div class="home-hero__search">
				<?= $this->insert('components::site/search-form', ['id' => 'hero-search']) ?>
			</div>

			<dl class="home-hero__stats">
				<div class="home-hero__stat">
					<dt><?= (int) $stats['total'] === 1 ? 'Document' : 'Documents' ?></dt>
					<dd><?= $stats['total'] ?></dd>
				</div>
				<div class="home-hero__stat">
					<dt><?= (int) $stats['topics'] === 1 ? 'Topic' : 'Topics' ?></dt>
					<dd><?= $stats['topics'] ?></dd>
				</div>
				<div class="home-hero__stat">
					<dt><?= (int) $stats['studies'] === 1 ? 'Study' : 'Studies' ?></dt>
					<dd><?= $stats['studies'] ?></dd>
				</div>
				<div class="home-hero__stat">
					<dt><?= (int) $stats['claims'] === 1 ? 'Claim' : 'Claims' ?></dt>
					<dd><?= $stats['claims'] ?></dd>
				</div>
			</dl>
		</div>
	</section>

	<section class="container home-section" aria-labelledby="home-topics">
		<div class="home-section__head">
			<h2 class="home-section__title" id="home-topics">Explore by topic</h2>
			<a class="home-section__more" href="/catalog">All documents</a>
		</div>

		<ul class="home-topics">
			<?php foreach ($topics as $slug => $topic): ?>
				<?php $isEmpty = $topic['count'] === 0; ?>
				<li class="home-topic<?= $isEmpty ? ' home-topic--empty' : '' ?>">
					<?php
					// Тематика без документов остаётся видимой — она показывает состав
					// энциклопедии, — но не притворяется ссылкой: переход в пустой
					// каталог читается как поломка, а не как «здесь пока ничего нет».
					?>
					<?php if ($isEmpty): ?>
						<span class="home-topic__label">
							<?= $this->e($topic['label']) ?>
							<span class="home-topic__count">0</span>
						</span>
					<?php else: ?>
						<a class="home-topic__link" href="/catalog?topic=<?= rawurlencode($slug) ?>">
							<span class="home-topic__label"><?= $this->e($topic['label']) ?></span>
							<span class="home-topic__count"><?= $topic['count'] ?></span>
						</a>
					<?php endif; ?>
					<?php if ($topic['description'] !== ''): ?>
						<p class="home-topic__text"><?= $this->e($topic['description']) ?></p>
					<?php endif; ?>
					<?php if ($isEmpty): ?>
						<p class="home-topic__empty">No documents yet</p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<div class="container home-columns">
		<section class="home-section" aria-labelledby="home-recent">
			<div class="home-section__head">
				<h2 class="home-section__title" id="home-recent">Recently updated</h2>
			</div>
			<div class="home-list">
				<?php foreach ($recent as $document): ?>
					<?= $this->insert('components::ui/document-card', ['document' => $document]) ?>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="home-section" aria-labelledby="home-popular">
			<div class="home-section__head">
				<h2 class="home-section__title" id="home-popular">Most connected</h2>
			</div>
			<p class="home-section__note">
				Ranked by how many other documents link to them — the graph, not page views.
			</p>
			<div class="home-list">
				<?php foreach ($popular as $document): ?>
					<?= $this->insert('components::ui/document-card', ['document' => $document]) ?>
				<?php endforeach; ?>
			</div>
		</section>
	</div>

	<section class="container home-section home-principles" aria-labelledby="home-principles">
		<h2 class="home-section__title" id="home-principles">How we work</h2>

		<ul class="home-principles__list">
			<li class="home-principle">
				<h3 class="home-principle__title">Every claim has a source</h3>
				<p class="home-principle__text">
					Statements link to the study they rest on. A claim without a source stays a
					draft and does not appear in the catalogue.
				</p>
			</li>
			<li class="home-principle">
				<h3 class="home-principle__title">Facts, hypotheses and guesses are separate</h3>
				<p class="home-principle__text">
					Each claim carries a confidence level and a direction of evidence, so a
					well-supported finding is never confused with a plausible idea.
				</p>
			</li>
			<li class="home-principle">
				<h3 class="home-principle__title">Contradicting results stay visible</h3>
				<p class="home-principle__text">
					When studies disagree, both sides stay in the record. Negative and null
					results are documented, not quietly dropped.
				</p>
			</li>
		</ul>

		<div class="home-principles__actions">
			<a class="home-cta" href="/research">
				Browse the studies
				<span class="home-cta__hint">Every record lists design, population and outcomes</span>
			</a>
			<a class="home-cta home-cta--ghost" href="<?= $this->e($repositoryUrl) ?>" rel="noopener">
				Read the source on GitHub
				<span class="home-cta__hint">Markdown and YAML in Git — the same files this site renders</span>
			</a>
		</div>
	</section>
</div>

<style lang="scss">
	/* Плоская подложка вместо градиента: первый экран должен читаться как
	   страница энциклопедии, а не как лендинг. Границей отделяем от остального. */
	/* Главная примыкает к шапке: поля .page__main дали бы полосу фонового
	   цвета между шапкой и тонированным hero — она читается как сбитый отступ.
	   flex: none — потому что иначе main забирает часть свободной высоты окна
	   и добавляет тот же воздух снизу. */
	.page--home .page__main {
		flex: none;
		padding-block: 0;
	}

	/* У последней секции уже есть собственный нижний отступ, поэтому
	   внешний отступ подвала здесь только удваивал пустоту. */
	.page--home .site-footer {
		margin-top: 0;
	}

	/* Растягивать страницу до высоты окна на главной не нужно: она всегда
	   длиннее окна, а min-height: 100dvh отдавал остаток высоты .page__main
	   и между «How we work» и подвалом вырастала полоса пустого фона в
	   сотни пикселей — она читается как сбитый отступ. */
	.page--home .page {
		min-height: 0;
	}

	.home-hero {
		border-bottom: 1px solid var(--color-border);
		background: var(--color-surface-alt);
	}

	/* Ширину ограничивает только текст: заголовок и поиск стоят на одной
	   линии сетки с остальными разделами страницы. */
	.home-hero__inner {
		padding-block: clamp(2.5rem, 5vw, 4.5rem);
	}

	.home-hero__title,
	.home-hero__lead {
		max-width: 46rem;
	}

	.home-hero__eyebrow {
		margin: 0 0 var(--space-3);
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.12em;
		color: var(--color-accent-text);
		font-weight: 600;
	}

	.home-hero__title {
		margin: 0 0 var(--space-3);
		font-family: var(--font-serif);
		font-size: clamp(2.6rem, 1.8rem + 3.6vw, 4rem);
		line-height: 1.05;
		letter-spacing: -0.02em;
	}

	.home-hero__lead {
		margin: 0 0 var(--space-6);
		font-size: var(--text-lg);
		line-height: var(--leading-relaxed);
		color: var(--color-text-muted);
	}

	.home-hero__search {
		margin-bottom: var(--space-6);
	}

	.home-hero__stats {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-8);
		margin: 0;
	}

	.home-hero__stat {
		display: flex;
		flex-direction: column-reverse;
		gap: 2px;

		dt {
			font-size: var(--text-xs);
			text-transform: uppercase;
			letter-spacing: 0.08em;
			color: var(--color-text-subtle);
		}

		dd {
			margin: 0;
			font-family: var(--font-serif);
			font-size: var(--text-2xl);
			line-height: 1;
		}
	}

	/* Вертикальный ритм держит контейнер, а не секции: если каждая секция
	   приносит свой padding, соседние отступы складываются и просвет между
	   блоками удваивается (96 px вместо 48). Один gap — один ритм. */
	.home {
		display: flex;
		flex-direction: column;
		gap: var(--space-12);
	}

	/* Поля .page__main на главной сняты, поэтому нижний воздух перед подвалом
	   даёт сама страница, а не последняя секция. */
	.page--home .home {
		padding-block-end: var(--space-12);
	}

	/* Заголовок секции без обёртки .home-section__head иначе прилипал бы к
	   содержимому: отступ даёт обёртка, а сам <h2> отступов не имеет. */
	.home-section > .home-section__title {
		margin-bottom: var(--space-4);
	}

	.home-section__head {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: var(--space-4);
		margin-bottom: var(--space-4);
	}

	.home-section__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-2xl);
		line-height: 1.2;
	}

	.home-section__more {
		font-size: var(--text-sm);
		white-space: nowrap;
	}

	.home-section__note {
		margin: calc(var(--space-1) * -1) 0 var(--space-4);
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.home-topics {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(min(100%, 15rem), 1fr));
		gap: var(--space-3);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.home-topic {
		padding: var(--space-3) var(--space-4);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.home-topic__link {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: var(--space-2);
		color: var(--color-text);
		text-decoration: none;

		&:hover {
			color: var(--color-accent-text);
		}
	}

	.home-topic__label {
		font-weight: 600;
		font-size: var(--text-base);
	}

	.home-topic__count {
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.home-topic__text {
		margin: var(--space-1) 0 0;
		font-size: var(--text-xs);
		line-height: var(--leading-normal);
		color: var(--color-text-muted);
	}

	/* Тематика без документов: карточка остаётся на месте, но читается как
	   «здесь пока пусто», а не как приглашение нажать. */
	.home-topic--empty {
		background: var(--color-surface-alt);
	}

	.home-topic > .home-topic__label {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: var(--space-2);
		color: var(--color-text-muted);
	}

	.home-topic__empty {
		margin: var(--space-1) 0 0;
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.home-columns {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr));
		gap: var(--space-8);
	}

	.home-list {
		display: grid;
		gap: var(--space-3);
	}

	.home-principles__list {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(min(100%, 17rem), 1fr));
		gap: var(--space-4);
		margin: 0 0 var(--space-6);
		padding: 0;
		list-style: none;
	}

	.home-principle {
		padding: var(--space-4) var(--space-5);
		border-inline-start: 2px solid var(--color-accent);
		background: var(--color-surface-alt);
		border-radius: var(--radius-sm);
	}

	.home-principle__title {
		margin: 0 0 var(--space-1);
		font-size: var(--text-base);
		font-weight: 600;
	}

	.home-principle__text {
		margin: 0;
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
		color: var(--color-text-muted);
	}

	.home-principles__actions {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(min(100%, 18rem), 1fr));
		gap: var(--space-3);
	}

	.home-cta {
		display: flex;
		flex-direction: column;
		gap: var(--space-1);
		padding: var(--space-4) var(--space-5);
		border-radius: var(--radius-md);
		background: var(--color-accent-fill);
		color: var(--color-on-accent);
		text-decoration: none;
		font-weight: 600;

		&:hover {
			background: var(--color-accent-fill-hover);
		}
	}

	.home-cta__hint {
		font-size: var(--text-xs);
		font-weight: 400;
		opacity: 0.85;
	}

	.home-cta--ghost {
		background: var(--color-surface);
		color: var(--color-text);
		border: 1px solid var(--color-border);
	}
</style>
<?php $this->stop('page'); ?>
