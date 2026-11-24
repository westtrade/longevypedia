# Как участвовать в Longevypedia

Спасибо, что читаете код и корпус. Здесь описано, как добавить документ, как проверить
правку до пуша и чего мы не принимаем.

## Прежде чем начать

- Опечатка, битая ссылка, неточная формулировка — можно сразу PR, без issue.
- Новый тип документа, изменение схемы, новый маршрут, переименование поля — сначала
  issue: это ломает существующий корпус и требует миграции.
- Крупная правка статьи (переписывание раздела, смена оценки уверенности) — тоже issue,
  чтобы обсудить источники до текста.

Все команды ниже выполняются из корня репозитория. Если окружение поднято, используйте
`ddev`; на хосте нужен PHP 8.2+ и установленный `vendor/`.

## Добавить документ

### 1. Выберите тип и каталог

Тип задаёт каталог, схему, маршрут и набор обязательных полей. Таблица типов —
`src/Content/DocumentTypes.php`; вот она целиком:

| Тип | Каталог | Группа | Схема |
| --- | --- | --- | --- |
| `concept` | `concepts/` | Knowledge | `entity.schema.json` |
| `mechanism` | `mechanisms/` | Knowledge | `entity.schema.json` |
| `pathway` | `pathways/` | Knowledge | `entity.schema.json` |
| `gene` | `genes/` | Knowledge | `entity.schema.json` |
| `protein` | `proteins/` | Knowledge | `entity.schema.json` |
| `biomarker` | `biomarkers/` | Knowledge | `entity.schema.json` |
| `disease` | `diseases/` | Knowledge | `entity.schema.json` |
| `compound` | `compounds/` | Knowledge | `entity.schema.json` |
| `intervention` | `interventions/` | Knowledge | `entity.schema.json` |
| `research` | `research/` | Evidence | `research.schema.json` |
| `claim` | `claims/` | Evidence | `claim.schema.json` |
| `guideline` | `guidelines/` | Guidance | `guideline.schema.json` |
| `article` | `articles/` | Guidance | `article.schema.json` |

Файл кладётся в `content/<locale>/<каталог>/<slug>.md`. Сейчас локаль одна — `en`;
для русского текста понадобится сначала добавить локаль в `config.php` (см.
`docs/specifications/longevypedia-markdown.md`, раздел про мультиязычность).

### 2. Назовите файл правильно

Slug — только строчная латиница, цифры и дефис: `/^[a-z0-9]+(?:-[a-z0-9]+)*$/`.
Имя файла без `.md` и есть slug. Плохие имена: `Rapamycin.md`, `rapamycin_mice.md`,
`rapamycin mice.md`. Хорошее: `rapamycin-extends-lifespan-in-mice.md`.

Slug уникален внутри локали. Два документа с одним slug в одной локали не загрузятся:
Wikilink `[[rapamycin]]` стал бы неоднозначным, и загрузчик отвергает оба.

### 3. Заполните Frontmatter

Обязательны девять полей:

```yaml
---
id: compound:rapamycin       # ровно <type>:<slug>, иначе документ не загрузится
type: compound               # один из 13 типов
title: Rapamycin             # 3–120 символов
summary: >-                  # 20–400 символов, одно-два предложения
  Ингибитор mTOR, который продлевает жизнь у мышей и остаётся
  предметом спора о переносимости на человека.
status: published            # draft | review | published | deprecated
locale: en                   # en
created: 2026-11-20          # дата, не строка с временем
updated: 2026-11-24
license: CC-BY-SA-4.0
---
```

Дальше — то, что относится к типу:

- `topics` — 1–6 слагов из `taxonomy/topics.php`;
- `tags` — до 20 свободных меток, 2–40 символов;
- `aliases` — до 20 альтернативных имён; по ним разрешаются Wikilinks;
- `review` — `level: unreviewed | community | expert`, `reviewers`, `reviewed_at`;
- `relations` — типизированные связи (см. ниже);
- `sources` — первоисточники;
- `identifiers` — для сущностей: `hgnc`, `uniprot`, `chembl`, `drugbank`, `mondo`,
  `mesh`, `omim`, `go`, `kegg`;
- `study` — для `research`; `claim` — для `claim`; `guideline` — для `guideline`;
  `article` — для `article`.

Точные типы, ограничения и enum'ы — в `schemas/*.schema.json`; пересказ с примерами —
в `docs/specifications/longevypedia-markdown.md`.

### 4. Напишите текст и связи

Тело — обычный Markdown (CommonMark + GFM: таблицы, зачёркивание, списки задач,
сноски). Заголовки — от `##`: `#` занят заголовком страницы.

Ссылка на другой документ — Wikilink:

```markdown
[[concepts/longevity|lifespan]]        # текст ссылки свой
[[concepts/healthspan]]                # текст — заголовок целевого документа
[[research/harrison-2009-rapamycin-mice#limitations]]  # якорь на раздел
```

Цель можно писать как `type:slug`, как путь `concepts/longevity`, как `/concepts/longevity/`
или как alias. Разрешение идёт сначала в текущей локали, затем в локали по умолчанию.

Незакрытая ссылка не ломает загрузку: она рендерится как `<span class="wikilink--missing">`
и попадает в отчёт `scripts/content-check.php`. Но в PR такие ссылки лучше не оставлять:
они означают обещание написать документ.

Связи в Frontmatter — для того, что должно попасть в граф, а не только в текст:

```yaml
relations:
  - type: studies
    target: compound:rapamycin
  - type: supports
    target: claim:rapamycin-extends-lifespan-in-mice
```

Полный список из 17 имён и обратных к ним — в спецификации. Обратную связь писать не
нужно: `[[protein:mtor]]` и `targets` дают обратную ссылку на странице mTOR автоматически.

### 5. Проверьте

```bash
ddev composer content    # схемы, id, дубликаты slug, связи, Wikilinks
ddev composer check      # бандлы, маршруты, иконки
ddev composer test       # инварианты корпуса и страниц
ddev composer repo       # гигиена репозитория: файлы, секреты, схемы, YAML, ссылки
```

`content-check` — гейт: код выхода 1 означает, что корпус публиковать нельзя. Он всегда
валидирует по JSON Schema, даже если отладка выключена.

Посмотреть страницу:

```bash
php vendor/bin/bolt serve --host=127.0.0.1 --port=8099
curl -sS http://127.0.0.1:8099/compounds/rapamycin/ | head -40
```

или открыть `https://longevypedia.localhost/compounds/rapamycin/` в DDEV.

## Научная добросовестность

Это главная часть ревью; текст без источников не принимается.

- **Не выдумывайте идентификаторы.** DOI и PMID проверяются схемой только на формат
  (`^10\.[0-9]{4,9}/...`, `^[1-9][0-9]{0,8}$`), но не на существование. Выдуманный DOI
  ведёт читателя к чужой работе и хуже отсутствующего. Если идентификатора нет — оставьте
  только `citation`.
- **Не выдумывайте результаты.** Числа, размеры выборки, длительность, виды животных —
  из абстракта или полного текста, а не «по памяти». Если в источнике нет числа, пишите
  качественно и не подставляйте правдоподобное.
- **Различайте факт и гипотезу.** «Показано в мышах» и «предполагается механизм» — разные
  утверждения. Гипотеза без прямого теста — это `confidence: speculative`, а не
  «предположительно доказано».
- **Показывайте противоречия.** Если есть исследование с противоположным результатом,
  оно идёт в текст и в связи (`contradicts` / `contradicted_by`), а не исчезает.
  Противоречие — часть записи, а не дефект.
- **Не переносите выводы между моделями.** Эффект у мышей не становится эффектом у
  людей; в `claim.scope` это написано явно.
- **Демонстрационные записи помечайте.** У исследования, которое описывает не реальную
  работу, а пример разметки, стоит `study.example: true` — страница покажет
  предупреждающий баннер.
- **Не выводите качество доказательств из p-value или размера эффекта.** Уверенность и
  качество присваиваются по методологии (`docs/specifications/methodology.md`), а не
  вычисляются из одного числа.

## Стиль Markdown

- Один `#` на страницу не нужен: заголовок и метаданные печатает шаблон.
- Разделы — `##`, подразделы — `###`; глубже `####` в содержании не индексируется.
- Таблицы — GFM; в тексте они прокручиваются внутри контейнера, ширину не задавайте.
- Код — в тройных обратных кавычках с языком.
- Внешние ссылки открываются в новой вкладке автоматически (`external_link` в
  `MarkdownRenderer`); атрибуты руками не пишите.
- HTML в Markdown разрешён (`html_input: allow`), но это исключение для встраивания
  виджетов, а не способ вёрстки. Обычный текст — Markdown.
- Сноски — `[^1]` и `[^1]: текст`; они рендерятся в блок `.footnotes`.
- Не используйте Obsidian-специфику: `![[вложение]]`, `> [!note]`-callout'ы,
  Dataview-запросы, `%%комментарии%%`. Подробнее — в спецификации, раздел
  «Obsidian-совместимость».

## Добавить компонент UI-набора

Компонент — это один PHP-файл: разметка, docblock с параметрами и стили в конце файла.
Стили из `<style lang="scss">` BOLT извлекает в сгенерированную точку входа
`.bolt/client/generated/main/index.ts`, поэтому отдельный CSS-файл не нужен и не должен
появляться.

1. **Создайте компонент** `app/components/ui/<name>.php`. Имя — `ui-*` в классах,
   параметры через `??=` с дефолтами, экранирование — `$this->e()`, иконки —
   `$this->insert('bolt://ui-kit/icon', ['name' => '...'])`. В конце файла — один блок
   `<style lang="scss">`.
2. **Опишите страницу** в `scripts/ui-kit-pages.php`: добавьте slug в нужную группу
   массива `$groups` и спецификацию в `$pages` — `title`, `note` и `specimens`
   (заголовок образца, пояснение, строка вызова). Код образца вставляется дважды: как
   исходник под примером и как живой пример, поэтому они не расходятся.
3. **Сгенерируйте страницы**: `ddev exec 'php scripts/ui-kit-pages.php'`. Команда пишет
   `data/ui-kit.php` и `app/pages/ui-kit/<slug>/page.php`; руками эти файлы не правятся.
4. **Проверьте** `/ui-kit/<slug>` и `git diff data/ui-kit.php`: в каталоге появился ровно
   один пункт, меню и индекс обновились.

Если компонент заменяет встроенный BOLT — положите его по тому же относительному пути
(`app/components/ui-kit/button.php` заменит `bolt://ui-kit/button`). В проекте так не
делается: свои компоненты лежат в `ui/` под своими именами, чтобы отличие от BOLT было
видно в разметке.

## Стиль кода

Полные правила — `docs/development/coding-guidelines.md`. Коротко:

- `declare(strict_types=1);` в каждом PHP-файле, табы для отступов;
- функции — `snake_case`, методы — `camelCase`, глобальные функции с префиксом
  `longevypedia_`;
- параметры шаблона получают дефолт через `??=`, а не через `isset()`;
- стили компонента — один `<style lang="scss">` в конце файла; media queries —
  desktop-first (`max-width`), BEM `block__element--modifier`;
- значения оформления — только токены из `app/client/styles/_tokens.scss`, не hex и не
  пиксели в компоненте;
- без новых зависимостей без обсуждения: фронтенд уже несёт htmx, Alpine (через BOLT) и
  Vite.

## Процесс PR

1. Ветка от `main`: `git checkout -b content/rapamycin-mice`.
2. Один PR — одна тема. Правка контента и рефакторинг шаблонов в одном PR ревьюятся
   плохо.
3. Коммиты — по смыслу; сообщение в императиве: `Add Harrison 2009 study`,
   `Fix broken wikilink in autophagy`.
4. В описании PR: что добавлено, какие источники использованы, что проверено
   (`content`, `check`, `test`, `repo`, `bun run build`).
5. Сквоттинг и «заготовки» страниц без содержания не принимаются: пустой документ
   с обещанием написать позже — это незакрытый Wikilink, а не документ.

## Что проверяет CI

Workflow — `.github/workflows/ci.yml`, запускается на push и pull request в `main`.
Проверки идут в том же порядке, что и локально, на матрице PHP **8.2** и **8.4**:

1. `composer validate --no-check-publish`;
2. `composer install` — оба репозитория раскладываются рядом (`longevypedia/` и
   `bolt/`), иначе path-репозиторий не разрешится;
3. `php -l` для `config.php` и всех PHP-файлов в `src`, `app`, `scripts`, `tests`,
   `public`;
4. `bolt config`, `bolt check`, `bolt assets --json`;
5. `php scripts/content-check.php` — корпус;
6. `php scripts/repo-check.php` — гигиена репозитория;
7. `php vendor/bin/phpunit`;
8. `bun install --frozen-lockfile` и `bun run build`;
9. проверка, что `public/build/.vite/manifest.json` существует, а `public/build/assets`
   не пуст.

Локальный эквивалент:

```bash
ddev composer content
ddev composer check
ddev composer test
ddev composer repo
ddev exec 'bun run build'
```

Если CI упал, воспроизводите шаг локально той же командой: набор проверок в проекте
один и тот же, отдельных «CI-only» скриптов нет.

## Куда смотреть дальше

- `docs/specifications/longevypedia-markdown.md` — формат файла.
- `docs/specifications/methodology.md` — как присваиваются уверенность и качество.
- `docs/architecture/content-architecture.md` — как корпус загружается и индексируется.
- `docs/design/ui-kit.md` — принципы оформления и список компонентов.
