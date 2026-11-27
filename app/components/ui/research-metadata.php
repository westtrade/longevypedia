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
 * Поля разделены на две группы: «кто и как изучали» и «что делали и с чем
 * сравнивали». Группы читаются быстрее, чем восемь строк подряд, а `wide`
 * отдаёт прозаические поля («Population», «Intervention») на всю ширину —
 * в двухколоночном режиме они иначе рвутся на пять строк.
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

$fields = [
	[
		'title' => 'Design and population',
		'items' => [
			['label' => 'Design', 'value' => $designValue, 'code' => $design !== '' ? $design : null],
			['label' => 'Species', 'value' => e((string) ($study['species'] ?? ''))],
			['label' => 'Population', 'value' => e((string) ($study['population'] ?? '')), 'wide' => true],
			['label' => 'Sample size', 'value' => e((string) ($study['sample_size'] ?? ''))],
			['label' => 'Registration', 'value' => e((string) ($study['registration'] ?? ''))],
		],
	],
	[
		'title' => 'Intervention and comparison',
		'items' => [
			['label' => 'Intervention', 'value' => e((string) ($study['intervention'] ?? '')), 'wide' => true],
			['label' => 'Comparator', 'value' => e((string) ($study['comparator'] ?? '')), 'wide' => true],
			['label' => 'Duration', 'value' => e((string) ($study['duration'] ?? ''))],
		],
	],
];

$groups = [];

foreach ($fields as $group) {
	$items = [];

	foreach ($group['items'] as $item) {
		// Код дизайна — не текст: пустое значение отсекаем по подписи, а не по
		// разметке, потому что у него значение уже без тегов.
		if (trim(strip_tags((string) $item['value'])) === '') {
			continue;
		}

		$items[] = $item;
	}

	if ($items === []) {
		continue;
	}

	$groups[] = ['title' => $group['title'], 'items' => $items];
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
		'groups' => $groups,
		// Две колонки: короткие поля встают парами, прозаические занимают строку.
		'columns' => 2,
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
