<?php

declare(strict_types=1);

/**
 * Цитата с атрибуцией.
 *
 * Отдельный компонент от `blockquote` в тексте статьи: здесь важна подпись
 * источника, иначе цитата читается как мнение редакции.
 *
 * @var string $text текст цитаты
 * @var string|null $cite кто сказал
 * @var string|null $source где опубликовано
 * @var string|null $url ссылка на источник
 */

$text ??= '';
$cite ??= null;
$source ??= null;
$url ??= null;
?>
<figure class="ui-quote">
	<blockquote class="ui-quote__text"><p><?= e($text) ?></p></blockquote>
	<?php if ($cite !== null || $source !== null): ?>
		<figcaption class="ui-quote__caption">
			<?php if ($cite !== null): ?>
				<span class="ui-quote__cite"><?= e($cite) ?></span>
			<?php endif ?>
			<?php if ($source !== null): ?>
				<?php if ($url !== null): ?>
					<a class="ui-quote__source" href="<?= e($url) ?>" rel="noopener noreferrer" target="_blank"><?= e($source) ?></a>
				<?php else: ?>
					<span class="ui-quote__source"><?= e($source) ?></span>
				<?php endif ?>
			<?php endif ?>
		</figcaption>
	<?php endif ?>
</figure>

<style lang="scss">
	.ui-quote {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		margin: 0;
		padding: var(--space-2) 0 var(--space-2) var(--space-5);
		border-left: 2px solid var(--color-accent);
	}

	.ui-quote__text {
		margin: 0;
		color: var(--color-text);
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		font-style: italic;
		line-height: var(--leading-relaxed);
	}

	.ui-quote__text p {
		margin: 0;
	}

	.ui-quote__caption {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.ui-quote__cite {
		font-weight: var(--weight-medium);
	}

	.ui-quote__source {
		color: var(--color-text-subtle);
	}
</style>
