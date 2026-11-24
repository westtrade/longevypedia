<?php

declare(strict_types=1);

/**
 * Список первоисточников.
 *
 * Идентификаторы не выдумываются и не досочиняются: если DOI или PMID в
 * источнике нет, ссылки просто не будет. Ссылка на DOI ведёт на doi.org,
 * на PMID — в PubMed; обе открываются в новой вкладке.
 *
 * @var array<int, array{citation: string, url?: string|null, doi?: string|null, pmid?: string|null, note?: string|null}> $sources
 * @var string $title заголовок блока
 */

$sources ??= [];
$title ??= 'Sources';

if ($sources === []) {
	return;
}
?>
<section class="ui-sources">
	<h2 class="ui-sources__title"><?= e($title) ?></h2>
	<ol class="ui-sources__list">
		<?php foreach ($sources as $source): ?>
			<li class="ui-sources__item">
				<span class="ui-sources__citation">
					<?php if (($source['url'] ?? null) !== null): ?>
						<a class="ui-sources__link" href="<?= e((string) $source['url']) ?>"
							rel="noopener noreferrer" target="_blank"><?= e((string) ($source['citation'] ?? '')) ?></a>
					<?php else: ?>
						<?= e((string) ($source['citation'] ?? '')) ?>
					<?php endif ?>
				</span>
				<span class="ui-sources__links">
					<?php if (($source['doi'] ?? null) !== null): ?>
						<a class="ui-sources__link" href="https://doi.org/<?= e((string) $source['doi']) ?>"
							rel="noopener noreferrer" target="_blank">doi:<?= e((string) $source['doi']) ?></a>
					<?php endif ?>
					<?php if (($source['pmid'] ?? null) !== null): ?>
						<a class="ui-sources__link" href="https://pubmed.ncbi.nlm.nih.gov/<?= e((string) $source['pmid']) ?>/"
							rel="noopener noreferrer" target="_blank">PMID <?= e((string) $source['pmid']) ?></a>
					<?php endif ?>
				</span>
				<?php if (($source['note'] ?? null) !== null): ?>
					<span class="ui-sources__note"><?= e((string) $source['note']) ?></span>
				<?php endif ?>
			</li>
		<?php endforeach ?>
	</ol>
</section>

<style lang="scss">
	.ui-sources {
		display: flex;
		flex-direction: column;
		gap: var(--space-3);
	}

	.ui-sources__title {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-sources__list {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		margin: 0;
		padding-left: var(--space-5);
		color: var(--color-text);
		font-family: var(--font-serif);
		font-size: var(--text-sm);
		line-height: var(--leading-relaxed);
	}

	.ui-sources__item::marker {
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-sources__links {
		display: inline-flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		margin-left: var(--space-2);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-sources__link {
		color: var(--color-brand-text);
		text-decoration: underline;
		text-underline-offset: 0.18em;
	}

	/* Перенос разрешён по умолчанию: ссылка-цитата — это обычный текст
	   библиографии, и `nowrap` на ней растягивает всю страницу. Запрещаем
	   перенос только коротким идентификаторам (DOI, PMID), где разрыв
	   посреди номера читается как ошибка. */
	.ui-sources__links .ui-sources__link {
		white-space: nowrap;
	}

	.ui-sources__citation {
		overflow-wrap: anywhere;
	}

	.ui-sources__note {
		display: block;
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}
</style>
