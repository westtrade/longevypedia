<?php

declare(strict_types=1);

/**
 * Образец UI-набора: заголовок, живой пример и его исходник.
 *
 * @var string        $title заголовок образца
 * @var string|null   $note  одна строка «что здесь показано»
 * @var string|null   $code  исходник того же вызова
 * @var string|null   $lang  подпись языка исходника
 * @var mixed         $body  живой пример: замыкание или строка
 * @var bool          $stack пример в столбик
 * @var bool          $plain без внутренних отступов
 */

$title ??= '';
$note ??= null;
$code ??= null;
$lang ??= 'php';
$body ??= null;
$stack ??= false;
$plain ??= false;
?>
<section class="specimen">
	<header class="specimen__head">
		<h3 class="specimen__title"><?= e($title) ?></h3>
		<?php if ($note !== null && $note !== ''): ?>
			<p class="specimen__note"><?= e($note) ?></p>
		<?php endif ?>
	</header>

	<div class="specimen__body<?= $stack ? ' specimen__body--stack' : '' ?><?= $plain ? ' specimen__body--plain' : '' ?>">
		<?php render($body); ?>
	</div>

	<?php if ($code !== null && trim($code) !== ''): ?>
		<?= $this->insert('components::ui-kit/snippet', ['code' => $code, 'lang' => $lang]) ?>
	<?php endif ?>
</section>

<style lang="scss">
	.specimen {
		display: flex;
		flex-direction: column;
		gap: var(--space-3);
		padding-block: var(--space-6);
		border-top: 1px solid var(--color-border);
	}

	.specimen__head {
		display: flex;
		flex-direction: column;
		gap: var(--space-1);
	}

	.specimen__title {
		margin: 0;
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.specimen__note {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
	}

	/* По верху, а не по центру: когда у одного поля появляется строка ошибки,
	   оно становится выше соседа, и при `center` сосед съезжает вниз. В
	   образце ничего не должно двигаться от смены состояния. */
	.specimen__body {
		display: flex;
		flex-wrap: wrap;
		align-items: flex-start;
		gap: var(--space-3);
		padding: var(--space-6);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.specimen__body--stack {
		flex-direction: column;
		align-items: stretch;
	}

	.specimen__body--plain {
		padding: 0;
		border: 0;
		background: none;
	}

	.specimen__body > * {
		min-width: 0;
	}
</style>
