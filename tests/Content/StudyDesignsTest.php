<?php

declare(strict_types=1);

namespace Longevypedia\Tests\Content;

use Longevypedia\Content\StudyDesigns;
use Longevypedia\Tests\TestCase;

/**
 * Словарь дизайнов исследования и JSON Schema не должны расходиться.
 *
 * Раньше подписи жили двумя копиями в шаблонах, и обе содержали значения,
 * которых нет в `schemas/research.schema.json` (`observational_cohort`,
 * `non_randomized_trial`, `genetic_association`, `mendelian_randomization`).
 * Контент с таким значением не проходил валидацию, а страница его печатала —
 * то есть проверка и интерфейс противоречили друг другу.
 */
final class StudyDesignsTest extends TestCase
{
	public function testLabelsCoverExactlyTheSchemaEnum(): void
	{
		$schema = json_decode(
			(string) file_get_contents($this->projectRoot() . '/schemas/research.schema.json'),
			true,
			512,
			JSON_THROW_ON_ERROR,
		);

		$enum = [];

		foreach ($schema['allOf'] as $part) {
			$design = $part['properties']['study']['properties']['design']['enum'] ?? null;

			if (is_array($design)) {
				$enum = $design;
			}
		}

		self::assertNotSame([], $enum, 'в схеме исследования не найден enum study.design');

		$labels = StudyDesigns::names();

		sort($enum);
		sort($labels);

		self::assertSame(
			$enum,
			$labels,
			'словарь StudyDesigns и enum схемы разошлись — добавьте/уберите значение в обоих местах',
		);
	}

	public function testUnknownDesignIsPrintedAsIs(): void
	{
		// Опечатка в контенте должна быть видна на странице, а не превращаться
		// в пустую строку: иначе документ выглядит полным, хотя поля нет.
		self::assertSame('observational_cohort', StudyDesigns::label('observational_cohort'));
		self::assertFalse(StudyDesigns::exists('observational_cohort'));
	}

	public function testKnownDesignHasHumanLabel(): void
	{
		self::assertTrue(StudyDesigns::exists('animal_experiment'));
		self::assertSame('Animal experiment', StudyDesigns::label('animal_experiment'));
		self::assertSame('Randomised controlled trial', StudyDesigns::label('randomized_controlled_trial'));
	}
}
