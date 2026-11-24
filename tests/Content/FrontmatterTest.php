<?php

declare(strict_types=1);

namespace Longevypedia\Tests\Content;

use Longevypedia\Content\ContentException;
use Longevypedia\Content\Frontmatter;
use Longevypedia\Tests\TestCase;

/**
 * Разбор YAML-заголовка: то, что ломается тише всего.
 *
 * Главная проверка — даты. `Yaml::parse()` без флагов превращает
 * `2026-11-20` в Unix-время, и тогда `updated` в каталоге показывает 1970 год.
 */
final class FrontmatterTest extends TestCase
{
	public function testSplitsMetaAndBody(): void
	{
		$raw = "---\nid: concept:longevity\ntitle: Longevity\n---\n\nBody text.\n";

		$result = Frontmatter::split($raw);

		self::assertSame('concept:longevity', $result['meta']['id']);
		self::assertSame('Longevity', $result['meta']['title']);
		self::assertSame('Body text.', trim($result['body']));
	}

	public function testKeepsDatesAsStringsNotTimestamps(): void
	{
		$result = Frontmatter::split("---\ncreated: 2026-11-01\nupdated: 2026-11-20\n---\n");

		self::assertSame('2026-11-01', $result['meta']['created']);
		self::assertSame('2026-11-20', $result['meta']['updated']);
	}

	public function testKeepsDateTimeWithTime(): void
	{
		$result = Frontmatter::split("---\nreviewed_at: 2026-11-20T14:30:00\n---\n");

		self::assertSame('2026-11-20T14:30:00+00:00', $result['meta']['reviewed_at']);
	}

	public function testNormalizesWindowsLineEndings(): void
	{
		$result = Frontmatter::split("---\r\nid: gene:foxo3\r\n---\r\n\r\nText\r\n");

		self::assertSame('gene:foxo3', $result['meta']['id']);
		self::assertSame('Text', trim($result['body']));
	}

	public function testStripsByteOrderMark(): void
	{
		$result = Frontmatter::split("\xEF\xBB\xBF---\nid: gene:foxo3\n---\n\nText");

		self::assertSame('gene:foxo3', $result['meta']['id']);
	}

	public function testRejectsFileWithoutFrontmatter(): void
	{
		$this->expectException(ContentException::class);
		$this->expectExceptionMessage('YAML');

		Frontmatter::split("# Just markdown\n\nNo frontmatter here.\n");
	}

	public function testRejectsUnclosedFrontmatter(): void
	{
		$this->expectException(ContentException::class);

		Frontmatter::split("---\nid: gene:foxo3\n\nBody without closing marker\n");
	}

	public function testRejectsBrokenYaml(): void
	{
		$this->expectException(ContentException::class);

		Frontmatter::split("---\nid: [unclosed\n---\n\nBody\n");
	}

	public function testRejectsScalarFrontmatter(): void
	{
		$this->expectException(ContentException::class);

		Frontmatter::split("---\njust a string\n---\n\nBody\n");
	}

	public function testParsesNestedStructures(): void
	{
		$raw = <<<'MD'
		---
		id: research:example
		study:
		  design: animal_experiment
		  year: 2009
		  outcomes:
		    - name: lifespan
		      direction: increase
		relations:
		  - type: studies
		    target: compound:rapamycin
		---
		MD;

		$meta = Frontmatter::split($raw)['meta'];

		self::assertSame('animal_experiment', $meta['study']['design']);
		self::assertSame(2009, $meta['study']['year']);
		self::assertSame('lifespan', $meta['study']['outcomes'][0]['name']);
		self::assertSame('studies', $meta['relations'][0]['type']);
	}
}
