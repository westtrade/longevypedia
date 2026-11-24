<?php

declare(strict_types=1);

namespace Longevypedia\Search;

use Longevypedia\Content\Document;

/**
 * Поисковый движок.
 *
 * Интерфейс отдельный, потому что поиск — первое, что захочется заменить:
 * локальный индекс не переживёт десятков тысяч документов. Замена движка не
 * должна трогать ни формат контента, ни страницы: страницы работают с
 * `SearchHit`, а не с внутренним устройством индекса.
 */
interface SearchEngine
{
    /**
     * Строит индекс заново.
     *
     * @param list<Document> $documents
     */
    public function index(array $documents): void;

    /**
     * @param array{type?: string, topic?: string, locale?: string} $filters
     *
     * @return list<SearchHit>
     */
    public function search(string $query, array $filters = []): array;
}
