<?php

declare(strict_types=1);

/**
 * Логотип Longevypedia: знак и текстовый вордмарк.
 *
 * Знак повторяет контур `public/favicon.svg` — тот же, что виден во вкладке, —
 * но без подложки: в шапке квадрат не нужен, знак рисуется линиями прямо на
 * фоне страницы. Поэтому весь рисунок в `currentColor`, а цвет задаёт токен
 * `--color-wordmark-mark`: в светлой теме брендовый, в тёмной — цвет названия.
 * У фавикона подложка остаётся: там знак живёт на произвольном фоне браузера.
 * Когда появится настоящий SVG-логотип, меняется только содержимое
 * `.ui-wordmark__mark`; разметка шапки и этот компонент остаются те же.
 *
 * @var string $href адрес ссылки; null — логотип не ссылка
 * @var string $label видимое имя
 * @var string|null $tagline подпись под именем
 * @var string $size md | lg
 * @var array $attrs дополнительные атрибуты ссылки
 */

$href ??= '/';
$label ??= 'Longevypedia';
$tagline ??= null;
$size ??= 'md';
$attrs ??= [];

$tag = $href !== null ? 'a' : 'span';
?>
<<?= $tag ?><?= attrs($attrs, ['class' => clsx('ui-wordmark', 'ui-wordmark--' . $size)]) ?><?= $href !== null ? ' href="' . e($href) . '"' : '' ?>>
	<span class="ui-wordmark__mark" aria-hidden="true">
		<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
			<circle cx="16" cy="16" r="9.5" fill="none" stroke="currentColor" stroke-width="1.4" opacity=".45"/>
			<path d="M16 6.5a9.5 9.5 0 0 1 0 19" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
			<circle cx="16" cy="16" r="3.4" fill="currentColor"/>
		</svg>
	</span>
	<span class="ui-wordmark__text">
		<span class="ui-wordmark__name"><?= e($label) ?></span>
		<?php if ($tagline !== null): ?>
			<span class="ui-wordmark__tagline"><?= e($tagline) ?></span>
		<?php endif ?>
	</span>
</<?= $tag ?>>

<style lang="scss">
	.ui-wordmark {
		display: inline-flex;
		align-items: center;
		gap: var(--space-2);
		color: var(--color-text);
		text-decoration: none;
	}

	/* Наведение красит название и знак вместе: иначе в тёмной теме знак
	   остаётся светлым, а название уходит в бирюзу — логотип распадается на
	   две части. */
	.ui-wordmark:hover .ui-wordmark__name,
	.ui-wordmark:hover .ui-wordmark__mark {
		color: var(--color-brand-text);
	}

	.ui-wordmark:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
		border-radius: var(--radius-sm);
	}

	/* Цвет знака — отдельный токен: в светлой теме он брендовый, в тёмной
	   совпадает с названием. Бирюзовый знак рядом со светлым текстом в тёмной
	   теме читался как вторая ссылка, а не как часть одного логотипа. */
	.ui-wordmark__mark {
		display: inline-flex;
		flex: none;
		width: 1.75rem;
		height: 1.75rem;
		color: var(--color-wordmark-mark);
	}

	.ui-wordmark__mark svg {
		width: 100%;
		height: 100%;
	}

	.ui-wordmark--lg .ui-wordmark__mark {
		width: 2.5rem;
		height: 2.5rem;
	}

	/* Строки не должны соприкасаться: у Source Serif 4 выносные элементы
	   (g, y, p) выходят за кегельную площадку, и при line-height 1.1 нижние
	   хвосты названия налезали на капитель подписи. */
	.ui-wordmark__text {
		display: flex;
		flex-direction: column;
		gap: 0.125rem;
		line-height: 1.25;
	}

	.ui-wordmark__name {
		font-family: var(--font-serif);
		font-size: var(--text-xl);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-tight);
	}

	.ui-wordmark--lg .ui-wordmark__name {
		font-size: var(--text-3xl);
	}

	/* Подпись набрана капителью, как в брендбуке: строчные в мелком кегле
	   читаются как обычный текст и спорят с названием. */
	.ui-wordmark__tagline {
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: 0.625rem;
		line-height: 1.2;
		letter-spacing: 0.12em;
		text-transform: uppercase;
	}
</style>
