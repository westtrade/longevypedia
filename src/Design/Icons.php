<?php

declare(strict_types=1);

namespace Longevypedia\Design;

/**
 * Реестр иконок спрайта.
 *
 * Иконки берутся из двух каталогов: свои (`app/icons`) и встроенные в Bolt
 * (`vendor/bsti/bolt/icons`). Спрайт собирается по тем же источникам, поэтому
 * страница UI-набора показывает ровно то, что доступно в шаблонах, — список не
 * дублируется вручную.
 *
 * Имя иконки — имя файла без суффикса `-icon` и без расширения: файл
 * `sun-icon.svg` вызывается как `['name' => 'sun']`.
 */
final class Icons
{
	/**
	 * @param list<array{path: string, source: string}> $sources
	 */
	public function __construct(
		private readonly array $sources,
	) {
	}

	public static function fromProject(string $root): self
	{
		$root = rtrim($root, '/\\');

		return new self([
			['path' => $root . '/app/icons', 'source' => 'Longevypedia'],
			['path' => $root . '/vendor/bsti/bolt/icons', 'source' => 'Bolt'],
		]);
	}

	/**
	 * @return list<array{name: string, source: string, file: string}>
	 */
	public function all(): array
	{
		$icons = [];

		foreach ($this->sources as $source) {
			$files = glob($source['path'] . '/*.svg') ?: [];
			sort($files);

			foreach ($files as $file) {
				$name = basename($file, '.svg');

				if (str_ends_with($name, '-icon')) {
					$name = substr($name, 0, -5);
				}

				// Своя иконка с тем же именем перекрывает встроенную.
				$icons[$name] ??= ['name' => $name, 'source' => $source['source'], 'file' => $file];
			}
		}

		ksort($icons);

		return array_values($icons);
	}

	/**
	 * @return array<string, list<array{name: string, source: string, file: string}>>
	 */
	public function grouped(): array
	{
		$groups = [];

		foreach ($this->all() as $icon) {
			$groups[$icon['source']][] = $icon;
		}

		return $groups;
	}
}
