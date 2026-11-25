<?php

declare(strict_types=1);

/**
 * Параметры исследования: дизайн, популяция, вмешательство, исходы.
 *
 * Собирается из `ui/key-value` и `ui/data-table`, а не повторяет их разметку:
 * поля исследования — это список «поле — значение», исходы — таблица с
 * горизонтальной прокруткой на узком экране, и обе вещи в наборе уже есть.
 *
 * Подписи дизайна берутся из `StudyDesigns::LABELS` — единого словаря со
 * схемой `research.schema.json`. Машинный код остаётся в разметке рядом с
 * переводом: подпись читает человек, `data-code` — тест, экспорт или фильтр.
 *
 * Пустые поля не печатаются: строка «Sample size: —» создаёт впечатление, что
 * данные есть, но не внесены.
 *
 * @var array<string, mixed> $study поля `study` из фронтматтера
 * @var bool $isExample демонстрационная запись, а не публикация
 * @var string $title заголовок блока
 * @var string $id идентификатор заголовка для `aria-labelledby`
 */

$study ??= [];
$isExample ??= false;
$title ??= 'Study';
$id ??= 'study';

if ($study === []) {
	return;
}

$design = (string) ($study['design'] ?? '');
$outcomes = is_array($study['outcomes'] ?? null) ? $study['outcomes'] : [];

$directions = [
	'increase' => 'Increase',
	'decrease' => 'Decrease',
	'no_change' => 'No change',
	'mixed' => 'Mixed',
	'unclear' => 'Unclear',
];

$designLabel = $design !== '' ? \Longevypedia\Content\StudyDesigns::label($design) : '';

// Значение дизайна — готовая разметка (key-value печатает её как есть), поэтому
// машинный код едет вместе с переводом, а не вместо него.
$designValue = $design !== ''
	? '<span data-code="' . e($design) . '">' . e($designLabel) . '</span>'
	: '';

$fields = [
	'Design' => $designValue,
	'Species' => e((string) ($study['species'] ?? '')),
	'Population' => e((string) ($study['population'] ?? '')),
	'Sample size' => e((string) ($study['sample_size'] ?? '')),
	'Intervention' => e((string) ($study['intervention'] ?? '')),
	'Comparator' => e((string) ($study['comparator'] ?? '')),
	'Duration' => e((string) ($study['duration'] ?? '')),
	'Registration' => e((string) ($study['registration'] ?? '')),
];

$items = [];

foreach ($fields as $label => $value) {
	if (trim(strip_tags($value)) === '') {
		continue;
	}

	$items[] = ['label' => $label, 'value' => $value];
}

$columns = [
	['key' => 'outcome', 'label' => 'Outcome'],
	['key' => 'direction', 'label' => 'Direction'],
	['key' => 'measure', 'label' => 'Measure'],
];

$rows = [];

foreach ($outcomes as $outcome) {
	if (!is_array($outcome)) {
		continue;
	}

	$direction = (string) ($outcome['direction'] ?? '');

	$rows[] = [
		'cells' => [
			'outcome' => e((string) ($outcome['name'] ?? '')),
			'direction' => '<span class="ui-research-meta__direction ui-research-meta__direction--' . e($direction) . '">'
				. e($directions[$direction] ?? $direction) . '</span>',
			'measure' => e((string) ($outcome['measure'] ?? '')),
		],
	];
}
?>
<section class="ui-research-meta" aria-labelledby="<?= e($id) ?>">
	<h2 class="ui-research-meta__title" id="<?= e($id) ?>"><?= e($title) ?></h2>

	<?php if ($isExample): ?>
		<?= $this->insert('components::ui/alert', [
			'type' => 'warning',
			'text' => 'This is an example record that demonstrates the document format. It is not a real publication.',
			'attrs' => ['class' => 'ui-research-meta__example'],
		]) ?>
	<?php endif ?>

	<?= $this->insert('components::ui/key-value', [
		'items' => $items,
		'density' => 'compact',
		// Карточка уже даёт рамку: линии списка добавляли вторую, и блок
		// «Design / Species / …» читался как таблица в таблице.
		'dividers' => false,
	]) ?>

	<?php if ($rows !== []): ?>
		<?= $this->insert('components::ui/data-table', [
			'caption' => 'Reported outcomes',
			'columns' => $columns,
			'rows' => $rows,
			'density' => 'compact',
			// Исходы — продолжение паспорта исследования, а не отдельный блок:
			// своя рамка внутри карточки давала двойную.
			'variant' => 'plain',
			'attrs' => ['class' => 'ui-research-meta__outcomes'],
		]) ?>
	<?php endif ?>
</section>

<style lang="scss">
	.ui-research-meta {
		display: flex;
		flex-direction: column;
		gap: var(--space-4);
		padding: var(--space-5);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.ui-research-meta__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		font-weight: var(--weight-semibold);
	}

	.ui-research-meta__example {
		margin: 0;
	}

	/* Паспорт и исходы разделены воздухом, а не линиями: внутри карточки
	   достаточно одной границы — её собственной. */
	.ui-research-meta__outcomes {
		margin-block-start: var(--space-1);
	}

	/* Направление исхода — не оценка качества доказательств: цвет здесь
	   различает направление, а не «хорошо/плохо», и всегда идёт со словом. */
	.ui-research-meta__direction {
		white-space: nowrap;
		font-weight: var(--weight-semibold);

		&--increase {
			color: var(--color-accent-text);
		}

		&--decrease {
			color: var(--color-danger-text);
		}

		&--no_change,
		&--mixed,
		&--unclear {
			color: var(--color-text-muted);
		}
	}
</style>
