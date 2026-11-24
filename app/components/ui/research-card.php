<?php

declare(strict_types=1);

/**
 * Карточка исследования.
 *
 * Собирается из `components::ui/card`, а не повторяет её разметку: у карточки
 * уже есть надзаголовок, мета-строка и подвал, здесь добавляется только то,
 * что относится к исследованию — дизайн, авторы, журнал, год и отметка о
 * демонстрационной записи.
 *
 * Дизайн берётся из `StudyDesigns::LABELS` — единого словаря со схемой, чтобы
 * подпись на карточке не разошлась с `research.schema.json`.
 *
 * @param \Longevypedia\Content\Document $document документ типа research
 * @param int $headingLevel уровень заголовка внутри карточки (2–4)
 * @param bool $showSummary показывать аннотацию
 * @param array $attrs дополнительные атрибуты карточки
 */

$document ??= null;
$headingLevel ??= 3;
$showSummary ??= true;
$attrs ??= [];

if (!$document instanceof \Longevypedia\Content\Document) {
	return;
}

$meta = (array) $document->metaValue('study', []);
$design = (string) ($meta['design'] ?? '');
$authors = is_array($meta['authors'] ?? null) ? array_values(array_filter($meta['authors'], 'is_string')) : [];
$journal = (string) ($meta['journal'] ?? '');
$year = (string) ($meta['year'] ?? '');
$outcomes = is_array($meta['outcomes'] ?? null) ? count($meta['outcomes']) : 0;
$isExample = (bool) ($meta['example'] ?? false);

$authorLine = '';
if ($authors !== []) {
	$authorLine = implode(', ', array_slice($authors, 0, 3)) . (count($authors) > 3 ? ' et al.' : '');
}

$metaLine = implode(' · ', array_filter([$authorLine, $journal, $year], static fn (string $part): bool => $part !== ''));

$headingLevel = max(2, min(4, (int) $headingLevel));

$this->insert('components::ui/card', [
	'eyebrow' => $design !== '' ? \Longevypedia\Content\StudyDesigns::label($design) : null,
	'title' => $document->title(),
	'href' => $document->url,
	'meta' => $metaLine !== '' ? $metaLine : null,
	'titleTag' => 'h' . $headingLevel,
	'variant' => 'outlined',
	'attrs' => ['class' => clsx('ui-research-card', $attrs['class'] ?? null)] + $attrs,
	'children' => function () use ($document, $showSummary): void {
		if (!$showSummary) {
			return;
		}
		?>
		<p class="ui-research-card__summary"><?= e($document->summary()) ?></p>
		<?php
	},
	'footer' => function () use ($isExample, $outcomes): void {
		if (!$isExample && $outcomes === 0) {
			return;
		}
		?>
		<ul class="ui-research-card__flags">
			<?php if ($isExample): ?>
				<li><?= $this->insert('components::ui/badge', ['label' => 'Example record', 'variant' => 'warning', 'size' => 'sm']) ?></li>
			<?php endif ?>
			<?php if ($outcomes > 0): ?>
				<li class="ui-research-card__outcomes"><?= e((string) $outcomes) ?> outcome<?= $outcomes === 1 ? '' : 's' ?></li>
			<?php endif ?>
		</ul>
		<?php
	},
]);
?>

<style lang="scss">
	.ui-research-card__summary {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-serif);
		font-size: var(--text-base);
		line-height: var(--leading-normal);
	}

	.ui-research-card__flags {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: var(--space-3);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.ui-research-card__outcomes {
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}
</style>
