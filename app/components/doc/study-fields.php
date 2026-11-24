<?php

declare(strict_types=1);

/**
 * Карточка исследования: дизайн, популяция, вмешательство, результаты.
 *
 * Поля печатаются только при наличии — пустая строка «Sample size: —» создаёт
 * впечатление, что данные есть, но не внесены. Подписи дизайна берутся из
 * `StudyDesigns`: схема фиксирует машинные значения, словарь — их перевод, и
 * словарь один на весь проект, иначе страница индекса и карточка расходятся.
 *
 * @var array<string, mixed> $study
 * @var bool                 $isExample
 */

$study ??= [];
$isExample ??= false;

$designs = \Longevypedia\Content\StudyDesigns::LABELS;

$directions = [
	'increase' => 'Increase',
	'decrease' => 'Decrease',
	'no_change' => 'No change',
	'mixed' => 'Mixed',
	'unclear' => 'Unclear',
];

$design = (string) ($study['design'] ?? '');
$outcomes = is_array($study['outcomes'] ?? null) ? $study['outcomes'] : [];

// Машинный код остаётся в разметке рядом с переводом: подпись читает человек,
// `data-code` — скрипт (тест, экспорт, фильтр). Без него `animal_experiment`
// из фронтматтера исчезает из HTML, и сверить страницу с источником нечем.
$codes = ['Design' => $design];

$fields = [
	'Design' => $designs[$design] ?? ($design !== '' ? $design : ''),
	'Species' => (string) ($study['species'] ?? ''),
	'Population' => (string) ($study['population'] ?? ''),
	'Sample size' => (string) ($study['sample_size'] ?? ''),
	'Intervention' => (string) ($study['intervention'] ?? ''),
	'Comparator' => (string) ($study['comparator'] ?? ''),
	'Duration' => (string) ($study['duration'] ?? ''),
	'Registration' => (string) ($study['registration'] ?? ''),
];
?>
<section class="study" aria-labelledby="study-title">
	<h2 class="study__title" id="study-title">Study</h2>

	<?php if ($isExample): ?>
		<p class="study__example">
			This is an example record that demonstrates the document format. It is not a real publication.
		</p>
	<?php endif; ?>

	<dl class="study__fields">
		<?php foreach ($fields as $label => $value): ?>
			<?php if (trim($value) !== ''): ?>
				<div class="study__field"<?= attrs(['data-code' => ($codes[$label] ?? '') !== '' ? $codes[$label] : null]) ?>>
					<dt class="study__label"><?= $this->e($label) ?></dt>
					<dd class="study__value"><?= $this->e($value) ?></dd>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</dl>

	<?php if ($outcomes !== []): ?>
		<table class="study__outcomes">
			<caption class="study__caption">Reported outcomes</caption>
			<thead>
				<tr>
					<th scope="col">Outcome</th>
					<th scope="col">Direction</th>
					<th scope="col">Measure</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($outcomes as $outcome): ?>
					<?php $direction = (string) ($outcome['direction'] ?? ''); ?>
					<tr>
						<th scope="row"><?= $this->e((string) ($outcome['name'] ?? '')) ?></th>
						<td>
							<span class="study__direction study__direction--<?= $this->e($direction) ?>">
								<?= $this->e($directions[$direction] ?? $direction) ?>
							</span>
						</td>
						<td><?= $this->e((string) ($outcome['measure'] ?? '')) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</section>

<style lang="scss">
	.study {
		padding: 1.1rem 1.25rem;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.study__title {
		margin: 0 0 0.75rem;
		font-size: var(--text-lg);
		font-family: var(--font-text);
	}

	.study__example {
		margin: 0 0 0.9rem;
		padding: 0.6rem 0.75rem;
		border-inline-start: 3px solid var(--color-warning);
		background: var(--color-warning-soft);
		font-size: var(--text-sm);
		border-radius: var(--radius-sm);
	}

	.study__fields {
		display: grid;
		grid-template-columns: minmax(8rem, 12rem) minmax(0, 1fr);
		gap: 0.45rem 1rem;
		margin: 0 0 1.1rem;
		font-size: var(--text-sm);
	}

	.study__field {
		display: contents;
	}

	.study__label {
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.06em;
		padding-top: 0.15rem;
	}

	.study__value {
		margin: 0;
		line-height: 1.5;
	}

	.study__outcomes {
		width: 100%;
		border-collapse: collapse;
		font-size: var(--text-sm);
	}

	.study__caption {
		text-align: start;
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.06em;
		color: var(--color-text-subtle);
		padding-bottom: 0.4rem;
	}

	.study__outcomes th,
	.study__outcomes td {
		text-align: start;
		padding: 0.4rem 0.6rem;
		border-bottom: 1px solid var(--color-border);
		vertical-align: top;
	}

	.study__outcomes thead th {
		font-size: var(--text-xs);
		color: var(--color-text-subtle);
		text-transform: uppercase;
		letter-spacing: 0.05em;
	}

	.study__direction {
		white-space: nowrap;
		font-weight: 600;

		&--increase {
			color: var(--color-accent);
		}

		&--decrease {
			color: var(--color-danger);
		}

		&--no_change,
		&--mixed,
		&--unclear {
			color: var(--color-text-muted);
		}
	}

	@media (max-width: 40rem) {
		.study__fields {
			grid-template-columns: 1fr;
			gap: 0.15rem;
		}

		.study__label {
			padding-top: 0.4rem;
		}
	}
</style>
