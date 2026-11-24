<?php

declare(strict_types=1);

namespace Longevypedia\Content;

use Bolt\Core\Bundle\Bundle;

/**
 * Маршруты документов: единственное место, где адрес страницы связан с типом.
 *
 * Один и тот же класс порождает и определения маршрутов для `config.php`, и
 * URL при рендеринге ссылок. Поэтому адрес в шапке статьи и адрес, который
 * разбирает роутер, не могут разойтись.
 */
final class DocumentRoutes
{
    /**
     * Определения маршрутов для `urlpatterns`.
     *
     * Три группы:
     * 1. индекс типа — `/concepts/` открывает каталог, отфильтрованный по типу.
     *    Исключение — `research`: у исследований свой индекс `/research/`,
     *    который отдаёт файловая страница `app/pages/research/page.php`;
     * 2. страницы документов — `/<dir>/<slug>/` и `/<locale>/<dir>/<slug>/`;
     * 3. локализованные служебные страницы — `/<locale>/`, `/<locale>/catalog/`,
     *    `/<locale>/search/`, чтобы навигация не сбрасывала язык.
     *
     * @param array<string, string> $locales код языка => подпись
     *
     * @return list<array<string, mixed>>
     */
    public static function definitions(array $locales, string $defaultLocale): array
    {
        $routes = [];

        foreach (DocumentTypes::TYPES as $type => $definition) {
            if ($type === 'research') {
                continue;
            }

            $routes[] = [
                'path' => $definition['dir'] . '/',
                'bundle' => Bundle::SYSTEM_NAME,
                'dir' => 'catalog',
                'type' => $type,
                'name' => 'catalog.' . $type,
            ];
        }

        foreach (array_keys($locales) as $locale) {
            foreach (DocumentTypes::TYPES as $type => $definition) {
                $routes[] = [
                    'path' => self::prefix($locale, $defaultLocale) . $definition['dir'] . '/<slug:slug>/',
                    'bundle' => Bundle::SYSTEM_NAME,
                    'dir' => 'document',
                    'type' => $type,
                    'locale' => $locale,
                    'name' => 'document.' . $type . ($locale === $defaultLocale ? '' : '.' . $locale),
                ];
            }
        }

        foreach (array_keys($locales) as $locale) {
            if ($locale === $defaultLocale) {
                continue;
            }

            // Главная для второго языка — тот же шаблон `pages/page.php`, а не
            // его копия: язык приходит в `$route['locale']`.
            $routes[] = [
                'path' => $locale . '/',
                'bundle' => Bundle::SYSTEM_NAME,
                'page' => 'page',
                'locale' => $locale,
                'name' => 'home.' . $locale,
            ];
            $routes[] = [
                'path' => $locale . '/catalog/',
                'bundle' => Bundle::SYSTEM_NAME,
                'dir' => 'catalog',
                'locale' => $locale,
                'name' => 'catalog.' . $locale,
            ];
            $routes[] = [
                'path' => $locale . '/search/',
                'bundle' => Bundle::SYSTEM_NAME,
                'dir' => 'search',
                'locale' => $locale,
                'name' => 'search.' . $locale,
            ];
        }

        return $routes;
    }

    public static function url(
        string $locale,
        string $type,
        string $slug,
        string $defaultLocale,
    ): string {
        return self::prefix($locale, $defaultLocale) . DocumentTypes::dir($type) . '/' . $slug . '/';
    }

    /**
     * Префикс языка: пустой для языка по умолчанию, `/ru` для остальных.
     */
    public static function prefix(string $locale, string $defaultLocale): string
    {
        return $locale === $defaultLocale ? '/' : '/' . $locale . '/';
    }
}
