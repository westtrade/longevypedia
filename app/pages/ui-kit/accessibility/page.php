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
				<div class="kit-a11y__table-wrap">
					<table class="kit-a11y__table">
						<caption class="visually-hidden">Отношение контраста пар «текст на фоне» по темам</caption>
						<thead>
							<tr>
								<th scope="col">Пара</th>
								<th scope="col">Тема</th>
								<th scope="col">Цвет текста</th>
								<th scope="col">Фон</th>
								<th scope="col" class="kit-a11y__num">Отношение</th>
								<th scope="col" class="kit-a11y__num">Порог</th>
								<th scope="col">Результат</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($rows as $row): ?>
								<tr>
									<th scope="row" class="kit-a11y__pair"><?= e($row['label']) ?></th>
									<td><?= e($row['theme']) ?></td>
									<td><code><?= e($row['fg']) ?></code></td>
									<td><code><?= e($row['bg']) ?></code></td>
									<td class="kit-a11y__num"><?= e(number_format($row['ratio'], 2)) ?>:1</td>
									<td class="kit-a11y__num"><?= e(number_format($row['minimum'], 1)) ?>:1</td>
									<td>
										<?php if ($row['passes']): ?>
											<?= $this->insert('components::ui/badge', ['label' => 'Проходит', 'variant' => 'success', 'size' => 'sm']) ?>
										<?php else: ?>
											<?= $this->insert('components::ui/badge', ['label' => 'Ниже порога', 'variant' => 'danger', 'size' => 'sm']) ?>
										<?php endif ?>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
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

			.kit-a11y__table-wrap {
				overflow-x: auto;
				border: 1px solid var(--color-border);
				border-radius: var(--radius-md);
			}

			.kit-a11y__table {
				width: 100%;
				border-collapse: collapse;
				font-family: var(--font-ui);
				font-size: var(--text-sm);
			}

			.kit-a11y__table th,
			.kit-a11y__table td {
				padding: var(--space-2) var(--space-3);
				border-bottom: 1px solid var(--color-border);
				text-align: left;
				vertical-align: middle;
				white-space: nowrap;
			}

			.kit-a11y__table thead th {
				background: var(--color-surface-alt);
				color: var(--color-text-muted);
				font-size: var(--text-xs);
				font-weight: var(--weight-semibold);
				letter-spacing: var(--tracking-wide);
				text-transform: uppercase;
			}

			.kit-a11y__table tbody tr:last-child th,
			.kit-a11y__table tbody tr:last-child td {
				border-bottom: 0;
			}

			.kit-a11y__table code {
				font-family: var(--font-mono);
				font-size: var(--text-xs);
			}

			.kit-a11y__pair {
				font-weight: var(--weight-regular);
				white-space: normal;
			}

			.kit-a11y__num {
				text-align: right;
				font-variant-numeric: tabular-nums;
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
