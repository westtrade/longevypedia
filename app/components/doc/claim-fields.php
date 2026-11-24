<?php

declare(strict_types=1);

/**
 * Карточка научного утверждения: формулировка, уверенность, направление
 * доказательств и границы применимости.
 *
 * Уверенность — это не «рейтинг статьи», а оценка того, насколько доказательства
 * выдерживают перенос: `established` для многократно воспроизведённого,
 * `speculative` для гипотезы. Направление доказательств отдельно от уверенности:
 * утверждение может быть уверенно опровергнутым.
 *
 * @var array<string, mixed> $claim
 */

$claim ??= [];

$confidenceLabels = [
	'established' => 'Established',
	'likely' => 'Likely',
	'uncertain' => 'Uncertain',
	'contested' => 'Contested',
	'speculative' => 'Speculative',
];

$directionLabels = [
	'supports' => 'Evidence supports it',
	'contradicts' => 'Evidence contradicts it',
	'mixed' => 'Evidence is mixed',
	'insufficient' => 'Evidence is insufficient',
];

$confidence = (string) ($claim['confidence'] ?? '');
$direction = (string) ($claim['evidence_direction'] ?? '');
$statement = (string) ($claim['statement'] ?? '');
$scope = (string) ($claim['scope'] ?? '');
$asOf = (string) ($claim['as_of'] ?? '');
?>
// Коды уверенности и направления — в data-атрибутах: на экране стоит перевод
// («Likely»), а машинное значение (`likely`) нужно тестам, экспорту и будущим
// фильтрам. Подменять одно другим нельзя: перевод — часть текста страницы,
// код — часть данных.
<section class="claim" aria-labelledby="claim-title"
	<?= attrs([
		'data-confidence' => $confidence !== '' ? $confidence : null,
		'data-evidence' => $direction !== '' ? $direction : null,
	]) ?>>
	<h2 class="claim__title" id="claim-title">Claim</h2>

	<?php if ($statement !== ''): ?>
		<blockquote class="claim__statement"><?= $this->e($statement) ?></blockquote>
	<?php endif; ?>

	<div class="claim__badges">
		<?php if ($confidence !== ''): ?>
			<span class="claim__badge claim__badge--<?= $this->e($confidence) ?>">
				<span class="claim__badge-label">Confidence</span>
				<span class="claim__badge-value"><?= $this->e($confidenceLabels[$confidence] ?? $confidence) ?></span>
			</span>
		<?php endif; ?>

		<?php if ($direction !== ''): ?>
			<span class="claim__badge claim__badge--direction">
				<span class="claim__badge-label">Evidence</span>
				<span class="claim__badge-value"><?= $this->e($directionLabels[$direction] ?? $direction) ?></span>
			</span>
		<?php endif; ?>

		<?php if ($asOf !== ''): ?>
			<span class="claim__badge">
				<span class="claim__badge-label">Assessed</span>
				<span class="claim__badge-value"><?= $this->e(longevypedia_date($asOf)) ?></span>
			</span>
		<?php endif; ?>
	</div>

	<?php if ($scope !== ''): ?>
		<p class="claim__scope">
			<span class="claim__scope-label">Scope.</span>
			<?= $this->e($scope) ?>
		</p>
	<?php endif; ?>
</section>

<style lang="scss">
	.claim {
		padding: 1.1rem 1.25rem;
		border: 1px solid var(--color-border);
		border-inline-start: 3px solid var(--color-accent);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.claim__title {
		margin: 0 0 0.6rem;
		font-size: var(--text-lg);
		font-family: var(--font-text);
	}

	.claim__statement {
		margin: 0 0 0.9rem;
		padding: 0;
		border: 0;
		font-size: var(--text-lg);
		line-height: 1.45;
		font-family: var(--font-text);
		color: var(--color-text);
	}

	.claim__badges {
		display: flex;
		flex-wrap: wrap;
		gap: 0.5rem;
	}

	.claim__badge {
		display: inline-flex;
		flex-direction: column;
		gap: 0.1rem;
		padding: 0.4rem 0.65rem;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-sm);
		background: var(--color-bg);
	}

	.claim__badge-label {
		font-size: var(--text-xs);
		text-transform: uppercase;
		letter-spacing: 0.06em;
		color: var(--color-text-subtle);
	}

	.claim__badge-value {
		font-size: var(--text-sm);
		font-weight: 600;
	}

	.claim__badge--established .claim__badge-value {
		color: var(--color-accent);
	}

	.claim__badge--likely .claim__badge-value {
		color: var(--color-accent);
	}

	.claim__badge--uncertain .claim__badge-value {
		color: var(--color-warning);
	}

	.claim__badge--contested .claim__badge-value {
		color: var(--color-danger);
	}

	.claim__badge--speculative .claim__badge-value {
		color: var(--color-text-muted);
	}

	.claim__scope {
		margin: 0.9rem 0 0;
		font-size: var(--text-sm);
		color: var(--color-text-muted);
		line-height: 1.55;
	}

	.claim__scope-label {
		font-weight: 600;
		color: var(--color-text);
	}
</style>
