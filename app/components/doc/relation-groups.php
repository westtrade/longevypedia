<?php

declare(strict_types=1);

/**
 * Связи документа: «часть чего», «на что действует», обратные ссылки.
 *
 * Группы готовит `ContentRepository::related()` и `backlinks()` — шаблон не
 * знает, откуда взялась связь: из `relations` этого файла или из обратного
 * ребра чужого документа.
 *
 * Тип связи — заголовок группы, тип документа — бейдж из набора: связь читают
 * как «этот документ regulates тот», поэтому подпись связи стоит над списком, а
 * тип уточняет соседа, а не наоборот.
 *
 * @var list<array{label: string, documents: list<\Longevypedia\Content\Document>}> $groups
 * @var string                                                                      $title
 */

$groups ??= [];
$title ??= 'Connections';

if ($groups === []) {
	return;
}
?>
<section class="doc-relations" aria-labelledby="doc-relations-title">
	<h2 class="doc-relations__title" id="doc-relations-title"><?= $this->e($title) ?></h2>

	<?php foreach ($groups as $group): ?>
		<div class="doc-relations__group">
			<h3 class="doc-relations__label"><?= $this->e($group['label']) ?></h3>
			<ul class="doc-relations__list">
				<?php foreach ($group['documents'] as $related): ?>
					<li class="doc-relations__item">
						<a class="doc-relations__link" href="<?= $this->e($related->url) ?>"><?= $this->e($related->title()) ?></a>
						<?= $this->insert('components::ui/badge', [
							'label' => $related->typeLabel(),
							'variant' => 'outline',
							'size' => 'sm',
						]) ?>
					</li>
				<?php endforeach ?>
			</ul>
		</div>
	<?php endforeach ?>
</section>

<style lang="scss">
	.doc-relations__title {
		margin: 0 0 var(--space-3);
		font-family: var(--font-serif);
		font-size: var(--text-lg);
	}

	.doc-relations__group + .doc-relations__group {
		margin-top: var(--space-4);
	}

	.doc-relations__label {
		margin: 0 0 var(--space-1);
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.doc-relations__list {
		display: grid;
		gap: var(--space-1);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.doc-relations__item {
		display: flex;
		align-items: baseline;
		gap: var(--space-2);
		font-size: var(--text-sm);
	}

	.doc-relations__link {
		color: var(--color-text);
		text-decoration: none;
		border-bottom: 1px solid transparent;

		&:hover {
			color: var(--color-brand-text);
			border-bottom-color: currentColor;
		}
	}
</style>
