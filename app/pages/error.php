<?php

declare(strict_types=1);

/**
 * Страница ошибки.
 *
 * Ядро BOLT ищет шаблон `error` в бандлах и подставляет этот файл вместо
 * встроенной страницы. Данные: `code`, `error`, `exception` (только в debug) и
 * `request`.
 *
 * @var int         $code
 * @var mixed       $error
 * @var \Throwable|null $exception
 */

$code ??= 500;
$error ??= null;
$exception ??= null;

$messages = [
	404 => ['Page not found', 'That address is not part of the encyclopedia. It may have been renamed, or the document may not exist yet.'],
	405 => ['Method not allowed', 'This address exists, but not for this kind of request.'],
	403 => ['Forbidden', 'You do not have access to this page.'],
	500 => ['Something went wrong', 'The page could not be rendered. The error has been logged.'],
];

[$heading, $text] = $messages[$code] ?? [$messages[500][0], $messages[500][1]];

$this->layout('components::layout/site-layout', [
	'title' => $heading,
	'description' => $text,
	'canonical' => '',
	'current' => '',
]);
?>
<?php $this->start('page'); ?>
<div class="container error-page">
	<p class="error-page__code"><?= $code ?></p>
	<h1 class="error-page__title"><?= $this->e($heading) ?></h1>
	<p class="error-page__text"><?= $this->e($text) ?></p>

	<ul class="error-page__actions">
		<li><a href="/">Home</a></li>
		<li><a href="/catalog">Knowledge base</a></li>
		<li><a href="/research">Research</a></li>
		<li><a href="/search">Search</a></li>
	</ul>

	<?php if ($exception !== null): ?>
		<details class="error-page__details">
			<summary>Debug details</summary>
			<p class="error-page__exception"><?= $this->e($exception::class . ': ' . $exception->getMessage()) ?></p>
			<pre class="error-page__trace"><?= $this->e($exception->getTraceAsString()) ?></pre>
		</details>
	<?php endif; ?>
</div>

<style lang="scss">
	.error-page {
		padding-block: 4rem;
		max-width: 40rem;
	}

	.error-page__code {
		margin: 0;
		font-family: var(--font-mono);
		font-size: var(--text-sm);
		color: var(--color-accent-text);
		letter-spacing: 0.1em;
	}

	.error-page__title {
		margin: 0.35rem 0 0.6rem;
		font-family: var(--font-serif);
		font-size: clamp(1.8rem, 1.4rem + 1.6vw, 2.4rem);
	}

	.error-page__text {
		margin: 0 0 1.25rem;
		color: var(--color-text-muted);
		line-height: 1.6;
	}

	.error-page__actions {
		display: flex;
		flex-wrap: wrap;
		gap: 1rem;
		margin: 0 0 2rem;
		padding: 0;
		list-style: none;
		font-size: var(--text-sm);
	}

	.error-page__details {
		font-size: var(--text-xs);

		summary {
			cursor: pointer;
			color: var(--color-text-subtle);
		}
	}

	.error-page__exception {
		font-family: var(--font-mono);
		color: var(--color-danger);
		word-break: break-word;
	}

	.error-page__trace {
		overflow-x: auto;
		padding: 0.75rem;
		border-radius: var(--radius-sm);
		background: var(--color-surface-alt);
		font-size: 0.72rem;
		line-height: 1.45;
	}
</style>
<?php $this->stop('page'); ?>
