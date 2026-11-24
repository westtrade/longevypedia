<?php

declare(strict_types=1);

/**
 * Индикатор доказательности.
 *
 * Два независимых параметра, и это главное:
 *   `direction` — что говорят результаты относительно утверждения
 *                 (подтверждают, противоречат, смешанно, нейтрально, мало данных);
 *   `quality`   — насколько надёжны сами данные (высокая, умеренная, низкая,
 *                 очень низкая, не оценена).
 *
 * Компонент не выводит одно из другого: сильный эффект в маленькой выборке —
 * это `supports` + `low`, а не «умеренное доказательство». Качество
 * присваивается по методологии (docs/specifications/methodology.md), а не
 * вычисляется из текста, поэтому `quality` приходит параметром и не имеет
 * значения по умолчанию, скрывающего отсутствие оценки.
 *
 * @var string $direction supports | contradicts | mixed | neutral | insufficient
 * @var string $quality high | moderate | low | very-low | unassessed
 * @var string|null $basis на чём основана оценка качества
 * @var string|null $label подпись блока
 */

$direction ??= 'insufficient';
$quality ??= 'unassessed';
$basis ??= null;
$label ??= 'Доказательность';

$directions = [
	'supports' => ['label' => 'Подтверждает', 'icon' => 'check', 'tone' => 'success'],
	'contradicts' => ['label' => 'Противоречит', 'icon' => 'close', 'tone' => 'danger'],
	'mixed' => ['label' => 'Смешанные результаты', 'icon' => 'graph', 'tone' => 'warning'],
	'neutral' => ['label' => 'Без эффекта', 'icon' => 'document', 'tone' => 'neutral'],
	'insufficient' => ['label' => 'Недостаточно данных', 'icon' => 'alert', 'tone' => 'neutral'],
];

$qualities = [
	'high' => ['label' => 'Высокое', 'level' => 4],
	'moderate' => ['label' => 'Умеренное', 'level' => 3],
	'low' => ['label' => 'Низкое', 'level' => 2],
	'very-low' => ['label' => 'Очень низкое', 'level' => 1],
	'unassessed' => ['label' => 'Не оценено', 'level' => 0],
];

$dir = $directions[$direction] ?? $directions['insufficient'];
$qual = $qualities[$quality] ?? $qualities['unassessed'];
?>
<div class="ui-evidence">
	<span class="ui-evidence__label"><?= e($label) ?></span>

	<span class="ui-evidence__direction ui-evidence__direction--<?= e($dir['tone']) ?>">
		<?= $this->insert('bolt://ui-kit/icon', ['name' => $dir['icon'], 'size' => 14]) ?>
		<span><?= e($dir['label']) ?></span>
	</span>

	<span class="ui-evidence__quality">
		<span class="ui-evidence__scale" aria-hidden="true">
			<?php for ($step = 1; $step <= 4; $step++): ?>
				<span class="ui-evidence__step<?= $step <= $qual['level'] ? ' ui-evidence__step--on' : '' ?>"></span>
			<?php endfor ?>
		</span>
		<span class="ui-evidence__quality-text">
			Качество данных: <strong><?= e($qual['label']) ?></strong>
		</span>
	</span>

	<?php if ($basis !== null && $basis !== ''): ?>
		<span class="ui-evidence__basis"><?= e($basis) ?></span>
	<?php endif ?>
</div>

<style lang="scss">
	.ui-evidence {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: var(--space-2) var(--space-3);
		padding: var(--space-2) var(--space-3);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface-alt);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-evidence__label {
		color: var(--color-text-subtle);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-evidence__direction {
		display: inline-flex;
		align-items: center;
		gap: var(--space-1);
		font-weight: var(--weight-medium);
	}

	.ui-evidence__direction--success {
		color: var(--color-success-text);
	}

	.ui-evidence__direction--danger {
		color: var(--color-danger-text);
	}

	.ui-evidence__direction--warning {
		color: var(--color-warning-text);
	}

	.ui-evidence__direction--neutral {
		color: var(--color-text-muted);
	}

	.ui-evidence__quality {
		display: inline-flex;
		align-items: center;
		gap: var(--space-2);
		color: var(--color-text-muted);
	}

	.ui-evidence__scale {
		display: inline-flex;
		gap: 2px;
	}

	.ui-evidence__step {
		width: 0.4rem;
		height: 0.9rem;
		border-radius: 1px;
		background: var(--color-border-strong);
	}

	.ui-evidence__step--on {
		background: var(--color-brand);
	}

	.ui-evidence__quality-text strong {
		color: var(--color-text);
		font-weight: var(--weight-semibold);
	}

	.ui-evidence__basis {
		flex-basis: 100%;
		color: var(--color-text-subtle);
		line-height: var(--leading-normal);
	}
</style>
