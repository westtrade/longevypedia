<?php

declare(strict_types=1);

/**
 * Связанные утверждения, разложенные по направлению доказательств.
 *
 * Блок отвечает на вопрос «что известно по этому вопросу и в какую сторону»,
 * не вынося приговора: направления идут отдельными группами, а не единым
 * рейтингом, поэтому противоречащие данные видно так же хорошо, как
 * подтверждающие. Цвет группы — только подсказка, смысл несут подпись и счётчик.
 *
 * Качество доказательств здесь не выводится: это отдельная характеристика,
 * которая присваивается по методологии и живёт в самом документе. Уверенность
 * утверждения (established … speculative) — тоже его собственное поле, а не
 * вычисленное из направления.
 *
 * @var array<string, list<\Longevypedia\Content\Document>> $groups код направления => утверждения
 * @var string $title заголовок блока
 * @var string $id якорь блока
 * @var int $headingLevel уровень заголовка
 * @var array<string, string> $labels переопределение подписей направлений
 */

$groups ??= [];
$title ??= 'Related claims';
$id ??= 'related-claims';
$headingLevel ??= 2;
$labels ??= [];

// Подписи по умолчанию английские: это язык интерфейса сайта.
$directions = [
	'supports' => ['label' => 'Supporting evidence', 'tone' => 'success'],
	'contradicts' => ['label' => 'Contradicting evidence', 'tone' => 'danger'],
	'mixed' => ['label' => 'Mixed results', 'tone' => 'warning'],
	'insufficient' => ['label' => 'Insufficient data', 'tone' => 'info'],
];

$confidences = [
	'established' => 'Established',
	'likely' => 'Likely',
	'uncertain' => 'Uncertain',
	'contested' => 'Contested',
	'speculative' => 'Speculative',
];

$headingTag = 'h' . max(2, min(6, (int) $headingLevel));

// Порядок задаёт шкала направлений, а не порядок ключей в данных: иначе
// группы переставлялись бы при каждом изменении набора утверждений.
$ordered = [];

foreach ($directions as $code => $direction) {
	if (($groups[$code] ?? []) !== []) {
		$ordered[$code] = $groups[$code];
	}
}

foreach ($groups as $code => $documents) {
	if (!isset($ordered[$code]) && $documents !== []) {
		$ordered[$code] = $documents;
	}
}

if ($ordered === []) {
	return;
}
?>
<section class="ui-evidence-summary" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-title">
	<<?= $headingTag ?> class="ui-evidence-summary__title" id="<?= e($id) ?>-title"><?= e($title) ?></<?= $headingTag ?>>

	<div class="ui-evidence-summary__groups">
		<?php foreach ($ordered as $code => $documents): ?>
			<?php
			$direction = $directions[$code] ?? ['label' => $code, 'tone' => 'neutral'];
			$directionLabel = $labels[$code] ?? $direction['label'];
			?>
			<div class="ui-evidence-summary__group ui-evidence-summary__group--<?= e($direction['tone']) ?>">
				<p class="ui-evidence-summary__label">
					<span class="ui-evidence-summary__marker" aria-hidden="true"></span>
					<span class="ui-evidence-summary__label-text"><?= e($directionLabel) ?></span>
					<span class="ui-evidence-summary__count"><?= count($documents) ?></span>
				</p>

				<ul class="ui-evidence-summary__list">
					<?php foreach ($documents as $claim): ?>
						<?php $claimMeta = (array) $claim->metaValue('claim', []); ?>
						<li class="ui-evidence-summary__item">
							<a class="ui-evidence-summary__link" href="<?= e($claim->url) ?>"><?= e($claim->title()) ?></a>
							<?php if (isset($confidences[(string) ($claimMeta['confidence'] ?? '')])): ?>
								<?= $this->insert('components::ui/badge', [
									'label' => $confidences[(string) $claimMeta['confidence']],
									'variant' => 'outline',
									'size' => 'sm',
								]) ?>
							<?php endif ?>
						</li>
					<?php endforeach ?>
				</ul>
			</div>
		<?php endforeach ?>
	</div>
</section>

<style lang="scss">
	.ui-evidence-summary {
		display: flex;
		flex-direction: column;
		gap: var(--space-4);
		padding: var(--space-5);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.ui-evidence-summary__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		font-weight: var(--weight-semibold);
		line-height: var(--leading-snug);
	}

	.ui-evidence-summary__groups {
		display: grid;
		gap: var(--space-4);
		grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr));
	}

	.ui-evidence-summary__group {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		padding-inline-start: var(--space-3);
		border-inline-start: 2px solid var(--color-border-strong);
	}

	/* Цвет полосы дублирует подпись, но никогда её не заменяет. */
	.ui-evidence-summary__group--success {
		border-inline-start-color: var(--color-success);
	}

	.ui-evidence-summary__group--danger {
		border-inline-start-color: var(--color-danger);
	}

	.ui-evidence-summary__group--warning {
		border-inline-start-color: var(--color-warning);
	}

	.ui-evidence-summary__group--info {
		border-inline-start-color: var(--color-info);
	}

	.ui-evidence-summary__label {
		display: flex;
		align-items: baseline;
		gap: var(--space-2);
		margin: 0;
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
		color: var(--color-text-muted);
	}

	.ui-evidence-summary__marker {
		align-self: center;
		width: 0.5rem;
		height: 0.5rem;
		border-radius: var(--radius-full);
		background: var(--color-border-strong);
	}

	.ui-evidence-summary__group--success .ui-evidence-summary__marker {
		background: var(--color-success);
	}

	.ui-evidence-summary__group--danger .ui-evidence-summary__marker {
		background: var(--color-danger);
	}

	.ui-evidence-summary__group--warning .ui-evidence-summary__marker {
		background: var(--color-warning);
	}

	.ui-evidence-summary__group--info .ui-evidence-summary__marker {
		background: var(--color-info);
	}

	.ui-evidence-summary__count {
		color: var(--color-text-subtle);
		font-variant-numeric: tabular-nums;
		letter-spacing: normal;
	}

	.ui-evidence-summary__list {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.ui-evidence-summary__item {
		display: flex;
		flex-wrap: wrap;
		align-items: baseline;
		gap: var(--space-1) var(--space-2);
	}

	.ui-evidence-summary__link {
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
		text-decoration-color: color-mix(in oklch, var(--color-brand) 40%, transparent);
		text-underline-offset: 0.2em;
	}

	.ui-evidence-summary__link:hover {
		color: var(--color-brand-text);
		text-decoration-color: currentColor;
	}
</style>
