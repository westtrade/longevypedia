<?php

declare(strict_types=1);

namespace Longevypedia\Content\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * Подключает Wikilinks к CommonMark.
 *
 * Отдельный класс нужен потому, что рендерер один на язык: он держит
 * разрешитель ссылок конкретной локали, а расширение — только регистрация.
 */
final class WikilinkExtension implements ExtensionInterface
{
    public function __construct(private readonly WikilinkRenderer $renderer)
    {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new WikilinkParser(), 100);
        $environment->addRenderer(WikilinkNode::class, $this->renderer, 100);
    }
}
