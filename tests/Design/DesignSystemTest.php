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
