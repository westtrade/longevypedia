<?php

declare(strict_types=1);

/**
 * Логотип Longevypedia: знак и текстовый вордмарк.
 *
 * Знак — зарезервированное место, а не готовая эмблема: простые окружности
 * читаются как «след» и не притворяются научной схемой. Когда появится
 * настоящий SVG-логотип, меняется только содержимое `.ui-wordmark__mark`,
 * разметка шапки и этот компонент остаются те же.
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
		<svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="16" cy="16" r="14" stroke="currentColor" stroke-opacity="0.35" stroke-width="1.5"/>
			<path d="M6 19c4-6 8-9 13-9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
			<path d="M13 24c5-4 8-8 9-13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
			<circle cx="20" cy="12" r="2" fill="currentColor"/>
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

	.ui-wordmark:hover .ui-wordmark__name {
		color: var(--color-brand-text);
	}

	.ui-wordmark:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
		border-radius: var(--radius-sm);
	}

	.ui-wordmark__mark {
		display: inline-flex;
		width: 1.75rem;
		height: 1.75rem;
		color: var(--color-brand);
	}

	.ui-wordmark__mark svg {
		width: 100%;
		height: 100%;
	}

	.ui-wordmark--lg .ui-wordmark__mark {
		width: 2.5rem;
		height: 2.5rem;
	}

	.ui-wordmark__text {
		display: flex;
		flex-direction: column;
		gap: 0;
		line-height: 1.1;
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
		letter-spacing: 0.12em;
		text-transform: uppercase;
	}
</style>
