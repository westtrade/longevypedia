<?php

declare(strict_types=1);

/**
 * UI-набор: иконки.
 *
 * Список читается из тех же каталогов, по которым BOLT собирает спрайт:
 * свои `app/icons` и встроенные `vendor/bsti/bolt/icons`. Имя иконки — имя
 * файла без `-icon`, поэтому `sun-icon.svg` вызывается как `sun`.
 */

$grouped = longevypedia_icons()->grouped();

$this->layout('components::layout/site-layout', [
	'title' => 'Иконки — UI-набор',
	'description' => 'Штриховые иконки Longevypedia и встроенного набора BOLT.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'icons',
	'title' => 'Иконки',
	'lead' => 'Один штриховой набор: толщина 1.75, размер по умолчанию 24, цвет наследуется от текста.',
	'body' => function () use ($grouped): void { ?>
		<div class="kit-icons">
			<p class="kit-icons__note">
				Иконки не заменяют текст: рядом с иконкой-кнопкой обязательна
				<code>aria-label</code>, а смысл состояния всё равно передаётся словами.
				Эмодзи в интерфейсе не используются — они выглядят по-разному в разных системах
				и не масштабируются вместе с текстом.
			</p>

			<?php foreach ($grouped as $source => $icons): ?>
				<section class="kit-icons__group">
					<h2 class="kit-icons__group-title"><?= e($source) ?> · <?= e((string) count($icons)) ?></h2>
					<ul class="kit-icons__list">
						<?php foreach ($icons as $icon): ?>
							<li class="kit-icon">
								<span class="kit-icon__preview">
									<?= $this->insert('bolt://ui-kit/icon', ['name' => $icon['name'], 'size' => 24]) ?>
								</span>
								<code class="kit-icon__name"><?= e($icon['name']) ?></code>
							</li>
						<?php endforeach ?>
					</ul>
				</section>
			<?php endforeach ?>

			<section class="kit-icons__group">
				<h2 class="kit-icons__group-title">Размеры и цвет</h2>
				<ul class="kit-icons__list kit-icons__list--inline">
					<?php foreach ([16, 20, 24, 32] as $size): ?>
						<li class="kit-icon">
							<span class="kit-icon__preview">
								<?= $this->insert('bolt://ui-kit/icon', ['name' => 'graph', 'size' => $size]) ?>
							</span>
							<code class="kit-icon__name"><?= e((string) $size) ?>px</code>
						</li>
					<?php endforeach ?>
					<li class="kit-icon">
						<span class="kit-icon__preview kit-icon__preview--brand">
							<?= $this->insert('bolt://ui-kit/icon', ['name' => 'flask', 'size' => 24]) ?>
						</span>
						<code class="kit-icon__name">brand</code>
					</li>
				</ul>
			</section>
		</div>

		<style lang="scss">
			.kit-icons {
				display: flex;
				flex-direction: column;
				gap: var(--space-6);
			}

			.kit-icons__note {
				max-width: var(--layout-measure);
				margin: 0;
				color: var(--color-text-muted);
				font-family: var(--font-ui);
				font-size: var(--text-sm);
				line-height: var(--leading-normal);
			}

			.kit-icons__note code {
				padding: 0.1em 0.35em;
				border-radius: var(--radius-sm);
				background: var(--color-surface-inset);
				font-family: var(--font-mono);
				font-size: 0.9em;
			}

			.kit-icons__group {
				display: flex;
				flex-direction: column;
				gap: var(--space-3);
			}

			.kit-icons__group-title {
				margin: 0;
				padding-bottom: var(--space-2);
				border-bottom: 1px solid var(--color-border);
				color: var(--color-text-muted);
				font-family: var(--font-ui);
				font-size: var(--text-xs);
				font-weight: var(--weight-semibold);
				letter-spacing: var(--tracking-wide);
				text-transform: uppercase;
			}

			.kit-icons__list {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr));
				gap: var(--space-2);
				margin: 0;
				padding: 0;
				list-style: none;
			}

			.kit-icons__list--inline {
				display: flex;
				flex-wrap: wrap;
				gap: var(--space-3);
			}

			.kit-icon {
				display: flex;
				align-items: center;
				gap: var(--space-2);
				padding: var(--space-2);
				border: 1px solid var(--color-border);
				border-radius: var(--radius-sm);
				background: var(--color-surface);
			}

			.kit-icon__preview {
				display: inline-flex;
				color: var(--color-text);
			}

			.kit-icon__preview--brand {
				color: var(--color-brand);
			}

			.kit-icon__name {
				color: var(--color-text-muted);
				font-family: var(--font-mono);
				font-size: var(--text-xs);
			}
		</style>
	<?php },
]) ?>
<?php $this->end(); ?>
