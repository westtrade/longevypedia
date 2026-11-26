<?php

declare(strict_types=1);

namespace Longevypedia\Tests\Design;

use Longevypedia\Design\Contrast;
use Longevypedia\Design\Icons;
use Longevypedia\Design\Tokens;
use Longevypedia\Tests\TestCase;

/**
 * Дизайн-система как проверяемое утверждение, а не как обещание.
 *
 * `/ui-kit/accessibility` считает контраст на лету — значит, те же числа можно
 * проверить в CI. Если кто-то поправит цвет в `_tokens.scss` и не пересчитает
 * контраст, тест упадёт здесь, а не в браузере у читателя.
 */
final class DesignSystemTest extends TestCase
{
	private Tokens $tokens;

	protected function setUp(): void
	{
		parent::setUp();

		$this->tokens = Tokens::fromProject($this->projectRoot());
	}

	public function testTokensFileIsParsed(): void
	{
		$light = $this->tokens->light();

		self::assertGreaterThan(40, count($light), 'токенов должно быть много: цвета, типографика, отступы');
		self::assertSame('#176b67', strtolower((string) ($light['--color-brand'] ?? '')));
		self::assertSame('#f7f8f5', strtolower((string) ($light['--color-bg'] ?? '')));
	}

	public function testDarkThemeRedefinesEveryColourToken(): void
	{
		$light = $this->tokens->light();
		$dark = $this->tokens->dark();

		self::assertNotSame([], $dark, 'миксин dark-tokens должен разбираться');

		$missing = [];

		foreach (array_keys($light) as $name) {
			if (!str_starts_with($name, '--color-')) {
				continue;
			}

			if (!isset($dark[$name])) {
				$missing[] = $name;
			}
		}

		self::assertSame(
			[],
			$missing,
			'в тёмной теме не переопределены цвета: ' . implode(', ', $missing),
		);
	}

	public function testDarkThemeIsNotAMechanicalInversion(): void
	{
		// Тёмная тема задана отдельными значениями: если бы её получали
		// инверсией, `--color-bg` стал бы почти белым, а он тёмный.
		$darkBg = (string) ($this->tokens->dark()['--color-bg'] ?? '');

		self::assertNotSame('', $darkBg);
		self::assertLessThan(0.2, Contrast::luminance($darkBg), 'фон тёмной темы должен быть тёмным');
	}

	public function testEveryContrastPairPassesAaInBothThemes(): void
	{
		$failures = [];

		foreach (['light' => 'светлая тема', 'dark' => 'тёмная тема'] as $theme => $label) {
			$values = $this->tokens->theme($theme);

			foreach ($this->tokens->contrastPairs() as $pair) {
				$fg = $values[$pair['fg']] ?? null;
				$bg = $values[$pair['bg']] ?? null;

				self::assertNotNull($fg, $label . ': нет токена ' . $pair['fg']);
				self::assertNotNull($bg, $label . ': нет токена ' . $pair['bg']);

				$ratio = Contrast::ratio((string) $fg, (string) $bg);
				$minimum = Contrast::minimum($pair['size']);

				if (!Contrast::passes($ratio, $minimum)) {
					$failures[] = sprintf(
						'%s, %s: %.2f < %.1f (%s на %s)',
						$label,
						$pair['label'],
						$ratio,
						$minimum,
						$pair['fg'],
						$pair['bg'],
					);
				}
			}
		}

		self::assertSame([], $failures, "пары ниже AA:\n" . implode("\n", $failures));
	}

	public function testContrastMathMatchesKnownValues(): void
	{
		// Чёрный на белом — ровно 21:1, белый на белом — 1:1. Если формула
		// сломается, «всё проходит» станет ложью, а не проверкой.
		self::assertSame(21.0, round(Contrast::ratio('#000000', '#ffffff'), 2));
		self::assertSame(1.0, round(Contrast::ratio('#ffffff', '#ffffff'), 2));
		self::assertSame(1.0, round(Contrast::luminance('#ffffff'), 4));
		self::assertSame(0.0, round(Contrast::luminance('#000000'), 4));
	}

	public function testObsoleteTokenNamesAreGoneFromTemplates(): void
	{
		// Имена из первой версии дизайна. Оставленный где-то `--color-fg`
		// молча получает пустое значение и превращается в унаследованный цвет,
		// который на тёмной теме читается как «случайный».
		$obsolete = [
			'--color-fg',
			'--color-warn',
			'--page-width',
			'--text-width',
			'--gutter',
			'--color-bg-alt',
			'--color-accent-soft',
			'--font-text',
			'--color-accent-fg',
		];

		$offenders = [];

		foreach ($this->templateFiles() as $file) {
			$contents = (string) file_get_contents($file);

			foreach ($obsolete as $name) {
				// `--color-fg-muted` тоже устарел, поэтому проверяем префикс
				// целиком, а не точное совпадение.
				if (preg_match('/(?<![\w-])' . preg_quote($name, '/') . '(?![\w-])/', $contents) === 1) {
					$offenders[] = $this->shortPath($file) . ': ' . $name;
				}
			}
		}

		self::assertSame([], $offenders, "устаревшие токены:\n" . implode("\n", $offenders));
	}

	public function testEveryCustomPropertyIsDefinedOrAssignedLocally(): void
	{
		// `var(--нет-такого)` не ломает сборку: CSS просто отбрасывает
		// объявление, и свойство наследуется. Ошибка видна только глазами —
		// заголовок молча рисуется не тем шрифтом. Поэтому проверяем, что
		// каждое имя либо объявлено в токенах, либо задано в проекте локально.
		$tokens = (string) file_get_contents($this->projectRoot() . '/app/client/styles/_tokens.scss');
		preg_match_all('/(--[a-z0-9-]+)\s*:/', $tokens, $definedMatch);
		$defined = array_fill_keys($definedMatch[1], true);

		$used = [];
		$assigned = [];

		foreach ($this->templateFiles() as $file) {
			$contents = (string) file_get_contents($file);

			preg_match_all('/var\((--[a-z0-9-]+)/', $contents, $usedMatch);
			preg_match_all('/(--[a-z0-9-]+)\s*:/', $contents, $assignedMatch);

			foreach ($usedMatch[1] as $name) {
				$used[$name][] = $this->shortPath($file);
			}

			foreach ($assignedMatch[1] as $name) {
				$assigned[$name] = true;
			}
		}

		$offenders = [];

		foreach ($used as $name => $files) {
			if (isset($defined[$name]) || isset($assigned[$name])) {
				continue;
			}

			$offenders[] = $name . ' (' . implode(', ', array_unique($files)) . ')';
		}

		self::assertSame([], $offenders, "необъявленные CSS-переменные:\n" . implode("\n", $offenders));
	}

	public function testProjectIconsAreDiscoveredWithoutSuffix(): void
	{
		$icons = Icons::fromProject($this->projectRoot())->all();
		$names = array_column($icons, 'name');

		self::assertContains('search', $names);
		self::assertContains('github', $names);
		self::assertNotContains('search-icon', $names, 'суффикс -icon снимается: имя иконки — `search`');
	}

	/**
	 * Знак в шапке повторяет контур фавикона: пользователь узнаёт один логотип
	 * и во вкладке, и на странице. Тест держит геометрию синхронной — если
	 * фавикон перерисуют, расхождение всплывёт здесь, а не в вёрстке.
	 */
	public function testWordmarkRepeatsTheFaviconContour(): void
	{
		$root = $this->projectRoot();
		$favicon = (string) file_get_contents($root . '/public/favicon.svg');
		$wordmark = (string) file_get_contents($root . '/app/components/ui/wordmark.php');

		preg_match('/<path[^>]*\sd="([^"]+)"/', $favicon, $faviconPath);
		preg_match('/<path[^>]*\sd="([^"]+)"/', $wordmark, $wordmarkPath);

		self::assertNotEmpty($faviconPath[1] ?? '', 'В фавиконе не найден контур');
		self::assertNotEmpty($wordmarkPath[1] ?? '', 'В знаке шапки не найден контур');
		self::assertSame(
			$faviconPath[1],
			$wordmarkPath[1],
			'Контур знака в шапке разошёлся с public/favicon.svg',
		);
	}

	/**
	 * В шапке знак живёт на фоне страницы, поэтому подложки нет — только
	 * линии в currentColor. Литеральные цвета сломали бы тёмную тему.
	 */
	public function testWordmarkMarkMatchesTheNameInDarkTheme(): void
	{
		// В тёмной теме бирюзовый знак рядом со светлым названием читался как
		// вторая ссылка. Цвет знака — отдельный токен, и в тёмной теме он
		// совпадает с цветом текста.
		$tokens = (string) file_get_contents($this->projectRoot() . '/app/client/styles/_tokens.scss');

		preg_match('/@mixin dark-tokens\s*\{(.*?)\n\}/s', $tokens, $dark);
		self::assertNotEmpty($dark[1] ?? '', 'Не найден блок тёмной темы');

		preg_match('/--color-text:\s*(#[0-9a-fA-F]{6})/', $dark[1], $text);
		preg_match('/--color-wordmark-mark:\s*(#[0-9a-fA-F]{6})/', $dark[1], $mark);

		self::assertNotEmpty($text[1] ?? '', 'В тёмной теме нет --color-text');
		self::assertNotEmpty($mark[1] ?? '', 'В тёмной теме нет --color-wordmark-mark');
		self::assertSame(
			strtolower($text[1]),
			strtolower($mark[1]),
			'В тёмной теме знак логотипа должен быть того же цвета, что и название',
		);
	}

	public function testWordmarkMarkHasNoPlateAndNoLiteralColours(): void
	{
		$wordmark = (string) file_get_contents($this->projectRoot() . '/app/components/ui/wordmark.php');

		preg_match('/<span class="ui-wordmark__mark".*?<\/span>/s', $wordmark, $mark);

		self::assertNotEmpty($mark[0] ?? '', 'Не найден знак вордмарка');
		self::assertStringNotContainsString('<rect', $mark[0], 'У знака в шапке не должно быть подложки');
		self::assertStringNotContainsString('var(--color-on-brand)', $mark[0], 'Знак рисуется в currentColor');
		self::assertDoesNotMatchRegularExpression('/#[0-9a-f]{3,8}\b/i', $mark[0], 'В знаке не должно быть литеральных цветов');
	}

	public function testProjectIconsWinOverBuiltinOnes(): void
	{
		$icons = Icons::fromProject($this->projectRoot());
		$byName = [];

		foreach ($icons->all() as $icon) {
			$byName[$icon['name']] = $icon['source'];
		}

		// Обе иконки есть и в пакете, и в проекте — своя должна победить.
		foreach (['search', 'github'] as $name) {
			self::assertSame('Longevypedia', $byName[$name] ?? null, $name . ' должна браться из проекта');
		}

		self::assertSame('Bolt', $byName['burger'] ?? null, 'иконки пакета остаются доступны');
	}

	public function testEveryIconFileIsReferencedSomewhere(): void
	{
		// Неиспользуемая иконка — это либо забытая разметка, либо мёртвый файл.
		// Обе причины стоит увидеть в CI, а не искать глазами.
		$icons = Icons::fromProject($this->projectRoot())->all();
		$sources = '';

		foreach ($this->templateFiles() as $file) {
			$sources .= (string) file_get_contents($file);
		}

		$unused = [];

		foreach ($icons as $icon) {
			if ($icon['source'] !== 'Longevypedia') {
				continue;
			}

			if (preg_match('/[\'"]' . preg_quote($icon['name'], '/') . '[\'"]/', $sources) !== 1) {
				$unused[] = $icon['name'];
			}
		}

		self::assertSame([], $unused, 'иконки проекта, нигде не вставленные: ' . implode(', ', $unused));
	}

	public function testComponentsDoNotRedefineTokens(): void
	{
		// Компонент вправе задать локальную переменную (`--ui-card-gap`), но не
		// переопределять глобальную роль: иначе тема перестаёт быть единой.
		$offenders = [];

		foreach (glob($this->projectRoot() . '/app/components/ui/*.php') ?: [] as $file) {
			$contents = (string) file_get_contents($file);

			if (preg_match('/^\s*--color-[a-z-]+\s*:/m', $contents) === 1) {
				$offenders[] = $this->shortPath($file);
			}
		}

		self::assertSame([], $offenders, 'компоненты переопределяют глобальные цвета: ' . implode(', ', $offenders));
	}

	/**
	 * @return list<string>
	 */
	/**
	 * Точки перехода заданы одной шкалой.
	 *
	 * Медиазапрос не читает CSS-переменные, поэтому значения живут
	 * SCSS-переменными в `_breakpoints.scss`. Проверка держит два обещания:
	 * шкала действительно объявлена (иначе `$bp-md` тихо станет пустым) и ни
	 * один медиазапрос не написан числом мимо неё — иначе «точки перехода» есть
	 * только на бумаге.
	 */
	public function testBreakpointsComeFromTheSharedScale(): void
	{
		$file = $this->projectRoot() . '/app/client/styles/_breakpoints.scss';
		self::assertFileExists($file);

		$source = (string) file_get_contents($file);
		preg_match_all('/^\$bp-([a-z0-9]+):\s*([0-9.]+rem);/m', $source, $matches);

		self::assertNotSame([], $matches[1], 'Шкала точек перехода не объявлена.');

		$values = $matches[2];
		self::assertContains('48rem', $values, 'Планшетная точка 48rem должна быть в шкале.');

		$offenders = [];

		foreach ($this->templateFiles() as $path) {
			if (str_ends_with($path, '_breakpoints.scss')) {
				continue;
			}

			$text = (string) file_get_contents($path);

			// `@media (max-width: 48rem)` и `@media (min-width: 48rem)` — числа
			// мимо шкалы; `@media (width<=48rem)` — тоже.
			if (preg_match_all('/@media[^{]*?(?:max-width|min-width|width\s*[<>]=?)\s*:?\s*([0-9.]+(?:rem|px))/', $text, $found) > 0) {
				foreach ($found[1] as $value) {
					$offenders[] = $this->shortPath($path) . ' — ' . $value;
				}
			}
		}

		self::assertSame([], $offenders, 'Медиазапросы написаны числами мимо шкалы: ' . implode(', ', $offenders));
	}

	/**
	 * Акцентный цвет не используется как цвет текста.
	 *
	 * `--color-accent` (#c47a45) на светлом фоне даёт 3.17:1 — ниже AA для
	 * обычного текста. Он остаётся для заливок, рамок и линий, а текст берёт
	 * `--color-accent-text` (#9a5a2e, 5.08:1). Брендовый `--color-brand`
	 * проверять не нужно: 6.30:1 на поверхности и 5.91:1 на фоне.
	 */
	public function testAccentIsNotUsedAsTextColour(): void
	{
		$offenders = [];

		foreach ($this->templateFiles() as $path) {
			$text = (string) file_get_contents($path);

			// `color:` именно как цвет текста; `border-color`, `background`,
			// `outline-color` сюда не попадают.
			if (preg_match_all('/(?<![\w-])color:\s*var\(--color-accent\)/', $text, $found) > 0) {
				$offenders[] = $this->shortPath($path);
			}
		}

		self::assertSame([], $offenders, 'Акцент как цвет текста (нужен --color-accent-text): ' . implode(', ', $offenders));
	}

	public function testHomePageDoesNotStretchToViewportHeight(): void
	{
		$text = (string) file_get_contents($this->projectRoot() . '/app/pages/page.php');

		// `.page { min-height: 100dvh }` + `.page__main { flex: 1 }` — это приём
		// «подвал прижат к низу окна». На главной он давал полосу пустого фона
		// между «How we work» и подвалом, которую видно на высоком окне.
		self::assertStringContainsString('bodyClass', $text, 'главной нужен свой класс на body');
		self::assertStringContainsString('page--home', $text);
		self::assertMatchesRegularExpression(
			'/\.page--home \.page \{[^}]*min-height:\s*0/',
			$text,
			'главная не должна растягиваться до высоты окна',
		);
		self::assertDoesNotMatchRegularExpression(
			'/\.page--home \.site-footer \{[^}]*flex:\s*1/',
			$text,
			'растягивать подвал нельзя: он превращается в пустую плиту',
		);
	}

	public function testHoverOnFilledSurfaceRestatesTheTextColour(): void
	{
		// Общее правило `a:hover { color: var(--color-brand-hover) }` из базового
		// слоя по специфичности (0,1,1) перебивает одиночный класс (0,1,0).
		// Если правило наведения меняет заливку на акцентную или брендовую, но не
		// задаёт цвет текста, ссылка получает тёмно-бирюзовый текст на коричневой
		// заливке — 1.35:1, то есть не читается вообще.
		$fills = '/background(-color)?\s*:\s*var\(--color-(accent|accent-fill|accent-fill-hover|brand|brand-hover|danger|success)\)/';
		$offenders = [];

		foreach ($this->templateFiles() as $path) {
			$text = (string) file_get_contents($path);

			if (preg_match_all('/([^{}]*:hover[^{}]*)\{([^{}]*)\}/', $text, $blocks, PREG_SET_ORDER) === 0) {
				continue;
			}

			foreach ($blocks as $block) {
				$declarations = array_filter(array_map('trim', explode(';', $block[2])));

				$hasFill = false;
				$hasColour = false;

				foreach ($declarations as $declaration) {
					$hasFill = $hasFill || preg_match($fills, $declaration) === 1;
					$hasColour = $hasColour || preg_match('/^color\s*:/', $declaration) === 1;
				}

				if ($hasFill && !$hasColour) {
					$offenders[] = $this->shortPath($path) . ': ' . trim(explode('\n', $block[1])[count(explode('\n', $block[1])) - 1]);
				}
			}
		}

		self::assertSame([], $offenders, 'Наведение меняет заливку, но не задаёт цвет текста: ' . implode(', ', $offenders));
	}

	public function testHeaderControlsAreKitButtons(): void
	{
		// Переключатель темы и ссылка на исходники стоят в шапке рядом с поиском.
		// Раньше оба были свёрстаны вручную: кнопка 2.25rem со своей рамкой и
		// значок без полей. Теперь это `components::ui/button`, иначе рядом с
		// поиском оказывались три элемента разной высоты и разного фокуса.
		$header = (string) file_get_contents($this->projectRoot() . '/app/components/layout/site-header.php');

		self::assertStringContainsString("components::ui/theme-toggle", $header);
		self::assertStringContainsString("components::ui/button", $header);
		self::assertStringNotContainsString('class="theme-toggle"', $header);
		self::assertStringNotContainsString('.theme-toggle {', $header);
	}

	public function testTemplatesDoNotHandRollButtons(): void
	{
		// Кнопка, селект, поле и таблица живут в наборе. Вне `app/components/ui`
		// и `bolt://ui-kit` их разметку писать не нужно: иначе рядом с набором
		// появляется второй размер, второй фокус и второй набор состояний.
		$offenders = [];

		foreach ($this->templateFiles() as $path) {
			$relative = $this->shortPath($path);

			if (!str_starts_with($relative, 'app/') || preg_match('/\.(php)$/', $relative) !== 1) {
				continue;
			}

			if (str_starts_with($relative, 'app/components/ui/') || str_starts_with($relative, 'app/components/ui-kit/snippet')) {
				continue;
			}

			$text = (string) file_get_contents($path);

			if (preg_match('/<button\b/', $text) === 1) {
				$offenders[] = $relative . ': <button>';
			}

			if (preg_match('/<(select|textarea)\b/', $text) === 1) {
				$offenders[] = $relative . ': <select>/<textarea>';
			}

			if (preg_match('/<table\b/', $text) === 1) {
				$offenders[] = $relative . ': <table>';
			}
		}

		self::assertSame([], $offenders, 'Разметка контролов мимо набора: ' . implode(', ', $offenders));
	}

	public function testChoiceSignIsDrawnByAnElementNotAClipPath(): void
	{
		// Галочка и точка рисуются отдельными элементами внутри контрола с
		// `place-items: center`. Псевдоэлемент с `clip-path` считается от бокса
		// целиком, поэтому треугольник галочки уезжает вниз, а точка с `inset`
		// отступает от границы вместе с её толщиной — знак выглядел смещённым.
		$choice = (string) file_get_contents($this->projectRoot() . '/app/components/ui/choice.php');

		self::assertStringContainsString('ui-choice__tick', $choice);
		self::assertStringContainsString('ui-choice__dot', $choice);
		self::assertStringContainsString('place-items: center', $choice);
		self::assertSame(
			0,
			preg_match('/^\s*clip-path\s*:/m', $choice),
			'Знак выбора рисуется элементом, а не clip-path: псевдоэлемент считается от бокса целиком и смещает знак',
		);
	}

	public function testSkeletonAlwaysGetsAWidth(): void
	{
		// Строки заглушки заданы процентами. У flex-элемента без явной ширины
		// проценты считаются от нуля, и образец рисовал пустое место.
		$skeleton = (string) file_get_contents($this->projectRoot() . '/app/components/ui/skeleton.php');

		self::assertMatchesRegularExpression('/\.ui-skeleton\s*\{[^}]*width:\s*100%/', $skeleton);
	}

	public function testThemeSwitchOffersAllThreeStates(): void
	{
		// Раньше кнопка темы просто перебирала три состояния по кругу: какое
		// сейчас — видно только по значку, а вернуться к «как в системе» можно
		// было лишь нажав дважды. Теперь это список с явным выбором, поэтому
		// в разметке обязаны быть все три варианта и признак выбранного.
		$toggle = (string) file_get_contents($this->projectRoot() . '/app/components/ui/theme-toggle.php');
		$script = (string) file_get_contents($this->projectRoot() . '/app/client/theme.ts');

		self::assertStringContainsString('data-theme-switch', $toggle);
		self::assertStringContainsString('aria-pressed', $toggle);

		// Пункты списка строятся циклом, поэтому в шаблоне проверяется сам
		// перечень состояний, а не готовая разметка. Разметку проверяет
		// `PagesTest::testHeaderThemeSwitchOffersThreeStates` на живом ответе.
		foreach (['auto', 'light', 'dark'] as $value) {
			self::assertStringContainsString(
				"'value' => '{$value}'",
				$toggle,
				"В переключателе темы нет состояния {$value}",
			);
		}

		self::assertStringContainsString('aria-pressed', $script);
		self::assertStringContainsString('themeState', $script);
	}

	public function testDropdownsOpenOnHoverAndCloseWhenThePointerLeaves(): void
	{
		// Меню раскрывается при наведении и закрывается, когда курсор ушёл.
		// Закрытие отложено: между триггером и панелью есть зазор, и без
		// задержки меню закрывалось бы ровно на пути к пункту.
		$script = (string) file_get_contents($this->projectRoot() . '/app/client/hover-menu.ts');
		$main = (string) file_get_contents($this->projectRoot() . '/app/client/main.ts');
		$toggle = (string) file_get_contents($this->projectRoot() . '/app/components/ui/theme-toggle.php');
		$menu = (string) file_get_contents($this->projectRoot() . '/app/components/ui/menu.php');

		self::assertStringContainsString('[data-hover-menu]', $script);
		self::assertStringContainsString("addEventListener('pointerenter'", $script);
		self::assertStringContainsString("addEventListener('pointerleave'", $script);
		self::assertStringContainsString('initHoverMenus', $main);
		self::assertStringContainsString('data-hover-menu', $toggle);
		self::assertStringContainsString('data-hover-menu', $menu);

		// Отложенное закрытие, а не мгновенное: зазор между триггером и
		// панелью проходится за время задержки, а `pointerenter` её отменяет.
		self::assertMatchesRegularExpression('/const CLOSE_DELAY_MS = \\d+;/', $script);
		self::assertStringContainsString('cancelClose();', $script);

		// Только мышь: на сенсорном экране `pointerleave` приходит после
		// касания и закрывал бы меню в момент выбора.
		self::assertMatchesRegularExpression(
			"/pointerleave'[\\s\\S]{0,240}pointerType !== 'mouse'/",
			$script,
			'закрытие по уходу должно проверять pointerType',
		);
	}
	public function testDataTableKeepsCallerAttributes(): void
	{
		// Обёртка таблицы собирается из `attrs()`. Пока класс был записан в
		// шаблоне строкой, `attrs` из вызова молча терялись: таблица исходов
		// получала свои `ui-table`, но не отступ, который передавала карточка.
		$table = (string) file_get_contents($this->projectRoot() . '/app/components/ui/data-table.php');

		self::assertMatchesRegularExpression('/\$attrs\s*\?\?=\s*\[\]/', $table);
		self::assertStringContainsString('attrs($wrapperAttrs)', $table);
		self::assertStringNotContainsString('class="ui-table<?=', $table);
	}

	public function testNestedBlocksDoNotDrawTheirOwnFrame(): void
	{
		// Внутри карточки исследования живут список «поле — значение» и таблица
		// исходов. Оба рисовали свою рамку и свои линии, поэтому блок читался
		// как три вложенных прямоугольника. Вложенному блоку нужен вариант без
		// границ, а карточка передаёт его явно.
		$kv = (string) file_get_contents($this->projectRoot() . '/app/components/ui/key-value.php');
		$table = (string) file_get_contents($this->projectRoot() . '/app/components/ui/data-table.php');
		$meta = (string) file_get_contents($this->projectRoot() . '/app/components/ui/research-metadata.php');

		self::assertStringContainsString('ui-kv--plain', $kv);
		self::assertStringContainsString('ui-table--plain', $table);
		self::assertStringContainsString("'dividers' => false", $meta);
		self::assertStringContainsString("'variant' => 'plain'", $meta);
	}

	private function templateFiles(): array
	{
		$files = [];

		foreach (['/app', '/public'] as $dir) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($this->projectRoot() . $dir, \FilesystemIterator::SKIP_DOTS),
			);

			foreach ($iterator as $file) {
				if (!$file instanceof \SplFileInfo) {
					continue;
				}

				$path = $file->getPathname();

				if (preg_match('/\.(php|ts|scss|svg)$/', $path) === 1) {
					$files[] = $path;
				}
			}
		}

		sort($files);

		return $files;
	}

	private function shortPath(string $path): string
	{
		return ltrim(str_replace($this->projectRoot(), '', $path), '/');
	}
}
