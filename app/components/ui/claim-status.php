<?php

declare(strict_types=1);

/**
 * Статус научного утверждения: уверенность и направление доказательств.
 *
 * Две независимые шкалы, как и в `ui/evidence`: уверенность описывает, насколько
 * сообщество согласно, направление — что показывают данные. Обе приходят из
 * frontmatter утверждения и не вычисляются на странице.
 *
 * @var string $confidence established | likely | uncertain | contested | speculative
 * @var string $direction supports | contradicts | mixed | insufficient
 * @var string|null $asOf дата оценки, ISO
 * @var string|null $label подпись блока
 */

$confidence ??= 'uncertain';
$direction ??= 'insufficient';
$asOf ??= null;
$label ??= 'Статус утверждения';

$confidences = [
	'established' => ['label' => 'Установлено', 'tone' => 'success'],
	'likely' => ['label' => 'Вероятно', 'tone' => 'brand'],
	'uncertain' => ['label' => 'Неопределённо', 'tone' => 'neutral'],
	'contested' => ['label' => 'Оспаривается', 'tone' => 'warning'],
	'speculative' => ['label' => 'Спекулятивно', 'tone' => 'danger'],
];

$directions = [
	'supports' => 'Данные подтверждают',
	'contradicts' => 'Данные противоречат',
	'mixed' => 'Данные смешанные',
	'insufficient' => 'Данных недостаточно',
];

$conf = $confidences[$confidence] ?? $confidences['uncertain'];
?>
<div class="ui-claim-status">
	<span class="ui-claim-status__label"><?= e($label) ?></span>

	<span class="ui-claim-status__badge ui-claim-status__badge--<?= e($conf['tone']) ?>"><?= e($conf['label']) ?></span>

	<span class="ui-claim-status__direction"><?= e($directions[$direction] ?? $directions['insufficient']) ?></span>

	<?php if ($asOf !== null && $asOf !== ''): ?>
		<span class="ui-claim-status__date">на <?= e($asOf) ?></span>
	<?php endif ?>
</div>

<style lang="scss">
	.ui-claim-status {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: var(--space-2) var(--space-3);
		padding: var(--space-2) var(--space-3);
		border-left: 3px solid var(--color-brand);
		background: var(--color-brand-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-claim-status__label {
		color: var(--color-text-subtle);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-claim-status__badge {
		padding: 1px var(--space-2);
		border: 1px solid currentColor;
		border-radius: var(--radius-full);
		font-weight: var(--weight-semibold);
	}

	.ui-claim-status__badge--success {
		color: var(--color-success-text);
	}

	.ui-claim-status__badge--brand {
		color: var(--color-brand-text);
	}

	.ui-claim-status__badge--neutral {
		color: var(--color-text-muted);
	}

	.ui-claim-status__badge--warning {
		color: var(--color-warning-text);
	}

	.ui-claim-status__badge--danger {
		color: var(--color-danger-text);
	}

	.ui-claim-status__direction {
		color: var(--color-text);
	}

	.ui-claim-status__date {
		color: var(--color-text-subtle);
	}
</style>
