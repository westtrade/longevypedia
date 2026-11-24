<?php

declare(strict_types=1);

/**
 * Карточка научного утверждения: формулировка, уверенность, направление
 * доказательств и границы применимости.
 *
 * Собирается из `ui/quote` и `ui/claim-status`, а не повторяет их разметку:
 * статус утверждения — самостоятельный компонент набора, и дублировать его
 * шкалы здесь значило бы держать две копии одной семантики.
 *
 * Уверенность — не «рейтинг статьи», а оценка того, насколько доказательства
 * выдерживают перенос: `established` для многократно воспроизведённого,
 * `speculative` для гипотезы. Направление доказательств отдельно от
 * уверенности: утверждение может быть уверенно опровергнутым.
 *
 * @var array<string, mixed> $claim поля `claim` из фронтматтера
 */

$claim ??= [];

$confidence = (string) ($claim['confidence'] ?? '');
$direction = (string) ($claim['evidence_direction'] ?? '');
$statement = (string) ($claim['statement'] ?? '');
$scope = (string) ($claim['scope'] ?? '');
$asOf = (string) ($claim['as_of'] ?? '');

if ($statement === '' && $confidence === '' && $direction === '') {
	return;
}

// Коды уверенности и направления — в data-атрибутах: на экране стоит перевод
// («Likely»), а машинное значение (`likely`) нужно тестам, экспорту и будущим
// фильтрам. Подменять одно другим нельзя: перевод — часть текста страницы,
// код — часть данных.
?>
<section class="ui-claim" aria-labelledby="claim-title"<?= attrs([
	'data-confidence' => $confidence !== '' ? $confidence : null,
	'data-evidence' => $direction !== '' ? $direction : null,
]) ?>>
	<h2 class="ui-claim__title" id="claim-title">Claim</h2>

	<?php if ($statement !== ''): ?>
		<?= $this->insert('components::ui/quote', ['text' => $statement]) ?>
	<?php endif ?>

	<?php if ($confidence !== '' || $direction !== ''): ?>
		<?= $this->insert('components::ui/claim-status', [
			'confidence' => $confidence !== '' ? $confidence : 'uncertain',
			'direction' => $direction !== '' ? $direction : 'insufficient',
			'asOf' => $asOf !== '' ? longevypedia_date($asOf) : null,
		]) ?>
	<?php endif ?>

	<?php if ($scope !== ''): ?>
		<p class="ui-claim__scope">
			<span class="ui-claim__scope-label">Scope.</span>
			<?= e($scope) ?>
		</p>
	<?php endif ?>
</section>

<style lang="scss">
	.ui-claim {
		display: flex;
		flex-direction: column;
		gap: var(--space-4);
		padding: var(--space-5);
		border: 1px solid var(--color-border);
		border-inline-start: 3px solid var(--color-accent);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.ui-claim__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		font-weight: var(--weight-semibold);
	}

	/* Цитата уже несёт собственную типографику и отступы — здесь только
	   убираем внешние поля, чтобы блок не разъезжался. */
	.ui-claim .ui-quote {
		margin: 0;
	}

	.ui-claim__scope {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-serif);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.ui-claim__scope-label {
		color: var(--color-text);
		font-family: var(--font-ui);
		font-weight: var(--weight-semibold);
	}
</style>
