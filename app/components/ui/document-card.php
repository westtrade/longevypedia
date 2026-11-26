<?php

declare(strict_types=1);

/**
 * Карточка документа в списках: каталог, главная, связанные материалы.
 *
 * @var \Longevypedia\Content\Document $document
 * @var bool                           $showType
 * @var bool                           $showSummary
 * @var bool                           $showTopics
 *
 * Подвал карточки прижат к низу: карточки стоят в сетке одной высоты, и без
 * `margin-top: auto` теги висели бы сразу под текстом — на разной высоте в
 * соседних карточках, потому что summary занимает две или три строки.
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
		<footer class="doc-card__footer">
			<ul class="doc-card__topics">
				<?php foreach ($document->topics() as $topic): ?>
					<li>
						<?= $this->insert('components::ui/tag', [
							'label' => longevypedia_topic_label($topic, $document->locale),
							'href' => '/catalog?topic=' . rawurlencode($topic),
						]) ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</footer>
	<?php endif; ?>
</article>

<style lang="scss">
	.doc-card {
		position: relative;
		display: flex;
		flex-direction: column;
		gap: var(--space-1);
		padding: var(--space-4);
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
		gap: var(--space-2);
		margin: 0;
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}

	.doc-card__type {
		text-transform: uppercase;
		letter-spacing: var(--tracking-wide);
		font-weight: var(--weight-semibold);
		color: var(--color-accent-text);
	}

	.doc-card__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		font-weight: var(--weight-semibold);
		line-height: var(--leading-snug);
	}

	.doc-card__link {
		color: var(--color-text);
		text-decoration: none;

		&:hover {
			color: var(--color-accent-text);
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
		line-height: var(--leading-normal);
	}

	/* Подвал: `margin-top: auto` прижимает его к низу карточки, поэтому теги
	   стоят на одной линии во всём ряду, сколько бы строк ни заняло описание.
	   Разделительной линии сверху нет: карточка уже обведена рамкой, а теги —
	   самостоятельные плашки, и вторая линия только дублировала бы первую. */
	.doc-card__footer {
		position: relative;
		z-index: 1;
		margin-top: auto;
		padding-top: var(--space-3);

		/* Подвал лежит выше ссылки карточки, иначе клик по тегу уводил бы в
		   документ. Пустое место подвала при этом остаётся частью карточки:
		   без `pointer-events` клик по нему перестал бы открывать документ. */
		pointer-events: none;
	}

	.doc-card__footer a {
		pointer-events: auto;
	}
	.doc-card__topics {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-1);
		margin: 0;
		padding: 0;
		list-style: none;
	}
</style>
