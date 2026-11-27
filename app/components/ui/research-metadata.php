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
 * Печатает его `ui/key-value` (моноширинным, рядом со значением), поэтому
 * здесь едет только пара «подпись — код».
 *
 * Поля идут одним плоским списком в порядке чтения: сначала «кто и как
 * изучали», затем «что делали и с чем сравнивали». Заголовки групп пробовали —
 * в маленькой карточке они добавляли третий этаж подписей («Study» →
 * «Design and population» → «DESIGN») и читались как мешанина.
 *
 * Пустые поля не печатаются: строка «Sample size: —» создаёт впечатление, что
 * данные есть, но не внесены. Группа, в которой не осталось полей, тоже не
 * печатается.
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

// Значение дизайна приходит парой: подпись для человека и машинный код рядом.
$designValue = $design !== '' ? e($designLabel) : '';

$items = [
	['label' => 'Design', 'value' => $designValue, 'code' => $design !== '' ? $design : null],
	['label' => 'Species', 'value' => e((string) ($study['species'] ?? ''))],
	['label' => 'Population', 'value' => e((string) ($study['population'] ?? ''))],
	['label' => 'Sample size', 'value' => e((string) ($study['sample_size'] ?? ''))],
	['label' => 'Registration', 'value' => e((string) ($study['registration'] ?? ''))],
	['label' => 'Intervention', 'value' => e((string) ($study['intervention'] ?? ''))],
	['label' => 'Comparator', 'value' => e((string) ($study['comparator'] ?? ''))],
	['label' => 'Duration', 'value' => e((string) ($study['duration'] ?? ''))],
];

// Пустые поля не печатаются: строка «Sample size: —» создаёт впечатление,
// что данные есть, но не внесены. Код дизайна — не текст, поэтому пустое
// значение отсекаем по подписи, а не по разметке.
$items = array_values(array_filter(
	$items,
	static fn (array $item): bool => trim(strip_tags((string) $item['value'])) !== '',
));

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
