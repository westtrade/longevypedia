# Спецификация формата Longevypedia Markdown

Версия формата: **1.0.0**. Машинночитаемого поля версии в файлах нет — версия относится к
этому документу и к набору схем в `schemas/`. Совместимость проверяется схемами, а не
номером.

Документ описывает формат файла: каталоги, Frontmatter, блоки типов, связи, источники,
Markdown, локали и правила валидации. Как корпус загружается и индексируется — в
[`../architecture/content-architecture.md`](../architecture/content-architecture.md).
Как присваиваются оценки — в [`methodology.md`](methodology.md).

## 1. Файл и каталоги

```
content/<locale>/<каталог типа>/<slug>.md
```

- `<locale>` — код языка: `en`, `ru`, `pt-BR` (шаблон `^[a-z]{2}(-[A-Z]{2})?$`).
  Список локалей задан в `config.php` → `content.locales`; сейчас там только `en`.
- `<каталог типа>` — из таблицы ниже, ровно один из тринадцати.
- `<slug>` — `^[a-z0-9]+(?:-[a-z0-9]+)*$`: строчная латиница, цифры, дефис. Slug
  уникален внутри локали.
- Расширение — только `.md`. Файлы в скрытых каталогах (`/.`) игнорируются.

Пример: `content/en/research/harrison-2009-rapamycin-mice.md`.

## 2. Структура файла

```markdown
---
id: concept:healthspan
type: concept
title: Healthspan
summary: Период жизни, прожитый в состоянии, которое человек считает здоровым.
status: published
locale: en
topics:
  - aging-mechanisms
tags:
  - frailty
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
---

## Definition

Текст. Ссылка: [[concepts/longevity|lifespan]].
```

Правила:

1. Файл начинается с `---` в первой строке (BOM снимается, CRLF нормализуется).
2. Блок закрывается второй строкой `---`; всё после неё — Markdown.
3. YAML разбирается с `Yaml::PARSE_DATETIME`, но даты нормализуются обратно в строки:
   `2026-11-20` остаётся датой без времени, `2026-11-20T14:30:00` получает смещение
   `+00:00`.
4. Пустой Frontmatter, скаляр вместо объекта или битый YAML — документ не загружается.

## 3. Типы документов

| `type` | Каталог | Метка | Группа | Схема |
| --- | --- | --- | --- | --- |
| `concept` | `concepts/` | Concept | Knowledge | `entity.schema.json` |
| `mechanism` | `mechanisms/` | Mechanism | Knowledge | `entity.schema.json` |
| `pathway` | `pathways/` | Pathway | Knowledge | `entity.schema.json` |
| `gene` | `genes/` | Gene | Knowledge | `entity.schema.json` |
| `protein` | `proteins/` | Protein | Knowledge | `entity.schema.json` |
| `biomarker` | `biomarkers/` | Biomarker | Knowledge | `entity.schema.json` |
| `disease` | `diseases/` | Disease | Knowledge | `entity.schema.json` |
| `compound` | `compounds/` | Compound | Knowledge | `entity.schema.json` |
| `intervention` | `interventions/` | Intervention | Knowledge | `entity.schema.json` |
| `research` | `research/` | Study | Evidence | `research.schema.json` |
| `claim` | `claims/` | Claim | Evidence | `claim.schema.json` |
| `guideline` | `guidelines/` | Guideline | Guidance | `guideline.schema.json` |
| `article` | `articles/` | Article | Guidance | `article.schema.json` |

Источник истины — `src/Content/DocumentTypes.php`; каталоги и метки оттуда попадают в
маршруты, каталог и хлебные крошки.

## 4. Общие поля Frontmatter

Обязательны: `id`, `type`, `title`, `summary`, `status`, `locale`, `created`, `updated`,
`license`.

| Поле | Тип | Ограничения | Назначение |
| --- | --- | --- | --- |
| `id` | string | `^[a-z][a-z0-9-]*:[a-z0-9]+(?:-[a-z0-9]+)*$` | стабильный id `type:slug`; обязан совпадать с файлом |
| `type` | string | один из 13 | тип документа |
| `title` | string | 3–120 | заголовок страницы и карточки |
| `summary` | string | 20–400 | одно-два предложения; карточки, поиск, meta description |
| `status` | string | `draft`, `review`, `published`, `deprecated` | публикация |
| `locale` | string | `^[a-z]{2}(-[A-Z]{2})?$` | язык текста, совпадает с каталогом |
| `topics` | array | 1–6 уникальных слагов | навигация; слаги из `taxonomy/topics.php` |
| `tags` | array | ≤ 20, каждый 2–40 символов | свободные ключевые слова для поиска |
| `aliases` | array | ≤ 20, каждый 2–120 | альтернативные имена для Wikilinks |
| `created` | date | `YYYY-MM-DD` | когда документ написан |
| `updated` | date | `YYYY-MM-DD` | последняя содержательная правка; показывается на странице |
| `license` | string | — | лицензия прозы, по умолчанию `CC-BY-SA-4.0` |
| `review` | object | см. ниже | редакционное ревью, отдельно от `status` |
| `relations` | array | см. §6 | типизированные рёбра графа |
| `sources` | array | см. §7 | первоисточники |

### `review`

```yaml
review:
  level: community          # unreviewed | community | expert
  reviewers:
    - name-or-handle
  reviewed_at: 2026-11-22
  notes: Проверены числа и дизайн исследования.
```

`status` и `review` — разные вещи: опубликованный документ может быть
`unreviewed`, а черновик — уже отревьюенным. `notes` — до 600 символов.

## 5. Блоки типов

### 5.1. Сущности: `identifiers`

Для девяти типов Knowledge (`concept`, `mechanism`, `pathway`, `gene`, `protein`,
`biomarker`, `disease`, `compound`, `intervention`) доступен необязательный объект
`identifiers`. Заполняется только тем, что действительно указано в источнике.

```yaml
identifiers:
  hgnc: HGNC:12345
  uniprot: P42345
  chembl: CHEMBL413
  drugbank: DB00877
  mondo: MONDO:0000001
  mesh: D000001
  omim: 123456
  go: GO:0000001
  kegg: hsa:1234
```

| Ключ | Шаблон |
| --- | --- |
| `hgnc` | `^HGNC:[0-9]+$` |
| `uniprot` | `^([OPQ][0-9][A-Z0-9]{3}[0-9]\|[A-NR-Z][0-9]([A-Z][A-Z0-9]{3}[0-9]){1,2})$` |
| `chembl` | `^CHEMBL[0-9]+$` |
| `drugbank` | `^DB[0-9]{5}$` |
| `mondo` | `^MONDO:[0-9]{7}$` |
| `mesh` | `^(D\|C\|G)[0-9]{6}$` |
| `omim` | `^[0-9]{6}$` |
| `go` | `^GO:[0-9]{7}$` |
| `kegg` | `^[a-z]{2,4}[0-9]{5}$` |

### 5.2. Исследование: `study`

Обязателен для `type: research`. Обязательное поле внутри — `design`.

```yaml
study:
  design: animal_experiment
  species: mouse (Mus musculus)
  population: outbred mice, 9 months at start
  sample_size: 1900
  intervention: encapsulated rapamycin, 14 mg per kg of food
  comparator: identical diet without rapamycin
  duration: until death
  outcomes:
    - name: Median lifespan
      direction: increase
      measure: Kaplan–Meier median, +9% in males, +14% in females
    - name: Body weight
      direction: no_change
  authors:
    - David E. Harrison
    - Randy Strong
  journal: Nature
  year: 2009
  doi: 10.1038/nature08221
  pmid: "19587680"
  registration: NCT00000000
  preprint: false
  example: false
```

`design` — один из: `systematic_review`, `meta_analysis`,
`randomized_controlled_trial`, `controlled_trial`, `cohort`, `case_control`,
`cross_sectional`, `case_series`, `case_report`, `animal_experiment`, `in_vitro`,
`ecological`, `narrative_review`, `other`.

| Поле | Тип | Ограничения |
| --- | --- | --- |
| `design` | string | обязателен, enum выше |
| `species` | string | `human` или модельный организм |
| `population` | string | ≤ 300 |
| `sample_size` | integer | ≥ 1; для обзоров можно опустить |
| `intervention` | string | ≤ 300 |
| `comparator` | string | ≤ 300 |
| `duration` | string | ≤ 120 |
| `outcomes` | array | элементы: `name` (3–160, обязателен), `direction`, `measure` (≤ 160) |
| `authors` | array | строки ≥ 2 символов |
| `journal` | string | ≤ 200 |
| `year` | integer | 1800–2200 |
| `doi` | string | шаблон DOI |
| `pmid` | string | `^[1-9][0-9]{0,8}$` |
| `registration` | string | ≤ 60 |
| `preprint` | boolean | — |
| `example` | boolean | `true` — демонстрационная запись, не описывающая реальное исследование; страница показывает предупреждение |

`direction` у исхода: `increase`, `decrease`, `no_change`, `mixed`, `unclear`.

Важно: **результаты в блок не входят.** В `study` лежат библиографические и дизайн-факты,
а находки — в теле Markdown, где их можно прочитать в контексте. `outcomes` — короткая
сводка направлений, а не замена разделу «Findings».

### 5.3. Утверждение: `claim`

Обязателен для `type: claim`. Обязательны `statement`, `confidence`,
`evidence_direction`.

```yaml
claim:
  statement: Rapamycin extends median lifespan in mice.
  confidence: likely
  evidence_direction: supports
  scope: Mice only; no human lifespan data. Doses and formulations differ between studies.
  as_of: 2026-11-20
```

| Поле | Тип | Ограничения | Смысл |
| --- | --- | --- | --- |
| `statement` | string | 20–500 | одно предложение, сформулированное так, чтобы его можно было опровергнуть; не пересказ статьи |
| `confidence` | string | `established`, `likely`, `uncertain`, `contested`, `speculative` | насколько утверждение поддержано в целом |
| `evidence_direction` | string | `supports`, `contradicts`, `mixed`, `insufficient` | куда указывают собранные доказательства |
| `scope` | string | ≤ 300 | где утверждение применимо: вид, популяция, дозы, сроки |
| `as_of` | date | `YYYY-MM-DD` | когда оценка последний раз проверялась |

`established` в схеме описано как «textbook-level consensus»; на странице `/about`
формулировка мягче — «воспроизведено в независимых исследованиях и популяциях».
Практическое правило: `established` — только для того, что не оспаривается в обзорах.

`confidence` и `evidence_direction` независимы. Утверждение может быть уверенно
опровергнутым (`contradicts` + `likely`), а может быть спорным (`mixed` + `contested`).
Как их присваивать — [`methodology.md`](methodology.md).

### 5.4. Руководство: `guideline`

Обязателен для `type: guideline`. Обязательны `organization`, `issued`.

```yaml
guideline:
  organization: World Health Organization
  issued: 2020-11-26
  version: "2.1"
  audience: clinicians
  url: https://example.org/guideline
  supersedes_url: https://example.org/guideline/2015
```

Ограничения: `organization` 2–200, `version` ≤ 40, `audience` ≤ 200, `url` и
`supersedes_url` — URI. Longevypedia пересказывает руководство и связывает его с
доказательствами; собственных медицинских рекомендаций проект не выпускает.

### 5.5. Статья: `article`

Блок необязателен для `type: article`.

```yaml
article:
  kind: explainer          # overview | explainer | essay | interview | news
  series: Mechanisms in brief
  reading_level: informed  # general | informed | specialist
```

`essay` и `interview` — авторские форматы; в тексте должно быть явно сказано, что это
мнение, а не консенсус.

## 6. Связи

```yaml
relations:
  - type: studies
    target: compound:rapamycin
    note: Исследование изучает именно это соединение.
  - type: supports
    target: claim:rapamycin-extends-lifespan-in-mice
```

`target` — id (`type:slug`), путь (`compounds/rapamycin`) или slug. `note` — до 300
символов, зачем ребро существует; для противоречащих данных фактически обязателен.

Семнадцать допустимых имён:

| Имя | Обратное | Смысл |
| --- | --- | --- |
| `related_to` | — (симметрично) | тематическая связь |
| `part_of` | `has_part` | часть целого |
| `has_part` | `part_of` | целое включает часть |
| `cites` | `cited_by` | документ ссылается на источник |
| `cited_by` | `cites` | на документ ссылаются |
| `supported_by` | `supports` | утверждение поддержано работой |
| `supports` | `supported_by` | работа поддерживает утверждение |
| `contradicted_by` | `contradicts` | утверждение опровергается работой |
| `contradicts` | `contradicted_by` | работа противоречит утверждению |
| `studies` | `studied_by` | исследование изучает объект |
| `studied_by` | `studies` | объект изучен исследованием |
| `measured_by` | `measures` | биомаркер измеряется методом/работой |
| `measures` | `measured_by` | работа измеряет биомаркер |
| `targets` | `targeted_by` | соединение/вмешательство действует на белок/путь |
| `targeted_by` | `targets` | белок/путь является мишенью |
| `regulates` | `regulated_by` | ген/белок регулирует процесс |
| `regulated_by` | `regulates` | процесс регулируется геном/белком |

Правила:

- обратное ребро **не пишется** в целевом документе: его вычисляет индекс;
- цель обязана существовать, иначе загрузка сообщает о проблеме;
- тип связи обязан быть в списке, иначе — «неизвестный тип связи»;
- связи не заменяют Wikilinks: Wikilink — текст статьи, связь — ребро графа.

## 7. Источники

```yaml
sources:
  - citation: Harrison DE, Strong R, Sharp ZD, et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature. 2009;460:392–395.
    doi: 10.1038/nature08221
    pmid: "19587680"
    url: https://doi.org/10.1038/nature08221
    note: Первичная публикация, на которой построено утверждение.
```

| Поле | Обязательность | Ограничения |
| --- | --- | --- |
| `citation` | да | ≥ 8 символов; полная ссылка как в публикации |
| `url` | нет | URI |
| `doi` | нет | `^10\.[0-9]{4,9}/[-._;()/:a-zA-Z0-9]+$` |
| `pmid` | нет | `^[1-9][0-9]{0,8}$` |
| `note` | нет | ≤ 300 |

Правила добросовестности:

- **DOI и PMID только реальные.** Схема проверяет формат, но не существование записи.
  Выдуманный идентификатор ведёт читателя к чужой работе.
- Если у источника нет DOI/PMID — не подставляйте правдоподобный; оставьте `citation`
  (и `url`, если есть).
- DOI удобно дублировать в `url` как `https://doi.org/<doi>` — так делает корпус.
- `sources` — первоисточники, а не «что попалось». Обзор может ссылаться на обзор, но
  утверждение должно опираться на работу, где результат получен.
- Источники не заменяют `relations`: связь с исследованием — ребро графа, источник —
  библиографическая запись.

## 8. Тело документа

Поддерживается CommonMark + GitHub Flavored Markdown:

- заголовки `##`–`######`; `#` не используется — заголовок печатает шаблон;
- `h2`–`h4` получают id и permalink `#`; повторяющиеся заголовки дедуплицируются
  (`-2`, `-3`);
- таблицы, зачёркивание, списки задач, сноски `[^1]` + `[^1]: текст`;
- код в тройных обратных кавычках с языком;
- HTML разрешён (`html_input: allow`), но это исключение, а не способ вёрстки;
- внешние ссылки получают `rel="noopener noreferrer"` и открываются в новой вкладке.

### Wikilinks

```markdown
[[concepts/longevity]]
[[concepts/longevity|lifespan]]
[[concepts/longevity#definition]]
[[research/harrison-2009-rapamycin-mice#limitations|ограничения]]
```

- цель — до `|` и `#`; не может содержать `[`, `]`, перевод строки;
- якорь — после `#`, кодируется в URL;
- подпись — после `|`;
- разрешение: id → путь → абсолютный путь → slug → alias; сначала текущая локаль, затем
  локаль по умолчанию;
- неразрешённая ссылка рендерится как `<span class="wikilink--missing">` и попадает в
  отчёт `scripts/content-check.php`.

Внутри одного документа ссылки на заголовок (`[[#section]]`) не поддерживаются: нужен
целевой документ.

## 9. Obsidian-совместимость

Формат совместим с Obsidian в главном:

| Совместимо | Детали |
| --- | --- |
| YAML properties | обычный Frontmatter; Obsidian покажет поля в панели свойств |
| Wikilinks | `[[target]]`, `[[target|alias]]`, `[[target#heading]]` |
| `aliases` | используются и Obsidian, и Longevypedia |
| `tags` | читаются как ключевые слова поиска |
| Markdown | CommonMark + GFM, таблицы и сноски |

Чего избегать (не поддерживается или ведёт себя иначе):

- **Вложения** `![[image.png]]` — поля для изображений в формате нет; такой синтаксис
  останется текстом.
- **Callout'ы** `> [!note]` — GFM не знает этого синтаксиса; блок отрендерится как
  обычная цитата с текстом `[!note]`. Для врезок используйте компонент `callout` на
  странице, а в Markdown — обычную цитату.
- **Dataview / Templater / плагинные запросы** — не исполняются.
- **Комментарии** `%%текст%%` — попадут в текст.
- **Вложенные теги** `tag/subtag` — схема разрешает строку, но иерархия не
  интерпретируется.
- **Ссылки в свойствах** (`related: "[[x]]"`) — не разрешаются; для графа есть
  `relations`.
- **`cssclasses`** — игнорируется.

Практика: пишите в Obsidian, но проверяйте `content-check` — он ловит то, что Obsidian
прощает (несовпадение id, несуществующие цели связей, дубликаты slug).

## 10. Мультиязычность

- Файлы: `content/<locale>/<каталог>/<slug>.md`.
- Поле `locale` должно совпадать с каталогом.
- Основная локаль — `content.default_locale` (`en`). Для неё URL без префикса; для
  остальных — с префиксом (`/ru/concepts/longevity/`).
- Перевод — документ с тем же `id` в другой локали. `ContentRepository::translations()`
  находит его автоматически.
- **Поля `translations` нет.** Список переводов вычисляется, а не хранится: иначе он
  рассинхронизируется при добавлении языка.
- Если перевода нет, Wikilink из другой локали разрешается в документ основной локали.
- `title`, `summary` и тело переводятся; `id`, `type`, `created` — нет. `updated` у
  каждой локали свой.

Добавление локали: запись в `content.locales`, каталог `content/<locale>/`, маршруты
появятся сами (`DocumentRoutes` читает конфиг).

## 11. Статусы публикации

| `status` | Значение | Видимость |
| --- | --- | --- |
| `draft` | черновик | не в каталоге, не на главной, не в подборках; доступен по прямому URL |
| `review` | на ревью | так же |
| `published` | опубликован | каталог, главная, поиск, подборки |
| `deprecated` | устарел | остаётся доступным, но помечен как устаревший |

Правило: `status` — про публикацию, `review.level` — про проверку. Не путайте их:
`published` + `unreviewed` — нормальное временное состояние, `draft` + `expert` —
тоже.

## 12. Правила валидации

Схемы проверяют типы, обязательность, enum'ы, длины и шаблоны. Загрузчик
(`ContentRepository`) дополнительно проверяет то, что схемой не выражается:

1. Slug файла соответствует `^[a-z0-9]+(?:-[a-z0-9]+)*$`.
2. `id` присутствует и равен `<type>:<slug>`.
3. `title`, `summary`, `status`, `updated` непустые.
4. Slug уникален внутри локали; alias уникален внутри локали.
5. `relations[].type` существует в `RelationTypes`.
6. `relations[].target` разрешается в загруженный документ.
7. Frontmatter валиден по схеме своего типа (в `content-check` — всегда).

Что **не** проверяется автоматически:

- существует ли DOI/PMID и соответствует ли он цитате;
- совпадает ли `locale` с каталогом (локаль берётся из каталога);
- не устарел ли `updated`;
- не противоречит ли текст источникам;
- не является ли `established` преувеличением.

Это работа ревью, а не схемы. Поэтому `content-check` — необходимый, но недостаточный
гейт.

## 13. Версионирование формата

- Версия формата — **1.0.0** (этот документ).
- Машинного поля версии в файлах нет: схемы адресуются по `$id`
  (`https://longevypedia.localhost/schemas/<file>`), а не по номеру.
- Обратная совместимость не гарантируется. Меняя поле, вы меняете одновременно:
  `schemas/*.schema.json`, затронутый контент, `src/Content/*` и этот документ.
- Добавление необязательного поля — минорное изменение; переименование или смена
  семантики обязательного — мажорное, с миграцией корпуса.
- Схемы — часть контракта: `scripts/content-check.php` валидирует по ним всегда, поэтому
  расхождение схемы и контента ловится до публикации.

## 14. Чего в формате НЕТ

Формат намеренно не хранит:

- **Качество доказательств** (`high`/`moderate`/`low`/`very-low`/`unassessed`). Оно
  присваивается по методологии и передаётся в компонент `ui/evidence` параметром; поля в
  Frontmatter для него нет. Причина: качество — суждение о совокупности данных, а не
  свойство одного файла.
- **Методологию оценки** — правила, по которым выносится суждение, живут в
  [`methodology.md`](methodology.md), а не в YAML.
- **Рабочий процесс ревью** — есть только `review.level`, `reviewers`, `reviewed_at`,
  `notes`; истории согласований и задач нет.
- **Пользовательские данные** — закладки, комментарии, история чтения, счётчики
  просмотров. «Популярное» считается по связям в графе.
- **Изображения и вложения** — поля для картинок, обложек и файлов нет.
- **Список переводов** — вычисляется по `id`.
- **Проверку идентификаторов** — DOI/PMID валидируются по формату, не по базе.
- **Полнотекстовые поля для внешнего поиска** — индекс строится из title, aliases,
  summary, tags, topics и body.

Если чего-то из этого не хватает для задачи — это повод обсудить расширение формата
(issue + схема + миграция), а не добавлять поле «на глаз»: неизвестные поля схемы
запрещены (`unevaluatedProperties: false`), и документ просто не загрузится.

## 15. Полные примеры

### Утверждение

```markdown
---
id: claim:rapamycin-extends-lifespan-in-mice
type: claim
title: Rapamycin extends lifespan in mice
summary: Ингибирование mTOR рапамицином продлевает медианную и максимальную жизнь у мышей.
status: published
locale: en
topics:
  - interventions
  - aging-mechanisms
tags:
  - rapamycin
  - mtor
  - lifespan
aliases:
  - Rapamycin longevity claim
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
claim:
  statement: Rapamycin extends median and maximum lifespan in mice.
  confidence: likely
  evidence_direction: supports
  scope: Mice only; no human lifespan data. Doses and formulations differ between studies.
  as_of: 2026-11-20
relations:
  - type: supported_by
    target: research:harrison-2009-rapamycin-mice
  - type: related_to
    target: compound:rapamycin
  - type: related_to
    target: protein:mtor
sources:
  - citation: Harrison DE, Strong R, Sharp ZD, et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature. 2009;460:392–395.
    doi: 10.1038/nature08221
    pmid: "19587680"
---

## The proposition

Рапамицин продлевает жизнь у мышей, когда начат в зрелом возрасте.

## Evidence in favour

[[research/harrison-2009-rapamycin-mice|Harrison 2009]] показывает рост медианной
продолжительности жизни в трёх независимых когортах.

## Evidence against or complicating

Дозы и переносимость у людей изучены недостаточно; см. [[compounds/rapamycin]].
```

### Исследование

```markdown
---
id: research:harrison-2009-rapamycin-mice
type: research
title: Rapamycin fed late in life extends lifespan in mice
summary: Рандомизированное исследование на генетически гетерогенных мышах показало рост продолжительности жизни при позднем начале рапамицина.
status: published
locale: en
topics:
  - interventions
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
study:
  design: animal_experiment
  species: mouse (Mus musculus)
  sample_size: 1900
  intervention: encapsulated rapamycin, 14 mg per kg of food
  comparator: diet without rapamycin
  duration: until death
  outcomes:
    - name: Median lifespan
      direction: increase
  authors:
    - David E. Harrison
  journal: Nature
  year: 2009
  doi: 10.1038/nature08221
  pmid: "19587680"
relations:
  - type: studies
    target: compound:rapamycin
  - type: supports
    target: claim:rapamycin-extends-lifespan-in-mice
---

## Design

Три независимые когорты, начало лечения в 9 и 20 месяцев.

## Findings

Медианная продолжительность жизни выросла в обеих группах.

## Limitations

Мыши; перенос на человека не показан.
```
