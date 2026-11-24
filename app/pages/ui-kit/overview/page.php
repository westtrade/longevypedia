<?php

declare(strict_types=1);

/**
 * UI-набор: обзор.
 *
 * Объясняет, из чего состоит набор, где что лежит и как добавить компонент.
 * Это документация, а не витрина: страница описывает принятые решения, чтобы
 * следующий разработчик не выбирал заново.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Обзор — UI-набор',
	'description' => 'Из чего состоит дизайн-система Longevypedia и как её расширять.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'overview',
	'title' => 'Обзор',
	'lead' => 'Дизайн-система Longevypedia: токены, базовые стили и компоненты, из которых собираются страницы энциклопедии.',
	'body' => function (): void { ?>
		<div class="kit-prose">
			<h2>Зачем это отдельно от страниц</h2>
			<p>
				Страница статьи, каталог и главная собираются из одних и тех же блоков: карточка
				документа, бейдж состояния, список источников, индикатор доказательности. Если
				каждый шаблон рисует их сам, через десять страниц появляются десять разных
				отступов и три оттенка зелёного. Поэтому разметка и оформление живут в
				компонентах, а страницы только передают данные.
			</p>

			<h2>Из чего состоит набор</h2>
			<ul>
				<li>
					<strong>Токены</strong> — единственный источник значений:
					<code>app/client/styles/_tokens.scss</code>. Цвета, типографика, шкала отступов,
					радиусы, раскладка, тени. Компоненты обращаются к ролям
					(<code>--color-text-muted</code>), а не к оттенкам.
				</li>
				<li>
					<strong>Базовые стили</strong> — <code>_base.scss</code> и <code>_prose.scss</code>:
					сброс, типографика документа, фокус, ссылки, длинный текст статьи.
				</li>
				<li>
					<strong>Компоненты</strong> — <code>app/components/ui/*.php</code>. Один файл:
					разметка, параметры и собственный <code>&lt;style lang="scss"&gt;</code>.
				</li>
				<li>
					<strong>Страницы набора</strong> — <code>app/pages/ui-kit/&lt;slug&gt;/page.php</code>:
					живые примеры и их исходники.
				</li>
				<li>
					<strong>Каталог</strong> — <code>data/ui-kit.php</code>: единственный список
					страниц. Меню, индекс и проверка читают только его.
				</li>
			</ul>

			<h2>Почему стили лежат в PHP-компонентах</h2>
			<p>
				BOLT извлекает <code>&lt;style lang="scss"&gt;</code> и
				<code>&lt;script lang="ts"&gt;</code> из шаблонов и складывает их в сгенерированную
				точку входа (<code>.bolt/client/generated/main/index.ts</code>), которую собирает
				Vite. Поэтому стили компонента физически лежат рядом с его разметкой и не требуют
				второго дерева файлов: правка одного компонента не открывает два файла, а удаление
				компонента удаляет и его стили.
			</p>
			<p>
				Это осознанное отличие от схемы «шаблон + отдельный
				<code>client/styles/components/&lt;slug&gt;.css</code>»: в стеке BOLT второй файл
				пришлось бы держать вручную в синхронизации с шаблоном, а механизм извлечения
				стилей уже существует и работает.
			</p>

			<h2>Слои CSS</h2>
			<p>
				Стили BOLT попадают в <code>@layer bolt.&lt;bundle&gt;</code>, стили проекта — вне
				слоёв. Каскадные слои дают предсказуемый порядок: встроенный UI-kit ниже, проектные
				компоненты выше, и переопределение не зависит от порядка подключения файлов.
			</p>

			<h2>Как добавить компонент</h2>
			<ol>
				<li>
					Создать <code>app/components/ui/&lt;slug&gt;.php</code>: объявить параметры через
					<code>??=</code>, собрать классы через <code>clsx()</code>, атрибуты — через
					<code>attrs()</code>, стили — в конце файла.
				</li>
				<li>
					Добавить slug в спецификацию <code>scripts/ui-kit-pages.php</code>: заголовок,
					пояснение и образцы.
				</li>
				<li>
					Запустить <code>php scripts/ui-kit-pages.php</code> — он перезапишет каталог и
					страницы компонентов.
				</li>
				<li>
					Проверить <code>bun run build</code> и страницу
					<code>/ui-kit/&lt;slug&gt;</code> в обеих темах.
				</li>
			</ol>

			<h2>Правила, которые набор соблюдает</h2>
			<ul>
				<li>Цвет не единственный носитель смысла: рядом всегда текст или иконка.</li>
				<li>Состояния есть у всего интерактивного: наведение, фокус, нажатие, недоступность, загрузка, ошибка.</li>
				<li>Взаимодействие — сначала HTML и CSS, JavaScript только там, где без него нельзя.</li>
				<li>Числа в таблицах и показателях набраны табличными цифрами.</li>
				<li>Анимации отключаются при <code>prefers-reduced-motion</code>.</li>
				<li>Компонент не задаёт внешние отступы: расстояние задаёт тот, кто его ставит.</li>
			</ul>

			<h2>Чего в наборе нет</h2>
			<p>
				Нет своей системы компонентов вместо BOLT: встроенный
				<code>bolt://ui-kit</code> используется там, где подходит (иконки, SEO-метаданные,
				модальное окно). Свои компоненты появляются только когда встроенный закрывает
				меньше, чем нужно странице, и причина записана в docblock компонента.
			</p>
		</div>

		<style lang="scss">
			.kit-prose {
				display: flex;
				flex-direction: column;
				gap: var(--space-4);
				max-width: var(--layout-measure);
			}

			.kit-prose h2 {
				margin: var(--space-6) 0 0;
				font-family: var(--font-serif);
				font-size: var(--text-2xl);
				font-weight: var(--weight-semibold);
				line-height: var(--leading-snug);
			}

			.kit-prose h2:first-child {
				margin-top: 0;
			}

			.kit-prose p,
			.kit-prose li {
				margin: 0;
				color: var(--color-text);
				font-family: var(--font-serif);
				font-size: var(--text-base);
				line-height: var(--leading-relaxed);
			}

			.kit-prose ul,
			.kit-prose ol {
				display: flex;
				flex-direction: column;
				gap: var(--space-2);
				margin: 0;
				padding-left: var(--space-5);
			}

			.kit-prose code {
				padding: 0.1em 0.35em;
				border-radius: var(--radius-sm);
				background: var(--color-surface-inset);
				color: var(--color-text);
				font-family: var(--font-mono);
				font-size: 0.9em;
			}

			.kit-prose strong {
				font-weight: var(--weight-semibold);
			}
		</style>
	<?php },
]) ?>
<?php $this->end(); ?>
