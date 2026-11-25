# Архитектура контента

Документ описывает, как корпус превращается в страницы: модель документа, три прохода
загрузки, идентификаторы, локали, Wikilinks, типизированные связи, статусы, валидацию и
поисковый индекс. Формат файла — в
[`../specifications/longevypedia-markdown.md`](../specifications/longevypedia-markdown.md).

## Модель документа

`src/Content/Document.php` — неизменяемый объект. Создаётся один раз при загрузке
корпуса и дальше только читается.

| Свойство | Тип | Что содержит |
| --- | --- | --- |
| `type` | `string` | тип из `DocumentTypes` (`concept`, `research`, …) |
| `slug` | `string` | имя файла без `.md` |
| `locale` | `string` | `en` |
| `file` | `string` | абсолютный путь к `.md` |
| `relativePath` | `string` | путь от корня проекта — для ссылки «source» |
| `url` | `string` | канонический адрес (`/compounds/rapamycin/`) |
| `meta` | `array` | весь Frontmatter как есть |
| `body` | `string` | Markdown без Frontmatter |
| `html` | `string` | отрендеренный HTML |
| `toc` | `array` | `{level, id, text}` по заголовкам `h2–h4` |
| `links` | `array` | id документов, на которые есть Wikilinks |
| `unresolved` | `array` | цели Wikilinks, которых нет в корпусе |

Методы-аксессоры: `id()` (`type:slug`), `key()` (`id@locale`), `title()`, `summary()`,
`status()`, `isPublished()`, `created()`, `updated()`, `license()`, `tags()`, `topics()`,
`aliases()`, `synonyms()`, `relations()`, `sources()`, `metaValue()`, `metaList()`,
`wordCount()`, `readingMinutes()` (220 слов в минуту, минимум 1), `typeLabel()`,
`repositoryPath()`.

`metaValue('study')` возвращает **весь** блок массивом: точечной нотации нет ни в
`Document`, ни в `Bundle::metaValue()`. В шаблоне читается так:

```php
$study = $document->metaValue('study', []);
$design = $study['design'] ?? '';
```

## Три прохода загрузки

Загрузка ленивая: `ContentRepository::load()` вызывается при первом обращении к данным и
защищена флагом `$loaded`. Внутри — четыре шага, которые для читателя выглядят как три
прохода.

```
parseFiles()        найти файлы, разобрать Frontmatter, собрать «сырые» записи
      ↓
buildIndexes()      проверить id, обязательные поля, схему, дубликаты; построить bySlug/byId/byAlias
      ↓
buildDocuments()    отрендерить Markdown → html/toc/links/unresolved; создать Document
      ↓
buildBacklinks()    проверить связи, разрешить цели, записать обратные рёбра
```

### 1. Parse

`parseFiles()` обходит локали × типы из `DocumentTypes::TYPES`, находит `*.md`
рекурсивно (`.md` — единственное расширение), пропускает пути с `/.` (скрытые каталоги).
Имя файла обязано совпадать с `/^[a-z0-9]+(?:-[a-z0-9]+)*$/`; иначе запись не
загружается, а в `problems()` появляется сообщение про slug в нижнем регистре.

`Frontmatter::split()` нормализует переводы строк, снимает BOM, требует, чтобы файл
начинался с `---`, и разбирает YAML с `Yaml::PARSE_DATETIME`. Даты нормализуются обратно
в строки: `2026-11-20` остаётся `2026-11-20`, а `2026-11-20T14:30:00` превращается в
`2026-11-20T14:30:00+00:00`. Это важно: без нормализации YAML превратил бы дату в Unix
timestamp, и шаблоны получили бы число вместо даты.

Ошибки на этом шаге — битый YAML, отсутствующий или незакрытый Frontmatter, скаляр
вместо массива — дают `ContentException` и попадают в `problems()`, а не роняют весь
сайт.

### 2. Index

`buildIndexes()` проверяет то, что нельзя проверить схемой:

- `id` присутствует и **точно** равен `<type>:<slug>`; иначе документ не грузится
  (сообщения «во Frontmatter нет обязательного поля id» и «id "%s" не совпадает с типом
  и именем файла (ожидается "%s")»);
- `title`, `summary`, `status`, `updated` непустые;
- Frontmatter валиден по JSON Schema своего типа (`SchemaValidator`);
- slug уникален внутри локали: второй документ с тем же slug отвергается, потому что
  Wikilink `[[slug]]` стал бы неоднозначным;
- alias уникален внутри локали.

Здесь же строятся индексы: `bySlug[locale|type|slug]`, `byId[locale|id]`,
`byAlias[locale|alias]` и таблица целей `targets[key] = {url, title, id}` для
разрешения Wikilinks.

Валидация по схемам включается, когда `content.validate` включён или когда `debug: true`;
`scripts/content-check.php` всегда передаёт `forceValidation: true`, поэтому гейт не
зависит от режима отладки.

### 3. Render

`buildDocuments()` создаёт `MarkdownRenderer` с замыканием-резолвером
(`resolve($target, $locale): ?array{url,title,id}`) и рендерит тело каждого принятого
документа. Результат — `html`, `toc`, `links`, `unresolved`. Конвертер кешируется по
локали: резолвер привязан к локали, и один конвертер на все языки дал бы ссылки не на
тот язык.

Пайплайн Markdown: CommonMark + GFM + HeadingPermalink + Footnote + ExternalLink +
Wikilink. Заголовки `h2–h4` получают id и якорь `#`; повторяющиеся заголовки
дедуплицируются суффиксами `-2`, `-3` (CommonMark сам этого не делает и выдаёт
одинаковые id). `toc` собирается постобработкой HTML, а не из AST, — так оглавление
совпадает с тем, что реально попало на страницу.

### 4. Backlinks

`buildBacklinks()` идёт по `relations[]` каждого документа:

- имя связи должно быть из `RelationTypes` (иначе проблема «неизвестный тип связи»);
- цель должна разрешаться в существующий документ (иначе «связь %s указывает на
  несуществующий документ "%s"»);
- для ребра записывается обратное: `backlinks[targetKey][] = {type, source}`.

Обратные связи **не хранятся в файлах**: их вычисляет индекс. Поэтому `content/en/proteins/mtor.md`
не содержит списка «кто на меня ссылается», а страница mTOR его показывает.

Порядок важен: backlinks считаются после рендера, но на HTML не влияют — они нужны
только странице документа и компоненту `doc/relation-groups`.

## Идентификаторы

- **id** — `type:slug`, например `concept:longevity`. Обязателен и совпадает с файлом.
- **key** — `id@locale`, например `concept:longevity@en`. Внутренний ключ хранилища:
  один документ в двух локалях — два ключа.
- **url** — `DocumentRoutes::url($locale, $type, $slug, $defaultLocale)`:
  `/concepts/longevity/` для основной локали и `/ru/concepts/longevity/` для остальных.
- **alias** — альтернативное имя из Frontmatter; разрешается только в пределах локали.

`ContentRepository::resolve(string $target, string $locale)` принимает четыре формы:

| Форма | Пример | Как разрешается |
| --- | --- | --- |
| id | `protein:mtor` | прямой индекс `byId` |
| путь | `proteins/mtor` | первый сегмент → тип через `DocumentTypes::fromDir()`, остаток → slug |
| абсолютный путь | `/proteins/mtor/` | то же после снятия слэшей |
| slug или alias | `mtor` | перебор типов (`bySlug`) и таблица alias'ов |

Сначала поиск идёт в запрошенной локали, затем в локали по умолчанию. Это даёт
естественный fallback: русская страница может ссылаться на английский документ, пока
перевода нет.

## Локали

- Список локалей — `content.locales` в `config.php` (`['en' => 'English']`), основная —
  `content.default_locale` (`en`).
- Файлы лежат в `content/<locale>/<каталог типа>/<slug>.md`.
- Маршруты для неосновных локалей добавляет `DocumentRoutes`: `/ru/`, `/ru/catalog/`,
  `/ru/search/` и `/ru/<каталог>/<slug>/`.
- Перевод — это **тот же id в другой локали**. `ContentRepository::translations()`
  ищет документы с тем же `id` в других локалях. Поля `translations` в Frontmatter нет
  и не должно быть: список переводов вычисляется, а не дублируется в каждом файле.
- `stats()`, `all()`, `recent()`, `popular()`, `topics()` принимают локаль и по умолчанию
  используют основную. У `popular()` есть третий параметр `$exclude` — ключи
  документов, которые уже показаны рядом; главная передаёт туда результат `recent()`,
  чтобы две соседние колонки не повторяли одну карточку.

Добавление локали — это правка `config.php` плюс создание `content/<locale>/...`.
Схемы и типы менять не нужно: `locale` валидируется шаблоном `^[a-z]{2}(-[A-Z]{2})?$`.

## Wikilinks

Синтаксис:

```markdown
[[concepts/longevity]]                     → текст = заголовок целевого документа
[[concepts/longevity|lifespan]]            → свой текст
[[research/harrison-2009-rapamycin-mice#limitations]]  → якорь на раздел
```

Парсер — `WikilinkParser`, регулярное выражение
`\[\[([^\[\]\n|#]+)(?:#([^\[\]\n|]+))?(?:\|([^\]\n]+))?\]\]`: цель не может содержать
`[`, `]`, перевод строки, `|` и `#`; якорь и подпись — необязательны.

Поведение:

- **разрешено** → `<a class="wikilink" href="..." data-doc="concept:longevity" title="...">`;
- **не разрешено** → `<span class="wikilink wikilink--missing" title="Article not written yet: ..."
  data-wikilink="...">`. Ссылка не становится битой ссылкой на 404, но и не притворяется
  рабочей;
- все unresolved-цели собираются в `Document::$unresolved` и печатаются
  `scripts/content-check.php` блоком «Незакрытые Wikilinks».

Якорь кодируется через `rawurlencode` и дописывается как `#fragment`. Ссылки на внешние
сайты обрабатывает `ExternalLinkExtension`: они получают `rel="noopener noreferrer"` и
открываются в новой вкладке.

## Типизированные связи

`src/Content/RelationTypes.php` знает 17 имён: 10 канонических рёбер и 7 обратных.

| Каноническое ребро | Обратное | Метка / обратная метка |
| --- | --- | --- |
| `related_to` | — (симметрично) | Related to |
| `part_of` | `has_part` | Part of / Includes |
| `cites` | `cited_by` | Cites / Cited by |
| `supported_by` | `supports` | Supported by / Supports |
| `contradicted_by` | `contradicts` | Contradicted by / Contradicts |
| `studies` | `studied_by` | Studies / Studied by |
| `measured_by` | `measures` | Measured by / Measures |
| `targets` | `targeted_by` | Targets / Targeted by |
| `regulates` | `regulated_by` | Regulates / Regulated by |

В файле пишется только каноническое направление; обратное вычисляется. `related_to`
симметрично: ребро появляется у обоих документов.

Правила:

- `target` — id, путь или slug; резолвится тем же `resolve()`;
- несуществующая цель — проблема загрузки, а не молчаливый пропуск;
- связи не заменяют Wikilinks: Wikilink — это текст статьи, связь — ребро графа.
  Обычно они дублируются осознанно (в тексте ссылка, в Frontmatter — типизированное
  ребро), но требование только одно: цель должна существовать.

`ContentRepository::related(Document)` группирует исходящие связи по меткам, а
`backlinks(Document)` отдаёт обратные; страница документа склеивает оба набора
(`array_merge($relations, $backlinks)`) и передаёт в компонент `doc/relation-groups`.

Отдельно `evidenceGroups(Document)` собирает связанные **утверждения** (тип `claim`) и
раскладывает их по `claim.evidence_direction`: `supports`, `contradicts`, `mixed`,
`insufficient`. Направление берётся из Frontmatter утверждения, а не из типа ребра:
связь `related_to` ничего не говорит о том, подтверждают данные тезис или нет. Внутри
группы утверждения упорядочены по `claim.confidence` (`established` → `speculative`).
Качество доказательств в этой выборке не участвует — оно принадлежит самому документу и
присваивается по методологии.

## Статусы

`DocumentTypes::STATUSES`: `draft`, `review`, `published`, `deprecated`.

- `all()`, `recent()`, `popular()`, каталог и главная показывают только `published`.
- `find()` и `byId()` статус **не** проверяют: документ в `draft` откроется по прямому
  адресу. Это удобно для ревью по ссылке, но означает, что черновик не является
  секретом — не кладите в него то, что не должно быть публичным.
- Поисковый индекс строится по всем загруженным документам
  (`$this->search->index(array_values($this->documents))`), а `NativeSearchEngine` не
  фильтрует по статусу. Поэтому черновик может попасть в результаты `/search`, хотя в
  каталог не попадёт. Если это нежелательно — фильтруйте по статусу на уровне движка
  или репозитория.
- `deprecated` — документ остаётся доступным, но помечен как устаревший; шаблон не
  скрывает его автоматически.

## Валидация схемами

`src/Content/SchemaValidator.php` регистрирует все `schemas/*.schema.json` в
`opis/json-schema` под `$id` вида `https://longevypedia.localhost/schemas/<file>` и
сопоставляет тип документа с файлом схемы через `DocumentTypes::schema()`.

- `document.schema.json` — общая часть: id, type, title, summary, status, locale,
  topics, tags, aliases, created, updated, license, review, relations, sources.
- `entity.schema.json` — 9 типов сущностей плюс `identifiers` (HGNC, UniProt, ChEMBL,
  DrugBank, MONDO, MeSH, OMIM, GO, KEGG).
- `research.schema.json` — блок `study`.
- `claim.schema.json` — блок `claim`.
- `guideline.schema.json` — блок `guideline`.
- `article.schema.json` — блок `article`.

Валидатор собирает до 50 ошибок и печатает их как `path: message`. Ошибка схемы не
бросает исключение: документ отвергается, сообщение попадает в `problems()`. Поэтому
`content-check` перечисляет все проблемы сразу, а не по одной за прогон.

Схемы — часть контракта: меняя поле, правьте схему, контент и этот документ. Обратная
совместимость не гарантируется: отдельного поля версии формата нет.

## Поисковый индекс

`NativeSearchEngine` строит обратный индекс в памяти при первом поиске
(`ensureSearchIndex()`).

**Веса полей:**

| Поле | Вес |
| --- | --- |
| `title` | 10.0 |
| `aliases` | 6.0 |
| `summary` | 5.0 |
| `tags` | 4.0 |
| `topics` | 3.0 |
| `body` | 1.0 |

**Как считается запрос:**

1. Токенизация: `mb_strtolower`, затем разбиение по `/[^\p{L}\p{N}]+/u`.
2. Удаление стоп-слов (`a, an, and, are, …`, список в `NativeSearchEngine::STOPWORDS`) и
   токенов длиной ≤ 1.
3. Для каждого токена: точное совпадение по индексу; если токен длиной ≥ 3 и точного нет
   — префиксный перебор всех термов (`senesc` находит `senescence`).
4. `idf = log(1 + total / count(matched))`; вклад терма — `weight * idf`.
5. Если заголовок документа точно совпадает с нормализованным запросом (без учёта
   регистра), score умножается на `TITLE_EXACT_BONUS = 4.0`: иначе запрос `Rapamycin`
   открывает утверждение, где слово встречается чаще, а не статью о соединении.
6. Фильтры `type`, `topic`, `locale` применяются к документам.
7. Сортировка: score по убыванию, затем заголовок.

Сниппет — окно 240 символов вокруг первого вхождения терма с многоточиями. Текст для
него берётся из отрендеренного HTML через `NativeSearchEngine::plainText()`: значок-якорь
заголовка (`heading-permalink`) вырезается, а границы блоков (`</p>`, `</h2>`, `</li>` …)
становятся пробелом — иначе `strip_tags()` склеивал бы соседние блоки в
«originRapamycin», а `#` попадал бы в подсказку.
`SearchHit::highlighted()` экранирует HTML и оборачивает совпадения в `<mark>`, причём
берёт только термы длиной ≥ 3 и сортирует их от длинных к коротким, чтобы `senescence`
не был перекрыт `senes`.

Ограничения, которые надо знать:

- индекс не персистентный и строится на каждый запрос;
- нет морфологии и синонимов (кроме `aliases` документа);
- нет ранжирования по свежести и ссылкам;
- префиксный перебор — линейный по словарю;
- черновики индексируются (см. «Статусы»).

## Точки расширения

**Заменить поиск.** Реализуйте `Longevypedia\Search\SearchEngine`:

```php
interface SearchEngine
{
    public function index(array $documents): void;

    /** @return list<SearchHit> */
    public function search(string $query, array $filters = []): array;
}
```

и передайте движок в конструктор `ContentRepository` (шестой параметр) или
зарегистрируйте свой репозиторий в контейнере под `longevypedia.content`. Внешний движок
(Meilisearch, SQLite FTS) потребует синхронизации при деплое — это отдельная задача,
которой сейчас нет.

**Добавить БД пользователей.** Контент остаётся файловым; пользователи, закладки и
история чтения — отдельное хранилище. Не смешивайте: корпус должен оставаться
воспроизводимым из Git.

**Добавить кеш.** Самый дешёвый шаг — сериализовать разобранный корпус в `var/` и
инвалидировать по mtime файлов. Следующий — кешировать готовый HTML по
`key + updated`. Оба варианта не меняют публичный API репозитория.

**Добавить тип документа.** Нужны: запись в `DocumentTypes::TYPES`, схема в `schemas/`,
каталог в `content/en/`, при необходимости компонент полей. Маршруты и каталог появятся
сами — `DocumentRoutes` читает типы.

**Добавить локаль.** `content.locales` + каталог `content/<locale>/`; маршруты
сгенерируются. Переводы связываются по `id`, поле `translations` не нужно.

**Добавить связь.** Имя в `RelationTypes` (каноническое ребро + обратное), значение в
enum `schemas/document.schema.json`, подпись в `RelationTypes::DIRECT`. Без схемы
валидация отвергнет новое имя.
