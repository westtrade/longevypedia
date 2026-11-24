<?php

declare(strict_types=1);

namespace Longevypedia\Search;

use Longevypedia\Content\Document;

/**
 * Результат поиска: документ, вес и фрагмент текста вокруг первого совпадения.
 *
 * Фрагмент отдаётся двух видов: `snippet` — чистый текст (для meta, для
 * вывода в текстовых форматах) и `highlighted()` — он же, экранированный и с
 * подсветкой найденных слов. Подсветка живёт здесь, а не в шаблоне: шаблон не
 * должен знать, как движок разбивает запрос на слова.
 */
final class SearchHit
{
    /**
     * @param list<string> $terms слова запроса, по которым построен фрагмент
     */
    public function __construct(
        public readonly Document $document,
        public readonly float $score,
        public readonly string $snippet = '',
        public readonly array $terms = [],
    ) {
    }

    /**
     * Фрагмент с подсветкой: готовый HTML, уже экранированный.
     *
     * Слово запроса совпадает с началом слова в тексте (движок ищет по
     * префиксу), поэтому хвост слова остаётся внутри `<mark>`.
     */
    public function highlighted(): string
    {
        $snippet = htmlspecialchars($this->snippet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $terms = array_values(array_filter(
            $this->terms,
            static fn (string $term): bool => mb_strlen($term) >= 3,
        ));

        if ($snippet === '' || $terms === []) {
            return $snippet;
        }

        // Длинные слова первыми: иначе «cell» съест начало «cellular».
        usort($terms, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        $alternation = implode('|', array_map(
            static fn (string $term): string => preg_quote($term, '/'),
            $terms,
        ));

        $highlighted = preg_replace(
            '/(' . $alternation . ')[\p{L}\p{N}]*/iu',
            '<mark>$0</mark>',
            $snippet,
        );

        return $highlighted ?? $snippet;
    }
}
