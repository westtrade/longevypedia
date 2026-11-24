<?php

declare(strict_types=1);

/**
 * Связи документа: «часть чего», «на что действует», обратные ссылки.
 *
 * Группы готовит `ContentRepository::related()` и `backlinks()` — шаблон не
 * знает, откуда взялась связь: из `relations` этого файла или из обратного
 * ребра чужого документа.
 *
 * @var list<array{label: string, documents: list<\Longevypedia\Content\Document>}> $groups
 * @var string                                                                      $title
 */

$groups ??= [];
$title ??= 'Connections';
?>
<?php if ($groups !== []): ?>
	<section class="doc-relations" aria-labelledby="doc-relations-title">
		<h2 class="doc-relations__title" id="doc-relations-title"><?= $this->e($title) ?></h2>

		<?php foreach ($groups as $group): ?>
			<div class="doc-relations__group">
				<h3 class="doc-relations__label"><?= $this->e($group['label']) ?></h3>
				<ul class="doc-relations__list">
					<?php foreach ($group['documents'] as $related): ?>
						<li class="doc-relations__item">
							<a class="doc-relations__link" href="<?= $this->e($related->url) ?>"><?= $this->e($related->title()) ?></a>
							<span class="doc-relations__type"><?= $this->e($related->typeLabel()) ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</section>
<?php endif; ?>

<style lang="scss">
	.doc-relations__title {
		margin: 0 0 0.75rem;
		font-size: var(--text-lg);
		font-family: var(--font-text);
	}

	.doc-relations__group + .doc-relations__group {
		margin-top: 1rem;
	}

	.doc-relations__label {
		margin: 0 0 0.35rem;
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.07em;
		color: var(--color-text-subtle);
		font-weight: 600;
	}

	.doc-relations__list {
		margin: 0;
		padding: 0;
		list-style: none;
		display: grid;
		gap: 0.3rem;
	}

	.doc-relations__item {
		display: flex;
		align-items: baseline;
		gap: 0.5rem;
		font-size: var(--text-sm);
	}

	.doc-relations__link {
		color: var(--color-text);
		text-decoration: none;
		border-bottom: 1px solid transparent;

		&:hover {
			color: var(--color-accent);
			border-bottom-color: var(--color-accent);
		}
	}

	.doc-relations__type {
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
	}
</style>
