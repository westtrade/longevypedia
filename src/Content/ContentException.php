<?php

declare(strict_types=1);

namespace Longevypedia\Content;

/**
 * Ошибка чтения или разбора документа контента.
 *
 * Отдельный тип нужен, чтобы отличить «сломанный документ» от любой другой
 * ошибки приложения: страница статьи по такой ошибке отвечает 404, а
 * `scripts/content-check.php` показывает её как ошибку проверки.
 */
final class ContentException extends \RuntimeException
{
}
