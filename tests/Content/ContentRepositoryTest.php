<?php

declare(strict_types=1);

namespace Longevypedia\Tests\Content;

use Longevypedia\Content\ContentRepository;
use Longevypedia\Content\Document;
use Longevypedia\Content\DocumentTypes;
use Longevypedia\Content\SchemaValidator;
use Longevypedia\Tests\TestCase;

/**
 * Репозиторий контента на настоящем корпусе `content/en`.
 *
 * Тесты намеренно не фиксируют точное число документов: корпус растёт. Они
 * проверяют инварианты — уникальность ID, разрешимость Wikilinks, отсутствие
 * проблем загрузки и работоспособность индексов.
 */
final class ContentRepositoryTest extends TestCase
{
	private ContentRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = $this->app->make(
			'longevypedia.content',
			static fn ($app) => ContentRepository::fromConfig($app->config(), true),
		);
	}

	public function testCorpusLoadsWithoutProblems(): void
	{
		self::assertSame([], $this->repository->problems());
		self::assertSame([], $this->repository->unresolvedLinks());
	}

	public function testCorpusHasDocumentsAndTopics(): void
	{
		$documents = $this->repository->all('en');

		self::assertGreaterThanOrEqual(10, count($documents));

		foreach ($documents as $document) {
			self::assertSame('en', $document->locale);
			self::assertTrue($document->isPublished());
			self::assertNotSame('', $document->title());
			self::assertNotSame('', $document->summary());
		}

		self::assertGreaterThanOrEqual(5, count($this->repository->topics('en')));
	}

	public function testEveryDocumentMatchesItsSchema(): void
	{
		$validator = new SchemaValidator($this->projectRoot() . '/schemas');
		$failures = [];

		foreach ($this->repository->allIncludingDrafts('en') as $document) {
			foreach ($validator->validate($document->type, $document->meta) as $error) {
				$failures[] = $document->relativePath . ': ' . $error;
			}
		}

		self::assertSame([], $failures);
	}

	public function testDocumentIdsAreUnique(): void
	{
		$seen = [];

		foreach ($this->repository->allIncludingDrafts() as $document) {
			$key = $document->id() . '@' . $document->locale;

			self::assertArrayNotHasKey($key, $seen, sprintf('Дублирующийся идентификатор %s', $key));

			$seen[$key] = $document->relativePath;
		}

		self::assertNotSame([], $seen);
	}

	public function testIdMatchesTypeAndSlug(): void
	{
		foreach ($this->repository->allIncludingDrafts() as $document) {
			self::assertSame(
				$document->type . ':' . $document->slug,
				$document->id(),
				$document->relativePath,
			);
		}
	}

	public function testFindReturnsKnownDocument(): void
	{
		$document = $this->repository->find('compound', 'rapamycin', 'en');

		self::assertInstanceOf(Document::class, $document);
		self::assertSame('compound:rapamycin', $document->id());
		self::assertSame('/compounds/rapamycin/', $document->url);
		self::assertStringContainsString('Rapamycin', $document->title());
	}

	public function testFindReturnsNullForUnknownDocument(): void
	{
		self::assertNull($this->repository->find('compound', 'does-not-exist', 'en'));
		self::assertNull($this->repository->find('unknown-type', 'rapamycin', 'en'));
	}

	public function testByIdAndResolveAgreeOnSameDocument(): void
	{
		$byId = $this->repository->byId('protein:mtor', 'en');

		self::assertNotNull($byId);

		foreach (['protein:mtor', 'proteins/mtor', '/proteins/mtor', 'mtor'] as $target) {
			$resolved = $this->repository->resolve($target, 'en');

			self::assertNotNull($resolved, sprintf('Не разрешилась ссылка "%s"', $target));
			self::assertSame($byId->key(), $resolved->key(), sprintf('Ссылка "%s" ведёт не туда', $target));
		}
	}

	public function testWikilinksInRenderedHtmlPointAtRealPages(): void
	{
		$document = $this->repository->find('mechanism', 'autophagy', 'en');
		self::assertNotNull($document);

		self::assertNotSame([], $document->links, 'В документе нет ни одной внутренней ссылки');
		self::assertSame([], $document->unresolved, 'В документе остались неразрешённые Wikilinks');

		foreach ($document->links as $target) {
			$resolved = $this->repository->resolve($target, $document->locale);

			self::assertNotNull($resolved, sprintf('Wikilink "%s" никуда не ведёт', $target));
		}
	}

	public function testRelationTargetsExistAndProduceBacklinks(): void
	{
		$rapamycin = $this->repository->find('compound', 'rapamycin', 'en');
		$mtor = $this->repository->find('protein', 'mtor', 'en');

		self::assertNotNull($rapamycin);
		self::assertNotNull($mtor);

		$targets = array_column($rapamycin->relations(), 'target');
		self::assertContains('protein:mtor', $targets);

		$backlinks = $this->repository->backlinks($mtor);
		$titles = [];

		foreach ($backlinks as $group) {
			self::assertNotSame('', $group['label']);

			foreach ($group['documents'] as $document) {
				self::assertInstanceOf(Document::class, $document);
				$titles[] = $document->title();
			}
		}

		self::assertContains($rapamycin->title(), $titles, 'Обратная ссылка на mTOR не появилась');
	}

	public function testRelatedGroupsAreLabelled(): void
	{
		$document = $this->repository->find('mechanism', 'cellular-senescence', 'en');
		self::assertNotNull($document);

		$groups = $this->repository->related($document);
		self::assertNotSame([], $groups);

		foreach ($groups as $group) {
			self::assertNotSame('', $group['label']);
			self::assertNotSame([], $group['documents']);
		}
	}

	public function testRecentIsSortedByUpdatedDescending(): void
	{
		$recent = $this->repository->recent(5, 'en');

		self::assertNotSame([], $recent);

		$dates = array_map(static fn (Document $document): string => (string) $document->updated(), $recent);
		$sorted = $dates;
		rsort($sorted);

		self::assertSame($sorted, $dates);
	}

	public function testPopularIsRankedByGraphLinks(): void
	{
		$popular = $this->repository->popular(5, 'en');

		self::assertNotSame([], $popular);
		self::assertLessThanOrEqual(5, count($popular));
	}

	public function testSearchFindsDocumentByTitle(): void
	{
		$hits = $this->repository->search('rapamycin', ['locale' => 'en']);

		self::assertNotSame([], $hits);
		self::assertSame('compound:rapamycin', $hits[0]->document->id());
		self::assertNotSame('', $hits[0]->snippet);
	}

	public function testSearchSupportsPrefixes(): void
	{
		$hits = $this->repository->search('senesc', ['locale' => 'en']);

		self::assertNotSame([], $hits);
		self::assertContains('mechanism:cellular-senescence', array_map(static fn ($hit) => $hit->document->id(), $hits));
	}

	public function testSearchRespectsTypeFilter(): void
	{
		$hits = $this->repository->search('rapamycin', ['locale' => 'en', 'type' => 'research']);

		foreach ($hits as $hit) {
			self::assertSame('research', $hit->document->type);
		}
	}

	public function testSearchReturnsEmptyForUnknownWord(): void
	{
		self::assertSame([], $this->repository->search('zzzzznotaword', ['locale' => 'en']));
	}

	public function testSearchHitHighlightingEscapesHtml(): void
	{
		$hits = $this->repository->search('rapamycin', ['locale' => 'en']);
		self::assertNotSame([], $hits);

		$highlighted = $hits[0]->highlighted();

		self::assertStringContainsString('<mark>', $highlighted);
		self::assertStringNotContainsString('<script', $highlighted);
	}

	public function testStatsMatchDocumentCounts(): void
	{
		$stats = $this->repository->stats('en');
		$documents = $this->repository->all('en');

		self::assertSame(count($documents), $stats['total']);

		$byType = [];
		foreach ($documents as $document) {
			$byType[$document->type] = ($byType[$document->type] ?? 0) + 1;
		}

		ksort($byType);
		$actual = $stats['byType'];
		ksort($actual);

		self::assertSame($byType, $actual);
	}

	public function testTopicsCarryCountsAndDescriptions(): void
	{
		$topics = $this->repository->topics('en');

		self::assertNotSame([], $topics);

		foreach ($topics as $slug => $topic) {
			self::assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', (string) $slug);
			self::assertNotSame('', $topic['label']);
			self::assertArrayHasKey('count', $topic);
			self::assertIsInt($topic['count']);
		}
	}

	public function testOfTypeReturnsOnlyThatType(): void
	{
		foreach ($this->repository->ofType('research', 'en') as $document) {
			self::assertSame('research', $document->type);
		}
	}

	public function testTranslationsAreEmptyWithSingleLocale(): void
	{
		$document = $this->repository->find('concept', 'longevity', 'en');
		self::assertNotNull($document);

		self::assertSame([], $this->repository->translations($document));
	}

	public function testEveryTypeDirectoryIsKnown(): void
	{
		foreach (DocumentTypes::all() as $type => $definition) {
			self::assertSame($type, DocumentTypes::fromDir($definition['dir']));
			self::assertFileExists(
				$this->projectRoot() . '/schemas/' . $definition['schema'],
				sprintf('Нет схемы для типа %s', $type),
			);
		}
	}
}
