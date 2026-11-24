<?php

declare(strict_types=1);

use Longevypedia\Content\DocumentRoutes;

/**
 * Конфигурация Longevypedia.
 *
 * Читается `Bolt\Core\Application::boot()` — файла достаточно, второй аргумент
 * с массивом не нужен. `vendor/bin/bolt` находит этот же файл сам, поэтому
 * CLI-команды видят ровно тот конфиг, что и сайт.
 *
 * Чего здесь сознательно нет:
 * - `bundles` — каталог `app/` подключается сам как системный бандл `_app`;
 * - `vite.port` — порт берётся из механизма Bolt (`vite_port()`:
 *   `config['vite.port']` → `VITE_PORT` → 5176). Значение совпадает с
 *   `web_extra_exposed_ports` в `.ddev/config.yaml`; менять нужно оба места;
 * - `vite.dev` — режим выводится из `APP_ENV` (в ddev это `dev`).
 */

$locales = [
	'en' => 'English',
];

$defaultLocale = 'en';

return [
	'debug' => env('BOLT_DEBUG', true),

	'app' => [
		'name' => 'Longevypedia',
		// Ссылки «исходник на GitHub» и «история изменений» строятся отсюда, а не
		// из захардкоженного адреса в шаблонах: репозиторий может быть форкнут.
		'repository' => env('APP_REPOSITORY', 'https://github.com/westtrade/longevypedia'),
		'branch' => env('APP_BRANCH', 'main'),
	],

	// Контент: Markdown + YAML Frontmatter в Git. Пути разворачиваются от корня
	// проекта, поэтому проект переносится без правок.
	'content' => [
		'path' => '%root%/content',
		'schemas' => '%root%/schemas',
		'topics' => '%root%/taxonomy/topics.php',
		'locales' => $locales,
		'default_locale' => $defaultLocale,
	],

	// Маршруты документов строятся из списка типов: каталоги, страницы статей,
	// страницы языков. Держать их руками в конфиге значило бы рассинхронизировать
	// конфиг и `DocumentTypes`.
	'urlpatterns' => DocumentRoutes::definitions($locales, $defaultLocale),

	// Каталог `app/` — системный бандл `_app`, поэтому его иконки адресуются
	// схемой `_app://icons`: `app://` указывает на обычный бандл с именем `app`,
	// которого в проекте нет, и источник молча пропускался бы.
	'icons' => [
		'sources' => ['_app://icons', 'bolt://icons'],
	],

	'vite' => [
		'entries' => ['app/client/main.ts'],
		'manifest' => '%root%/public/build/.vite/manifest.json',
		'port' => 5176,
	],

	'seo' => [
		'site_name' => 'Longevypedia',
		'title_separator' => ' · ',
		'locale' => 'en',
		'description' => 'Open scientific knowledge base about longevity, aging biology and healthspan.',
	],
];
