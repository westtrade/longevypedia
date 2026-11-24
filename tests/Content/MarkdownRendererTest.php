<?php

declare(strict_types=1);

namespace Longevypedia\Tests\Content;

use Longevypedia\Content\Markdown\MarkdownRenderer;
use Longevypedia\Tests\TestCase;

/**
 * Рендер Markdown: Wikilinks, заголовки, оглавление, таблицы, ссылки.
 *
 * Резолвер подменён замыканием: тест не зависит от корпуса и проверяет только
 * поведение рендера.
 */
final class MarkdownRendererTest extends TestCase
{
	private function renderer(): MarkdownRenderer
	{
		return new MarkdownRenderer(
			static fn (string $target, string $locale): ?array => $target === 'concepts/longevity'
				? ['url' => '/concepts/longevity/', 'title' => 'Longevity', 'id' => 'concept:longevity']
				: null,
		);
	}

	public function testRendersWikilinkAsInternalLink(): void
	{
		$result = $this->renderer()->render('See [[concepts/longevity|longevity]] for details.', 'en');

		self::assertStringContainsString('href="/concepts/longevity/"', $result['html']);
		self::assertStringContainsString('class="wikilink"', $result['html']);
		self::assertStringContainsString('>longevity</a>', $result['html']);
		self::assertSame(['concept:longevity'], $result['links']);
		self::assertSame([], $result['unresolved']);
	}

	public function testRendersWikilinkWithoutLabelUsingTitle(): void
	{
		$result = $this->renderer()->render('See [[concepts/longevity]].', 'en');

		self::assertStringContainsString('>Longevity</a>', $result['html']);
	}

	public function testRendersUnresolvedWikilinkAsMissing(): void
	{
		$result = $this->renderer()->render('See [[mechanisms/unknown|unknown]].', 'en');

		self::assertStringContainsString('wikilink--missing', $result['html']);
		self::assertStringNotContainsString('href=', $result['html']);
		self::assertSame(['mechanisms/unknown'], $result['unresolved']);
		self::assertSame([], $result['links']);
	}

	public function testWikilinkFragmentBecomesAnchor(): void
	{
		$result = $this->renderer()->render('See [[concepts/longevity#history]].', 'en');

		self::assertStringContainsString('href="/concepts/longevity/#history"', $result['html']);
	}

	public function testBuildsTableOfContents(): void
	{
		$markdown = "## First section\n\nText.\n\n### Nested\n\nMore.\n\n## Second section\n";

		$result = $this->renderer()->render($markdown, 'en');

		self::assertCount(3, $result['toc']);
		self::assertSame(2, $result['toc'][0]['level']);
		self::assertSame('First section', $result['toc'][0]['text']);
		self::assertSame(3, $result['toc'][1]['level']);
		self::assertSame('Nested', $result['toc'][1]['text']);
		self::assertSame('first-section', $result['toc'][0]['id']);
	}

	public function testDuplicateHeadingsGetUniqueIds(): void
	{
		$markdown = "## Results\n\nA.\n\n## Results\n\nB.\n";

		$result = $this->renderer()->render($markdown, 'en');

		$ids = array_column($result['toc'], 'id');

		self::assertCount(2, $ids);
		self::assertNotSame($ids[0], $ids[1]);
		self::assertSame('results', $ids[0]);
		self::assertSame('results-1', $ids[1]);
	}

	public function testHeadingIdsAreAppliedToHeadings(): void
	{
		$result = $this->renderer()->render("## Rapamycin\n\nText.\n", 'en');

		self::assertStringContainsString('<h2 id="rapamycin">', $result['html']);
	}

	public function testRendersGithubTable(): void
	{
		$markdown = "| Design | Year |\n| --- | --- |\n| animal_experiment | 2009 |\n";

		$html = $this->renderer()->render($markdown, 'en')['html'];

		self::assertStringContainsString('<table>', $html);
		self::assertStringContainsString('<th>Design</th>', $html);
		self::assertStringContainsString('<td>animal_experiment</td>', $html);
	}

	public function testExternalLinksGetSafeAttributes(): void
	{
		$html = $this->renderer()->render('[Crossref](https://doi.org/10.1038/nature08221)', 'en')['html'];

		self::assertStringContainsString('href="https://doi.org/10.1038/nature08221"', $html);
		self::assertStringContainsString('rel="noopener noreferrer"', $html);
		self::assertStringContainsString('external-link', $html);
	}

	public function testUnsafeLinksAreStripped(): void
	{
		$html = $this->renderer()->render('[click](javascript:alert(1))', 'en')['html'];

		self::assertStringNotContainsString('javascript:', $html);
	}

	public function testRendersFootnotes(): void
	{
		$html = $this->renderer()->render("Text with a note.[^1]\n\n[^1]: The note.\n", 'en')['html'];

		self::assertStringContainsString('footnote', $html);
		self::assertStringContainsString('The note.', $html);
	}

	public function testKeepsInlineHtml(): void
	{
		$html = $this->renderer()->render('<span class="marker">kept</span>', 'en')['html'];

		self::assertStringContainsString('<span class="marker">kept</span>', $html);
	}

	public function testRendersPlainMarkdownText(): void
	{
		$result = $this->renderer()->render("Just a paragraph.\n\n- one\n- two\n", 'en');

		self::assertStringContainsString('<p>Just a paragraph.</p>', $result['html']);
		self::assertStringContainsString('<li>one</li>', $result['html']);
		self::assertSame([], $result['toc']);
	}
}
