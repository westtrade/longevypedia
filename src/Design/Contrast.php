<?php

declare(strict_types=1);

namespace Longevypedia\Design;

/**
 * Контраст по WCAG 2.2.
 *
 * Считается по относительной яркости sRGB. Нужен, чтобы утверждение «пары
 * проходят AA» было проверяемым: страница доступности и тесты считают те же
 * числа по тем же токенам.
 */
final class Contrast
{
	/** Минимум для обычного текста. */
	public const AA_NORMAL = 4.5;

	/** Минимум для крупного текста, иконок и границ элементов управления. */
	public const AA_LARGE = 3.0;

	/**
	 * Отношение контраста двух цветов.
	 *
	 * @param string $foreground цвет в формате #rgb или #rrggbb
	 * @param string $background цвет в формате #rgb или #rrggbb
	 */
	public static function ratio(string $foreground, string $background): float
	{
		$fg = self::luminance($foreground);
		$bg = self::luminance($background);

		$light = max($fg, $bg);
		$dark = min($fg, $bg);

		return ($light + 0.05) / ($dark + 0.05);
	}

	public static function passes(float $ratio, float $minimum): bool
	{
		return $ratio >= $minimum;
	}

	public static function minimum(string $size): float
	{
		return $size === 'large' ? self::AA_LARGE : self::AA_NORMAL;
	}

	/**
	 * Относительная яркость цвета.
	 */
	public static function luminance(string $hex): float
	{
		[$r, $g, $b] = self::rgb($hex);

		return 0.2126 * self::channel($r) + 0.7152 * self::channel($g) + 0.0722 * self::channel($b);
	}

	/**
	 * @return array{0: float, 1: float, 2: float}
	 */
	private static function rgb(string $hex): array
	{
		$hex = ltrim(trim($hex), '#');

		if (strlen($hex) === 3) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
			return [0.0, 0.0, 0.0];
		}

		return [
			hexdec(substr($hex, 0, 2)) / 255,
			hexdec(substr($hex, 2, 2)) / 255,
			hexdec(substr($hex, 4, 2)) / 255,
		];
	}

	private static function channel(float $value): float
	{
		return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
	}
}
