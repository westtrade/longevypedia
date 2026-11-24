<?php

declare(strict_types=1);

/**
 * О проекте: назначение, методология, участие, формат хранения, лицензии.
 *
 * Страница намеренно описывает правила, которые проверяются автоматически
 * (`scripts/content-check.php` и CI), а не благие пожелания: если правило не
 * проверяется, оно здесь не заявлено.
 *
 * @var array $route
 */

$repository = longevypedia_content();
$locale = (string) ($route['locale'] ?? $repository->defaultLocale());
$stats = $repository->stats($locale);
$repositoryUrl = (string) ($config['app']['repository'] ?? 'https://github.com/westtrade/longevypedia');

$this->layout('components::layout/site-layout', [
	'title' => 'About Longevypedia',
	'description' => 'What Longevypedia is, how claims are graded, how the content is stored and licensed, and how to contribute.',
	'canonical' => '/about',
	'current' => 'about',
]);
?>
<?php $this->start('page'); ?>
<div class="container about">
	<header class="about__header">
		<h1 class="about__title">About Longevypedia</h1>
		<p class="about__lead">
			An open scientific encyclopedia about longevity, the biology of aging and
			healthspan. It exists to make the evidence legible: what is known, how strongly
			it is known, and where the studies disagree.
		</p>
	</header>

	<div class="about__grid">
		<nav class="about__nav" aria-label="On this page">
			<p class="about__nav-title">Sections</p>
			<ol class="about__nav-list">
				<li><a href="#purpose">Purpose</a></li>
				<li><a href="#openness">Openness</a></li>
				<li><a href="#method">Method</a></li>
				<li><a href="#contributing">Contributing</a></li>
				<li><a href="#format">Content format</a></li>
				<li><a href="#license">Licensing</a></li>
			</ol>
		</nav>

		<div class="about__body">
			<section class="about__section" id="purpose">
				<h2 class="about__section-title">Purpose</h2>
				<p>
					Aging research produces a large, fast-moving and unevenly reliable body of
					literature. Longevypedia organises it into typed documents — concepts,
					mechanisms, genes, proteins, compounds, biomarkers, diseases, interventions,
					studies, claims and guidelines — and connects them into a graph, so a reader
					can move from a claim to the study behind it in one step.
				</p>
				<p>
					The encyclopedia currently holds <?= $stats['total'] ?> published documents
					across <?= $stats['topics'] ?> topics, including <?= $stats['studies'] ?>
					<?= $stats['studies'] === 1 ? 'study' : 'studies' ?> and
					<?= $stats['claims'] ?> <?= $stats['claims'] === 1 ? 'claim' : 'claims' ?>.
				</p>
			</section>

			<section class="about__section" id="openness">
				<h2 class="about__section-title">Openness</h2>
				<ul>
					<li>The content lives in a public Git repository as Markdown with YAML frontmatter.</li>
					<li>Every page links to the exact file it was rendered from.</li>
					<li>Every change is a commit with an author and a diff — no silent edits.</li>
					<li>Contradicting results are kept; the record shows the disagreement instead of resolving it by deletion.</li>
				</ul>
			</section>

			<section class="about__section" id="method">
				<h2 class="about__section-title">Method</h2>
				<p>
					Claims are graded on two independent axes. <strong>Confidence</strong> says how
					firmly the current evidence supports the statement; <strong>evidence
					direction</strong> says which way the evidence points. A claim can therefore be
					contested, or firmly contradicted, without the two judgements being mixed.
				</p>

				<table class="about__table">
					<caption>Confidence levels used on claim pages</caption>
					<thead>
						<tr>
							<th scope="col">Level</th>
							<th scope="col">Meaning</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th scope="row">Established</th>
							<td>Reproduced across independent studies and populations; the effect direction is not in dispute.</td>
						</tr>
						<tr>
							<th scope="row">Likely</th>
							<td>Consistent evidence, but with important gaps — often a single model organism, or no human data.</td>
						</tr>
						<tr>
							<th scope="row">Uncertain</th>
							<td>Evidence exists but is weak, indirect or conflicting enough that the answer could change.</td>
						</tr>
						<tr>
							<th scope="row">Contested</th>
							<td>Credible studies point in different directions and the disagreement is unresolved.</td>
						</tr>
						<tr>
							<th scope="row">Speculative</th>
							<td>A hypothesis with a mechanistic rationale but no direct test.</td>
						</tr>
					</tbody>
				</table>

				<p>
					Every claim states its <strong>scope</strong> — the species, population or
					condition it applies to. A finding in mice stays a finding in mice; the record
					says so explicitly instead of implying a human effect.
				</p>
			</section>

			<section class="about__section" id="contributing">
				<h2 class="about__section-title">Contributing</h2>
				<p>
					Contributions are pull requests against the content repository. A change is
					merged when it passes the automated checks and states its sources:
				</p>
				<ol>
					<li>Add or edit a Markdown file with a valid frontmatter block.</li>
					<li>Cite the primary source for every factual statement; do not cite a review as if it were the study.</li>
					<li>Keep identifiers real. If a DOI or PMID is not verified, leave it out.</li>
					<li>Link related documents with wikilinks and typed relations so the graph stays connected.</li>
				</ol>
				<p>
					The full workflow is in
					<a href="<?= $this->e($repositoryUrl) ?>/blob/main/CONTRIBUTING.md" rel="noopener">CONTRIBUTING.md</a>.
					Reviewers check sources and scope; they do not need to agree with the conclusion.
				</p>
			</section>

			<section class="about__section" id="format">
				<h2 class="about__section-title">Content format</h2>
				<p>
					Documents are Markdown files with YAML frontmatter — readable in any editor and
					compatible with Obsidian properties and wikilinks. There is no database: the
					site reads the files, builds an in-memory index and renders them. The format is
					specified in
					<a href="<?= $this->e($repositoryUrl) ?>/blob/main/docs/specifications/longevypedia-markdown.md" rel="noopener">the Markdown specification</a>,
					and every document is validated against a JSON Schema before it is published.
				</p>
			</section>

			<section class="about__section" id="license">
				<h2 class="about__section-title">Licensing</h2>
				<ul>
					<li><strong>Text</strong> — <a href="https://creativecommons.org/licenses/by-sa/4.0/" rel="noopener">CC BY-SA 4.0</a>: reuse is allowed with attribution and under the same licence.</li>
					<li><strong>Code</strong> — <a href="https://opensource.org/licenses/MIT" rel="noopener">MIT</a>.</li>
					<li><strong>Scientific sources</strong> — quoted or summarised; the rights stay with their publishers.</li>
					<li><strong>Images</strong> — only with a documented source and licence; none are bundled without one.</li>
				</ul>
				<p class="about__disclaimer">
					Longevypedia is not a medical service and nothing here is medical advice.
				</p>
			</section>
		</div>
	</div>
</div>

<style lang="scss">
	.about {
		padding-block: 2rem 0;
	}

	.about__header {
		max-width: var(--layout-measure);
		margin-bottom: 2rem;
	}

	.about__title {
		margin: 0 0 0.6rem;
		font-family: var(--font-text);
		font-size: clamp(1.9rem, 1.4rem + 2vw, 2.6rem);
	}

	.about__lead {
		margin: 0;
		font-size: var(--text-lg);
		line-height: 1.55;
		color: var(--color-text-muted);
	}

	.about__grid {
		display: grid;
		grid-template-columns: minmax(0, 13rem) minmax(0, 1fr);
		gap: 2.5rem;
		align-items: start;
	}

	.about__nav {
		position: sticky;
		top: 5rem;
		font-size: var(--text-sm);
	}

	.about__nav-title {
		margin: 0 0 0.5rem;
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: var(--color-text-subtle);
	}

	.about__nav-list {
		margin: 0;
		padding-inline-start: 0.9rem;
		display: grid;
		gap: 0.3rem;
		color: var(--color-text-muted);

		a {
			color: inherit;
			text-decoration: none;

			&:hover {
				color: var(--color-accent);
			}
		}
	}

	.about__body {
		max-width: 44rem;
	}

	.about__section {
		padding-block: 1.5rem;
		border-top: 1px solid var(--color-border);
		line-height: 1.65;

		&:first-child {
			border-top: 0;
			padding-top: 0;
		}

		p {
			margin: 0 0 0.85rem;
		}

		ul,
		ol {
			margin: 0 0 0.85rem;
			padding-inline-start: 1.25rem;
			display: grid;
			gap: 0.4rem;
		}
	}

	.about__section-title {
		margin: 0 0 0.75rem;
		font-family: var(--font-text);
		font-size: var(--text-xl);
	}

	.about__table {
		width: 100%;
		border-collapse: collapse;
		margin: 0 0 1rem;
		font-size: var(--text-sm);

		caption {
			text-align: start;
			font-size: var(--text-xs);
			text-transform: uppercase;
			letter-spacing: 0.06em;
			color: var(--color-text-subtle);
			padding-bottom: 0.4rem;
		}

		th,
		td {
			text-align: start;
			padding: 0.45rem 0.6rem;
			border-bottom: 1px solid var(--color-border);
			vertical-align: top;
		}

		thead th {
			font-size: var(--text-xs);
			color: var(--color-text-subtle);
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		tbody th {
			white-space: nowrap;
			font-weight: 600;
		}
	}

	.about__disclaimer {
		padding: 0.75rem 0.9rem;
		border-inline-start: 3px solid var(--color-warning);
		background: var(--color-warning-soft);
		border-radius: var(--radius-sm);
		font-size: var(--text-sm);
	}

	@media (max-width: 56rem) {
		.about__grid {
			grid-template-columns: 1fr;
			gap: 1rem;
		}

		.about__nav {
			position: static;
			border-bottom: 1px solid var(--color-border);
			padding-bottom: 1rem;
		}
	}
</style>
<?php $this->stop('page'); ?>
