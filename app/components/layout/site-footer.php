<?php

declare(strict_types=1);

/**
 * Подвал: лицензии, принципы, ссылки на исходники.
 *
 * Отдельная лицензия на текст и на код — не формальность: энциклопедический
 * текст распространяется по CC BY-SA 4.0, а движок по MIT, и читатель должен
 * видеть это без перехода в README.
 *
 * Адрес репозитория берётся из `app.repository`, а не пишется здесь: ссылки на
 * GitHub есть ещё на странице документа и на главной, и три копии адреса
 * расходятся при первом же переименовании.
 *
 * @var array<string, mixed> $config
 */

$config ??= [];
$repository = (string) ($config['app']['repository'] ?? 'https://github.com/westtrade/longevypedia');
$branch = (string) ($config['app']['branch'] ?? 'main');

$columns = [
	'Content' => [
		['label' => 'All documents', 'url' => '/catalog'],
		['label' => 'Studies', 'url' => '/research'],
		['label' => 'Claims', 'url' => '/claims'],
		['label' => 'Methodology', 'url' => '/about'],
	],
	'Project' => [
		['label' => 'GitHub repository', 'url' => $repository, 'external' => true],
		['label' => 'Contributing', 'url' => $repository . '/blob/' . $branch . '/CONTRIBUTING.md', 'external' => true],
		['label' => 'Content format', 'url' => $repository . '/blob/' . $branch . '/docs/specifications/longevypedia-markdown.md', 'external' => true],
	],
];
?>
<footer class="site-footer">
	<div class="container site-footer__inner">
		<div class="site-footer__about">
			<?= $this->insert('components::ui/wordmark', [
				'href' => '/',
				'label' => 'Longevypedia',
				'size' => 'sm',
			]) ?>
			<p class="site-footer__text">
				An open, structured and verifiable knowledge base on longevity,
				the biology of aging and healthspan.
			</p>
		</div>

		<?php foreach ($columns as $heading => $links): ?>
			<nav class="site-footer__column" aria-label="<?= $this->e($heading) ?>">
				<p class="site-footer__heading"><?= $this->e($heading) ?></p>
				<ul class="site-footer__list">
					<?php foreach ($links as $link): ?>
						<li>
							<a class="site-footer__link" href="<?= $this->e($link['url']) ?>"<?= ($link['external'] ?? false) ? ' rel="noopener noreferrer"' : '' ?>>
								<?= $this->e($link['label']) ?>
							</a>
						</li>
					<?php endforeach ?>
				</ul>
			</nav>
		<?php endforeach ?>
	</div>

	<div class="container site-footer__legal">
		<p>
			Text is licensed under
			<a href="https://creativecommons.org/licenses/by-sa/4.0/" rel="noopener noreferrer">CC BY-SA 4.0</a>,
			code under <a href="https://opensource.org/licenses/MIT" rel="noopener noreferrer">MIT</a>.
			Scientific sources keep the rights of their publishers.
		</p>
		<p>
			Longevypedia is not a medical service. Nothing here is medical advice.
		</p>
	</div>
</footer>

<style lang="scss">
	.site-footer {
		margin-top: var(--space-16);
		border-top: 1px solid var(--color-border);
		background: var(--color-surface-alt);
		color: var(--color-text-muted);
		font-size: var(--text-sm);
	}

	.site-footer__inner {
		display: grid;
		grid-template-columns: minmax(0, 2fr) repeat(2, minmax(0, 1fr));
		gap: var(--space-8);
		padding-block: var(--space-10);
	}

	.site-footer__text {
		margin: var(--space-3) 0 0;
		max-width: 34ch;
		line-height: var(--leading-normal);
	}

	.site-footer__heading {
		margin: 0 0 var(--space-2);
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.site-footer__list {
		display: grid;
		gap: var(--space-1);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.site-footer__link {
		color: var(--color-text-muted);
		text-decoration: none;

		&:hover {
			color: var(--color-brand-text);
			text-decoration: underline;
		}
	}

	.site-footer__legal {
		padding-block: var(--space-5);
		border-top: 1px solid var(--color-border);
		font-size: var(--text-xs);

		p {
			margin: var(--space-1) 0;
		}

		a {
			color: var(--color-brand-text);
		}
	}

	@media (max-width: $bp-md) {
		.site-footer__inner {
			grid-template-columns: minmax(0, 1fr);
			gap: var(--space-6);
		}
	}
</style>
