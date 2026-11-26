# Соглашения кода

Правила выведены из существующего кода и из соглашений BOLT. Они не проверяются
автоматически (линтеров в проекте нет), поэтому соблюдение — часть ревью.

## Общие принципы

- **Не переизобретать.** Роутер, шаблонизатор, DI, систему компонентов и сборку даёт
  BOLT; вторая реализация начнёт расходиться с первой. Список — в
  [`../architecture/bolt-analysis.md`](../architecture/bolt-analysis.md), раздел «Что не
  надо писать самому».
- **Комментарий объясняет «почему», а не «что».** В проекте много docblock'ов вида
  «Почему не `bolt://ui-kit/button`» — это полезно: через полгода видно, что решение
  осознанное.
- **Один файл — одна ответственность.** Страница выбирает данные, компонент рисует блок,
  `src/` считает.
- **Никаких новых зависимостей без обсуждения.** Уже есть htmx и Vite. Alpine в проекте
  нет: единственным его потребителем была встроенная модалка BOLT, и рантайм ехал в
  бандл мёртвым грузом (69 КБ из 96). Поведение страниц — ванильный TS в `app/client/`,
  он идемпотентен и переинициализируется после HTMX-подмены.

## PHP

### Файл

```php
<?php

declare(strict_types=1);

/**
 * Что это и зачем.
 *
 * @param string $label
 * @param array  $attrs
 */

$label ??= '';
$attrs ??= [];
?>
```

- `declare(strict_types=1);` — в каждом файле, сразу после `<?php`.
- Отступы — **табы** (так во всём PHP-коде проекта и BOLT).
- Кодировка UTF-8 без BOM, перевод строки `\n`.
- Закрывающий `?>` в файле только если дальше идёт HTML; в классах — не ставится.

### Типы и классы

- PSR-4: `Longevypedia\` → `src/`.
- Классы — `final`, если не предполагается наследование; свойства — типизированные,
  по возможности `readonly` (пример — `Document`).
- Промотированные параметры конструктора вместо сеттеров.
- Возвращаемые типы обязательны, включая `void` и `never`.
- Массивы описываются в docblock: `list<string>`, `array<string, Document>`.
- Исключения — свои: `ContentException` для битого документа. Не бросайте
  `\Exception` с текстом «error».

### Функции и методы

- Функции — `snake_case`: `longevypedia_content()`, `longevypedia_date()`, `longevypedia_topic_label()`.
- Методы — `camelCase`: `backlinksOf()`, `isPublished()`.
- Глобальные функции — только с префиксом `longevypedia_` и только в
  `src/Content/functions.php` / `src/Design/functions.php` (они подключены через
  `composer.json` → `autoload.files`). Короткие глобальные имена вроде `content()` или
  `date_fmt()` запрещены: они конфликтуют с BOLT и будущими зависимостями.
- Функция без побочных эффектов, если её имя не глагол: `tokens()` возвращает, `load()`
  загружает.

### Шаблонные параметры

Параметр шаблона получает дефолт через `??=`, а не через `isset()` или `array_key_exists()`:

```php
$label ??= '';
$variant ??= 'neutral';
$items ??= [];
```

Это короче, безопаснее при `null` и одинаково работает для страниц и компонентов.
Обязательный параметр без дефолта — ошибка: шаблон должен рендериться и без него, иначе
падение будет в проде, а не в тесте.

### Экранирование и хелперы

- Текст — `<?= $this->e($value) ?>`; никогда не печатайте пользовательские данные сырыми.
- HTML-строки, собранные в PHP (например, `html` документа), выводятся как есть — это
  доверенный Markdown, прошедший `MarkdownRenderer`.
- Атрибуты — `attrs($attrs, ['class' => clsx(...)])`; классы — `clsx()`.
  С версии BOLT `61231b5` (`src/Support/helpers.php:157`) `attrs()` возвращает строку
  с ведущим пробелом, поэтому пробел перед `<?=` не ставится:
  `<body<?= attrs([...]) ?>>`. Старая запись `<div <?= attrs(...) ?>>` работает, но даёт
  двойной пробел; пустой массив возвращает `''`.
- Ссылки — `$document->url`, `$app->url('route.name', [...])` или `app_base_url()`;
  не склеивайте пути руками.

### Ошибки и данные

- «Документа нет» — `PageNotFoundException`, а не `return ''`.
- Ошибка конфигурации — исключение при старте, а не молчаливый дефолт.
- Не глушите ошибки: `@` запрещён, `try/catch` — только там, где есть осмысленная
  реакция.

## Шаблоны (Plates)

### Страница

```php
<?php

declare(strict_types=1);

/**
 * Индекс исследований.
 *
 * @var array $route
 */

$repository = longevypedia_content();
$locale = (string) ($route['locale'] ?? $repository->defaultLocale());

$this->layout('components::layout/site-layout', [
	'title' => 'Research',
	'description' => '...',
	'canonical' => '/research',
	'current' => 'research',
]);
?>
<?php $this->start('page'); ?>
<div class="container">
	<h1>Research</h1>
</div>
<?php $this->end(); ?>
```

- Тело — только внутри `start('page')` / `end()`: `content` зарезервирован в Plates.
- Данные — через `longevypedia_content()`; страница не создаёт репозиторий сама.
- Локаль — из `$route['locale']` с fallback на `defaultLocale()`.
- `<style lang="scss">` — в конце файла, если стили нужны только этой странице.

### Компонент

```php
<?php

declare(strict_types=1);

/**
 * Бейдж — короткий статус или категория.
 *
 * Проектный компонент: `components::ui/badge`. В ui-kit Bolt бейджа нет.
 *
 * @param string      $label
 * @param string      $variant neutral | brand | success | warning | danger | info | outline
 * @param bool        $dot
 * @param string|null $icon    имя иконки слева (без -icon)
 * @param string      $size    sm | md
 * @param array       $attrs
 */

$label ??= '';
$variant ??= 'neutral';
$dot ??= false;
$icon ??= null;
$size ??= 'md';
$attrs ??= [];
?>
<span<?= attrs($attrs, ['class' => clsx('ui-badge', 'ui-badge--' . $variant)]) ?>>
	<?= $this->e((string) $label) ?>
</span>

<style lang="scss">
	.ui-badge {
		display: inline-flex;
		gap: var(--space-2);
	}
</style>
```

Правила:

- docblock перечисляет **все** параметры с типами и допустимыми значениями;
- дефолты — `??=`;
- один `<style lang="scss">` в конце файла, без отдельного CSS-файла;
- в стилях — только токены (`var(--color-…)`, `var(--space-…)`), не hex и не «магические»
  пиксели;
- имя компонента — `ui-<name>`, классы — `ui-<name>__element--modifier`;
- если компонент сознательно не используется из `bolt://ui-kit/*`, в docblock пишется
  почему (как в `button.php`, `field.php`, `choice.php`, `card.php`).

## SCSS

- Токены — в `app/client/styles/_tokens.scss`. Роли, а не оттенки: `--color-text-muted`,
  а не `--gray-500`.
- Тёмная тема — через `@mixin dark-tokens`; компонент **не** пишет
  `[data-theme='dark'] &` и не хардкодит тёмные цвета. Исключение — переключатель темы в
  шапке, где нужно показать солнце/луну.
- BEM: `block`, `block__element`, `block--modifier`, `block__element--modifier`.
- Media queries — desktop-first: базовый макет широкий, дальше `@media (max-width: 60rem)`.
  Брейкпоинты по комментарию в токенах: `40rem`, `48rem`, `60rem`, `72rem`, `90rem`.
- Вложенность — максимум 2–3 уровня; `&` — для модификаторов и псевдоклассов.
- Фокус — `box-shadow: var(--focus-ring)`, не `outline: none` без замены.
- Анимации — только `transform`/`opacity`, с уважением к `prefers-reduced-motion`
  (глобальный блок уже есть в `_base.scss`).
- Запрещено: градиенты, неон, glassmorphism, тени «как в SaaS-дашборде». Подробнее —
  [`../design/ui-kit.md`](../design/ui-kit.md).

## TypeScript

- Модуль экспортирует одну `init*`-функцию, которая вызывается из `main.ts`:
  `initTheme()`, `initCatalogFilters()`, `initSearchSuggest()`.
- Никаких фреймворков: DOM API, htmx-события, `localStorage` в `try/catch`.
- События htmx 4: `htmx:after:swap`, `htmx:before:response`. Старые имена
  (`htmx:afterSwap`) не сработают молча.
- Привязка — идемпотентная: проверяйте флаг в `dataset`, иначе после HTMX-свопа
  обработчик навесится дважды.
- `tsconfig.json` в проекте нет: Vite транспилирует TS через esbuild **без проверки
  типов**. Типы держатся на дисциплине и редакторе, поэтому избегайте `any` и
  неочевидных приведений.
- Импорты — сначала сгенерированный barrel, затем библиотеки, затем свои модули и стили.

## Имена

| Сущность | Стиль | Пример |
| --- | --- | --- |
| PHP-класс | `PascalCase` | `ContentRepository` |
| PHP-метод | `camelCase` | `backlinksOf()` |
| PHP-функция | `snake_case` + префикс | `longevypedia_date()` |
| переменная шаблона | `camelCase` | `$documents`, `$current` |
| CSS-блок | `kebab-case` | `ui-badge`, `document-card` |
| CSS-элемент | `block__element` | `ui-badge__dot` |
| CSS-модификатор | `block--modifier` | `ui-badge--success` |
| файл компонента | `kebab-case.php` | `empty-state.php` |
| иконка | `<name>-icon.svg` | `search-icon.svg` |
| маршрут | `kebab-case` | `document.concept` |

## Как добавить токен

1. Добавьте переменную в `:root` в `app/client/styles/_tokens.scss`.
2. Добавьте её тёмное значение в `@mixin dark-tokens` — не инверсией, а осознанным
   цветом (в комментарии к файлу это уже сказано).
3. Если токен — текстовый цвет на фоне, добавьте пару в
   `src/Design/Tokens::contrastPairs()`, иначе `/ui-kit/accessibility` её не проверит.
4. Проверьте страницу `/ui-kit/accessibility`: все пары должны проходить AA
   (4.5:1 для обычного текста, 3:1 для крупного).
5. Значения `--color-text-subtle` подобраны именно под AA — не затемняйте их «для
   красоты».

## Как добавить маршрут

**Файловая страница** — просто создайте `app/pages/<slug>/page.php`. Для вложенного пути —
`app/pages/<a>/<b>/page.php`; для перехвата всего поддерева — `[...path]`.

**Маршрут с параметром** — в `config.php`:

```php
'urlpatterns' => [
	// ...
	[
		'path' => 'topics/<slug:slug>/',
		'bundle' => Bolt\Core\Bundle\Bundle::SYSTEM_NAME,
		'dir' => 'topic',
		'name' => 'topic',
	],
],
```

`dir` — каталог в `app/pages/`, `name` — имя для `$app->url()`. Лишние ключи (например,
`type`) попадут в `$route`.

**Маршруты, порождаемые типами**, добавляйте в `DocumentRoutes::definitions()`, а не в
конфиг: иначе 13 типов × локали придётся поддерживать руками.

После правки:

```bash
php vendor/bin/bolt routes   # маршрут появился и не перекрыт
php vendor/bin/bolt check    # нет дублей и мёртвых маршрутов
```

## Запреты

| Нельзя | Почему |
| --- | --- |
| свой роутер, шаблонизатор, DI, компонентная система | уже есть в BOLT; вторая реализация расходится |
| ORM и «база на будущее» | контент читается только с диска; БД в проекте нет |
| фреймворки (Laravel, Symfony full-stack, React, Vue) | несовместимо с моделью BOLT и весом страницы |
| короткие глобальные функции (`content()`, `url()`) | конфликты с BOLT и зависимостями |
| `@` перед вызовами, `error_reporting(0)` | скрывает причины 500 |
| hex-цвета и пиксели в компонентах | ломают тёмную тему и шкалу |
| `!important` для переопределения BOLT | каскадные слои уже дают приоритет |
| отдельный CSS-файл на компонент | стили должны лежать в PHP-файле, иначе рассинхрон |
| правки в `vendor/bsti/bolt` | это симлинк на отдельный репозиторий; правка потеряется |
| `start('content')` | имя секции зарезервировано в Plates |
| инлайновые `<script>` с логикой | логика — в `app/client/*.ts`, иначе не собирается и не тестируется |
| секреты в `config.php` | конфиг в Git; используйте переменные окружения |
