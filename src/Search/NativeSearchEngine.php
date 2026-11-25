<?php

declare(strict_types=1);

namespace Longevypedia\Search;

use Longevypedia\Content\Document;

/**
 * Локальный полнотекстовый поиск в памяти.
 *
 * Индекс строится на запрос из уже разобранных документов: отдельного файла
 * индекса нет, потому что при текущем объёме (десятки документов) его сборка
 * дешевле чтения с диска. Схема простая: термин → документ → накопленный вес.
 *
 * Веса полей подобраны так, чтобы совпадение в заголовке перевешивало
 * совпадение в тексте: заголовок 10, синонимы 6, описание 5, теги 4,
 * тематики 3, текст 1. Редкий термин (высокий IDF) весит больше частого.
 */
final class NativeSearchEngine implements SearchEngine
{
    private const TITLE_EXACT_BONUS = 4.0;

    private const WEIGHTS = [
        'title' => 10.0,
        'aliases' => 6.0,
        'summary' => 5.0,
        'tags' => 4.0,
        'topics' => 3.0,
        'body' => 1.0,
    ];

    private const SNIPPET_LENGTH = 240;

    /**
     * @var list<string>
     */
    private const STOPWORDS = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'have',
        'in', 'is', 'it', 'its', 'of', 'on', 'or', 'that', 'the', 'their', 'to', 'was',
        'were', 'which', 'with',
    ];

    /**
     * @var array<string, Document>
     */
    private array $documents = [];

    /**
     * @var array<string, array<string, float>> термин => ключ документа => вес
     */
    private array $terms = [];

    /**
     * @var array<string, array<string, string>> ключ документа => поле => нормализованный текст
     */
    private array $fields = [];

    private bool $indexed = false;

    public function index(array $documents): void
    {
        $this->documents = [];
        $this->terms = [];
        $this->fields = [];
        $this->indexed = true;

        foreach ($documents as $document) {
            $key = $document->key();
            $this->documents[$key] = $document;

            $fields = [
                'title' => $document->title(),
                'aliases' => implode(' ', $document->aliases()),
                'summary' => $document->summary(),
                'tags' => implode(' ', $document->tags()),
                'topics' => implode(' ', $document->topics()),
                'body' => trim(strip_tags($document->html)),
            ];

            $this->fields[$key] = $fields;

            foreach ($fields as $field => $text) {
                $weight = self::WEIGHTS[$field] ?? 1.0;

                foreach (self::tokenize($text) as $term) {
                    $this->terms[$term][$key] = ($this->terms[$term][$key] ?? 0.0) + $weight;
                }
            }
        }
    }

    public function search(string $query, array $filters = []): array
    {
        if (!$this->indexed) {
            return [];
        }

        $tokens = array_values(array_unique(array_filter(
            self::tokenize($query),
            static fn (string $token): bool => !in_array($token, self::STOPWORDS, true) && mb_strlen($token) > 1,
        )));

        if ($tokens === []) {
            return [];
        }

        $total = max(1, count($this->documents));
        $scores = [];

        foreach ($tokens as $token) {
            $matched = [];

            if (isset($this->terms[$token])) {
                $matched = $this->terms[$token];
            } elseif (mb_strlen($token) >= 3) {
                // Префиксный поиск: «senesc» находит «senescence».
                foreach ($this->terms as $term => $weights) {
                    // Ключи-числа ("2009") PHP превращает в int: без приведения
                    // str_starts_with() падает с TypeError на первом же годе.
                    if (str_starts_with((string) $term, $token)) {
                        foreach ($weights as $key => $weight) {
                            $matched[$key] = ($matched[$key] ?? 0.0) + $weight;
                        }
                    }
                }
            }

            if ($matched === []) {
                continue;
            }

            $idf = log(1 + $total / count($matched));

            foreach ($matched as $key => $weight) {
                $scores[$key] = ($scores[$key] ?? 0.0) + $weight * $idf;
            }
        }

        $normalizedQuery = mb_strtolower(trim($query));
        $hits = [];

        foreach ($scores as $key => $score) {
            $document = $this->documents[$key];

            // Точное совпадение с заголовком важнее частоты: иначе запрос
            // «Rapamycin» открывает утверждение, где слово повторяется чаще,
            // а не статью о самом соединении.
            if (mb_strtolower($document->title()) === $normalizedQuery) {
                $score *= self::TITLE_EXACT_BONUS;
            }

            if (!self::matchesFilters($document, $filters)) {
                continue;
            }

            $hits[] = new SearchHit(
                document: $document,
                score: round($score, 3),
                snippet: self::snippet($document, $tokens),
                terms: $tokens,
            );
        }

        usort(
            $hits,
            static fn (SearchHit $a, SearchHit $b): int => $b->score <=> $a->score
                ?: strcmp($a->document->title(), $b->document->title()),
        );

        return $hits;
    }

    /**
     * @param array{type?: string, topic?: string, locale?: string} $filters
     */
    private static function matchesFilters(Document $document, array $filters): bool
    {
        $type = $filters['type'] ?? '';

        if ($type !== '' && $document->type !== $type) {
            return false;
        }

        $topic = $filters['topic'] ?? '';

        if ($topic !== '' && !in_array($topic, $document->topics(), true)) {
            return false;
        }

        $locale = $filters['locale'] ?? '';

        return $locale === '' || $document->locale === $locale;
    }

    /**
     * Фрагмент текста вокруг первого вхождения одного из терминов.
     *
     * @param list<string> $tokens
     */
    private static function snippet(Document $document, array $tokens): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags(self::plainText($document->html))));

        if ($text === '') {
            return '';
        }

        $position = null;

        foreach ($tokens as $token) {
            $found = mb_stripos($text, $token);

            if ($found !== false && ($position === null || $found < $position)) {
                $position = $found;
            }
        }

        if ($position === null) {
            return mb_substr($text, 0, self::SNIPPET_LENGTH);
        }

        $start = max(0, $position - (int) (self::SNIPPET_LENGTH / 3));
        $snippet = mb_substr($text, $start, self::SNIPPET_LENGTH);

        if ($start > 0) {
            $snippet = '…' . ltrim($snippet);
        }

        if ($start + self::SNIPPET_LENGTH < mb_strlen($text)) {
            $snippet = rtrim($snippet) . '…';
        }

        return $snippet;
    }

    /**
     * Разметка без служебных символов: подсказка не должна показывать `#`.
     *
     * `strip_tags()` склеивает соседние блоки («…and origin» + «Rapamycin…»
     * дают «originRapamycin»), а значок-якорь у заголовка вообще не текст
     * документа. Поэтому границы блоков становятся пробелом, а ссылка
     * `heading-permalink` вырезается до `strip_tags()`.
     */
    private static function plainText(string $html): string
    {
        $html = (string) preg_replace(
            '/<a\\b[^>]*class="[^"]*heading-permalink[^"]*"[^>]*>.*?<\/a>/su',
            '',
            $html,
        );

        $html = (string) preg_replace(
            '/<\/(p|h[1-6]|li|td|th|blockquote|div|section|figcaption|dd|dt)>/iu',
            '</$1> ',
            $html,
        );

        return (string) preg_replace('/<br\b[^>]*>/iu', ' ', $html);
    }

    /**
     * @return list<string>
     */
    public static function tokenize(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : array_values($parts);
    }
}
