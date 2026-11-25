<?php

declare(strict_types=1);

/**
 * Исходник образца: блок кода с копированием.
 *
 * Разметку копируют отсюда, а не восстанавливают по инспектору браузера,
 * поэтому блок стоит рядом с живым примером и содержит ровно тот вызов,
 * которым пример отрендерен.
 *
 * @var string      $code исходник
 * @var string|null $lang подпись языка
 */

$code ??= '';
$lang ??= 'php';

/* Общий отступ снимается: heredoc в шаблоне стоит с отступом по месту файла,
   а копировать нужно код с начала строки. */
$lines = preg_split('/\R/u', $code) ?: [];
$indent = null;

foreach ($lines as $line) {
	if (trim($line) === '') {
		continue;
	}

	$length = strlen($line) - strlen(ltrim($line, "\t "));
	$indent = $indent === null ? $length : min($indent, $length);
}

$indent ??= 0;
$clean = [];

foreach ($lines as $line) {
	$clean[] = substr($line, $indent);
}

$text = implode("\n", $clean);
?>
<div class="code-block">
	<div class="code-block__head">
		<span class="code-block__lang"><?= e($lang) ?></span>
		<?= $this->insert('components::ui/button', [
			'label' => 'Копировать',
			'variant' => 'link',
			'size' => 'sm',
			'attrs' => ['class' => 'code-block__copy', 'data-copy-text' => $text],
		]) ?>
	</div>
	<pre class="code-block__pre"><code class="code-block__code"><?= e($text) ?></code></pre>
</div>

<style lang="scss">
	.code-block {
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface-alt);
		overflow: hidden;
	}

	.code-block__head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: var(--space-3);
		padding: var(--space-2) var(--space-3);
		border-bottom: 1px solid var(--color-border);
		background: var(--color-surface);
	}

	.code-block__lang {
		color: var(--color-text-subtle);
		font-family: var(--font-mono);
		font-size: var(--text-xs);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.code-block__copy:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
		border-radius: var(--radius-sm);
	}

	.code-block__pre {
		margin: 0;
		padding: var(--space-3) var(--space-4);
		overflow-x: auto;
		font-size: var(--text-xs);
		line-height: var(--leading-table);
		tab-size: 2;
	}

	.code-block__code {
		font-family: var(--font-mono);
		white-space: pre;
	}
</style>
