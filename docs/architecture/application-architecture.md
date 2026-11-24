# Архитектура приложения

Документ описывает, как устроен сайт Longevypedia: маршруты, страницы, шаблоны, компоненты,
стили, сборку, кеширование и обработку ошибок. Ядро BOLT — в
[`bolt-analysis.md`](bolt-analysis.md); загрузка контента — в
[`content-architecture.md`](content-architecture.md).

## Маршруты

Маршруты делятся на три вида: явные `urlpatterns`, файловые страницы и системный маршрут
спрайта. Порядок обработки — `urlpatterns` → файловая структура → 404.

### `urlpatterns` (26)

Двенадцать каталогов, тринадцать страниц документов и спрайт. Каталог для `research` не
создаётся: у исследований своя страница `/research`.

| Путь | Шаблон | Назначение |
| --- | --- | --- |
| `/concepts` | `catalog` | каталог понятий |
| `/mechanisms` | `catalog` | каталог механизмов |
| `/pathways` | `catalog` | каталог путей |
| `/genes` | `catalog` | каталог генов |
| `/proteins` | `catalog` | каталог белков |
| `/biomarkers` | `catalog` | каталог биомаркеров |
| `/diseases` | `catalog` | каталог заболеваний |
| `/compounds` | `catalog` | каталог соединений |
| `/interventions` | `catalog` | каталог вмешательств |
| `/claims` | `catalog` | каталог утверждений |
| `/guidelines` | `catalog` | каталог руководств |
| `/articles` | `catalog` | каталог обзорных статей |
| `/concepts/<slug:slug>` | `document` | страница понятия |
| `/mechanisms/<slug:slug>` | `document` | страница механизма |
| `/pathways/<slug:slug>` | `document` | страница пути |
| `/genes/<slug:slug>` | `document` | страница гена |
| `/proteins/<slug:slug>` | `document` | страница белка |
| `/biomarkers/<slug:slug>` | `document` | страница биомаркера |
| `/diseases/<slug:slug>` | `document` | страница заболевания |
| `/compounds/<slug:slug>` | `document` | страница соединения |
| `/interventions/<slug:slug>` | `document` | страница вмешательства |
| `/research/<slug:slug>` | `document` | страница исследования |
| `/claims/<slug:slug>` | `document` | страница утверждения |
| `/guidelines/<slug:slug>` | `document` | страница руководства |
| `/articles/<slug:slug>` | `document` | страница статьи |
| `/assets/sprite.svg` | — | спрайт иконок (ядро, `bolt-icons-sprite`) |

Все маршруты генерирует `DocumentRoutes::definitions($locales, $defaultLocale)`; руками
в `config.php` они не перечислены. У маршрута документа есть ключи `type` и `locale`,
поэтому одна и та же страница `app/pages/document/page.php` обслуживает все 13 типов.

### Файловые страницы (42)

| Путь | Файл | Назначение |
| --- | --- | --- |
| `/` | `app/pages/page.php` | главная: статистика, тематики, свежее и связное |
| `/catalog` | `app/pages/catalog/page.php` | сводный каталог с фильтрами |
| `/search` | `app/pages/search/page.php` | поиск, в том числе фрагмент для HTMX |
| `/research` | `app/pages/research/page.php` | список исследований по годам |
| `/about` | `app/pages/about/page.php` | принципы, методология, лицензии |
| `/demo` | `app/pages/demo/page.php` | витрина компонентов на данных рапамицина |
| `/ui-kit` | `app/pages/ui-kit/page.php` | индекс UI-набора |
| `/ui-kit/<slug>` | `app/pages/ui-kit/<slug>/page.php` | 33 страницы компонентов и основ |
| `/error` | `app/pages/error.php` | шаблон ошибки, который подхватывает ядро |

`/error` не предназначен для прямого захода: это шаблон, который `ErrorRenderer` BOLT
использует для 404/405/500.

## Страницы

### Главная (`app/pages/page.php`)

Берёт `stats($locale)`, `topics($locale)`, `recent(4)`, `popular(4)`. Показывает
hero-поиск, четыре счётчика (документы, тематики, исследования, утверждения), сетку
тематик со ссылками на `/catalog?topic=…`, блоки «Recently updated» и «Most connected»
(карточки документов) и раздел о принципах. Ссылка на репозиторий — из
`$config['app']['repository']`.

### Каталог (`app/pages/catalog/page.php`)

Один шаблон на 13 маршрутов. Тип берётся из `$route['type']`, для `/catalog` — из
`?type=`. Параметры: `topic`, `sort` (`recent` | `title`), `q`. Если есть `q`,
документы — результаты поиска; иначе `all($locale)` с фильтрами по типу и тематике.
Сортировка: по `updated` убыванию, затем по заголовку.

Фильтры работают без JS (обычный GET) и с HTMX:

```html
hx-get="/catalog"
hx-target="#catalog-results"
hx-select="#catalog-results"
hx-swap="outerHTML"
hx-push-url="true"
hx-trigger="change, submit, input changed delay:400ms from:find input[type='search']"
```

`app/client/catalog-filters.ts` после `htmx:after:swap` копирует счётчик из
`.catalog-results__count` в `[data-catalog-status]` (aria-live) и прокручивает к началу
результатов.

### Документ (`app/pages/document/page.php`)

Один шаблон на все типы:

1. `$repository->find($type, $slug, $locale)`; `null` → `PageNotFoundException` → 404.
2. Ссылки «source» и «history»: `{repository}/blob/{branch}/{relativePath}` и
   `{repository}/commits/{branch}/{relativePath}`.
3. JSON-LD через `bolt://ui-kit/seo`: для `claim` — `Claim` с `text` из
   `claim.statement`; для `research` — `ScholarlyArticle` с `datePublished` из
   `study.year` и `identifier` из `study.doi`; для остальных — `Article` с
   `dateModified`.
4. Хлебные крошки: Knowledge → каталог типа → заголовок.
5. Метаданные: метка типа, дата обновления, время чтения (`readingMinutes()`), тематики.
6. Блоки типа: `components::doc/claim-fields` для `claim`,
   `components::doc/study-fields` для `research` (с флагом `isExample` из
   `study.example`).
7. `.prose` с готовым HTML, оглавление, источники (`doc/source-list`), связи и обратные
   ссылки (`doc/relation-groups`), подвал со ссылками на файл и историю.

Статус в `find()` не проверяется: черновик откроется по прямому адресу, хотя в каталог и
на главную не попадёт.

### Поиск (`app/pages/search/page.php`)

`q` и `type` из запроса; `type` проверяется через `DocumentTypes::exists()`, иначе
игнорируется. Результаты — `SearchHit`: документ, сниппет, `highlighted()` с `<mark>`.
Чипы типов строятся из `stats()['byType']`, то есть показывают только непустые типы.
Форма поиска в шапке умеет отдавать подсказки фрагментом
`app/fragments/search-results.php` (`?_fragments=search-results`, до 6 результатов).

### Исследования (`app/pages/research/page.php`)

`ofType('research')`, сортировка по `study.year` убыванию, затем по заголовку. Метка
дизайна берётся из `Longevypedia\Content\StudyDesigns::LABELS` — единственного словаря
подписей в проекте. Значения словаря совпадают с enum `study.design` в
`schemas/research.schema.json`: `systematic_review`, `meta_analysis`,
`randomized_controlled_trial`, `controlled_trial`, `cohort`, `case_control`,
`cross_sectional`, `case_series`, `case_report`, `animal_experiment`, `in_vitro`,
`ecological`, `narrative_review`, `other`. Авторы — первые три плюс «et al.». Запись с
`study.example: true` получает бейдж «Example record».

Неизвестное значение печатается как есть (подстановки нет), поэтому опечатка в контенте
видна на странице. Согласованность словаря и схемы проверяет
`tests/Content/StudyDesignsTest.php`: добавили значение в схему — тест напомнит добавить
подпись, и наоборот. Раньше подписи жили двумя копиями в `research/page.php` и
`doc/study-fields.php`, и обе содержали значения, которых в схеме нет
(`observational_cohort`, `non_randomized_trial`, `genetic_association`,
`mendelian_randomization`): контент с ними не проходил валидацию, а страница его
печатала.

### About, demo, UI-набор

- `/about` — назначение, открытость, методология (таблица пяти уровней уверенности),
  участие, формат, лицензии, дисклеймер.
- `/demo` — витрина: собирает почти все компоненты на данных рапамицина; полезен, чтобы
  увидеть состояния без похода по 33 страницам.
- `/ui-kit` — индекс по группам из `data/ui-kit.php`; страницы компонентов генерируются
  `scripts/ui-kit-pages.php` и показывают живой пример рядом с исходником.

## Компоненты и их роли

| Каталог | Роль | Примеры |
| --- | --- | --- |
| `app/components/layout/` | каркас страницы | `site-layout`, `site-header`, `site-footer` |
| `app/components/site/` | блоки сайта | `search-form`, `breadcrumbs` |
| `app/components/doc/` | документ и его части | `document-card`, `claim-fields`, `study-fields`, `source-list`, `relation-groups`, `table-of-contents` |
| `app/components/ui/` | элементы интерфейса | `button`, `field`, `choice`, `card`, `badge`, `tag`, `callout`, `quote`, `alert`, `tabs`, `menu`, `pagination`, `data-table`, `evidence`, `claim-status`, `toast`, `skeleton`, … |
| `app/components/ui-kit/` | инфраструктура набора | `nav`, `shell`, `specimen`, `snippet` |
| `app/components/catalog/` | результаты каталога | `results` |
| `app/fragments/` | HTMX-фрагменты | `search-results` |

Правило: страница выбирает данные и раскладывает блоки, компонент рисует один блок и
ничего не знает о репозитории. Компоненты получают готовые значения (строки, массивы,
`Document`) и не ходят в контент сами — исключение составляют `doc/relation-groups` и
`doc/source-list`, которым передают уже подготовленные группы и источники.

## Layout и секции

`app/components/layout/site-layout.php` — единственный layout. Параметры:
`$title`, `$description`, `$canonical`, `$ogType`, `$schema`, `$current`,
`$section = 'page'`, `$bodyClass`.

```php
$this->layout('components::layout/site-layout', [
    'title' => '...',
    'description' => '...',
    'canonical' => '/catalog',
    'current' => 'catalog',
]);
?>
<?php $this->start('page'); ?>
    ... разметка ...
<?php $this->end(); ?>
```

Почему `page`, а не `content`: имя `content` зарезервировано в Plates, `start('content')`
бросает `LogicException`. Layout печатает `section('page')` на строке 74.

Что делает layout:

- `<html lang="en">`, `<meta name="color-scheme" content="light dark">`;
- инлайновый скрипт в `<head>` до отрисовки применяет сохранённую тему из
  `localStorage['longevypedia.theme']` — поэтому нет вспышки светлой темы;
- `bolt://ui-kit/seo` с title/description/canonical/OG/JSON-LD;
- `vite_assets(['app/client/main.ts'], '/build')` — dev-сервер или манифест;
- skip-link на `#main`, шапку, `<main id="main">`, подвал.

`site-header.php` содержит навигацию (`catalog`, `research`, `about`) с `aria-current`,
компактный поиск, переключатель темы `[data-theme-toggle]` и ссылку на GitHub. Шапка
липкая, фон — `color-mix(in oklch, var(--color-bg) 88%, transparent)` с backdrop blur.

`site-footer.php` — три колонки ссылок и юридический блок: CC BY-SA 4.0 для текста, MIT
для кода, «not medical advice».

## Где живут стили

```
app/client/styles/_tokens.scss     роли: --color-*, --text-*, --space-*, --radius-*,
                                   --layout-*, --shadow-*, --focus-ring*
app/client/styles/_base.scss       сброс, body, ссылки, фокус, .container, .page
app/client/styles/_prose.scss      оформление статьи (.prose)
app/client/styles/_fonts.scss      @font-face, unicode-range
app/client/styles/main.scss        @use 'tokens' → 'base' → 'prose'
app/components/**/<name>.php       <style lang="scss"> в конце файла
app/pages/**/page.php              <style lang="scss"> для стилей страницы
```

Порядок каскада: стили BOLT попадают в `@layer bolt.<bundle>`, стили проекта — вне слоёв,
поэтому проектные правила выигрывают без `!important`. `main.scss` импортируется после
сгенерированного barrel'а (см. `app/client/main.ts`).

Правила: в компоненте нет hex-цветов и «магических» пикселей — только токены; media
queries пишутся desktop-first (`max-width`), потому что базовый макет — широкий.

## Сборка Vite

- Точка входа — `app/client/main.ts` (указана в `vite.entries` и в
  `rollupOptions.input`).
- Плагин `componentAssets` собирает `<style lang="scss">` и `<script lang="ts">` из
  шаблонов в `.bolt/client/generated/main/index.ts`.
- `bun run build` → `public/build/` с `manifest.json` в `public/build/.vite/`.
- `base` в production — `/build/`, поэтому ссылки выглядят как
  `/build/assets/main-*.css`.
- `copyPublicDir: false`: `public/` не копируется в `public/build`, статика отдаётся из
  docroot напрямую.
- В dev Vite работает демоном DDEV на 5176; `vite_assets()` печатает
  `@vite/client` и входы, HMR живёт.

Манифест после сборки выглядит так:

```json
{
  "app/client/main.ts": {
    "file": "assets/main-7XmtTDVN.js",
    "name": "main",
    "src": "app/client/main.ts",
    "isEntry": true,
    "css": ["assets/main-BK5GGC_2.css"]
  }
}
```

Если манифеста нет, страница отдаётся без CSS/JS, а в лог пишется предупреждение
`точки входа "app/client/main.ts" нет в манифесте Vite…`. Это самая частая причина
«сломанной вёрстки» после деплоя.

## Кеш и производительность

- **Контент.** `ContentRepository` загружается один раз на запрос (флаг `$loaded`);
  внутри запроса все страницы и компоненты используют один и тот же объект. Между
  запросами кеша нет.
- **Markdown.** Конвертер кешируется по локали внутри `MarkdownRenderer`, но живёт
  столько же, сколько репозиторий.
- **Поиск.** Индекс строится при первом `search()` за запрос.
- **Сортировка.** `sortByTitle()` использует `\Collator('en_US')`, если расширение
  intl доступно; иначе — обычное сравнение строк.
- **Спрайт иконок.** Отдаётся маршрутом с ETag по хешу содержимого и
  `Cache-Control: immutable`; повторные запросы получают 304.
- **Шрифты и статика.** Обычные файлы в `public/`, кешируются браузером; версионирования
  имён нет, поэтому при замене шрифта кеш надо сбрасывать.
- **HTML.** Заголовков кеширования у страниц нет: каждый запрос — полный рендер. Для
  текущего корпуса это миллисекунды, для роста нужен кеш (см.
  `content-architecture.md`, «Точки расширения»).

## Обработка ошибок

| Ситуация | Что происходит |
| --- | --- |
| Документа нет | `app/pages/document/page.php` бросает `PageNotFoundException` → 404 |
| Метод не GET/HEAD | `Router` бросает `MethodNotAllowedException` → 405 + `Allow` |
| Ошибка в шаблоне или коде | 500, в debug — класс, сообщение и стек |
| Битый Frontmatter | документ не загружается, проблема в `content-check`; страница → 404 |
| Нет манифеста Vite | страница без ассетов + предупреждение в лог |

Шаблон ошибки — `app/pages/error.php`. Он получает `$code`, `$error`, `$exception` (только
в debug) и печатает заголовок, пояснение, ссылки на главную/каталог/исследования/поиск и
блок `Debug details` с `<details>`. Коды 403 и 500 описаны в шаблоне, но 403 ядро само не
бросает: это заготовка на случай появления защищённых разделов.

В продакшене `BOLT_DEBUG=false`: тогда `$exception` не передаётся и стек не утекает.
Это первое, что надо проверить при настройке окружения.
