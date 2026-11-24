<?php

declare(strict_types=1);

/**
 * UI-набор: токены.
 *
 * Значения не переписаны в шаблон, а прочитаны из `_tokens.scss` через
 * `Longevypedia\Design\Tokens`. Поэтому страница не может показать палитру,
 * которой нет в сборке: поменяли значение в файле — поменялось и здесь.
 */

$tokens = longevypedia_tokens();
$groups = $tokens->grouped('light');

/** Подписи к цветовым ролям: имя токена само по себе не объясняет назначение. */
$roleNotes = [
	'--color-bg' => 'Фон страницы',
	'--color-surface' => 'Карточки и панели',
	'--color-surface-alt' => 'Вложенные блоки',
	'--color-surface-inset' => 'Код и цитаты',
	'--color-text' => 'Основной текст',
	'--color-text-muted' => 'Второстепенный текст',
	'--color-text-subtle' => 'Подписи и метки',
	'--color-border' => 'Обычные границы',
	'--color-border-strong' => 'Активные границы',
	'--color-control-border' => 'Граница поля ввода',
	'--color-brand' => 'Заливки и иконки бренда',
	'--color-brand-hover' => 'Бренд при наведении',
	'--color-brand-subtle' => 'Подсветка бренда',
	'--color-brand-text' => 'Бренд как текст',
	'--color-on-brand' => 'Текст на заливке бренда',
	'--color-accent' => 'Акцентные заливки',
	'--color-accent-text' => 'Акцент как текст',
	'--color-accent-subtle' => 'Акцентная подсветка',
	'--color-success' => 'Успех: заливка',
	'--color-success-text' => 'Успех: текст',
	'--color-success-soft' => 'Успех: подложка',
	'--color-success-border' => 'Успех: граница',
	'--color-warning' => 'Предупреждение: заливка',
	'--color-warning-text' => 'Предупреждение: текст',
	'--color-warning-soft' => 'Предупреждение: подложка',
	'--color-warning-border' => 'Предупреждение: граница',
	'--color-danger' => 'Ошибка: заливка',
	'--color-danger-text' => 'Ошибка: текст',
	'--color-danger-soft' => 'Ошибка: подложка',
	'--color-danger-border' => 'Ошибка: граница',
	'--color-info' => 'Информация: заливка',
	'--color-info-text' => 'Информация: текст',
	'--color-info-soft' => 'Информация: подложка',
	'--color-info-border' => 'Информация: граница',
	'--color-skeleton' => 'Заглушка загрузки',
	'--color-skeleton-shine' => 'Блик заглушки',
	'--color-overlay' => 'Затемнение под модальным окном',
];

$isColor = static fn (string $value): bool => (bool) preg_match('/^#[0-9a-f]{3,8}$/i', $value);

$this->layout('components::layout/site-layout', [
	'title' => 'Токены — UI-набор',
	'description' => 'Цвета, типографика, отступы и раскладка дизайн-системы Longevypedia.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'tokens',
	'title' => 'Токены',
	'lead' => 'Значения прочитаны из app/client/styles/_tokens.scss — здесь нет второй копии палитры.',
	'body' => function () use ($tokens, $groups, $roleNotes, $isColor): void { ?>
		<div class="kit-tokens">
			<?php foreach ($groups as $groupTitle => $items): ?>
				<section class="kit-tokens__group">
					<h2 class="kit-tokens__group-title"><?= e($groupTitle) ?></h2>

					<?php if ($groupTitle === 'Цвета'): ?>
						<ul class="kit-tokens__swatches">
							<?php foreach ($items as $name => $value): ?>
								<li class="kit-swatch">
									<span class="kit-swatch__chip" style="background: var(<?= e($name) ?>)"></span>
									<span class="kit-swatch__body">
										<code class="kit-swatch__name"><?= e($name) ?></code>
										<span class="kit-swatch__note"><?= e($roleNotes[$name] ?? '') ?></span>
										<span class="kit-swatch__value"><?= e($value) ?></span>
									</span>
								</li>
							<?php endforeach ?>
						</ul>
					<?php else: ?>
						<ul class="kit-tokens__rows">
							<?php foreach ($items as $name => $value): ?>
								<li class="kit-token">
									<code class="kit-token__name"><?= e($name) ?></code>
									<?php if ($isColor($value)): ?>
										<span class="kit-token__preview" style="background: var(<?= e($name) ?>)"></span>
									<?php endif ?>
									<span class="kit-token__value"><?= e($value) ?></span>
								</li>
							<?php endforeach ?>
						</ul>
					<?php endif ?>
				</section>
			<?php endforeach ?>

			<section class="kit-tokens__group">
				<h2 class="kit-tokens__group-title">Тёмная тема</h2>
				<p class="kit-tokens__note">
					Тёмные значения объявлены отдельно и подключаются двумя путями: системная
					тема (<code>prefers-color-scheme</code>) и ручной выбор
					(<code>data-theme="dark"</code>). Переключатель в шапке циклически меняет
					авто → светлая → тёмная и запоминает выбор.
				</p>
				<ul class="kit-tokens__swatches">
					<?php foreach ($tokens->dark() as $name => $value): ?>
						<?php if (!$isColor($value) || !str_starts_with($name, '--color-')): ?>
							<?php continue ?>
						<?php endif ?>
						<li class="kit-swatch">
							<span class="kit-swatch__chip" style="background: <?= e($value) ?>"></span>
							<span class="kit-swatch__body">
								<code class="kit-swatch__name"><?= e($name) ?></code>
								<span class="kit-swatch__note"><?= e($roleNotes[$name] ?? '') ?></span>
								<span class="kit-swatch__value"><?= e($value) ?></span>
							</span>
						</li>
					<?php endforeach ?>
				</ul>
			</section>
		</div>

		<style lang="scss">
			.kit-tokens {
				display: flex;
				flex-direction: column;
				gap: var(--space-8);
			}

			.kit-tokens__group {
				display: flex;
				flex-direction: column;
				gap: var(--space-3);
			}

			.kit-tokens__group-title {
				margin: 0;
				padding-bottom: var(--space-2);
				border-bottom: 1px solid var(--color-border);
				font-family: var(--font-serif);
				font-size: var(--text-xl);
				font-weight: var(--weight-semibold);
			}

			.kit-tokens__note {
				max-width: var(--layout-measure);
				margin: 0;
				color: var(--color-text-muted);
				font-family: var(--font-ui);
				font-size: var(--text-sm);
				line-height: var(--leading-normal);
			}

			.kit-tokens__swatches {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(min(100%, 16rem), 1fr));
				gap: var(--space-3);
				margin: 0;
				padding: 0;
				list-style: none;
			}

			.kit-tokens__rows {
				display: flex;
				flex-direction: column;
				margin: 0;
				padding: 0;
				list-style: none;
			}

			.kit-swatch {
				display: flex;
				gap: var(--space-3);
				align-items: flex-start;
				padding: var(--space-2);
				border: 1px solid var(--color-border);
				border-radius: var(--radius-md);
				background: var(--color-surface);
			}

			.kit-swatch__chip {
				flex: 0 0 auto;
				width: 2.5rem;
				height: 2.5rem;
				border: 1px solid var(--color-border-strong);
				border-radius: var(--radius-sm);
			}

			.kit-swatch__body {
				display: flex;
				flex-direction: column;
				gap: 2px;
				min-width: 0;
			}

			.kit-swatch__name,
			.kit-token__name {
				color: var(--color-text);
				font-family: var(--font-mono);
				font-size: var(--text-xs);
				overflow-wrap: anywhere;
			}

			.kit-swatch__note {
				color: var(--color-text-muted);
				font-family: var(--font-ui);
				font-size: var(--text-xs);
			}

			.kit-swatch__value,
			.kit-token__value {
				color: var(--color-text-subtle);
				font-family: var(--font-mono);
				font-size: var(--text-xs);
			}

			.kit-token {
				display: grid;
				grid-template-columns: minmax(10rem, 20rem) auto minmax(0, 1fr);
				gap: var(--space-3);
				align-items: center;
				padding: var(--space-1) 0;
				border-bottom: 1px solid var(--color-border);
			}

			.kit-token:last-child {
				border-bottom: 0;
			}

			.kit-token__preview {
				width: 1.5rem;
				height: 1.5rem;
				border: 1px solid var(--color-border-strong);
				border-radius: var(--radius-sm);
			}

			@media (max-width: 40rem) {
				.kit-token {
					grid-template-columns: minmax(0, 1fr);
					gap: var(--space-1);
				}
			}
		</style>
	<?php },
]) ?>
<?php $this->end(); ?>
