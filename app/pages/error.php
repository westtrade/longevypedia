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
		<?php foreach ([['/', 'Home'], ['/catalog', 'Knowledge base'], ['/research', 'Research'], ['/search', 'Search']] as [$href, $label]): ?>
			<li>
				<?= $this->insert('components::ui/button', [
					'label' => $label,
					'variant' => 'outline',
					'href' => $href,
				]) ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ($exception !== null): ?>
		<div class="error-page__debug">
			<?= $this->insert('components::ui/accordion', [
				'name' => 'error-debug',
				'items' => [[
					'title' => 'Debug details',
					'content' => '<p class="error-page__exception">' . $this->e($exception::class . ': ' . $exception->getMessage()) . '</p>'
						. '<pre class="error-page__trace">' . $this->e($exception->getTraceAsString()) . '</pre>',
				]],
			]) ?>
		</div>
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
		gap: var(--space-3);
		margin: 0 0 var(--space-8);
		padding: 0;
		list-style: none;
	}

	.error-page__debug {
		font-size: var(--text-xs);
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
