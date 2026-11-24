<?php

declare(strict_types=1);

namespace Longevypedia\Content\Markdown;

use League\CommonMark\Node\Inline\AbstractInline;

/**
 * Узел разметки `[[цель|подпись]]`.
 *
 * Узел хранит только то, что было в тексте; превращение цели в URL делает
 * рендерер (`WikilinkRenderer`), у которого есть доступ к индексу документов.
 */
final class WikilinkNode extends AbstractInline
{
    public function __construct(
        public readonly string $target,
        public readonly string $label,
        public readonly string $fragment = '',
        public readonly string $raw = '',
    ) {
        parent::__construct();
    }
}
