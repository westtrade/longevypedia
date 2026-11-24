<?php

declare(strict_types=1);

namespace Longevypedia\Content;

/**
 * Один документ контента: Frontmatter, Markdown-тело и результат рендеринга.
 *
 * Объект неизменяемый. Всё, что нужно шаблону, читается методами, а не сырым
 * массивом Frontmatter: так шаблон не знает, что `title` может отсутствовать,
 * и не разбирает YAML повторно.
 */
final class Document
{
    /**
     * @param array<string, mixed>                         $meta       разобранный Frontmatter
     * @param list<array{level: int, id: string, text: string}> $toc    оглавление (h2–h4)
     * @param list<array{target: string, label: string, url: string, resolved: bool}> $links разрешённые Wikilinks
     * @param list<string>                                 $unresolved цели Wikilinks, которых нет в индексе
     */
    public function __construct(
        public readonly string $type,
        public readonly string $slug,
        public readonly string $locale,
        public readonly string $file,
        public readonly string $relativePath,
        public readonly string $url,
        public readonly array $meta,
        public readonly string $body,
        public readonly string $html = '',
        public readonly array $toc = [],
        public readonly array $links = [],
        public readonly array $unresolved = [],
    ) {
    }

    /**
     * Полный идентификатор с языком — ключ индекса.
     * `concept:longevity@en`.
     */
    public function key(): string
    {
        return $this->id() . '@' . $this->locale;
    }

    public function id(): string
    {
        return (string) ($this->meta['id'] ?? $this->type . ':' . $this->slug);
    }

    public function title(): string
    {
        return (string) ($this->meta['title'] ?? $this->slug);
    }

    public function summary(): string
    {
        return (string) ($this->meta['summary'] ?? '');
    }

    public function status(): string
    {
        return (string) ($this->meta['status'] ?? 'draft');
    }

    public function isPublished(): bool
    {
        return $this->status() === 'published';
    }

    public function created(): ?string
    {
        return isset($this->meta['created']) ? (string) $this->meta['created'] : null;
    }

    public function updated(): ?string
    {
        return isset($this->meta['updated']) ? (string) $this->meta['updated'] : null;
    }

    public function license(): ?string
    {
        return isset($this->meta['license']) ? (string) $this->meta['license'] : null;
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return $this->stringList('tags');
    }

    /**
     * @return list<string>
     */
    public function topics(): array
    {
        return $this->stringList('topics');
    }

    /**
     * @return list<string>
     */
    public function aliases(): array
    {
        return $this->stringList('aliases');
    }

    /**
     * @return list<string>
     */
    public function synonyms(): array
    {
        return $this->stringList('synonyms');
    }

    /**
     * Прямые связи из Frontmatter, в порядке объявления.
     *
     * @return list<array{type: string, target: string}>
     */
    public function relations(): array
    {
        $relations = $this->meta['relations'] ?? [];

        if (!is_array($relations)) {
            return [];
        }

        $result = [];

        foreach ($relations as $relation) {
            if (!is_array($relation)) {
                continue;
            }

            $type = (string) ($relation['type'] ?? '');
            $target = (string) ($relation['target'] ?? '');

            if ($type === '' || $target === '') {
                continue;
            }

            $result[] = ['type' => $type, 'target' => $target];
        }

        return $result;
    }

    /**
     * Источники: библиографическая ссылка и, если есть, машиночитаемые
     * идентификаторы. DOI/PMID не выдумываются — их отсутствие это нормально.
     *
     * @return list<array{citation: string, url: ?string, doi: ?string, pmid: ?string}>
     */
    public function sources(): array
    {
        $sources = $this->meta['sources'] ?? [];

        if (!is_array($sources)) {
            return [];
        }

        $result = [];

        foreach ($sources as $source) {
            if (is_string($source)) {
                $result[] = ['citation' => $source, 'url' => null, 'doi' => null, 'pmid' => null];

                continue;
            }

            if (!is_array($source)) {
                continue;
            }

            $citation = trim((string) ($source['citation'] ?? ''));

            if ($citation === '') {
                continue;
            }

            $result[] = [
                'citation' => $citation,
                'url' => self::optionalString($source['url'] ?? null),
                'doi' => self::optionalString($source['doi'] ?? null),
                'pmid' => self::optionalString($source['pmid'] ?? null),
            ];
        }

        return $result;
    }

    /**
     * Значение произвольного поля Frontmatter (для полей конкретного типа:
     * `authors`, `journal`, `evidence_level` и т. п.).
     */
    public function metaValue(string $key, mixed $default = null): mixed
    {
        return $this->meta[$key] ?? $default;
    }

    /**
     * @return list<string>
     */
    public function metaList(string $key): array
    {
        return $this->stringList($key);
    }

    public function wordCount(): int
    {
        $text = strip_tags($this->html !== '' ? $this->html : $this->body);
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        if ($text === '') {
            return 0;
        }

        return count(preg_split('/\s+/u', $text) ?: []);
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil($this->wordCount() / 220));
    }

    public function typeLabel(): string
    {
        return DocumentTypes::label($this->type);
    }

    /**
     * Ссылка на исходный Markdown-файл в GitHub (`content/<locale>/<dir>/<slug>.md`).
     */
    public function repositoryPath(): string
    {
        // `relativePath` считается от каталога контента (`en/concepts/longevity.md`),
        // а в репозитории файл лежит в `content/`. Без префикса ссылка на
        // GitHub ведёт в 404, и заметно это только по клику на странице.
        return 'content/' . $this->relativePath;
    }

    /**
     * @return list<string>
     */
    private function stringList(string $key): array
    {
        $value = $this->meta[$key] ?? [];

        if (is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $item) {
            if (is_scalar($item) && trim((string) $item) !== '') {
                $result[] = trim((string) $item);
            }
        }

        return $result;
    }

    private static function optionalString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
