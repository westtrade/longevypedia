<?php

declare(strict_types=1);

/**
 * Заглушка загрузки.
 *
 * Показывается, пока HTMX ждёт фрагмент. Скрыта от скринридера: озвучивать
 * «загружается» должен соседний live-регион, а не мигающие полосы.
 *
 * @var int $lines число строк
 * @var string $variant text | card | table
 * @var string $label доступная подпись для контейнера
 */

$lines ??= 3;
$variant ??= 'text';
$label ??= 'Loading';
?>
<div class="ui-skeleton ui-skeleton--<?= e($variant) ?>" aria-hidden="true" data-skeleton>
	<?php if ($variant === 'table'): ?>
		<?php for ($row = 0; $row < max(2, $lines); $row++): ?>
			<span class="ui-skeleton__row"></span>
		<?php endfor ?>
	<?php elseif ($variant === 'card'): ?>
		<span class="ui-skeleton__media"></span>
		<span class="ui-skeleton__line" style="width: 70%"></span>
		<span class="ui-skeleton__line" style="width: 90%"></span>
		<span class="ui-skeleton__line" style="width: 45%"></span>
	<?php else: ?>
		<?php for ($line = 0; $line < max(1, $lines); $line++): ?>
			<span class="ui-skeleton__line" style="width: <?= e((string) max(30, 100 - $line * 12)) ?>%"></span>
		<?php endfor ?>
	<?php endif ?>
</div>

<style lang="scss">
	.ui-skeleton {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
	}

	.ui-skeleton__line,
	.ui-skeleton__row,
	.ui-skeleton__media {
		display: block;
		border-radius: var(--radius-sm);
		background: linear-gradient(
			90deg,
			var(--color-skeleton) 0%,
			var(--color-skeleton-shine) 50%,
			var(--color-skeleton) 100%
		);
		background-size: 200% 100%;
		animation: ui-skeleton-shimmer 1.4s ease-in-out infinite;
	}

	.ui-skeleton__line {
		height: 0.75rem;
	}

	.ui-skeleton__row {
		height: 2rem;
	}

	.ui-skeleton__media {
		aspect-ratio: 16 / 9;
		margin-bottom: var(--space-1);
	}

	@keyframes ui-skeleton-shimmer {
		from {
			background-position: 200% 0;
		}

		to {
			background-position: -200% 0;
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.ui-skeleton__line,
		.ui-skeleton__row,
		.ui-skeleton__media {
			animation: none;
		}
	}
</style>
