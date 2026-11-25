<?php

declare(strict_types=1);

namespace Longevypedia\Tests\Http;

use Longevypedia\Tests\TestCase;

/**
 * Страницы через настоящий `Kernel::handle()`.
 *
 * Проверяется не только код ответа: если шаблон не отрендерил тело, ответ
 * всё равно будет 200, поэтому у каждой страницы ищется собственный маркер.
 */
final class PagesTest extends TestCase
{
	public function testHomePageRendersHeroAndStats(): void
	{
		$response = $this->client()->get('/')->assertOk();

		$response->assertSee('<title>')
			->assertSee('Longevypedia')
			->assertSee('home-hero')
			->assertSee('Explore by topic');

		self::assertStringContainsString('id="main"', $response->body());
		self::assertGreaterThan(4000, strlen($response->body()));
	}

	public function testHeaderThemeSwitchOffersThreeStates(): void
	{
		// Тема выбирается списком, а не перебором по кругу: в ответе должны быть
		// все три пункта и признак выбранного, иначе состояние снова придётся
		// угадывать по значку.
		$body = $this->client()->get('/')->assertOk()->body();

		self::assertStringContainsString('data-theme-switch', $body);
		self::assertStringContainsString('data-theme-choice="auto"', $body);
		self::assertStringContainsString('data-theme-choice="light"', $body);
		self::assertStringContainsString('data-theme-choice="dark"', $body);
		// Без скрипта тема системная, поэтому `auto` отмечен уже в разметке;
		// подпись с выбранным состоянием дописывает `app/client/theme.ts`.
		self::assertStringContainsString('aria-pressed="true"', $body);
		self::assertSame(3, substr_count($body, 'data-theme-choice="'));
		self::assertSame(1, substr_count($body, 'aria-pressed="true"'));
	}

	public function testHomePageLinksToCatalogAndResearch(): void
	{
		$body = $this->client()->get('/')->assertOk()->body();

		self::assertStringContainsString('href="/catalog"', $body);
		self::assertStringContainsString('href="/research"', $body);
	}

	public function testHomePageDoesNotShowTheSameDocumentTwice(): void
	{
		$body = $this->client()->get('/')->assertOk()->body();

		// Две колонки стоят рядом: документ, попавший в «обновлённые», не должен
		// повторяться в «связанных», иначе первый экран показывает одно и то же.
		preg_match_all(
			'~href="(/[a-z]+/[a-z0-9-]+/)"~',
			$body,
			$matches,
		);

		$duplicates = array_keys(array_filter(
			array_count_values($matches[1]),
			static fn (int $count): bool => $count > 1,
		));

		self::assertSame([], $duplicates, 'Документы повторяются на главной: ' . implode(', ', $duplicates));
	}

	public function testHomePageMarksTopicWithoutDocumentsAsNotLinked(): void
	{
		$body = $this->client()->get('/')->assertOk()->body();

		self::assertStringContainsString('home-topic--empty', $body);
		self::assertStringContainsString('No documents yet', $body);
		self::assertStringNotContainsString(
			'href="/catalog?topic=biomarkers"',
			$body,
			'Пустая тематика не должна вести в пустой каталог',
		);
	}

	public function testCatalogListsDocuments(): void
	{
		$response = $this->client()->get('/catalog')->assertOk();

		$response->assertSee('catalog-results')
			->assertSee('Rapamycin')
			->assertSee('catalog__chip');
	}

	public function testCatalogCardKeepsTopicsInTheFooter(): void
	{
		// Карточки в сетке одной высоты, а описание занимает две или три строки.
		// Пока теги шли сразу за описанием, они висели на разной высоте, и ряд
		// читался рвано. Теперь теги — подвал карточки с `margin-top: auto`.
		$body = $this->client()->get('/catalog')->assertOk()->body();

		self::assertStringContainsString('doc-card__footer', $body);
		// Стили карточки вынесены в сборку, поэтому прижатие проверяется по
		// исходнику компонента, а не по ответу страницы.
		self::assertStringContainsString(
			'margin-top: auto',
			(string) file_get_contents(dirname(__DIR__, 2) . '/app/components/ui/document-card.php'),
		);

		// Тег — ссылка в каталог по тематике, и подпись у него человеческая:
		// слаг `aging-mechanisms` рядом с фильтром, где та же тематика названа
		// «Aging mechanisms», выглядел машинным кодом.
		self::assertStringContainsString('href="/catalog?topic=aging-mechanisms"', $body);
		self::assertStringContainsString('>Aging mechanisms<', $body);
		self::assertStringNotContainsString('>aging-mechanisms<', $body);
	}

	public function testCatalogFiltersByType(): void
	{
		$body = $this->client()->get('/catalog', ['type' => 'gene'])->assertOk()->body();

		self::assertStringContainsString('FOXO3', $body);
		self::assertStringNotContainsString('Harrison', $body);
	}

	public function testCatalogTypeIndexRouteWorks(): void
	{
		$body = $this->client()->get('/compounds/')->assertOk()->body();

		self::assertStringContainsString('Rapamycin', $body);
		self::assertStringContainsString('catalog-results', $body);
	}

	public function testDocumentPageRendersMarkdownAndMetadata(): void
	{
		$response = $this->client()->get('/compounds/rapamycin/')->assertOk();

		$response->assertSee('doc__title')
			->assertSee('doc__summary')
			->assertSee('prose')
			->assertSee('Rapamycin')
			->assertSee('application/ld+json')
			->assertSee('ui-meta__eyebrow')
			->assertSee('min read');
	}

	public function testDocumentPageShowsSourcesAndHistory(): void
	{
		$body = $this->client()->get('/compounds/rapamycin/')->assertOk()->body();

		self::assertStringContainsString('10.1038/nature08221', $body);
		self::assertStringContainsString('https://doi.org/10.1038/nature08221', $body);
		self::assertStringContainsString('/blob/main/content/en/compounds/rapamycin.md', $body);
		self::assertStringContainsString('/commits/main/content/en/compounds/rapamycin.md', $body);
	}

	public function testDocumentPageGroupsRelatedClaimsByEvidenceDirection(): void
	{
		$body = $this->client()->get('/compounds/rapamycin/')->assertOk()->body();

		self::assertStringContainsString('ui-evidence-summary', $body);
		self::assertStringContainsString('Supporting evidence', $body);
		self::assertStringContainsString('Rapamycin extends lifespan in mice', $body);
		self::assertStringNotContainsString('Contradicting evidence', $body, 'Утверждений с противоречащими данными в корпусе нет');
	}

	public function testDocumentPageRendersTableOfContents(): void
	{
		$body = $this->client()->get('/mechanisms/autophagy/')->assertOk()->body();

		// Содержание — боковой список набора, а не отдельный компонент.
		self::assertStringContainsString('ui-sidebar-nav', $body);
		self::assertStringContainsString('href="#', $body);
	}

	public function testDocumentPageRendersConnections(): void
	{
		$body = $this->client()->get('/compounds/rapamycin/')->assertOk()->body();

		self::assertStringContainsString('doc-relations', $body);
		self::assertStringContainsString('href="/proteins/mtor/"', $body);
	}

	public function testResearchDocumentRendersStudyFields(): void
	{
		$body = $this->client()->get('/research/harrison-2009-rapamycin-mice/')->assertOk()->body();

		self::assertStringContainsString('ui-research-meta', $body);
		self::assertStringContainsString('ui-kv__row', $body);
		// Подпись «Animal experiment» — для человека, код дизайна — для машин.
		self::assertStringContainsString('Animal experiment', $body);
		self::assertStringContainsString('data-code="animal_experiment"', $body);
	}

	public function testClaimDocumentRendersClaimFields(): void
	{
		$body = $this->client()->get('/claims/rapamycin-extends-lifespan-in-mice/')->assertOk()->body();

		// Утверждение печатается цитатой из набора, а не отдельным классом.
		self::assertStringContainsString('ui-claim', $body);
		self::assertStringContainsString('ui-quote__text', $body);
		self::assertStringContainsString('ui-claim-status', $body);
		// Уверенность и направление доказательств — независимые параметры,
		// оба должны доехать до разметки машинным кодом, а не только переводом.
		self::assertStringContainsString('data-confidence="likely"', $body);
		self::assertStringContainsString('data-evidence="supports"', $body);
	}

	public function testResearchIndexListsStudies(): void
	{
		$body = $this->client()->get('/research')->assertOk()->body();

		self::assertStringContainsString('research-index', $body);
		self::assertStringContainsString('Harrison', $body);
	}

	public function testSearchPageReturnsHits(): void
	{
		$body = $this->client()->get('/search', ['q' => 'rapamycin'])->assertOk()->body();

		self::assertStringContainsString('search-page', $body);
		self::assertStringContainsString('Rapamycin', $body);
	}

	public function testSearchPageHandlesEmptyQuery(): void
	{
		$this->client()->get('/search')->assertOk()->assertSee('search-page');
	}

	public function testSearchPageHandlesUnknownQuery(): void
	{
		$body = $this->client()->get('/search', ['q' => 'zzzznotaword'])->assertOk()->body();

		self::assertStringContainsString('search-page', $body);
		self::assertStringNotContainsString('search-page__hit', $body);
	}

	public function testAboutPageExplainsLicensing(): void
	{
		$body = $this->client()->get('/about')->assertOk()->body();

		self::assertStringContainsString('CC BY-SA 4.0', $body);
		self::assertStringContainsString('MIT', $body);
	}

	public function testAboutPageNavigationSticksAndHighlightsTheCurrentSection(): void
	{
		$body = $this->client()->get('/about')->assertOk()->body();

		// Липнет и подсвечивает: обе вещи живут в одном компоненте.
		self::assertStringContainsString('ui-sidebar-nav--sticky', $body);
		self::assertStringContainsString('ui-sidebar-nav--headings', $body);
		self::assertStringContainsString('data-heading-nav', $body);

		// Пункты — якоря этой же страницы, иначе подсветке нечего отслеживать.
		self::assertStringContainsString('href="#method"', $body);
		self::assertStringContainsString('id="method"', $body);

		// Скрипт обязан доехать до страницы: AssetExtractingEngine вырезает
		// любой <script> без атрибута `inline`, и подсветка молча не работает.
		// В сам HTML атрибут не попадает — движок его снимает.
		self::assertStringContainsString("document.querySelector('.ui-sidebar-nav--headings')", $body);
		$component = (string) file_get_contents(__DIR__ . '/../../app/components/ui/sidebar-nav.php');
		self::assertMatchesRegularExpression('/<script type="module" inline>/', $component);
	}

	public function testHeadingTrackingIsNotShippedToOtherPages(): void
	{
		foreach (['/', '/catalog', '/search'] as $url) {
			$body = $this->client()->get($url)->assertOk()->body();
			self::assertStringNotContainsString('ui-sidebar-nav--headings', $body, $url);
		}
	}

	public function testUiKitIndexListsComponents(): void
	{
		$body = $this->client()->get('/ui-kit')->assertOk()->body();

		self::assertStringContainsString('ui-kit-index', $body);
		self::assertStringContainsString('href="/ui-kit/button"', $body);
	}

	public function testUiKitIndexIsInsideThePageContainer(): void
	{
		// `.page__main` не даёт боковых отступов — их даёт `.container`.
		// Без него индекс набора прилипал к краю окна, тогда как все
		// остальные страницы выровнены по общей сетке.
		$body = $this->client()->get('/ui-kit')->assertOk()->body();

		self::assertMatchesRegularExpression(
			'/<div class="container ui-kit-index">/',
			$body,
			'индекс UI-набора должен быть обёрнут в .container',
		);
	}

	public function testUiKitComponentPageShowsExampleAndSource(): void
	{
		$body = $this->client()->get('/ui-kit/button')->assertOk()->body();

		self::assertStringContainsString('specimen', $body);
		self::assertStringContainsString('code-block__code', $body);
		self::assertStringContainsString('ui-button', $body);
	}

	public function testUiKitAccessibilityPageReportsPassingPairs(): void
	{
		$body = $this->client()->get('/ui-kit/accessibility')->assertOk()->body();

		self::assertStringContainsString('проходят AA', $body);
		self::assertStringNotContainsString('Ниже порога', $body);
	}

	public function testDemoPageUsesScientificComponents(): void
	{
		$body = $this->client()->get('/demo')->assertOk()->body();

		self::assertStringContainsString('demo__article', $body);
		self::assertStringContainsString('ui-evidence', $body);
		self::assertStringContainsString('ui-sources__link', $body);
	}

	public function testUnknownPageReturns404(): void
	{
		$response = $this->client()->get('/does-not-exist')->assertStatus(404);

		$response->assertSee('Page not found');
	}

	public function testUnknownDocumentReturns404(): void
	{
		$this->client()->get('/compounds/not-a-compound/')->assertStatus(404);
	}

	public function testResponsesAreHtmlWithCharset(): void
	{
		// Заголовок ставит `Kernel::run()`, а не `handle()`: клиент из
		// `Bolt\Testing` работает через `handle()` и заголовков не видит.
		// Поэтому здесь полный проход точки входа, а не `$this->client()`.
		$response = $this->runEntry('/');

		self::assertSame(200, $response['code']);
		self::assertSame('text/html; charset=utf-8', $response['headers']['Content-Type'] ?? null);
		self::assertSame(1, preg_match('/^\s*<!doctype html>/i', $response['body']), 'ответ должен начинаться с doctype');
	}

	public function testNotFoundResponseCarriesHtmlBody(): void
	{
		$response = $this->runEntry('/no-such-page');

		self::assertSame(404, $response['code']);
		self::assertSame('text/html; charset=utf-8', $response['headers']['Content-Type'] ?? null);
		self::assertStringContainsString('Page not found', $response['body']);
	}

	public function testPagesCarrySeoMetadata(): void
	{
		$body = $this->client()->get('/compounds/rapamycin/')->assertOk()->body();

		self::assertStringContainsString('<meta name="description"', $body);
		self::assertStringContainsString('og:title', $body);
		self::assertStringContainsString('<link rel="canonical"', $body);
	}

	public function testSearchFragmentIsReturnedForHtmx(): void
	{
		$response = $this->client()->get(
			'/search',
			['q' => 'rapamycin', '_fragments' => 'search-results'],
			['HX-Request' => 'true'],
		)->assertOk();

		$body = $response->body();

		self::assertStringContainsString('search-hits', $body);
		self::assertStringNotContainsString('<html', $body);
	}
}
