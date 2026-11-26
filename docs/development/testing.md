# Тестирование

В проекте три разных гейта: **PHPUnit** проверяет инварианты кода, страниц и
дизайн-системы, **`scripts/content-check.php`** — корпус, **`scripts/repo-check.php`** —
гигиену репозитория. Они не заменяют друг друга: тесты не читают каждый документ,
content-check не рендерит страницы, а repo-check не знает про поведение сайта.

## Что где лежит

```
phpunit.xml                          конфигурация PHPUnit 11.5
tests/bootstrap.php                  APP_ENV=production + автозагрузка
tests/TestCase.php                   базовый класс: boot, reset, client()
tests/Content/FrontmatterTest.php    10 тестов
tests/Content/ContentRepositoryTest.php  28 тестов
tests/Content/MarkdownRendererTest.php   13 тестов
tests/Content/StudyDesignsTest.php    3 теста
tests/Http/PagesTest.php             36 тестов
tests/Design/DesignSystemTest.php    29 тестов
```

`phpunit.xml`:

- bootstrap — `tests/bootstrap.php`;
- наборы: `content` → `tests/Content`, `http` → `tests/Http`, `design` → `tests/Design`;
- `failOnRisky`, `failOnWarning`, `beStrictAboutOutputDuringTests` включены;
- кеш — `var/phpunit`;
- покрытие не настроено (нет `<coverage>`), поэтому `--coverage` без Xdebug бессмысленен.

`tests/bootstrap.php` выставляет `APP_ENV=production` (в `putenv`, `$_ENV`, `$_SERVER`) до
автозагрузки. Это важно: тесты не должны зависеть от dev-сервера Vite и от режима
отладки.

## Базовый класс

```php
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        Runtime::reset();
        $this->app = Application::boot(dirname(__DIR__));
    }

    protected function tearDown(): void
    {
        Runtime::reset();
    }

    protected function client(): TestClient;
    protected function projectRoot(): string;
}
```

`Runtime::reset()` между тестами обязателен: контейнер и статический `Runtime` иначе
переживут тест и принесут данные предыдущего.

## TestClient

`Bolt\Testing\TestClient` обрабатывает запрос в процессе, без HTTP и без сервера:

```php
$client = $this->client();

$client->get('/compounds/rapamycin/')
    ->assertOk()
    ->assertSee('Rapamycin')
    ->assertHeader('Content-Type');
```

Методы:

```php
get(string $uri, array $query = [], array $headers = []): TestResponse
post(string $uri, array $data = [], array $headers = []): TestResponse
request(string $method, string $uri, array $query = [], array $data = [], array $headers = []): TestResponse
```

`TestResponse`: `body()`, `status()`, `header($name)`, `headers()`, `assertStatus($code)`,
`assertOk()`, `assertSee($text)`, `assertDontSee($text)`, `assertRedirect($expected = null)`,
`assertHeader($name, $expected = null)`, `assertHeaderMissing($name)`.

Ограничения, которые влияют на стиль тестов:

- провал ассерта — `AssertionFailedException` (не ошибка PHPUnit); в выводе он выглядит
  как исключение с текстом проверки;
- POST передаёт только поля: файлов и multipart нет;
- приложение не пересоздаётся между запросами: `share()`-данные переживают запрос;
- заголовки берутся из `Bolt\Core\Http\Headers`, а не из `headers_list()`.

Пример с параметрами и заголовками:

```php
$this->client()
    ->get('/search', ['q' => 'rapamycin'])
    ->assertOk()
    ->assertSee('Rapamycin');

$this->client()
    ->get('/search', ['q' => 'rapamycin'], ['HX-Request' => 'true'])
    ->assertOk();
```

## Что покрыто

### Frontmatter (10)

Разбор блока, сохранение дат строками (`2026-11-01` не становится timestamp), дата со
временем получает `+00:00`, нормализация CRLF, снятие BOM, отказ при отсутствии
Frontmatter, незакрытом блоке, битом YAML, скаляре вместо объекта, разбор вложенных
структур.

### ContentRepository (23)

Загрузка корпуса без проблем и незакрытых ссылок; ≥ 10 документов и ≥ 5 тематик; каждый
документ валиден по схеме; уникальность id; `id` совпадает с типом и slug; `find()`
находит известный документ и возвращает `null` для неизвестного; `byId()` и `resolve()`
согласованы для `protein:mtor`, `proteins/mtor`, `/proteins/mtor`, `mtor`; Wikilinks в
HTML ведут на реальные страницы; связи дают обратные ссылки (rapamycin → protein:mtor);
группы связей подписаны; `recent()` отсортирован по `updated`; `popular()` ранжирует по
связям; поиск по заголовку, по префиксу (`senesc`), с фильтром типа, пустой результат для
неизвестного слова; подсветка экранирует HTML; `stats()` совпадает с количеством;
тематики несут счётчики и описания; `ofType()` возвращает только один тип; переводы пусты
при одной локали; каждый каталог типа известен и имеет файл схемы.

### MarkdownRenderer (13)

Wikilink как внутренняя ссылка; без подписи — заголовок цели; неразрешённая ссылка —
`wikilink--missing`; якорь `#fragment`; оглавление; уникальные id для повторяющихся
заголовков; id на заголовках; GFM-таблица; внешние ссылки с `rel`; небезопасные схемы
(`javascript:`) вырезаются; сноски; инлайновый HTML сохраняется; обычный текст.

### Дизайн-система (29)

`_tokens.scss` разбирается и содержит ожидаемые роли; тёмная тема переопределяет
**каждый** цветовой токен; тёмный фон действительно тёмный (тема не является инверсией);
все пары контраста проходят AA в обеих темах; формула контраста даёт известные значения
(21:1 для чёрного на белом, 1:1 для белого на белом); устаревшие имена токенов
(`--color-fg`, `--color-warn`, `--page-width`, `--text-width`, `--gutter`,
`--color-bg-alt`, `--color-accent-soft`, `--font-text`, `--color-accent-fg`) не
встречаются в шаблонах; **каждая** используемая CSS-переменная либо объявлена в
`_tokens.scss`, либо присвоена локально (иначе объявление молча становится
недействительным и свойство наследуется); акцентный цвет не используется как цвет текста
(3.17:1 против 5.08:1 у `--color-accent-text`); медиазапросы не пишут числа мимо шкалы
`_breakpoints.scss`; иконки проекта находятся без суффикса `-icon`; проектная иконка
перекрывает встроенную; каждая иконка проекта где-то используется; компоненты не
переопределяют глобальные `--color-*`; правило наведения, меняющее заливку на
акцентную или брендовую, обязано задать и цвет текста (иначе общее `a:hover` оставляет на
коричневой заливке бирюзовый текст — 1.35:1); главная не растягивается до высоты окна
(`.page--home .page { min-height: 0 }`), иначе на высоком окне между «How we work» и
подвалом появляется полоса пустого фона; контролы в шапке — компоненты набора, а вне
`app/components/ui/` нет собственных `<button>`, `<select>`, `<textarea>` и `<table>`;
знак в `ui/choice` нарисован отдельным элементом по центру контрола, а не псевдоэлементом
с `clip-path` (иначе галочка и точка смещены); у `ui/skeleton` задана ширина — иначе
проценты строк считаются от нуля и заглушка пустая.

### Страницы (40)

Главная (hero и счётчики, ссылки на каталог и исследования); каталог (список, фильтр по
типу, маршрут `/concepts`); документ (Markdown и метаданные, источники и история,
оглавление, связи); исследование и утверждение (блоки `study`/`claim`); индекс
исследований; поиск (результаты, пустой запрос, неизвестный запрос); about (лицензии, липкая
навигация с подсветкой текущего раздела — и её отсутствие на других страницах);
UI-кит (индекс, страница компонента с примером и исходником, страница доступности с
проходящими парами, индекс внутри `.container`); demo (научные компоненты); 404 для
страницы и документа; HTML с charset; тело 404; SEO-метаданные; HTMX-фрагмент поиска; раскладка на хостинге
(`DeploymentTest`: `.htaccess` разводит чистые URL по `public/`, закрывает каталоги с кодом
и `deploy.env` не коммитится).

## Как запускать

```bash
ddev composer test                 # phpunit из composer-скрипта
ddev exec vendor/bin/phpunit       # то же напрямую
php vendor/bin/phpunit             # на хосте, если есть PHP 8.2+
```

Полезное:

```bash
php vendor/bin/phpunit --testsuite content
php vendor/bin/phpunit --filter Wikilink
php vendor/bin/phpunit --filter 'PagesTest::testSearch'
php vendor/bin/phpunit --no-coverage
```

Ожидаемый результат на текущем корпусе:

```text
OK (123 tests, 669 assertions)
```

Два сообщения `[router] файловая страница не найдена для "/..."` в выводе — это
диагностика BOLT при проверке 404, а не падение теста. Она печатается в stdout и не
ломает прогон, потому что идёт от PHP-диагностики, а не от теста.

## Проверки контента

`scripts/content-check.php` — отдельный гейт. Он всегда валидирует по схемам
(`forceValidation: true`) и печатает:

- путь к контенту и список локалей;
- количество документов по локалям и по типам;
- `Опубликовано: N, тематик: N, исследований: N, утверждений: N`;
- `Проблемы (N):` — ошибки id, схемы, дубликаты, несуществующие связи;
- `Незакрытые Wikilinks (N):` — цели, которых нет в корпусе;
- `Контент в порядке.` и код 0 либо `Проверка не пройдена: N проблем(ы).` и код 1.

```bash
ddev composer content
# или
php scripts/content-check.php
```

Этот скрипт — единственная проверка, которая видит **весь** корпус. PHPUnit проверяет
инварианты на выборке и на нескольких известных документах.

## `bolt check`

Проверяет то, что не относится ни к контенту, ни к тестам: бандлы, маршруты, иконки.

```bash
ddev composer check
# OK: 1 бандл(ов), 26 маршрут(ов)
# иконок: 27 (источников: 3, режим: file)
```

Он ловит дубли маршрутов, маршруты без `dir`/`page`/`view` (всегда 404), ссылки на
несуществующие шаблоны и битые SVG. Эти ошибки не видны PHPUnit, потому что тесты ходят
по уже существующим адресам.

## `repo check`

Третий гейт — гигиена репозитория (`scripts/repo-check.php`, `composer repo`). Он смотрит
не на поведение сайта, а на то, что попадает в коммит:

- файлы из `vendor/`, `node_modules/`, `.bolt/`, `public/build/`, `var/`, `tmp/` и
  локальные `.env`/кеши — их не должно быть в индексе;
- секреты по узким шаблонам: токены GitHub (`ghp_…`, `github_pat_…`), ключи AWS,
  приватные ключи, строки вида `password: "…"` (подстановки `$VAR` не считаются);
- каждый файл `schemas/*.json` — валидный JSON с `$schema`, `$id` и `title`;
- каждый `.yaml`/`.yml` (включая `.ddev/config.yaml`) парсится Symfony YAML;
- относительные ссылки в `docs/*.md` ведут на существующие файлы.

```bash
ddev composer repo
# Файлов проверено: 211
# Схем: 6
# Репозиторий в порядке.
```

Этот скрипт особенно полезен перед PR: он ловит битую ссылку в документации и случайно
закоммиченный `public/build/`.

## Чего тесты НЕ проверяют

- **Визуальную согласованность.** Тесты проверяют токены и контраст, но не то, что
  компонент выглядит как задумано: отступы, выравнивание и «на глаз» — ручная проверка.
- **CLI-скрипты.** `scripts/content-check.php` и `scripts/ui-kit-pages.php` не имеют
  тестов; их поведение проверяется запуском.
- **Сборку Vite.** Манифест, barrel, слои и HMR тестами не покрыты; проверка —
  `bun run build` и открытая страница.
- **Визуальную регрессию.** Скриншот-тестов нет.
- **Доступность автоматически.** Нет axe/Pa11y; страница доступности считает контраст,
  но не проверяет фокус, порядок табуляции и ARIA.
- **Производительность.** Нет бенчмарков загрузки корпуса и поиска.
- **Семантику контента.** Тест не поймёт, что утверждение переоценено, а источник не
  подтверждает текст; это ревью.
- **Внешние ссылки и DOI.** Существование источников не проверяется (и не должно —
  это сеть).
- **Все 13 типов страниц.** Тесты покрывают research и claim; остальные типы идут через
  тот же шаблон, но отдельными тестами не закреплены.
- **Ошибки 405/500.** Проверяется 404; 405 и 500 — вручную.

## Как добавить тест

1. Файл — в `tests/Content`, `tests/Http` или `tests/Design`, пространство имён —
   `Longevypedia\Tests\...` (PSR-4 `Longevypedia\Tests\` → `tests/`, задано в
   `composer.json` → `autoload-dev`), класс наследует `Longevypedia\Tests\TestCase` и
   помечается `final`.
2. Для страниц — `$this->client()->get(...)` и цепочка ассертов.
3. Для корпуса — `$this->app->make('longevypedia.content', static fn ($app) =>
   ContentRepository::fromConfig($app->config(), true))`, чтобы валидация была включена
   независимо от режима.
4. Проверяйте не «текст вообще», а инвариант: ссылка ведёт на существующий документ,
   обратная связь появилась, счётчик совпал.
5. Запустите весь набор, а не только новый тест: `Runtime` и контейнер общие, порядок
   может влиять.

Пример:

```php
public function testDraftDocumentIsHiddenFromCatalog(): void
{
    $html = $this->client()->get('/catalog')->assertOk()->body();

    self::assertStringNotContainsString('draft-only-slug', $html);
}
```

## CI

`.github/workflows/ci.yml` запускается на push и pull request в `main`, на матрице PHP
8.2 и 8.4. Шаги:

1. `composer validate --no-check-publish` и `composer install`;
2. `php -l` для `config.php` и всех PHP-файлов;
3. `bolt config`, `bolt check`, `bolt assets --json`;
4. `php scripts/content-check.php`;
5. `php scripts/repo-check.php`;
6. `php vendor/bin/phpunit`;
7. `bun install --frozen-lockfile`, `bun run build`;
8. проверка `public/build/.vite/manifest.json` и непустого `public/build/assets`.

Локальный эквивалент — те же команды:

```bash
ddev composer content
ddev composer check
ddev composer test
ddev composer repo
ddev exec 'bun run build'
```

Отдельных «CI-only» скриптов нет: если проверка есть в workflow, её можно запустить
руками.
