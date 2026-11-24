<?php

declare(strict_types=1);

namespace Longevypedia\Content;

use Bolt\Core\Config\Configuration;
use Longevypedia\Content\Markdown\MarkdownRenderer;
use Longevypedia\Search\NativeSearchEngine;
use Longevypedia\Search\SearchEngine;
use Longevypedia\Search\SearchHit;

/**
 * Индекс контента: читает Markdown-файлы, собирает документы, разрешает ссылки.
 *
 * Единственный источник данных для страниц. Ни базы, ни кэша на диске: при
 * текущем объёме (десятки документов) разбор и рендеринг всего корпуса на
 * запрос занимают единицы миллисекунд, а кэш пришлось бы инвалидировать по
 * mtime каждого файла — то есть делать ту же работу.
 *
 * Порядок загрузки важен: сначала разбираются все Frontmatter (по ним строится
 * индекс), и только потом рендерится Markdown. Иначе Wikilink на документ,
 * который лежит дальше по алфавиту, не нашёлся бы.
 */
final class ContentRepository
{
    /**
     * @var array<string, Document> ключ `id@locale`
     */
    private array $documents = [];

    /**
     * @var array<string, string> `locale|type|slug` => ключ документа
     */
    private array $bySlug = [];

    /**
     * @var array<string, string> `locale|id` => ключ документа
     */
    private array $byId = [];

    /**
     * @var array<string, string> `locale|alias` => ключ документа
     */
    private array $byAlias = [];

    /**
     * @var array<string, array{url: string, title: string, id: string}> ключ документа => данные для `<a>`
     */
    private array $targets = [];

    /**
     * @var array<string, list<array{type: string, source: string}>> ключ цели => обратные связи
     */
    private array $backlinks = [];

    /**
     * @var array<string, true> `locale|type|slug` принятых файлов
     */
    private array $accepted = [];

    /**
     * @var list<string>
     */
    private array $problems = [];

    private bool $loaded = false;

    /**
     * @param array<string, string>                     $locales код языка => подпись
     * @param array<string, array{label: string, description?: string}> $topics  слаги тематик
     */
    public function __construct(
        private readonly string $contentPath,
        private readonly array $locales,
        private readonly string $defaultLocale,
        private readonly array $topics = [],
        private readonly SearchEngine $search = new NativeSearchEngine(),
        private readonly ?SchemaValidator $schemas = null,
    ) {
    }

    /**
     * @param bool $forceValidation включить проверку по схемам независимо от
     *                              режима отладки — так её запускают CI и
     *                              `scripts/content-check.php`
     */
    public static function fromConfig(Configuration $config, bool $forceValidation = false): self
    {
        $topicsFile = $config->resolvePath(
            (string) $config->get('content.topics', '%root%/taxonomy/topics.php'),
        );

        $topics = is_file($topicsFile) ? require $topicsFile : [];

        return new self(
            contentPath: $config->resolvePath((string) $config->get('content.path', '%root%/content')),
            locales: (array) $config->get('content.locales', ['en' => 'English']),
            defaultLocale: (string) $config->get('content.default_locale', 'en'),
            topics: is_array($topics) ? $topics : [],
            schemas: $forceValidation ? self::validator($config) : self::schemaValidator($config),
        );
    }

    /**
     * Проверка по схеме включается только в отладке: на боевом сайте она
     * ничего не даёт (контент уже проверен в CI), а схемы пришлось бы
     * разбирать на каждый запрос.
     */
    private static function schemaValidator(Configuration $config): ?SchemaValidator
    {
        if (!$config->bool('content.validate', $config->bool('debug', false))) {
            return null;
        }

        return self::validator($config);
    }

    private static function validator(Configuration $config): ?SchemaValidator
    {
        $schemaDir = $config->resolvePath((string) $config->get('content.schemas', '%root%/schemas'));

        return is_dir($schemaDir) ? new SchemaValidator($schemaDir) : null;
    }

    public function contentPath(): string
    {
        return $this->contentPath;
    }

    /**
     * @return array<string, string>
     */
    public function locales(): array
    {
        return $this->locales;
    }

    public function defaultLocale(): string
    {
        return $this->defaultLocale;
    }

    public function localeLabel(string $locale): string
    {
        return (string) ($this->locales[$locale] ?? $locale);
    }

    public function hasLocale(string $locale): bool
    {
        return isset($this->locales[$locale]);
    }

    /**
     * Опубликованные документы, по алфавиту.
     *
     * @return list<Document>
     */
    public function all(?string $locale = null): array
    {
        $this->load();

        $locale ??= $this->defaultLocale;
        $result = [];

        foreach ($this->documents as $document) {
            if ($document->locale === $locale && $document->isPublished()) {
                $result[] = $document;
            }
        }

        return self::sortByTitle($result);
    }

    /**
     * Все документы языка, включая черновики: нужно проверке контента и тестам.
     *
     * @return list<Document>
     */
    public function allIncludingDrafts(?string $locale = null): array
    {
        $this->load();

        $locale ??= $this->defaultLocale;
        $result = [];

        foreach ($this->documents as $document) {
            if ($document->locale === $locale) {
                $result[] = $document;
            }
        }

        return self::sortByTitle($result);
    }

    public function find(string $type, string $slug, string $locale): ?Document
    {
        $this->load();

        return $this->documentByKey($this->bySlug[$locale . '|' . $type . '|' . $slug] ?? null);
    }

    public function byId(string $id, string $locale): ?Document
    {
        $this->load();

        return $this->documentByKey($this->byId[$locale . '|' . $id] ?? null);
    }

    /**
     * Документ, на который смотрит ссылка: Wikilink, relation или id.
     *
     * Порядок попыток: язык запроса, затем язык по умолчанию. Поддерживаются
     * три формы цели, все — родные для Obsidian:
     * `concept:longevity` (id), `concepts/longevity` (путь от корня хранилища),
     * `longevity` (имя файла) и алиасы из Frontmatter.
     */
    public function resolve(string $target, string $locale): ?Document
    {
        return $this->documentByKey($this->lookup($target, $locale));
    }

    /**
     * Ключ документа, на который смотрит ссылка.
     *
     * Отдельный метод от `resolve()` нужен рендерингу: Wikilinks разрешаются
     * во время создания документов, когда часть из них ещё не построена, а
     * индексы (`byId`, `bySlug`, `byAlias`) уже заполнены целиком. Отсюда и
     * возвращаемый ключ, а не объект.
     *
     * Порядок попыток: язык запроса, затем язык по умолчанию. Поддерживаются
     * три формы цели, все — родные для Obsidian:
     * `concept:longevity` (id), `concepts/longevity` (путь от корня хранилища),
     * `longevity` (имя файла) и алиасы из Frontmatter.
     */
    private function lookup(string $target, string $locale): ?string
    {
        $this->load();

        $target = trim($target);

        if ($target === '') {
            return null;
        }

        $target = (string) preg_replace('/\.md$/i', '', $target);
        $target = trim($target, '/');

        if ($target === '') {
            return null;
        }

        if (str_contains($target, ':')) {
            return $this->byId[$locale . '|' . $target]
                ?? $this->byId[$this->defaultLocale . '|' . $target]
                ?? null;
        }

        if (str_contains($target, '/')) {
            $segments = array_values(array_filter(explode('/', $target), static fn (string $part): bool => $part !== ''));

            $targetLocale = $locale;

            if (isset($segments[0]) && isset($this->locales[$segments[0]])) {
                $targetLocale = (string) array_shift($segments);
            }

            $slug = (string) array_pop($segments);
            $type = isset($segments[0]) ? DocumentTypes::fromDir($segments[0]) : null;

            if ($type !== null) {
                return $this->bySlug[$targetLocale . '|' . $type . '|' . $slug]
                    ?? $this->bySlug[$this->defaultLocale . '|' . $type . '|' . $slug]
                    ?? null;
            }

            return $this->bySlugOnly($slug, $targetLocale) ?? $this->bySlugOnly($slug, $this->defaultLocale);
        }

        return $this->bySlugOnly($target, $locale)
            ?? $this->bySlugOnly($target, $this->defaultLocale)
            ?? $this->byAlias[$locale . '|' . $target]
            ?? $this->byAlias[$this->defaultLocale . '|' . $target]
            ?? null;
    }

    /**
     * Связи документа, сгруппированные по подписи.
     *
     * @return list<array{label: string, documents: list<Document>}>
     */
    public function related(Document $document): array
    {
        $groups = [];

        foreach ($document->relations() as $relation) {
            $target = $this->resolve($relation['target'], $document->locale);

            if ($target === null) {
                continue;
            }

            $label = RelationTypes::label($relation['type']);
            $groups[$label] ??= [];
            $groups[$label][$target->key()] = $target;
        }

        return self::toGroups($groups);
    }

    /**
     * Обратные связи: кто ссылается на этот документ.
     *
     * @return list<array{label: string, documents: list<Document>}>
     */
    public function backlinks(Document $document): array
    {
        $this->load();

        $groups = [];

        foreach ($this->backlinks[$document->key()] ?? [] as $backlink) {
            $source = $this->documents[$backlink['source']] ?? null;

            if ($source === null || !$source->isPublished()) {
                continue;
            }

            $label = RelationTypes::label($backlink['type']);
            $groups[$label] ??= [];
            $groups[$label][$source->key()] = $source;
        }

        return self::toGroups($groups);
    }

    /**
     * Переводы документа на другие языки.
     *
     * @return array<string, Document> код языка => документ
     */
    public function translations(Document $document): array
    {
        $this->load();

        $result = [];

        foreach (array_keys($this->locales) as $locale) {
            if ($locale === $document->locale) {
                continue;
            }

            $translation = $this->byId($document->id(), $locale);

            if ($translation !== null && $translation->isPublished()) {
                $result[$locale] = $translation;
            }
        }

        return $result;
    }

    /**
     * Последние обновления.
     *
     * @return list<Document>
     */
    public function recent(int $limit = 6, ?string $locale = null): array
    {
        $documents = $this->all($locale);

        usort(
            $documents,
            static fn (Document $a, Document $b): int => strcmp((string) $b->updated(), (string) $a->updated())
                ?: strcmp($a->title(), $b->title()),
        );

        return array_slice($documents, 0, $limit);
    }

    /**
     * Популярные материалы: больше всего связей и обратных ссылок.
     *
     * Это не статистика посещений — счётчика просмотров в проекте нет.
     * Считаются связи графа знаний: чем плотнее документ вплетён в энциклопедию,
     * тем выше он в списке.
     *
     * @return list<Document>
     */
    public function popular(int $limit = 6, ?string $locale = null): array
    {
        $documents = $this->all($locale);

        usort($documents, function (Document $a, Document $b): int {
            return $this->linkCount($b) <=> $this->linkCount($a)
                ?: strcmp($a->title(), $b->title());
        });

        return array_slice($documents, 0, $limit);
    }

    /**
     * @param array{type?: string, topic?: string, locale?: string} $filters
     *
     * @return list<SearchHit>
     */
    public function search(string $query, array $filters = []): array
    {
        $this->load();
        $this->ensureSearchIndex();

        return $this->search->search($query, $filters);
    }

    /**
     * Тематики с количеством опубликованных документов языка.
     *
     * @return array<string, array{label: string, description: string, count: int}>
     */
    public function topics(?string $locale = null): array
    {
        $locale ??= $this->defaultLocale;
        $counts = [];

        foreach ($this->all($locale) as $document) {
            foreach ($document->topics() as $topic) {
                $counts[$topic] = ($counts[$topic] ?? 0) + 1;
            }
        }

        $result = [];

        foreach ($this->topics as $slug => $definition) {
            $result[$slug] = [
                'label' => (string) ($definition['label'] ?? $slug),
                'description' => (string) ($definition['description'] ?? ''),
                'count' => $counts[$slug] ?? 0,
            ];
        }

        // Тематика, которую документы используют, но каталог не знает, — тоже
        // ошибка данных; показываем её как есть, чтобы она была видна.
        foreach ($counts as $slug => $count) {
            if (!isset($result[$slug])) {
                $result[$slug] = ['label' => $slug, 'description' => '', 'count' => $count];
            }
        }

        uasort($result, static fn (array $a, array $b): int => $b['count'] <=> $a['count'] ?: strcmp($a['label'], $b['label']));

        return $result;
    }

    /**
     * Сводка по корпусу для главной страницы.
     *
     * @return array{total: int, topics: int, studies: int, claims: int, byType: array<string, int>, byLocale: array<string, int>}
     */
    public function stats(?string $locale = null): array
    {
        $locale ??= $this->defaultLocale;
        $byType = [];
        $total = 0;

        foreach ($this->all($locale) as $document) {
            $byType[$document->type] = ($byType[$document->type] ?? 0) + 1;
            $total++;
        }

        $byLocale = [];

        foreach (array_keys($this->locales) as $code) {
            $byLocale[$code] = count($this->all($code));
        }

        return [
            'total' => $total,
            'topics' => count($this->topics($locale)),
            'studies' => $byType['research'] ?? 0,
            'claims' => $byType['claim'] ?? 0,
            'byType' => $byType,
            'byLocale' => $byLocale,
        ];
    }

    /**
     * Проблемы, замеченные при загрузке: файлы, которые не удалось принять.
     *
     * @return list<string>
     */
    public function problems(): array
    {
        $this->load();

        return $this->problems;
    }

    /**
     * Документы, у которых есть незакрытые Wikilinks.
     *
     * @return array<string, list<string>> ключ документа => цели
     */
    public function unresolvedLinks(): array
    {
        $this->load();

        $result = [];

        foreach ($this->documents as $key => $document) {
            if ($document->unresolved !== []) {
                $result[$key] = $document->unresolved;
            }
        }

        return $result;
    }

    /**
     * Документы по типу, по алфавиту.
     *
     * @return list<Document>
     */
    public function ofType(string $type, ?string $locale = null): array
    {
        return array_values(array_filter(
            $this->all($locale),
            static fn (Document $document): bool => $document->type === $type,
        ));
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $parsed = $this->parseFiles();

        $this->buildIndexes($parsed);
        $this->buildDocuments($parsed);
        $this->buildBacklinks();
    }

    /**
     * Первый проход: читает файлы и Frontmatter, ничего не рендерит.
     *
     * @return list<array{type: string, slug: string, locale: string, file: string, relative: string, meta: array<string, mixed>, body: string}>
     */
    private function parseFiles(): array
    {
        $parsed = [];

        foreach (array_keys($this->locales) as $locale) {
            foreach (DocumentTypes::TYPES as $type => $definition) {
                $directory = $this->contentPath . '/' . $locale . '/' . $definition['dir'];

                if (!is_dir($directory)) {
                    continue;
                }

                foreach ($this->markdownFiles($directory) as $file) {
                    $relative = $this->relativePath($file);

                    try {
                        $data = Frontmatter::split((string) file_get_contents($file));
                    } catch (ContentException $e) {
                        $this->problems[] = sprintf('%s: %s', $relative, $e->getMessage());

                        continue;
                    }

                    $slug = pathinfo($file, PATHINFO_FILENAME);

                    if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
                        $this->problems[] = sprintf(
                            '%s: имя файла должно быть слагом в нижнем регистре (латиница, цифры, дефис), получено "%s"',
                            $relative,
                            $slug,
                        );

                        continue;
                    }

                    $parsed[] = [
                        'type' => $type,
                        'slug' => $slug,
                        'locale' => $locale,
                        'file' => $file,
                        'relative' => $relative,
                        'meta' => $data['meta'],
                        'body' => $data['body'],
                    ];
                }
            }
        }

        return $parsed;
    }

    /**
     * Второй проход: индексы и проверки, которые не требуют рендеринга.
     *
     * @param list<array{type: string, slug: string, locale: string, file: string, relative: string, meta: array<string, mixed>, body: string}> $parsed
     */
    private function buildIndexes(array $parsed): void
    {
        $slugs = [];

        foreach ($parsed as $item) {
            $expectedId = $item['type'] . ':' . $item['slug'];
            $id = (string) ($item['meta']['id'] ?? '');

            if ($id === '') {
                $this->problems[] = sprintf('%s: во Frontmatter нет обязательного поля id', $item['relative']);

                continue;
            }

            if ($id !== $expectedId) {
                $this->problems[] = sprintf(
                    '%s: id "%s" не совпадает с типом и именем файла (ожидается "%s")',
                    $item['relative'],
                    $id,
                    $expectedId,
                );

                continue;
            }

            foreach (['title', 'summary', 'status', 'updated'] as $field) {
                if (trim((string) ($item['meta'][$field] ?? '')) === '') {
                    $this->problems[] = sprintf('%s: во Frontmatter нет обязательного поля %s', $item['relative'], $field);
                    continue 2;
                }
            }

            if ($this->schemas !== null) {
                foreach ($this->schemas->validate($item['type'], $item['meta']) as $message) {
                    $this->problems[] = sprintf('%s: %s', $item['relative'], $message);
                }
            }

            $slugKey = $item['locale'] . '|' . $item['type'] . '|' . $item['slug'];

            if (isset($this->accepted[$slugKey])) {
                $this->problems[] = sprintf(
                    '%s: документ "%s" уже объявлен в %s',
                    $item['relative'],
                    $item['slug'],
                    $this->bySlug[$slugKey],
                );

                continue;
            }

            // Слаг уникален в пределах языка: иначе `[[slug]]` неоднозначен.
            $plainKey = $item['locale'] . '|' . $item['slug'];

            if (isset($slugs[$plainKey])) {
                $this->problems[] = sprintf(
                    '%s: слаг "%s" уже занят документом %s — Wikilink [[%s]] стал бы неоднозначным',
                    $item['relative'],
                    $item['slug'],
                    $slugs[$plainKey],
                    $item['slug'],
                );

                continue;
            }

            $slugs[$plainKey] = $item['relative'];
            $this->accepted[$slugKey] = true;

            $key = $id . '@' . $item['locale'];
            $this->bySlug[$slugKey] = $key;
            $this->byId[$item['locale'] . '|' . $id] = $key;
            $this->targets[$key] = [
                'url' => DocumentRoutes::url($item['locale'], $item['type'], $item['slug'], $this->defaultLocale),
                'title' => (string) $item['meta']['title'],
                'id' => $id,
            ];

            $this->registerAliases($item, $key);
        }
    }

    /**
     * @param array{type: string, slug: string, locale: string, file: string, relative: string, meta: array<string, mixed>, body: string} $item
     */
    private function registerAliases(array $item, string $key): void
    {
        $aliases = $item['meta']['aliases'] ?? [];

        if (!is_array($aliases)) {
            return;
        }

        foreach ($aliases as $alias) {
            $alias = trim((string) $alias);

            if ($alias === '') {
                continue;
            }

            $aliasKey = $item['locale'] . '|' . $alias;

            if (isset($this->byAlias[$aliasKey])) {
                $this->problems[] = sprintf(
                    '%s: алиас "%s" уже занят документом %s',
                    $item['relative'],
                    $alias,
                    $this->byAlias[$aliasKey],
                );

                continue;
            }

            $this->byAlias[$aliasKey] = $key;
        }
    }

    /**
     * Третий проход: рендеринг Markdown и создание документов.
     *
     * @param list<array{type: string, slug: string, locale: string, file: string, relative: string, meta: array<string, mixed>, body: string}> $parsed
     */
    private function buildDocuments(array $parsed): void
    {
        $renderer = new MarkdownRenderer(
            fn (string $target, string $locale): ?array => $this->resolveLink($target, $locale),
        );

        foreach ($parsed as $item) {
            $slugKey = $item['locale'] . '|' . $item['type'] . '|' . $item['slug'];

            if (!isset($this->accepted[$slugKey])) {
                continue;
            }

            $key = $item['type'] . ':' . $item['slug'] . '@' . $item['locale'];

            $rendered = $renderer->render($item['body'], $item['locale']);

            $document = new Document(
                type: $item['type'],
                slug: $item['slug'],
                locale: $item['locale'],
                file: $item['file'],
                relativePath: $item['relative'],
                url: DocumentRoutes::url($item['locale'], $item['type'], $item['slug'], $this->defaultLocale),
                meta: $item['meta'],
                body: $item['body'],
                html: $rendered['html'],
                toc: $rendered['toc'],
                links: $rendered['links'],
                unresolved: $rendered['unresolved'],
            );

            $this->documents[$key] = $document;
        }
    }

    private function buildBacklinks(): void
    {
        foreach ($this->documents as $key => $document) {
            foreach ($document->relations() as $relation) {
                if (!RelationTypes::exists($relation['type'])) {
                    $this->problems[] = sprintf(
                        '%s: неизвестный тип связи "%s"',
                        $document->relativePath,
                        $relation['type'],
                    );

                    continue;
                }

                $target = $this->resolve($relation['target'], $document->locale);

                if ($target === null) {
                    $this->problems[] = sprintf(
                        '%s: связь %s указывает на несуществующий документ "%s"',
                        $document->relativePath,
                        $relation['type'],
                        $relation['target'],
                    );

                    continue;
                }

                $inverse = RelationTypes::inverse($relation['type']) ?? RelationTypes::RELATED_TO;

                $this->backlinks[$target->key()][] = [
                    'type' => $inverse,
                    'source' => $key,
                ];
            }
        }
    }

    /**
     * Разрешитель для Wikilinks: отдаёт данные для `<a>`, если документ найден.
     *
     * @return array{url: string, title: string, id: string}|null
     */
    private function resolveLink(string $target, string $locale): ?array
    {
        $key = $this->lookup($target, $locale);

        return $key === null ? null : ($this->targets[$key] ?? null);
    }

    private function linkCount(Document $document): int
    {
        return count($document->relations())
            + count($document->links)
            + count($this->backlinks[$document->key()] ?? []);
    }

    private function ensureSearchIndex(): void
    {
        $this->search->index(array_values($this->documents));
    }

    /**
     * @param array<string, array<string, Document>> $groups
     *
     * @return list<array{label: string, documents: list<Document>}>
     */
    private static function toGroups(array $groups): array
    {
        $result = [];

        foreach ($groups as $label => $documents) {
            $result[] = [
                'label' => $label,
                'documents' => self::sortByTitle(array_values($documents)),
            ];
        }

        return $result;
    }

    /**
     * @param list<Document> $documents
     *
     * @return list<Document>
     */
    private static function sortByTitle(array $documents): array
    {
        $collator = class_exists(\Collator::class) ? new \Collator('en_US') : null;

        usort(
            $documents,
            static function (Document $a, Document $b) use ($collator): int {
                if ($collator !== null) {
                    $result = $collator->compare($a->title(), $b->title());

                    if ($result !== 0) {
                        return $result;
                    }
                }

                return strcmp(mb_strtolower($a->title()), mb_strtolower($b->title()));
            },
        );

        return $documents;
    }

    /**
     * Все `.md` файлы каталога, рекурсивно, в устойчивом порядке.
     *
     * @return list<string>
     */
    private function markdownFiles(string $directory): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $path = $file->getPathname();

            if (!str_ends_with($path, '.md')) {
                continue;
            }

            if (str_contains($this->relativePath($path), '/.')) {
                continue;
            }

            $files[] = $path;
        }

        sort($files);

        return $files;
    }

    private function relativePath(string $file): string
    {
        $base = rtrim($this->contentPath, '/') . '/';

        return str_starts_with($file, $base) ? substr($file, strlen($base)) : $file;
    }

    private function documentByKey(?string $key): ?Document
    {
        return $key === null ? null : ($this->documents[$key] ?? null);
    }

    private function bySlugOnly(string $slug, string $locale): ?string
    {
        foreach (DocumentTypes::TYPES as $type => $definition) {
            $key = $this->bySlug[$locale . '|' . $type . '|' . $slug] ?? null;

            if ($key !== null) {
                return $key;
            }
        }

        return null;
    }
}
