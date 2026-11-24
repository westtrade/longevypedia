<?php

declare(strict_types=1);

namespace Longevypedia\Content\Markdown;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * Разбор Obsidian Wikilink: `[[цель]]`, `[[цель|подпись]]`, `[[цель#раздел]]`.
 *
 * Приоритет выше, чем у обычного разбора ссылок: `[[` должен достаться этому
 * парсеру, а не превратиться в текст со скобками.
 */
final class WikilinkParser implements InlineParserInterface
{
    /**
     * `[` `[` цель `#` раздел `|` подпись `]` `]`.
     * Цель и раздел не могут содержать `|`, `#`, скобки и перевод строки.
     */
    private const REGEX = '\[\[([^\[\]\n|#]+)(?:#([^\[\]\n|]+))?(?:\|([^\]\n]+))?\]\]';

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(self::REGEX);
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $matches = $inlineContext->getMatches();
        $target = trim($matches[1] ?? '');

        if ($target === '') {
            return false;
        }

        $fragment = trim($matches[2] ?? '');
        $label = trim($matches[3] ?? '');

        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());
        $inlineContext->getContainer()->appendChild(new WikilinkNode(
            target: $target,
            // Пустая подпись — не «показать путь»: рендер подставит заголовок
            // документа, как это делает Obsidian для `[[Заметка]]`.
            label: $label,
            fragment: $fragment,
            raw: $inlineContext->getFullMatch(),
        ));

        return true;
    }
}
