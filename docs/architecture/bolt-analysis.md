# BOLT: архитектура и границы

Документ описывает BOLT — пакет, на котором работает Longevypedia: что он делает, какие
его механизмы проект использует, где ограничения и как их обходить, не переписывая ядро.
Это не пересказ документации BOLT, а карта интеграции: каждый пункт отвечает на вопрос
«что это значит для нашего кода».

BOLT подключён как Composer-пакет `bsti/bolt` из path-репозитория `../bolt`
(`composer.json`, `repositories` + `symlink: true`). Поэтому исходники ядра доступны как
`vendor/bsti/bolt/src/...`, а в этом документе ссылки даны и на `bolt/src/...` (репозиторий
BOLT), и на `vendor/bsti/bolt/...` (то же самое внутри проекта).

## Коротко

BOLT — минимальное PHP-приложение для сайтов: роутер, шаблонизатор Plates, система
бандлов, сборка клиентских ассетов через Vite и небольшой UI-набор. В нём **нет** базы
данных, ORM, аутентификации, админки и поискового движка. Модель простая: запрос →
маршрут → PHP-шаблон страницы → HTML. Всё, что нужно сверх этого, проект пишет сам —
именно так появились `ContentRepository`, `NativeSearchEngine` и `src/Design`.

Версия PHP — 8.2+ (`bolt/composer.json`), в проекте используется 8.4 из DDEV.

## Ядро

### Application

`bolt/src/Core/Application.php` — контейнер и точка сборки.

- `Application::boot(?string $basePath = null, array $config = []): self` находит
  `config.php`, поднимает бандлы, создаёт `Router`, `View`, `Configuration`.
- `Runtime` хранит текущий экземпляр: `Runtime::app()`, `Runtime::has()`,
  `Runtime::reset()`. Проект использует это в `src/Content/functions.php` и
  `src/Design/functions.php`, а тесты — в `tests/TestCase.php`.
- Контейнер — это `make(string $id, callable $factory)` и `share()`; сервисы проекта
  регистрируются лениво: `longevypedia.content`, `longevypedia.tokens`,
  `longevypedia.icons`.
- `Application::render($result, $request, $data)` отдаёт страницу через `View`;
  `Application::match($request)` ищет маршрут.

Практическое следствие: точку входа писать не нужно, она есть —
`public/index.php`:

```php
require dirname(__DIR__) . '/vendor/autoload.php';

$app = Bolt\Core\Application::boot(dirname(__DIR__));

(new Bolt\Core\Http\Kernel($app))->run();
```

### Kernel: порядок обработки запроса

`bolt/src/Core/Http/Kernel.php` обрабатывает запрос в фиксированном порядке:

1. **PHP-эндпоинт.** Если в `public/` есть файл, совпадающий с путём (`/some` →
   `public/some.php`), выполняется он. В проекте таких файлов нет.
2. **Фрагменты.** `?_fragments=a,b` — HTMX получает только указанные фрагменты; обычный
   POST сначала прогревает кеш фрагментов, затем отвечает `303` (PRG).
3. **Страница.** Сначала явные `urlpatterns`, затем файловая структура `app/pages/`.
4. **Fallback**, если задан (в проекте не задан).
5. **Ошибка** — шаблон `error` из бандла или встроенная страница.

`renderError()` (`Kernel.php:296`) отображает исключения в коды: `PageNotFoundException` →
404, `MethodNotAllowedException` → 405 (с заголовком `Allow`), остальное → 500. Проект
переопределяет шаблон ошибки файлом `app/pages/error.php` — система бандлов находит его
раньше встроенного.

`Kernel::safeRedirectTarget()` отбрасывает `//host`, `/\host` и управляющие символы —
защита от open redirect после POST. Своих редиректов в проекте нет, но правило важно,
если появятся формы.

### Router и маршруты

`bolt/src/Core/Routing/Router.php`:

- маршруты из `urlpatterns` проверяются в порядке объявления;
- затем файловая структура: `/` → `pages/page.php`, `/o-nas` →
  `pages/o-nas/page.php`, `/o-nas/stati/abc` → `pages/o-nas/stati/[...path]/page.php`;
- иначе `PageNotFoundException`.

Конвертеры параметров: `str` (по умолчанию), `slug`, `int`, `path`. У маршрута могут быть
`bundle`, `dir`, `page`, `view`, `include`; лишние ключи попадают в `$route['section']`.
Проект использует это: у маршрута документа есть `type` и `locale`, и страница читает
`$route['type']`.

Все маршруты документов генерируются кодом, а не перечисляются руками в конфиге:

```php
// config.php
'urlpatterns' => DocumentRoutes::definitions($locales, $defaultLocale),
```

`src/Content/DocumentRoutes.php` строит три группы:

- каталог на каждый тип, кроме `research`: `['path' => 'concepts/', 'bundle' =>
  Bundle::SYSTEM_NAME, 'dir' => 'catalog', 'type' => 'concept', 'name' =>
  'catalog.concept']`;
- страницу документа на каждую локаль и тип: `'path' => 'concepts/<slug:slug>/'`,
  `'dir' => 'document'`, `'type' => 'concept'`, `'locale' => 'en'`;
- для неосновных локалей — главную, каталог и поиск с префиксом локали.

`research` исключён из каталогов, потому что у него своя страница
`app/pages/research/page.php` (список исследований, отсортированный по году). Итог виден
командой `php vendor/bin/bolt routes`: 26 маршрутов, из них 12 каталогов, 13 страниц
документов и спрайт иконок.

`DocumentRoutes::url()` собирает адрес документа: `prefix() . dir . '/' . slug . '/'`, где
`prefix()` — `/` для основной локали и `/ru/` для остальных. Этот же метод использует
`ContentRepository`, когда строит `Document::$url`; в шаблонах адреса не склеиваются
руками.

### Бандлы и приоритет

Бандл — каталог с необязательными секциями: `pages/`, `components/`, `fragments/`,
`views/`, `emails/`, `data/`, `client/`, `icons/`, `layouts/`. В конфиге бандлы задаются
только путём (`root://...`, `composer://...`, относительный, абсолютный), глобы не
поддерживаются. Порядок в конфиге задаёт приоритет: **чем ниже запись, тем выше
приоритет**, как в CSS; каталог проекта `app/` всегда поднимается наверх и получает имя
`_app` (`Bundle::SYSTEM_NAME`).

В Longevypedia ключа `bundles` в `config.php` нет вообще: `app/` автоматически
становится системным бандлом, отдельного `app/bundle.php` тоже нет. Это не экономия
строки — так меньше способов ошибиться: нельзя случайно объявить `app` дважды или
поставить его ниже встроенного набора.

Имя `_app` объясняет странность в конфиге иконок:

```php
'icons' => [
    'sources' => ['_app://icons', 'bolt://icons'],
],
```

`app://icons` означал бы бандл с именем `app`, которого не существует. `_app://` — это
системный бандл проекта, `bolt://` — встроенные иконки пакета. Итог: 27 иконок, из них 8
проектных (`book`, `external`, `flask`, `github`, `graph`, `moon`, `search`, `sun`).

### Конфигурация

`config.php` — обычный PHP-массив. Используются:

- `debug` (`BOLT_DEBUG`, по умолчанию `true`);
- `app.name`, `app.repository` (`APP_REPOSITORY`), `app.branch` (`APP_BRANCH`);
- `content.path`, `content.schemas`, `content.topics`, `content.locales`,
  `content.default_locale`;
- `urlpatterns`;
- `icons.sources`;
- `vite.entries`, `vite.manifest`, `vite.port`;
- `seo.site_name`, `seo.title_separator`, `seo.locale`, `seo.description`.

Чего в конфиге **нет** и почему:

- нет `bundles` — `app/` подхватывается сам;
- нет `vite.port` — порт берётся из `vite_port()`: конфиг → `VITE_PORT` → 5176; дублировать
  его в `vite.config.js` не нужно, оттуда он читается через `boltVitePort()`;
- нет `vite.dev` — режим выводится из `APP_ENV`/`VITE_DEV`/DDEV (см. ниже).

## Шаблоны

### Plates и схемы

Шаблонизатор — league/plates. Каждый бандл регистрируется как namespace, плюс есть
сквозные схемы:

| Схема | Что ищет | Пример в проекте |
| --- | --- | --- |
| `components::` | `components/` во всех бандлах, первый найденный | `components::ui/button` |
| `app://` | бандл с именем `app` (в проекте его нет) | — |
| `_app://` | системный бандл проекта | `_app://icons` |
| `bolt://` | корень пакета BOLT | `bolt://ui-kit/icon`, `bolt://ui-kit/seo` |
| `<bundle>://` | конкретный бандл | `bolt://ui-kit/pagination` |

Без секции (`app::o-nas/stati/page`) поиск идёт по корню бандла, затем `components`,
`fragments`, `layouts`, `pages`, `views`, `emails`.

API, которым пользуются страницы и компоненты:

```php
$this->layout('components::layout/site-layout', ['title' => '...', 'current' => 'catalog']);
$this->start('page');
// ... разметка страницы ...
$this->end();
```

```php
<?= $this->insert('components::ui/badge', ['label' => 'Published', 'tone' => 'success']) ?>
```

`insert()` возвращает строку, `layout()` печатает тело в указанную секцию. Параметры
компонента — обычные переменные, которые шаблон получает через `extract`.

### Секции и зарезервированное имя `content`

В Plates имя секции `content` занято: `Template.php` использует его для основного
содержимого и бросает `LogicException: The section name "content" is reserved`. Поэтому в
проекте тело страницы всегда кладётся в секцию `page`:

```php
<?php $this->start('page'); ?>
...
<?php $this->end(); ?>
```

а layout печатает `<?= $this->section($section) ?>` с `$section = 'page'` по умолчанию
(`app/components/layout/site-layout.php:74`). Если написать `start('content')`, страница
упадёт с `LogicException` — это самая частая ошибка при добавлении новой страницы.

### AssetExtractingEngine

`bolt/src/View/AssetExtractingEngine.php` наследует Plates `Engine` и после рендера
вырезает из HTML блоки `<style lang="scss">` и `<script lang="ts">`. `<style inline>`
остаётся в разметке как есть.

Извлечение выполняется только на внешнем рендере (`private int $depth`): layout — это
вложенный рендер, и без счётчика глубины он видел бы уже очищенный HTML и терял бы
стили.

Следствие для проекта: стили компонента живут в его PHP-файле, а в браузер попадают
через сгенерированную точку входа Vite. Отдельный CSS-файл на компонент не нужен и не
должен появляться — он бы дублировал источник и рассинхронизировался.

## Клиентская сборка

### componentAssets

`bolt/vite/plugins/component-assets/index.ts` — плагин Vite, который проект подключает в
`vite.config.js`:

```js
componentAssets({
    outputDir: '.bolt/client',
    scan: boltScan({ projectRoot }),
})
```

Что он делает:

1. Обходит секции `components`, `pages`, `fragments` всех бандлов (включая
   `vendor/bsti/bolt/ui-kit`).
2. Находит `<style lang="scss">` и `<script lang="ts">`, складывает их в
   `.bolt/client/generated/<bundle>/...` и генерирует barrel
   `.bolt/client/generated/main/index.ts`.
3. Спрашивает у PHP приоритеты бандлов и оборачивает каждый файл в
   `@layer bolt.<bundle>`, объявляя порядок слоёв.
4. Ведёт `.bolt/client/generated/.manifest.json`, чтобы не перегенерировать лишнее.

Barrel — единственный способ получить стили компонентов в сборке. Он генерируется и в Git
не коммитится; правки в нём теряются. `app/client/main.ts` импортирует его первой строкой:

```ts
import './styles/main.scss';
import '../../.bolt/client/generated/main/index';
import 'htmx.org';
```

Порядок важен: слои объявляются до того, как BOLT добавит свои, потом поведение.
Подробности — в разделе «Слои CSS».

### vite_assets

`bolt/src/Support/vite.php` — PHP-сторона сборки.

- `vite_is_production()`: явный `vite.dev`/`VITE_DEV` → `APP_ENV=production|prod` →
  `APP_ENV=development|dev|local` → наличие `DDEV_PRIMARY_URL_WITHOUT_PORT`. Если ничего не
  задано, считается production и пишется предупреждение в диагностику.
- `vite_dev_server_url()`: в production `null`; иначе `VITE_DEV_SERVER_URL` или
  `DDEV_PRIMARY_URL_WITHOUT_PORT:5176`.
- `vite_manifest_path()`: константа `VITE_MANIFEST_PATH` → `vite.manifest` → эвристика по
  `SCRIPT_FILENAME`/`DOCUMENT_ROOT`.
- `vite_assets(['app/client/main.ts'], '/build')`: в dev печатает `@vite/client` и входы,
  в production — `<link rel="stylesheet">` и `<script type="module">` из манифеста.

Ошибки не глушатся: битый манифест пишет `Diagnostics::error`, отсутствующая точка входа —
`Diagnostics::warn` («её CSS/JS на страницу не попали»). Поэтому «страница без стилей» —
это всегда конкретное сообщение в логе, а не молчание.

### Слои CSS

Стили BOLT попадают в `@layer bolt.<bundle>` (`bolt.ui-kit`, затем бандлы проекта), сам
плагин объявляет порядок. Стили проекта в это не вмешиваются: `_base.scss` и `_prose.scss`
лежат в своём слое `bolt.base`, объявленном первым.

Порядок слоёв задаёт **первое объявление в документе**, а собранный Vite CSS всегда
приходит последним. Поэтому список слоёв стоит инлайновым `<style inline>` в
`app/components/layout/site-layout.php` до тегов Vite:

```html
<style>@layer bolt.base, bolt.ui-kit, bolt._app;</style>
```

Атрибут `inline` обязателен: `AssetExtractingEngine` вырезает из ответа любой `<style>`
без него и уносит в barrel.

Почему база в слое, а не вне слоёв: неслоёное правило перебивает компонент по одному
факту «объявлено без слоя», и специфичность не спасает — `_base.scss` отменял бы
оформление кнопок и карточек. Слой `bolt.base` даёт базовому правилу низший приоритет:
компонент отменяет подчёркивание ссылки или поле у заголовка без `!important`, а страница
не перебивает компонент случайным селектором.

## UI-набор BOLT

Встроенные компоненты: `button`, `card`, `field`, `field-checkbox`, `form`,
`form-message`, `icon`, `logo`, `wordmark`, `modal`, `pagination`, `seo`. Все доступны как
`bolt://ui-kit/<name>`.

Что используется в проекте как есть: только `icon` (везде, включая `spinner` из
`bolt://icons`) и `seo` (в `site-layout.php`). `pagination`, `form`, `form-message`,
`modal`, `card`, `field` не используются: у проекта свои компоненты с нужными
состояниями.

Почему не всё: у встроенной кнопки закрытый список вариантов
(`primary|outline|blue|link`), два размера и нет состояния загрузки. Передать `danger`
или `ghost` снаружи нельзя — список классов фиксирован в шаблоне. Поэтому проект держит
свою кнопку (`app/components/ui/button.php`) с вариантами
`primary|secondary|outline|ghost|danger|link`, размерами `sm|md|lg`, `loading`,
`iconOnly`, `block`. Тот же аргумент действует для `badge`, `tag`, `alert`, `callout`,
`card`, `field`, `choice`: нужны состояния и семантика, которых встроенный набор не
предлагает.

Переопределить встроенный компонент можно, положив свой по тому же относительному пути:
`app/components/ui-kit/button.php` заменит `bolt://ui-kit/button`. Проект так **не**
делает: свои компоненты называются `ui/*`, чтобы в разметке было видно, где BOLT, а где
проектный код.

Иконки: файл `app/icons/<name>-icon.svg`, суффикс `-icon` обязателен (компонент
дописывает его сам). Спрайт собирается в `var/sprite.svg` и отдаётся маршрутом
`bolt-icons-sprite` (`/assets/sprite.svg`) с ETag по хешу содержимого и
`Cache-Control: immutable`. Проверка — `php vendor/bin/bolt icons --check`.

## Формы, фрагменты, HTMX

- **Формы.** BOLT даёт `process_form($key, $rules, $onSuccess)`: валидация, CSRF, flash и
  `303` после POST. В Longevypedia POST-форм нет; поиск и фильтры — GET.
- **Фрагменты.** Сервер отдаёт часть страницы по `?_fragments=search-results`; ядро
  собирает фрагменты и для HTMX отвечает только ими. Имя фрагмента — файл в
  `app/fragments/`. В проекте один: `app/fragments/search-results.php`.
- **HTMX 4.** Версия важна: имена событий в v4 — `htmx:before:response`,
  `htmx:after:swap` (в htmx 2 было `htmx:beforeRequest`, `htmx:afterSwap`). Проект слушает
  `htmx:after:swap` в `app/client/catalog-filters.ts`. Атрибуты `hx-get`, `hx-target`,
  `hx-select`, `hx-swap`, `hx-push-url`, `hx-trigger` работают как раньше;
  `hx-disabled-elt` заменён на `hx-disable`, `hx-disinherit` удалён.
- **Форма поиска** (`app/components/site/search-form.php`) — GET на `/search` плюс
  `hx-get="/search?_fragments=search-results"` с задержкой 250 мс; подсказки приходят
  фрагментом в `#<id>-suggest`.
- **Каталог** (`app/pages/catalog/page.php`) обновляет `#catalog-results` через
  `hx-select` + `hx-push-url`, поэтому фильтры остаются в адресной строке и работают без
  JS.
- **Alpine в бандл не попадает.** BOLT использует Alpine в компоненте `modal`
  (`bolt/ui-kit/modal.php`): он импортирует `alpinejs` и регистрирует
  `Alpine.data('modal', ...)`, а `Alpine.start()` должен вызвать проект. Проект
  модалку не использует и Alpine не стартовал — при этом barrel тянул рантайм
  целиком: 69 КБ из 96 КБ JS. Поэтому `vite.config.js` передаёт
  `boltScan({ uiKit: false })`: встроенный набор не сканируется, его скрипт в
  сборку не идёт. Компоненты набора остаются доступны как PHP (`bolt://ui-kit/icon`
  — разметка без ассетов). Если понадобится модалка или свои `x-data`, верните
  набор в `scan`, добавьте `alpinejs` в `package.json` и вызовите `Alpine.start()`
  один раз в `app/client/main.ts`.

## CLI

`vendor/bin/bolt` (в проекте — `php vendor/bin/bolt`) — единственный CLI ядра.

| Команда | Что делает | Используется в проекте |
| --- | --- | --- |
| `bolt check` | бандлы, маршруты, иконки; exit 1 при ошибке | да, `composer check` |
| `bolt routes` | таблица маршрутов с источником каждого | да, при отладке |
| `bolt render <path>` | рендер пути в stdout; `--strict` → exit 1 на 4xx/5xx | да, для проверки страниц |
| `bolt assets` | точки входа и компоненты по бандлам; `--json`, `--entries`, `--bundles` | да, `composer assets` |
| `bolt icons` | список иконок и источников; `--check` | да |
| `bolt serve` | встроенный сервер (`--host`, `--port`, docroot `public`) | да, быстрая проверка без DDEV |
| `bolt vite` | порт/URL dev-сервера; `--port`, `--json` | нет, но полезно |
| `bolt config` | итоговая конфигурация | нет |

Типичный вывод:

```text
$ php vendor/bin/bolt check
OK: 1 бандл(ов), 26 маршрут(ов)
иконок: 27 (источников: 3, режим: file)
```

`bolt check` не валидирует контент — для этого есть отдельный
`scripts/content-check.php` (см. `docs/development/testing.md`).

## TestClient

`bolt/src/Testing/TestClient.php` — HTTP-клиент без сети и без сервера:

```php
$client = new TestClient($app);
$client->get('/compounds/rapamycin/')->assertOk()->assertSee('Rapamycin');
$client->post('/feedback', ['name' => 'Ada'])->assertRedirect('/thanks');
```

Методы: `get($uri, $query, $headers)`, `post($uri, $data, $headers)`,
`request($method, $uri, $query, $data, $headers)`. Ответ — `TestResponse` с `body()`,
`status()`, `header()`, `headers()`, `assertStatus()`, `assertOk()`, `assertSee()`,
`assertDontSee()`, `assertRedirect()`, `assertHeader()`, `assertHeaderMissing()`.

Ограничения, о которых надо помнить:

- провал ассерта — `AssertionFailedException`, а не ошибка PHPUnit, поэтому в отчёте он
  выглядит иначе;
- POST передаёт только поля: файлов и multipart нет;
- приложение не пересоздаётся между запросами — данные `share()` переживают запрос;
- заголовки читаются из `Bolt\Core\Http\Headers`, а не из `headers_list()`.

Проект использует `TestClient` в `tests/Http/PagesTest.php`; базовый класс —
`tests/TestCase.php`.

## Что использует Longevypedia

| Возможность BOLT | Где в проекте | Зачем |
| --- | --- | --- |
| `Application`, `Kernel` | `public/index.php` | точка входа без своего фронт-контроллера |
| бандлы и `_app` | `app/` | страницы, компоненты, фрагменты, иконки в одном месте |
| `urlpatterns` | `config.php` + `DocumentRoutes` | 13 типов × локали без ручного списка |
| файловые страницы | `app/pages/**` | главная, каталог, поиск, about, demo, ui-kit |
| Plates + `components::` | все страницы и компоненты | переиспользование разметки |
| `bolt://ui-kit/seo` | `site-layout.php` | title/description/canonical/OG/JSON-LD |
| `bolt://ui-kit/icon` | шапка, футер, компоненты | спрайт вместо инлайновых SVG |
| извлечение `<style lang>` | все компоненты | стили рядом с разметкой |
| `componentAssets` + Vite | `vite.config.js` | barrel, слои, манифест |
| `vite_assets()` | `site-layout.php:64` | dev-сервер или манифест |
| `?_fragments=` | поиск, каталог | HTMX без отдельного API |
| `bolt check/routes/icons/serve` | разработка | проверки и быстрый просмотр |
| `TestClient` | `tests/Http` | HTTP-тесты без сервера |

## Ограничения и что с ними делать

**1. Нет базы данных.** Контент — файлы; пользователей, комментариев и счётчиков нет.
Обходной путь для «популярного» — ранг по числу связей в графе
(`ContentRepository::popular()`); для пользователей понадобится отдельный слой, которого
сейчас нет. Не добавляйте ORM «на будущее»: корпус читается только на чтение.

**2. Нет поиска из коробки.** Проект пишет свой `NativeSearchEngine` (обратный индекс в
памяти, веса полей, IDF, префиксы). Это работает на текущем корпусе, но не переживёт
десятков тысяч документов: индекс строится на каждый запрос. Точка замены — интерфейс
`Longevypedia\Search\SearchEngine`.

**3. UI-набор закрыт по вариантам.** Встроенные компоненты не принимают произвольные
классы и состояния. Вместо форка BOLT проект держит свои компоненты в `ui/`. Если
компонента не хватает — пишите свой, а не патчите `vendor/`.

**4. Alpine исключён из сборки.** BOLT тянет Alpine ради модалки, но `Alpine.start()`
должен вызвать проект — он его не вызывал. Встроенный набор не сканируется
(`uiKit: false`), поэтому рантайм в бандл не попадает; модалка не используется.

**5. ENOSPC на вотчерах.** Vite следит за `vendor/bsti/bolt` и сотнями PHP-шаблонов;
inotify-лимиты хоста из контейнера не поднять. Решение — polling:
`CHOKIDAR_USEPOLLING=1 CHOKIDAR_INTERVAL=400` в команде демона
(`.ddev/config.yaml`). Без него `ddev start` падает на старте Vite.

**6. Секция `content` зарезервирована.** Тело страницы — только в `start('page')`;
layout печатает `section('page')`. Иначе `LogicException`.

**7. `metaValue()` без точечной нотации.** `Bundle::metaValue('a.b')` не разворачивает
вложенность. У документов так же: `Document::metaValue('study')` возвращает весь блок
массивом, а не `study.design`. Вложенные поля читаются из массива в шаблоне:
`$study['design'] ?? ''`.

**8. `app/layouts/` не сканируется сборщиком ассетов.** Секция `layouts` не входит в
обход `componentAssets`, поэтому `<style lang="scss">` в layout не попал бы в сборку, а у
шаблонов из `layouts/` нет namespace для `components::`. Поэтому layout лежит в
`app/components/layout/site-layout.php` и вызывается как `components::layout/site-layout`.

**9. Бандлы задаются только путём.** Глобы (`root://bundles/*`) не поддерживаются,
дубликат пути даёт предупреждение. В проекте бандл один, так что это ограничение пока не
мешает.

**10. Нет персистентного кеша.** Репозиторий живёт в пределах запроса; между запросами
корпус парсится заново. Для текущего объёма это быстрее любого кеша, для роста — точка
расширения (см. `content-architecture.md`).

**11. Отсутствие манифеста = страница без ассетов.** В production `vite_assets()` читает
`public/build/.vite/manifest.json`; если сборки нет, ссылки не печатаются, а в лог идёт
предупреждение. Поэтому деплой без `bun run build` выглядит как «сломанная вёрстка».

## Как расширять

**Новая страница.** Файл `app/pages/<slug>/page.php`; адрес появляется сам. Для
вложенного пути — `app/pages/<a>/<b>/page.php`, для перехвата — `[...path]`. Внутри:
`$this->layout('components::layout/site-layout', [...])`, затем `start('page')`.

**Новый маршрут с параметром.** Добавьте массив в `urlpatterns` (или в
`DocumentRoutes`, если он порождается типами):

```php
['path' => 'topics/<slug:slug>/', 'bundle' => Bundle::SYSTEM_NAME,
 'dir' => 'topic', 'name' => 'topic'],
```

Страница получит `$route['slug']`.

**Новый компонент.** `app/components/ui/<name>.php`: docblock с `@param`, дефолты через
`??=`, разметка, один `<style lang="scss">` в конце. Стили подхватит Vite при следующем
запуске/сборке.

**Новая иконка.** `app/icons/<name>-icon.svg` с `viewBox`; проверить
`php vendor/bin/bolt icons --check`. Спрайт обновится по хешу.

**Новый сервис.** `Runtime::app()->make('longevypedia.x', fn ($app) => ...)` в
`src/.../functions.php`; так сделаны репозиторий, токены и иконки.

**Замена поиска.** Реализуйте `Longevypedia\Search\SearchEngine` (`index()`, `search()`)
и передайте его в конструктор `ContentRepository` (или зарегистрируйте свой репозиторий
в контейнере).

**Правка BOLT.** BOLT — отдельный репозиторий, подключённый симлинком. Менять
`vendor/bsti/bolt` бессмысленно: правка исчезнет при следующем `composer install` и не
попадёт в ревью. Меняйте `../bolt` и коммитьте туда.

## Правила разработки

- Не писать свой роутер, шаблонизатор, DI-контейнер и компонентную систему: всё это уже
  есть, а вторая реализация начнёт расходиться с первой на первом же багфиксе.
- Не обходить `vite_assets()` ручными `<script src>`: сломается HMR и production-сборка.
- Не добавлять `<style>` без `lang="scss"` для стилей проекта: такой блок останется
  инлайном и не попадёт в общий бандл (инлайн допустим только для критического CSS,
  которого в проекте нет).
- Не дублировать маршруты в `urlpatterns` и в файловой структуре: сработает первый,
  второй станет недостижимым, а `bolt check` предупредит.
- Не помещать бизнес-логику в шаблоны: страница выбирает данные, компонент рисует.
  Расчёты — в `src/`, доступ — через `longevypedia_content()`.
- Соглашения кода — `docs/development/coding-guidelines.md`.

## Что НЕ надо писать самому

| Соблазн | Почему не надо |
| --- | --- |
| роутер, разбор URL, конвертеры | `Router` + `urlpatterns`, конвертеры `slug/int/path` |
| шаблонизатор, экранирование, layout-наследование | Plates: `layout/start/section/insert` |
| DI-контейнер, сервис-локатор | `Application::make/share`, `Runtime::app()` |
| систему компонентов и «слоты» | `components::` + параметры шаблона + секции |
| сборщик CSS/JS, вотчер, манифест | Vite + `componentAssets` + `vite_assets()` |
| спрайт иконок и кеш спрайта | `bolt icons` + маршрут `bolt-icons-sprite` |
| SEO-теги и JSON-LD | `bolt://ui-kit/seo` (проект только передаёт данные) |
| тестовый HTTP-клиент | `Bolt\Testing\TestClient` |
| CSRF/валидацию форм | `process_form()` — когда формы появятся |

## Ссылки на файлы BOLT

- `bolt/src/Core/Application.php` — boot, контейнер, рендер.
- `bolt/src/Core/Http/Kernel.php` — порядок обработки, коды ошибок, `Allow`.
- `bolt/src/Core/Routing/Router.php` — маршруты и файловый fallback.
- `bolt/src/Core/Bundle/Bundle.php` — секции, `SYSTEM_NAME`, `metaValue()`.
- `bolt/src/Core/Bundle/BundleRegistry.php` — приоритеты и `promote()` для `app/`.
- `bolt/src/View/ViewFactory.php` — схемы Plates и `components::`.
- `bolt/src/View/AssetExtractingEngine.php` — извлечение `<style lang>`/`<script lang>`.
- `bolt/src/View/BoltScheme.php` — `bolt://`.
- `bolt/src/Support/vite.php` — режим, порт, манифест, `vite_assets()`.
- `bolt/src/Support/helpers.php` — `e()`, `vite_assets()`, `app_base_url()`, `process_form()`.
- `bolt/vite/plugins/component-assets/index.ts` — barrel и слои.
- `bolt/vite/plugins/component-assets/utils.ts` — `extractAssets`, `createBarrel`, `layerOrder`.
- `bolt/ui-kit/*.php` — встроенные компоненты и их закрытые варианты.
- `bolt/src/Icons/Sprite.php` — маршрут спрайта и ETag.
- `bolt/src/Testing/TestClient.php`, `TestResponse.php` — HTTP-тесты.
- `bolt/bin/bolt` — CLI и справка по командам.
- `bolt/PITFALLS.md` — грабли, на которые уже наступали (приоритеты, маршруты, иконки).
