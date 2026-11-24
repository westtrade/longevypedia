<?php

declare(strict_types=1);

/**
 * Демонстрационная страница энциклопедии.
 *
 * Показывает, как компоненты набора собираются в реальную страницу статьи:
 * заголовок, метаданные исследования, индикаторы доказательности, источники,
 * связанные материалы. Это витрина вёрстки, а не документ корпуса: данные
 * взяты из демонстрационных материалов `content/en`, поэтому страница честно
 * помечена как демонстрация.
 *
 * @var array $route
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Демонстрационная страница — Longevypedia',
	'description' => 'Пример вёрстки статьи научной энциклопедии из компонентов дизайн-системы.',
	'canonical' => '/demo',
	'current' => '',
]);
?>
<?php $this->start('page'); ?>
<div class="demo">
	<div class="container demo__inner">
		<?= $this->insert('components::ui/breadcrumbs', [
			'items' => [
				['label' => 'Энциклопедия', 'url' => '/catalog'],
				['label' => 'Соединения', 'url' => '/compounds'],
				['label' => 'Рапамицин'],
			],
		]) ?>

		<?= $this->insert('components::ui/alert', [
			'type' => 'info',
			'title' => 'Демонстрационная страница',
			'text' => 'Это витрина компонентов, а не документ корпуса. Тексты сокращены, данные взяты из демонстрационных материалов проекта и не являются медицинской рекомендацией.',
		]) ?>

		<div class="demo__layout">
			<article class="demo__article">
				<header class="demo__header">
					<div class="demo__meta">
						<?= $this->insert('components::ui/badge', ['label' => 'Соединение', 'variant' => 'brand']) ?>
						<?= $this->insert('components::ui/badge', ['label' => 'Обновлено 20 ноября 2026', 'variant' => 'neutral']) ?>
						<span class="demo__reading">6 минут чтения</span>
					</div>

					<h1 class="demo__title">Рапамицин</h1>

					<p class="demo__lead">
						Ингибитор mTORC1, одобренный как иммунодепрессант. В исследованиях на
						мышах продлевал медианную продолжительность жизни; у людей данные
						ограничены краткосрочными испытаниями на иммунный ответ.
					</p>

					<ul class="demo__tags">
						<?php foreach ([['Ингибиторы mTOR', 'interventions'], ['Старение', 'aging-mechanisms'], ['Фармакология', 'clinical-practice']] as [$tagLabel, $topic]): ?>
							<li>
								<?= $this->insert('components::ui/tag', [
									'label' => $tagLabel,
									'href' => '/catalog?topic=' . $topic,
								]) ?>
							</li>
						<?php endforeach ?>
					</ul>
				</header>

				<section class="demo__section">
					<h2 class="demo__heading">Что известно</h2>
					<p>
						Рапамицин связывается с белком FKBP12, и образовавшийся комплекс
						подавляет киназу mTORC1. Через mTORC1 клетка регулирует трансляцию,
						аутофагию и рост; подавление этого пути замедляет ряд процессов,
						связанных с клеточным старением.
					</p>
					<p>
						В исследовании 2009 года на мышах линии UM-HET3 препарат в дозе
						14 мг/кг корма увеличивал медианную продолжительность жизни в обеих
						возрастных когортах. Это результат на животной модели: он не
						переносится автоматически на людей.
					</p>

					<?= $this->insert('components::ui/callout', [
						'variant' => 'caution',
						'title' => 'Осторожно с переносом на человека',
						'children' => '<p>Рапамицин — иммунодепрессант с известными побочными эффектами. Данные о влиянии на продолжительность жизни людей отсутствуют; краткосрочные испытания изучали иммунный ответ, а не смертность.</p>',
					]) ?>
				</section>

				<section class="demo__section">
					<h2 class="demo__heading">Оценка доказательств</h2>
					<p class="demo__note">
						Направление результата и качество доказательств — независимые параметры:
						эффект может быть сильным, а доказательство — слабым.
					</p>
					<div class="demo__evidence">
						<?= $this->insert('components::ui/evidence', [
							'direction' => 'supports',
							'quality' => 'moderate',
							'basis' => 'Одно крупное исследование на мышах с двумя возрастными когортами; независимого воспроизведения на людях нет.',
						]) ?>
						<?= $this->insert('components::ui/claim-status', [
							'confidence' => 'likely',
							'direction' => 'supports',
							'asOf' => '2026-11-20',
						]) ?>
					</div>
				</section>

				<section class="demo__section">
					<h2 class="demo__heading">Ключевые показатели исследования</h2>
					<div class="demo__stats">
						<?= $this->insert('components::ui/stat', [
							'value' => '+9%',
							'label' => 'Медианная продолжительность жизни',
							'hint' => 'Когорта с 9 месяцев, мыши',
						]) ?>
						<?= $this->insert('components::ui/stat', [
							'value' => '+14%',
							'label' => 'Медианная продолжительность жизни',
							'hint' => 'Когорта с 20 месяцев, мыши',
						]) ?>
						<?= $this->insert('components::ui/stat', [
							'value' => '14 мг/кг',
							'label' => 'Доза в корме',
							'hint' => 'Ежедневно, до конца жизни',
						]) ?>
					</div>
				</section>

				<section class="demo__section">
					<h2 class="demo__heading">Параметры исследования</h2>
					<?= $this->insert('components::ui/key-value', [
						'items' => [
							['label' => 'Дизайн', 'value' => 'Эксперимент на животных'],
							['label' => 'Вид', 'value' => 'Mus musculus, линия UM-HET3'],
							['label' => 'Выборка', 'value' => 'Четыре площадки, около 1 900 животных'],
							['label' => 'Вмешательство', 'value' => 'Рапамицин, 14 мг/кг корма'],
							['label' => 'Сравнение', 'value' => 'Контроль без препарата'],
							['label' => 'Год', 'value' => '2009'],
						],
					]) ?>
				</section>

				<section class="demo__section">
					<h2 class="demo__heading">Результаты по исходам</h2>
					<?= $this->insert('components::ui/data-table', [
						'caption' => 'Исходы и их направление в исследовании Harrison et al., 2009',
						'columns' => [
							['label' => 'Исход', 'key' => 'outcome'],
							['label' => 'Направление', 'key' => 'direction'],
							['label' => 'Измерение', 'key' => 'measure', 'align' => 'right'],
						],
						'rows' => [
							['outcome' => 'Медианная продолжительность жизни', 'direction' => 'Увеличение', 'measure' => '+9% / +14%'],
							['outcome' => 'Максимальная продолжительность жизни', 'direction' => 'Увеличение', 'measure' => 'значимо'],
							['outcome' => 'Масса тела', 'direction' => 'Без изменений', 'measure' => '—'],
						],
					]) ?>
				</section>

				<section class="demo__section">
					<h2 class="demo__heading">Источники</h2>
					<?= $this->insert('components::ui/source-list', [
						'sources' => [
							[
								'citation' => 'Harrison DE, Strong R, Sharp ZD, et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature. 2009;460(7253):392–395.',
								'doi' => '10.1038/nature08221',
								'pmid' => '19587680',
							],
							[
								'citation' => 'Saxton RA, Sabatini DM. mTOR signaling in growth, metabolism, and disease. Cell. 2017;168(6):960–976.',
								'doi' => '10.1016/j.cell.2017.02.004',
							],
						],
					]) ?>
				</section>

				<section class="demo__section">
					<h2 class="demo__heading">Цитата</h2>
					<?= $this->insert('components::ui/quote', [
						'text' => 'Рапамицин, введённый мышам в возрасте 20 месяцев, продлевал жизнь даже при начале лечения в позднем возрасте.',
						'cite' => 'Harrison et al.',
						'source' => 'Nature, 2009',
						'sourceUrl' => 'https://doi.org/10.1038/nature08221',
					]) ?>
				</section>
			</article>

			<aside class="demo__aside">
				<section class="demo__panel">
					<h2 class="demo__panel-title">Содержание</h2>
					<?= $this->insert('components::ui/sidebar-nav', [
						'items' => [
							['label' => 'Что известно', 'url' => '#what'],
							['label' => 'Оценка доказательств', 'url' => '#evidence'],
							['label' => 'Ключевые показатели', 'url' => '#stats'],
							['label' => 'Параметры исследования', 'url' => '#study'],
							['label' => 'Результаты', 'url' => '#outcomes'],
							['label' => 'Источники', 'url' => '#sources'],
						],
					]) ?>
				</section>

				<section class="demo__panel">
					<h2 class="demo__panel-title">Связанные материалы</h2>
					<ul class="demo__related">
						<?php foreach ([
							['mTOR', '/proteins/mtor/', 'Белок'],
							['Аутофагия', '/mechanisms/autophagy/', 'Механизм'],
							['Клеточное старение', '/mechanisms/cellular-senescence/', 'Механизм'],
						] as [$relatedLabel, $relatedUrl, $relatedType]): ?>
							<li class="demo__related-item">
								<a class="demo__related-link" href="<?= e($relatedUrl) ?>"><?= e($relatedLabel) ?></a>
								<span class="demo__related-type"><?= e($relatedType) ?></span>
							</li>
						<?php endforeach ?>
					</ul>
				</section>

				<section class="demo__panel">
					<h2 class="demo__panel-title">Исходный файл</h2>
					<p class="demo__panel-text">
						Документы корпуса хранятся в Git как Markdown с YAML-заголовком.
						Каждая страница ссылается на свой файл и историю изменений.
					</p>
					<?= $this->insert('components::ui/button', [
						'label' => 'Открыть на GitHub',
						'href' => 'https://github.com/westtrade/longevypedia',
						'variant' => 'outline',
						'size' => 'sm',
						'iconRight' => 'external',
					]) ?>
				</section>
			</aside>
		</div>

		<section class="demo__section demo__section--wide">
			<h2 class="demo__heading">Ещё исследования по теме</h2>
			<div class="demo__cards">
				<?= $this->insert('components::ui/card', [
					'eyebrow' => 'Исследование · 2014',
					'title' => 'Влияние ингибиторов mTOR на иммунный ответ у пожилых людей',
					'href' => '/research/',
					'meta' => 'Mannick et al. · Science Translational Medicine',
					'children' => '<p>Краткосрочное применение низких доз ингибитора mTOR улучшало ответ на вакцинацию у пожилых участников.</p>',
					'footer' => 'DOI 10.1126/scitranslmed.3009892',
				]) ?>
				<?= $this->insert('components::ui/card', [
					'eyebrow' => 'Механизм',
					'title' => 'Аутофагия',
					'href' => '/mechanisms/autophagy/',
					'meta' => 'Клеточный процесс',
					'children' => '<p>Лизосомная деградация собственных компонентов клетки; один из путей, через которые mTORC1 регулирует гомеостаз.</p>',
				]) ?>
				<?= $this->insert('components::ui/card', [
					'eyebrow' => 'Утверждение',
					'title' => 'Рапамицин продлевает жизнь мышам',
					'href' => '/claims/rapamycin-extends-lifespan-in-mice/',
					'meta' => 'Уверенность: вероятно',
					'children' => '<p>Утверждение ограничено животной моделью; перенос на людей не подтверждён.</p>',
				]) ?>
			</div>
		</section>

		<section class="demo__section demo__section--wide">
			<h2 class="demo__heading">Пустое состояние</h2>
			<?= $this->insert('components::ui/empty-state', [
				'title' => 'Здесь пока нет материалов',
				'text' => 'Этот раздел заполняется по мере проверки документов. Черновики не публикуются.',
				'action' => function (): void {
					echo '<a class="ui-button ui-button--outline ui-button--md" href="/catalog">Перейти в каталог</a>';
				},
			]) ?>
		</section>
	</div>
</div>

<style lang="scss">
	.demo {
		padding: var(--space-6) 0 var(--space-12);
	}

	.demo__inner {
		display: flex;
		flex-direction: column;
		gap: var(--space-5);
	}

	.demo__layout {
		display: grid;
		grid-template-columns: minmax(0, 1fr) var(--layout-sidebar);
		gap: var(--space-8);
		align-items: start;
	}

	.demo__article {
		display: flex;
		flex-direction: column;
		gap: var(--space-6);
		min-width: 0;
	}

	.demo__header {
		display: flex;
		flex-direction: column;
		gap: var(--space-3);
		padding-bottom: var(--space-5);
		border-bottom: 1px solid var(--color-border);
	}

	.demo__meta {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		align-items: center;
	}

	.demo__reading {
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
	}

	.demo__title {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-4xl);
		font-weight: var(--weight-semibold);
		line-height: var(--leading-tight);
		letter-spacing: var(--tracking-tight);
	}

	.demo__lead {
		max-width: var(--layout-measure);
		margin: 0;
		color: var(--color-text);
		font-family: var(--font-serif);
		font-size: var(--text-lg);
		line-height: var(--leading-relaxed);
	}

	.demo__tags {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.demo__section {
		display: flex;
		flex-direction: column;
		gap: var(--space-3);
		max-width: var(--layout-measure);
	}

	.demo__section--wide {
		max-width: none;
	}

	.demo__heading {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-2xl);
		font-weight: var(--weight-semibold);
		line-height: var(--leading-snug);
	}

	.demo__section p {
		margin: 0;
		font-family: var(--font-serif);
		font-size: var(--text-base);
		line-height: var(--leading-relaxed);
	}

	.demo__note {
		color: var(--color-text-muted);
		font-family: var(--font-ui) !important;
		font-size: var(--text-sm) !important;
	}

	.demo__evidence {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-3);
	}

	.demo__stats {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(min(100%, 12rem), 1fr));
		gap: var(--space-3);
	}

	.demo__aside {
		position: sticky;
		top: calc(var(--layout-header) + var(--space-4));
		display: flex;
		flex-direction: column;
		gap: var(--space-4);
	}

	.demo__panel {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		padding: var(--space-3);
		border: 1px solid var(--color-border);
		border-radius: var(--radius-md);
		background: var(--color-surface);
	}

	.demo__panel-title {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	.demo__panel-text {
		margin: 0;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.demo__related {
		display: flex;
		flex-direction: column;
		gap: var(--space-1);
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.demo__related-item {
		display: flex;
		justify-content: space-between;
		gap: var(--space-2);
		padding: var(--space-1) 0;
		border-bottom: 1px solid var(--color-border);
	}

	.demo__related-item:last-child {
		border-bottom: 0;
	}

	.demo__related-link {
		font-family: var(--font-ui);
		font-size: var(--text-sm);
	}

	.demo__related-type {
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		white-space: nowrap;
	}

	.demo__cards {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(min(100%, 18rem), 1fr));
		gap: var(--space-3);
	}

	@media (max-width: 60rem) {
		.demo__layout {
			grid-template-columns: minmax(0, 1fr);
		}

		.demo__aside {
			position: static;
		}
	}
</style>
<?php $this->end(); ?>
