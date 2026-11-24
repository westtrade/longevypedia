<?php

declare(strict_types=1);

namespace Longevypedia\Content\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Превращает Markdown-тело документа в HTML и оглавление.
 *
 * Набор возможностей — ровно тот, который понимает Obsidian: CommonMark, GFM
 * (таблицы, зачёркивание, чекбоксы, автолинки), сноски и Wikilinks. Своего
 * синтаксиса не добавляется.
 *
 * Конвертер создаётся один на язык и переиспользуется: разрешитель Wikilinks
 * привязан к локали, поэтому у каждого языка свой экземпляр.
 */
final class MarkdownRenderer
{
    /**
     * @var array<string, array{converter: MarkdownConverter, renderer: WikilinkRenderer}>
     */
    private array $converters = [];

    /**
     * @param \Closure(string, string): ?array{url: string, title: string, id: string} $resolve
     *        разрешитель цели Wikilink: (цель, локаль) → документ или null
     */
    public function __construct(private readonly \Closure $resolve)
    {
    }

    /**
     * @return array{
     *     html: string,
     *     toc: list<array{level: int, id: string, text: string}>,
     *     links: list<string>,
     *     unresolved: list<string>
     * }
     */
    public function render(string $markdown, string $locale): array
    {
        ['converter' => $converter, 'renderer' => $renderer] = $this->converter($locale);

        $renderer->reset();

        $html = (string) $converter->convert($markdown);
        [$html, $toc] = $this->prepareHeadings($html);

        // Документы, на которые в тексте есть разрешённые Wikilinks.
        preg_match_all('~data-doc="([^"]*)"~u', $html, $matches);

        return [
            'html' => $html,
            'toc' => $toc,
            'links' => array_values(array_unique($matches[1])),
            'unresolved' => array_values(array_unique($renderer->unresolved())),
        ];
    }

    /**
     * @return array{converter: MarkdownConverter, renderer: WikilinkRenderer}
     */
    private function converter(string $locale): array
    {
        if (isset($this->converters[$locale])) {
            return $this->converters[$locale];
        }

        $resolve = $this->resolve;
        $renderer = new WikilinkRenderer(
            static fn (string $target, string $fragment): ?array => $resolve($target, $locale),
        );

        // Расширения подключаются через addExtension(), а не вторым аргументом
        // конструктора: установленная версия league/commonmark принимает в
        // конструкторе только конфиг, и переданный список молча игнорируется —
        // падение вылезает позже, на рендере первого узла.
        $environment = new Environment(self::config());

        foreach ([
            new CommonMarkCoreExtension(),
            new GithubFlavoredMarkdownExtension(),
            new HeadingPermalinkExtension(),
            new FootnoteExtension(),
            new ExternalLinkExtension(),
            new WikilinkExtension($renderer),
        ] as $extension) {
            $environment->addExtension($extension);
        }

        return $this->converters[$locale] = [
            'converter' => new MarkdownConverter($environment),
            'renderer' => $renderer,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function config(): array
    {
        return [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 25,
            'slug_normalizer' => ['max_length' => 96],
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'after',
                'symbol' => '#',
                'title' => 'Permalink',
                'aria_hidden' => true,
                'apply_id_to_heading' => true,
                'min_heading_level' => 2,
                'max_heading_level' => 4,
            ],
            'external_link' => [
                'internal_hosts' => [],
                'open_in_new_window' => true,
                'html_class' => 'external-link',
                'nofollow' => '',
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
            'footnote' => [
                'backref_class' => 'footnote-backref',
                'backref_symbol' => '↩',
                'container_add_hr' => false,
                'container_class' => 'footnotes',
                'ref_class' => 'footnote-ref',
                'ref_id_prefix' => 'fnref-',
                'footnote_class' => 'footnote',
                'footnote_id_prefix' => 'fn-',
            ],
        ];
    }

    /**
     * Собирает оглавление по готовому HTML и делает якоря заголовков уникальными.
     *
     * CommonMark нумерует одинаковые заголовки одинаково (`## Results` дважды —
     * два `id="results"`), и вторая ссылка ведёт не туда. Здесь дубликаты
     * получают суффикс `-2`, `-3`, а ссылка-якорь внутри заголовка правится
     * вместе с ним.
     *
     * @return array{0: string, 1: list<array{level: int, id: string, text: string}>}
     */
    private function prepareHeadings(string $html): array
    {
        $toc = [];
        $seen = [];

        $result = preg_replace_callback(
            '~<h([2-6])([^>]*)>(.*?)</h\1>~su',
            static function (array $match) use (&$toc, &$seen): string {
                $level = (int) $match[1];
                $attributes = $match[2];
                $inner = $match[3];

                if (!preg_match('~\sid="([^"]*)"~u', $attributes, $idMatch)) {
                    return $match[0];
                }

                $id = $idMatch[1];
                $seen[$id] = ($seen[$id] ?? 0) + 1;

                if ($seen[$id] > 1) {
                    $unique = $id . '-' . $seen[$id];
                    $attributes = str_replace('id="' . $id . '"', 'id="' . $unique . '"', $attributes);
                    $inner = str_replace('href="#' . $id . '"', 'href="#' . $unique . '"', $inner);
                    $id = $unique;
                }

                // Подпись для оглавления: без якоря-ссылки и без тегов.
                $text = (string) preg_replace('~<a[^>]*class="heading-permalink"[^>]*>.*?</a>~su', '', $inner);
                $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = trim((string) preg_replace('~\s+~u', ' ', $text));

                if ($text !== '') {
                    $toc[] = ['level' => $level, 'id' => $id, 'text' => $text];
                }

                return '<h' . $level . $attributes . '>' . $inner . '</h' . $level . '>';
            },
            $html,
        );

        return [$result ?? $html, $toc];
    }
}
