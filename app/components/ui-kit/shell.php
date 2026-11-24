<?php

declare(strict_types=1);

/**
 * Каркас страницы UI-набора: меню слева, содержимое справа.
 *
 * Каталог читается здесь, а не в каждой странице: список пунктов один —
 * `data/ui-kit.php`.
 *
 * @var string      $title  заголовок страницы
 * @var string|null $lead   вводная строка
 * @var string      $active slug текущей страницы
 * @var mixed       $body   содержимое: замыкание или строка
 */

$title ??= 'UI-набор';
$lead ??= null;
$active ??= null;
$body ??= null;

$kit = require dirname(__DIR__, 3) . '/data/ui-kit.php';
?>
<div class="ui-kit-page">
	<aside class="ui-kit-page__aside">
		<?= $this->insert('components::ui-kit/nav', [
			'groups' => (array) ($kit['groups'] ?? []),
			'active' => $active,
		]) ?>
	</aside>

	<div class="ui-kit-page__body">
		<header class="ui-kit-page__header">
			<p class="ui-kit-page__eyebrow">UI-набор</p>
			<h1 class="ui-kit-page__title"><?= e($title) ?></h1>
			<?php if ($lead !== null && $lead !== ''): ?>
				<p class="ui-kit-page__lead"><?= e($lead) ?></p>
			<?php endif ?>
		</header>

		<?php render($body); ?>
	</div>
</div>

<style lang="scss">
	.ui-kit-page {
		display: grid;
		grid-template-columns: var(--layout-sidebar) minmax(0, 1fr);
		gap: var(--layout-gap);
		width: 100%;
		max-width: var(--layout-page);
		margin-inline: auto;
		padding: var(--space-8) var(--layout-gutter) var(--space-16);
	}

	.ui-kit-page__aside {
		position: sticky;
		top: calc(var(--layout-header) + var(--space-4));
		align-self: start;
		max-height: calc(100vh - var(--layout-header) - var(--space-8));
		overflow-y: auto;
		padding-right: var(--space-4);
	}

	.ui-kit-page__body {
		display: flex;
		flex-direction: column;
		gap: var(--space-4);
		min-width: 0;
	}

	.ui-kit-page__header {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		padding-bottom: var(--space-6);
	}

	.ui-kit-page__eyebrow {
		margin: 0;
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.ui-kit-page__title {
		margin: 0;
		font-size: var(--text-3xl);
		line-height: var(--leading-tight);
	}

	.ui-kit-page__lead {
		max-width: var(--layout-measure);
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		line-height: var(--leading-relaxed);
	}

	@media (max-width: $bp-xl) {
		.ui-kit-page {
			grid-template-columns: minmax(0, 1fr);
		}

		.ui-kit-page__aside {
			position: static;
			max-height: none;
			padding: 0 0 var(--space-6);
			border-bottom: 1px solid var(--color-border);
		}
	}
</style>
