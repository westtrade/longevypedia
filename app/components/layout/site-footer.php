<?php

declare(strict_types=1);

/**
 * Подвал: лицензии, принципы, ссылки на исходники.
 *
 * Отдельная лицензия на текст и на код — не формальность: энциклопедический
 * текст распространяется по CC BY-SA 4.0, а движок по MIT, и читатель должен
 * видеть это без перехода в README.
 */
?>
<footer class="site-footer">
	<div class="container site-footer__inner">
		<div class="site-footer__about">
			<p class="site-footer__title">Longevypedia</p>
			<p class="site-footer__text">
				An open, structured and verifiable knowledge base on longevity,
				the biology of aging and healthspan.
			</p>
		</div>

		<div class="site-footer__column">
			<p class="site-footer__heading">Content</p>
			<ul class="site-footer__list">
				<li><a href="/catalog">All documents</a></li>
				<li><a href="/research">Studies</a></li>
				<li><a href="/claims">Claims</a></li>
				<li><a href="/about">Methodology</a></li>
			</ul>
		</div>

		<div class="site-footer__column">
			<p class="site-footer__heading">Project</p>
			<ul class="site-footer__list">
				<li><a href="https://github.com/westtrade/longevypedia" rel="noopener">GitHub repository</a></li>
				<li><a href="https://github.com/westtrade/longevypedia/blob/main/CONTRIBUTING.md" rel="noopener">Contributing</a></li>
				<li><a href="https://github.com/westtrade/longevypedia/blob/main/docs/specifications/longevypedia-markdown.md" rel="noopener">Content format</a></li>
			</ul>
		</div>
	</div>

	<div class="container site-footer__legal">
		<p>
			Text is licensed under
			<a href="https://creativecommons.org/licenses/by-sa/4.0/" rel="noopener">CC BY-SA 4.0</a>,
			code under <a href="https://opensource.org/licenses/MIT" rel="noopener">MIT</a>.
			Scientific sources keep the rights of their publishers.
		</p>
		<p>
			Longevypedia is not a medical service. Nothing here is medical advice.
		</p>
	</div>
</footer>

<style lang="scss">
	.site-footer {
		margin-top: 4rem;
		border-top: 1px solid var(--color-border);
		background: var(--color-surface-alt);
		font-size: var(--text-sm);
		color: var(--color-text-muted);
	}

	.site-footer__inner {
		display: grid;
		grid-template-columns: minmax(0, 2fr) repeat(2, minmax(0, 1fr));
		gap: 2rem;
		padding-block: 2.5rem;
	}

	.site-footer__title {
		margin: 0 0 0.35rem;
		font-family: var(--font-text);
		font-size: 1.1rem;
		color: var(--color-text);
	}

	.site-footer__text {
		margin: 0;
		max-width: 34ch;
	}

	.site-footer__heading {
		margin: 0 0 0.5rem;
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: var(--color-text-subtle);
	}

	.site-footer__list {
		margin: 0;
		padding: 0;
		list-style: none;
		display: grid;
		gap: 0.35rem;

		a {
			color: var(--color-text-muted);
			text-decoration: none;

			&:hover {
				color: var(--color-accent);
				text-decoration: underline;
			}
		}
	}

	.site-footer__legal {
		padding-block: 1.25rem;
		border-top: 1px solid var(--color-border);
		font-size: var(--text-xs);

		p {
			margin: 0.25rem 0;
		}
	}

	@media (max-width: 48rem) {
		.site-footer__inner {
			grid-template-columns: 1fr;
		}
	}
</style>
