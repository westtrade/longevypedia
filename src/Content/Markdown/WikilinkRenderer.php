<?php

declare(strict_types=1);

namespace Longevypedia\Content\Markdown;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Печатает Wikilink.
 *
 * Разрешённая ссылка становится обычным `<a>` с классом `wikilink`;
 * неразрешённая — `<span class="wikilink wikilink--missing">`: страница не
 * падает и не показывает битую ссылку, но цель запоминается, чтобы
 * `bolt content:check` и тесты сообщили о ней.
 */
final class WikilinkRenderer implements NodeRendererInterface
{
    /**
     * @var list<string>
     */
    private array $unresolved = [];

    /**
     * @param \Closure(string, string): ?array{url: string, title: string, id: string} $resolve
     */
    public function __construct(private readonly \Closure $resolve)
    {
    }

    public function reset(): void
    {
        $this->unresolved = [];
    }

    /**
     * @return list<string>
     */
    public function unresolved(): array
    {
        return $this->unresolved;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        WikilinkNode::assertInstanceOf($node);

        $resolved = ($this->resolve)($node->target, $node->fragment);

        // Без явной подписи показываем заголовок документа: путь
        // «concepts/longevity» в тексте читается как техническая строка.
        $label = $node->label !== ''
            ? $node->label
            : ($resolved['title'] ?? $node->target);

        $label = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($resolved === null) {
            $this->unresolved[] = $node->target;

            return new HtmlElement(
                'span',
                [
                    'class' => 'wikilink wikilink--missing',
                    'title' => 'Article not written yet: ' . $node->target,
                    'data-wikilink' => $node->target,
                ],
                $label,
            );
        }

        $href = $resolved['url'];

        if ($node->fragment !== '') {
            $href .= '#' . rawurlencode($node->fragment);
        }

        return new HtmlElement(
            'a',
            [
                'class' => 'wikilink',
                'href' => $href,
                'data-doc' => $resolved['id'],
                'title' => $resolved['title'],
            ],
            $label,
        );
    }
}
