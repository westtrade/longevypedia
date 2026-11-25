<?php

declare(strict_types=1);

/**
 * UI-набор: доступность.
 *
 * Таблица контраста считается здесь же: пары берутся из
 * `Tokens::contrastPairs()`, значения — из `_tokens.scss`, отношение — по
 * формуле WCAG. Поэтому «проходит AA» — это результат вычисления, а не
 * утверждение в тексте. Тот же расчёт повторяет тест, чтобы правка палитры
 * не проходила молча.
 */

$tokens = longevypedia_tokens();

$rows = [];

foreach (['light' => 'Светлая тема', 'dark' => 'Тёмная тема'] as $theme => $themeLabel) {
	$values = $tokens->theme($theme);

	foreach ($tokens->contrastPairs() as $pair) {
		$fg = $values[$pair['fg']] ?? null;
		$bg = $values[$pair['bg']] ?? null;

		if ($fg === null || $bg === null) {
			continue;
		}

		$ratio = \Longevypedia\Design\Contrast::ratio($fg, $bg);
		$minimum = \Longevypedia\Design\Contrast::minimum($pair['size']);

		$rows[] = [
			'theme' => $themeLabel,
			'label' => $pair['label'],
			'fg' => $fg,
			'bg' => $bg,
			'ratio' => $ratio,
			'minimum' => $minimum,
			'passes' => $ratio >= $minimum,
		];
	}
}

$failing = array_values(array_filter($rows, static fn (array $row): bool => !$row['passes']));

$this->layout('components::layout/site-layout', [
	'title' => 'Доступность — UI-набор',
	'description' => 'Контраст, фокус, клавиатура и семантика компонентов Longevypedia.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'accessibility',
	'title' => 'Доступность',
	'lead' => 'Цель — WCAG 2.2 AA. Контраст ниже посчитан по текущим токенам, а не списан из макета.',
	'body' => function () use ($rows, $failing): void { ?>
		<div class="kit-a11y">
			<?= $this->insert('components::ui/alert', [
				'type' => $failing === [] ? 'success' : 'danger',
				'title' => $failing === []
					? 'Все проверенные пары проходят AA'
					: 'Есть пары ниже порога AA: ' . (string) count($failing),
				'text' => $failing === []
					? 'Проверено пар: ' . (string) count($rows) . ' — обычный текст от 4.5:1, крупный текст и границы от 3:1.'
					: 'Проверьте значения токенов в app/client/styles/_tokens.scss.',
			]) ?>

			<section class="kit-a11y__section">
				<h2 class="kit-a11y__title">Контраст</h2>
				<?php
				$tableRows = [];

				foreach ($rows as $row) {
					$tableRows[] = [
						'highlight' => !$row['passes'],
						'cells' => [
							'pair' => $this->e($row['label']),
							'theme' => $this->e($row['theme']),
							'fg' => '<code>' . $this->e($row['fg']) . '</code>',
							'bg' => '<code>' . $this->e($row['bg']) . '</code>',
							'ratio' => $this->e(number_format($row['ratio'], 2)) . ':1',
							'minimum' => $this->e(number_format($row['minimum'], 1)) . ':1',
							'result' => $this->fetch('components::ui/badge', $row['passes']
								? ['label' => 'Проходит', 'variant' => 'success', 'size' => 'sm']
								: ['label' => 'Ниже порога', 'variant' => 'danger', 'size' => 'sm']),
						],
					];
				}
				?>
				<?= $this->insert('components::ui/data-table', [
					'caption' => 'Отношение контраста пар «текст на фоне» по темам',
					'columns' => [
						['label' => 'Пара', 'key' => 'pair', 'rowHeader' => true],
						['label' => 'Тема', 'key' => 'theme'],
						['label' => 'Цвет текста', 'key' => 'fg'],
						['label' => 'Фон', 'key' => 'bg'],
						['label' => 'Отношение', 'key' => 'ratio', 'numeric' => true],
						['label' => 'Порог', 'key' => 'minimum', 'numeric' => true],
						['label' => 'Результат', 'key' => 'result'],
					],
					'rows' => $tableRows,
				]) ?>
			</section>

			<section class="kit-a11y__section">
				<h2 class="kit-a11y__title">Что ещё проверяется</h2>
				<ul class="kit-a11y__list">
					<li>
						<strong>Фокус.</strong> У всех интерактивных элементов видимое кольцо
						<code>--focus-ring</code>; <code>outline</code> не убирается без замены.
					</li>
					<li>
						<strong>Клавиатура.</strong> Раскрывающиеся блоки и меню сделаны на
						<code>details</code>/<code>summary</code>, а не на скриптах: Tab, Enter и
						поиск по странице работают без JavaScript.
					</li>
					<li>
						<strong>Смысл не только цветом.</strong> Направление доказательств и статус
						утверждения всегда подписаны словами; бейдж состояния дублирует цвет текстом.
					</li>
					<li>
						<strong>Формы.</strong> Подпись связана с полем, ошибка и описание — через
						<code>aria-describedby</code>, невалидное поле помечено
						<code>aria-invalid</code>.
					</li>
					<li>
						<strong>Загрузка.</strong> Состояние объявляется через <code>aria-busy</code>
						или live-регион; заглушка-скелет скрыта от скринридера.
					</li>
					<li>
						<strong>Движение.</strong> При <code>prefers-reduced-motion</code> анимации
						отключаются, включая блик заглушки и вращение спиннера.
					</li>
					<li>
						<strong>Таблицы.</strong> Заголовки — <code>th</code> с <code>scope</code>,
						сортировка объявляется <code>aria-sort</code>, на узком экране таблица
						прокручивается, а не ломает вёрстку.
					</li>
				</ul>
			</section>

			<section class="kit-a11y__section">
				<h2 class="kit-a11y__title">Ручная проверка перед релизом</h2>
				<ol class="kit-a11y__list">
					<li>Пройти страницу только клавиатурой: Tab, Shift+Tab, Enter, Escape.</li>
					<li>Включить тёмную тему и системную тёмную — проверить оба пути.</li>
					<li>Увеличить масштаб до 200% — вёрстка не должна обрезать текст.</li>
					<li>Включить <code>prefers-reduced-motion</code> — убедиться, что нет движения.</li>
					<li>Проверить, что у каждой иконки-кнопки есть доступное имя.</li>
				</ol>
			</section>
		</div>

		<style lang="scss">
			.kit-a11y {
				display: flex;
				flex-direction: column;
				gap: var(--space-6);
			}

			.kit-a11y__section {
				display: flex;
				flex-direction: column;
				gap: var(--space-3);
			}

			.kit-a11y__title {
				margin: 0;
				padding-bottom: var(--space-2);
				border-bottom: 1px solid var(--color-border);
				font-family: var(--font-serif);
				font-size: var(--text-xl);
				font-weight: var(--weight-semibold);
			}

			.kit-a11y__list {
				display: flex;
				flex-direction: column;
				gap: var(--space-2);
				max-width: var(--layout-measure);
				margin: 0;
				padding-left: var(--space-5);
				color: var(--color-text);
				font-family: var(--font-ui);
				font-size: var(--text-sm);
				line-height: var(--leading-relaxed);
			}

			.kit-a11y__list code {
				padding: 0.1em 0.35em;
				border-radius: var(--radius-sm);
				background: var(--color-surface-inset);
				font-family: var(--font-mono);
				font-size: 0.9em;
			}
		</style>
	<?php },
]) ?>
<?php $this->end(); ?>
