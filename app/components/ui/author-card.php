<?php

declare(strict_types=1);

/**
 * Карточка автора: имя, роль и ссылка на профиль или источник.
 *
 * Отдельный компонент, а не карточка документа: у автора нет аннотации и
 * тематик, зато есть роль («Reviewer», «Corresponding author») и внешняя
 * ссылка — ORCID или страница публикации. Собирается из `ui/card`, чтобы
 * границы, отступы и типографика оставались общими с остальными карточками.
 *
 * @param string $name имя автора
 * @param string|null $role роль в публикации или рецензии
 * @param string|null $href ссылка на профиль (ORCID, сайт)
 * @param string|null $affiliation организация
 * @param array $attrs дополнительные атрибуты карточки
 */

$name ??= '';
$role ??= null;
$href ??= null;
$affiliation ??= null;
$attrs ??= [];

if ($name === '') {
	return;
}

$this->insert('components::ui/card', [
	'eyebrow' => $role,
	'title' => $name,
	'href' => $href,
	'meta' => $affiliation,
	'titleTag' => 'h3',
	'variant' => 'flat',
	'attrs' => ['class' => clsx('ui-author-card', $attrs['class'] ?? null)] + $attrs,
	'children' => static function () use ($href): void {
		if ($href === null) {
			return;
		}
		?>
		<p class="ui-author-card__link"><?= e($href) ?></p>
		<?php
	},
]);
?>

<style lang="scss">
	/* Внешний адрес печатается текстом, а не второй ссылкой: заголовок уже
	   ссылка, и две ссылки на один адрес путают и скринридер, и читателя. */
	.ui-author-card__link {
		margin: 0;
		color: var(--color-text-subtle);
		font-family: var(--font-mono);
		font-size: var(--text-xs);
		overflow-wrap: anywhere;
	}
</style>
