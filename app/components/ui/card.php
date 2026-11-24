<?php

declare(strict_types=1);

/**
 * Карточка.
 *
 * Проектный компонент: `components::ui/card`.
 *
 * Почему не `bolt://ui-kit/card`: встроенная карточка знает только заголовок,
 * тело и действия, у неё нет надзаголовка, медиа-слота и подвала, а вариантов
 * два (`blue|outlined`). Карточка проекта — оболочка, внутри которой живут
 * article-card, research-card и author-card.
 *
 * @param string|null $title
 * @param string|null $eyebrow   надзаголовок (тип документа, раздел)
 * @param mixed       $children  тело
 * @param mixed       $actions   действия в подвале
 * @param mixed       $footer    произвольный подвал вместо actions
 * @param mixed       $media     медиа-слот над заголовком
 * @param string|null $href      если задан — заголовок становится ссылкой
 * @param string      $variant   default | outlined | flat | brand
 * @param string|null $meta      строка метаданных под заголовком
 * @param array       $attrs
 */

$title ??= null;
$eyebrow ??= null;
$children ??= null;
$actions ??= null;
$footer ??= null;
$media ??= null;
$href ??= null;
$variant ??= 'default';
$meta ??= null;
$attrs ??= [];

$tag = $href !== null ? 'a' : 'div';
?>
<article<?= attrs($attrs, ['class' => clsx('ui-card', 'ui-card--' . $variant)]) ?>>
	<?php if ($media !== null): ?>
		<div class="ui-card__media"><?php render($media); ?></div>
	<?php endif; ?>

	<div class="ui-card__body">
		<?php if ($eyebrow !== null): ?>
			<p class="ui-card__eyebrow"><?= $this->e((string) $eyebrow) ?></p>
		<?php endif; ?>

		<?php if ($title !== null): ?>
			<h3 class="ui-card__title">
				<?php if ($href !== null): ?>
					<a class="ui-card__link" href="<?= $this->e((string) $href) ?>"><?= $this->e((string) $title) ?></a>
				<?php else: ?>
					<?= $this->e((string) $title) ?>
				<?php endif; ?>
			</h3>
		<?php endif; ?>

		<?php if ($meta !== null): ?>
			<p class="ui-card__meta"><?= $this->e((string) $meta) ?></p>
		<?php endif; ?>

		<?php if ($children !== null): ?>
			<div class="ui-card__content"><?php render($children); ?></div>
		<?php endif; ?>
	</div>

	<?php if ($actions !== null || $footer !== null): ?>
		<div class="ui-card__footer">
			<?php if ($footer !== null): ?>
				<?php render($footer); ?>
			<?php else: ?>
				<div class="ui-card__actions"><?php render($actions); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</article>

<style lang="scss">
	.ui-card {
		position: relative;
		display: flex;
		flex-direction: column;
		min-width: 0;
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.ui-card__media {
		overflow: hidden;
		border-start-start-radius: var(--radius-md);
		border-start-end-radius: var(--radius-md);
		background: var(--color-surface-alt);
	}

	.ui-card__media img {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.ui-card__body {
		display: flex;
		flex: 1;
		flex-direction: column;
		gap: var(--space-2);
		padding: var(--space-5);
	}

	.ui-card__eyebrow {
		color: var(--color-text-muted);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-card__title {
		font-size: var(--text-lg);
		line-height: var(--leading-snug);
	}

	/* Ссылка растянута на всю карточку: кликабельна вся площадь, но текст
	   остаётся текстом — выделение и контекстное меню работают как обычно. */
	.ui-card__link {
		color: var(--color-text);
		text-decoration: none;
	}

	.ui-card__link::after {
		content: '';
		position: absolute;
		inset: 0;
		border-radius: inherit;
	}

	.ui-card__link:hover {
		color: var(--color-brand);
		text-decoration: underline;
		text-underline-offset: 0.18em;
	}

	.ui-card__link:focus-visible {
		outline: none;
	}

	.ui-card__link:focus-visible::after {
		box-shadow: var(--focus-ring);
	}

	.ui-card__meta {
		color: var(--color-text-muted);
		font-size: var(--text-xs);
		line-height: var(--leading-normal);
	}

	.ui-card__content {
		color: var(--color-text-muted);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.ui-card__footer {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		align-items: center;
		justify-content: space-between;
		padding: var(--space-3) var(--space-5) var(--space-4);
		border-top: 1px solid var(--color-border);
	}

	.ui-card__actions {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
	}

	.ui-card--outlined {
		border-color: var(--color-border-strong);
		background: transparent;
	}

	.ui-card--flat {
		border-color: transparent;
		background: var(--color-surface-alt);
	}

	/* Brand: редкая карточка-акцент — призыв или важное методологическое
	   замечание. Больше одной на страницу не ставить. */
	.ui-card--brand {
		border-color: var(--color-brand);
		background: var(--color-brand-subtle);
	}

	.ui-card--brand .ui-card__content,
	.ui-card--brand .ui-card__meta {
		color: var(--color-brand-text);
	}

	.ui-card--brand .ui-card__footer {
		border-top-color: color-mix(in oklch, var(--color-brand) 25%, transparent);
	}
</style>
