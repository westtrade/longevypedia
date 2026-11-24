<?php

declare(strict_types=1);

/**
 * Ссылка на источник внутри текста: номер, автор и год.
 *
 * Отличается от `ui/source-list` назначением: список закрывает раздел
 * «Источники» со всеми идентификаторами, а цитата — это короткая отсылка в
 * предложении. Идентификаторы (DOI, PMID) здесь не печатаются: их место в
 * списке источников, иначе один и тот же номер занимает полстроки в тексте.
 *
 * @param string $text подпись: «Harrison et al., 2009»
 * @param string|null $href адрес источника
 * @param int|null $number номер в списке источников
 * @param string|null $note пояснение (например, «не рецензировано»)
 * @param string $id идентификатор элемента, на который ссылается сноска
 */

$text ??= '';
$href ??= null;
$number ??= null;
$note ??= null;
$id ??= null;

if ($text === '') {
	return;
}
?>
<span class="ui-citation"<?= attrs(['id' => $id]) ?>>
	<?php if ($number !== null): ?>
		<span class="ui-citation__number"><?= e((string) $number) ?></span>
	<?php endif ?>
	<?php if ($href !== null): ?>
		<a class="ui-citation__link" href="<?= e($href) ?>" rel="noopener noreferrer"><?= e($text) ?></a>
	<?php else: ?>
		<cite class="ui-citation__text"><?= e($text) ?></cite>
	<?php endif ?>
	<?php if ($note !== null): ?>
		<span class="ui-citation__note"><?= e($note) ?></span>
	<?php endif ?>
</span>

<style lang="scss">
	.ui-citation {
		display: inline;
		font-family: var(--font-ui);
		font-size: var(--text-sm);
	}

	.ui-citation__number {
		margin-inline-end: var(--space-1);
		color: var(--color-text-subtle);
		font-variant-numeric: tabular-nums;
	}

	.ui-citation__link,
	.ui-citation__text {
		color: var(--color-brand-text);
		font-style: normal;
	}

	.ui-citation__note {
		margin-inline-start: var(--space-1);
		color: var(--color-text-subtle);
		font-size: var(--text-xs);
	}
</style>
