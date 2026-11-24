<?php

declare(strict_types=1);

namespace Longevypedia\Design;

use RuntimeException;

/**
 * Чтение дизайн-токенов из `_tokens.scss`.
 *
 * Страница UI-набора показывает не копию списка токенов, а сам файл: значения
 * парсятся из исходника, поэтому палитра на странице не может разойтись с
 * палитрой в сборке. Тот же разбор использует проверка контраста.
 *
 * Файл устроен предсказуемо: светлые значения в блоке `:root`, тёмные — в
 * миксине `dark-tokens`, который подключается дважды (системная тема и ручной
 * выбор). Оба блока разбираются одним регулярным выражением по строкам
 * `--name: value;`.
 */
final class Tokens
{
	public function __construct(
		private readonly string $file,
	) {
	}

	public static function fromProject(string $root): self
	{
		return new self(rtrim($root, '/\\') . '/app/client/styles/_tokens.scss');
	}

	/**
	 * Светлая тема: значения из `:root`.
	 *
	 * @return array<string, string>
	 */
	public function light(): array
	{
		return $this->parse($this->rootBlock());
	}

	/**
	 * Тёмная тема: значения из миксина `dark-tokens`.
	 *
	 * @return array<string, string>
	 */
	public function dark(): array
	{
		return $this->parse($this->mixinBlock());
	}

	/**
	 * Все токены одной темы, сгруппированные по роли.
	 *
	 * @return array<string, array<string, string>>
	 */
	public function grouped(string $theme = 'light'): array
	{
		$groups = [
			'Цвета' => [],
			'Типографика' => [],
			'Отступы' => [],
			'Скругления' => [],
			'Раскладка' => [],
			'Тени и фокус' => [],
		];

		foreach ($this->theme($theme) as $name => $value) {
			$group = match (true) {
				str_starts_with($name, '--color-') => 'Цвета',
				str_starts_with($name, '--space-') => 'Отступы',
				str_starts_with($name, '--radius-') => 'Скругления',
				str_starts_with($name, '--layout-') => 'Раскладка',
				str_starts_with($name, '--shadow-'),
				$name === '--focus-ring',
				$name === '--focus-ring-inset' => 'Тени и фокус',
				default => 'Типографика',
			};

			$groups[$group][$name] = $value;
		}

		return array_filter($groups, static fn (array $items): bool => $items !== []);
	}

	/**
	 * @return array<string, string>
	 */
	public function theme(string $theme): array
	{
		return $theme === 'dark' ? $this->dark() : $this->light();
	}

	public function value(string $name, string $theme = 'light'): ?string
	{
		return $this->theme($theme)[$name] ?? null;
	}

	/**
	 * Пары «текст на фоне», которые обязаны проходить WCAG AA.
	 *
	 * Список фиксирован вручную: автоматически перебрать все сочетания цветов
	 * нельзя — часть из них (границы, заливки) к тексту не относится, и
	 * случайные пары дали бы ложные провалы.
	 *
	 * @return list<array{fg: string, bg: string, label: string, size: 'normal'|'large'}>
	 */
	public function contrastPairs(): array
	{
		return [
			['fg' => '--color-text', 'bg' => '--color-bg', 'label' => 'Основной текст на фоне страницы', 'size' => 'normal'],
			['fg' => '--color-text', 'bg' => '--color-surface', 'label' => 'Основной текст на поверхности', 'size' => 'normal'],
			['fg' => '--color-text-muted', 'bg' => '--color-bg', 'label' => 'Второстепенный текст на фоне', 'size' => 'normal'],
			['fg' => '--color-text-muted', 'bg' => '--color-surface', 'label' => 'Второстепенный текст на поверхности', 'size' => 'normal'],
			['fg' => '--color-text-subtle', 'bg' => '--color-surface', 'label' => 'Подписи на поверхности', 'size' => 'normal'],
			['fg' => '--color-brand-text', 'bg' => '--color-surface', 'label' => 'Ссылка на поверхности', 'size' => 'normal'],
			['fg' => '--color-brand-text', 'bg' => '--color-brand-subtle', 'label' => 'Ссылка на подсветке бренда', 'size' => 'normal'],
			['fg' => '--color-on-brand', 'bg' => '--color-brand', 'label' => 'Текст на заливке бренда', 'size' => 'normal'],
			['fg' => '--color-accent-text', 'bg' => '--color-surface', 'label' => 'Акцентный текст', 'size' => 'normal'],
			['fg' => '--color-on-accent', 'bg' => '--color-accent-fill', 'label' => 'Текст на акцентной заливке', 'size' => 'normal'],
			['fg' => '--color-on-accent', 'bg' => '--color-accent-fill-hover', 'label' => 'Текст на акцентной заливке (наведение)', 'size' => 'normal'],
			['fg' => '--color-success-text', 'bg' => '--color-success-soft', 'label' => 'Успех: текст на подложке', 'size' => 'normal'],
			['fg' => '--color-warning-text', 'bg' => '--color-warning-soft', 'label' => 'Предупреждение: текст на подложке', 'size' => 'normal'],
			['fg' => '--color-danger-text', 'bg' => '--color-danger-soft', 'label' => 'Ошибка: текст на подложке', 'size' => 'normal'],
			['fg' => '--color-info-text', 'bg' => '--color-info-soft', 'label' => 'Информация: текст на подложке', 'size' => 'normal'],
			['fg' => '--color-control-border', 'bg' => '--color-surface', 'label' => 'Граница поля ввода', 'size' => 'large'],
			['fg' => '--color-brand', 'bg' => '--color-bg', 'label' => 'Индикатор фокуса', 'size' => 'large'],
		];
	}

	private function rootBlock(): string
	{
		return $this->block('/^:root\s*\{(.*?)^\}/ms');
	}

	private function mixinBlock(): string
	{
		return $this->block('/@mixin\s+dark-tokens\s*\{(.*?)^\}/ms');
	}

	private function block(string $pattern): string
	{
		$css = $this->css();

		if (preg_match($pattern, $css, $match) !== 1) {
			throw new RuntimeException(sprintf('Не найден блок токенов в %s', $this->file));
		}

		return $match[1];
	}

	private function css(): string
	{
		$css = @file_get_contents($this->file);

		if ($css === false) {
			throw new RuntimeException(sprintf('Не удалось прочитать %s', $this->file));
		}

		return $css;
	}

	/**
	 * @return array<string, string>
	 */
	private function parse(string $block): array
	{
		preg_match_all('/^\s*(--[a-z0-9-]+)\s*:\s*([^;]+);/mi', $block, $matches, PREG_SET_ORDER);

		$tokens = [];

		foreach ($matches as $match) {
			$tokens[$match[1]] = trim($match[2]);
		}

		return $tokens;
	}
}
