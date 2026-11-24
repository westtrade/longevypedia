# Longevypedia

Открытая научная энциклопедия о старении, биологии старения и healthspan.

Контент — Markdown с YAML Frontmatter в Git. Сайт — PHP 8.4 на [BOLT](../bolt), без базы
данных: корпус читается с диска, индексируется в памяти и рендерится шаблонами Plates.
Клиентская часть — Vite 8 + TypeScript + SCSS, htmx 4 для фрагментов и Alpine.js,
который приходит зависимостью встроенного компонента BOLT.

Адрес локальной установки: **https://longevypedia.localhost**.

## Что это

Энциклопедия хранит не статьи «обо всём», а типизированные документы и связи между ними.
Типов тринадцать (`src/Content/DocumentTypes.php`): понятие, механизм, путь, ген, белок,
биомаркер, заболевание, соединение, вмешательство, исследование, утверждение, руководство,
обзорная статья. У каждого типа свой каталог, своя JSON-схема и свой маршрут; страницу
рендерит один шаблон `app/pages/document/page.php`.

Из этого получается граф: утверждение «рапамицин продлевает жизнь у мышей» ссылается на
исследование Harrison 2009, а исследование — на соединение и на белок mTOR. Обратные
ссылки не дублируются в файлах: их вычисляет индекс, поэтому страница mTOR показывает,
кто на неё ссылается, без правки `content/en/proteins/mtor.md`.

Каждая страница статьи содержит ссылки на исходный файл и историю его изменений в GitHub —
адрес репозитория берётся из конфига, а не зашит в шаблон.

## Принципы

1. **Открытость.** Контент лежит в публичном Git-репозитории; каждая правка — коммит с
   автором и диффом. Противоречащие результаты сохраняются: запись показывает разногласие,
   а не устраняет его удалением.
2. **Типизированность.** Тип документа — часть идентификатора (`concept:longevity`) и
   определяет каталог, схему, маршрут и набор полей. Свободный текст без типа в корпус не
   попадает.
3. **Две независимые оси оценки.** У утверждения есть *уверенность*
   (`established | likely | uncertain | contested | speculative`) и *направление
   доказательств* (`supports | contradicts | mixed | insufficient`). Уверенно
   опровергнутое утверждение — нормальная запись, а не ошибка.
4. **Качество данных не выводится из результата.** Сильный эффект в маленькой выборке —
   это «подтверждает» + «низкое качество», а не «умеренные доказательства». Подробнее —
   `docs/specifications/methodology.md`.
5. **Не медицинская рекомендация.** Энциклопедия описывает исследования, а не назначает
   лечение.
6. **Никаких внешних запросов.** Шрифты лежат в `public/fonts/`, иконки — локальные SVG,
   CDN не используется.

## Быстрый старт

Нужны только Docker, DDEV и git. PHP, Composer и Bun на хосте не требуются: всё
устанавливается и запускается внутри контейнера.

```bash
mkdir -p ~/work && cd ~/work

# 1. Репозиторий энциклопедии
git clone https://github.com/westtrade/longevypedia.git

# 2. BOLT — рядом, на том же уровне. Адрес репозитория BOLT возьмите тот,
#    из которого ставится пакет bsti/bolt в вашей организации.
git clone <URL-репозитория-BOLT> bolt

cd longevypedia
ddev start
ddev composer install
ddev exec 'bun install && bun run build'
```

Откройте https://longevypedia.localhost.

Почему два каталога рядом: `composer.json` подключает BOLT как path-репозиторий
(`../bolt`, `symlink: true`), поэтому `vendor/bsti/bolt` — символическая ссылка на
`../../bolt`. В контейнере она разрешается благодаря
`.ddev/docker-compose.bolt.yaml`, который монтирует `${DDEV_APPROOT}/../bolt` в
`/var/www/bolt`. Если клон BOLT лежит в другом месте, симлинк внутри контейнера повиснет
и сайт отдаст 500 на автозагрузке.

`ddev config` запускать не нужно: `.ddev/config.yaml` уже в репозитории (PHP 8.4,
nginx-fpm, docroot `public`, домен `longevypedia.localhost`). Образ веб-контейнера
собирается по `.ddev/web-build/Dockerfile` и ставит Bun 1.4.2 — им собирается фронтенд.

## Команды разработки

| Команда | Что делает |
| --- | --- |
| `ddev start` / `ddev stop` | поднять/остановить окружение |
| `ddev restart` | перезапуск после правки `.ddev/config.yaml` или Dockerfile |
| `ddev composer install` | PHP-зависимости внутри контейнера |
| `ddev exec 'bun install'` | JS-зависимости |
| `ddev exec 'bun run build'` | production-сборка в `public/build` |
| `ddev composer content` | проверка корпуса: схемы, id, связи, Wikilinks |
| `ddev composer check` | `bolt check`: бандлы, маршруты, иконки |
| `ddev composer test` | PHPUnit |
| `ddev composer repo` | гигиена репозитория: лишние файлы, секреты, схемы, YAML, ссылки в docs |
| `ddev exec vendor/bin/phpunit` | то же, без обёртки composer |
| `ddev exec 'php scripts/ui-kit-pages.php'` | перегенерировать страницы UI-набора |
| `ddev logs -s web` | логи веб-контейнера |

Vite в dev-режиме поднимается сам как демон DDEV на порту 5176 (снаружи — 5175 по http и
5176 по https). В команде демона обязательны `CHOKIDAR_USEPOLLING=1 CHOKIDAR_INTERVAL=400`:
Vite следит за `vendor/bsti/bolt` и сотнями PHP-шаблонов, и без polling старт падает с
`ENOSPC` по inotify — лимиты хоста из контейнера не поднять.

Проверка страниц без DDEV (быстрее всего для шаблонов и контента):

```bash
php vendor/bin/bolt serve --host=127.0.0.1 --port=8099
# в другом терминале
curl -sS http://127.0.0.1:8099/ | head
curl -sS http://127.0.0.1:8099/compounds/rapamycin/ | head
```

Встроенный сервер BOLT отдаёт статику из `public/` и передаёт остальное фронт-контроллеру
`public/index.php`. Для этого на хосте нужен PHP 8.2+; если его нет, те же команды
запускаются через `ddev exec`.

## Структура проекта

```
longevypedia/
├── app/                        системный бандл BOLT (_app)
│   ├── client/                 main.ts, тема, фильтры каталога, поиск, styles/
│   ├── components/             ui/, doc/, layout/, site/, catalog/, ui-kit/
│   ├── fragments/              HTMX-фрагменты (search-results.php)
│   ├── icons/                  SVG-иконки проекта
│   └── pages/                  page.php (главная), document/, catalog/, research/,
│                               search/, about/, demo/, ui-kit/, error.php
├── config.php                  конфиг BOLT: контент, маршруты, Vite, SEO
├── content/en/                 корпус: <тип>/<slug>.md
├── data/ui-kit.php             каталог UI-набора (генерируется)
├── docs/                       архитектура, спецификации, разработка, дизайн
├── public/                     docroot: index.php, fonts/, build/, favicon.svg
├── schemas/                    JSON Schema на каждый тип документа
├── scripts/                    content-check.php, repo-check.php, ui-kit-pages.php
├── src/                        PHP-код: Content/, Search/, Design/
├── taxonomy/topics.php         список тематик
├── tests/                      PHPUnit
├── vite.config.js              Vite + плагин извлечения стилей BOLT
└── .ddev/                      DDEV: конфиг, Dockerfile, монтирование BOLT
```

## Формат контента

Файл — `content/<locale>/<каталог типа>/<slug>.md`. Начало файла — YAML Frontmatter,
дальше Markdown с Wikilinks.

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
relations:
  - type: related_to
    target: concept:longevity
sources:
  - citation: Partridge L, Deelen J, Slagboom PE. Facing up to the global challenges of ageing. Nature. 2018;561:45–56.
    doi: 10.1038/s41586-018-0457-8
---

## Definition

Текст статьи. Ссылка на другой документ — `[[concepts/longevity|lifespan]]`.
```

Обязательные поля: `id`, `type`, `title`, `summary`, `status`, `locale`, `created`,
`updated`, `license`. `id` обязан совпадать с `<type>:<slug>`, а slug — с именем файла.

Полная спецификация: [`docs/specifications/longevypedia-markdown.md`](docs/specifications/longevypedia-markdown.md).
Методология оценки доказательств: [`docs/specifications/methodology.md`](docs/specifications/methodology.md).

## Документация

- [`docs/architecture/overview.md`](docs/architecture/overview.md) — слои и поток запроса.
- [`docs/architecture/bolt-analysis.md`](docs/architecture/bolt-analysis.md) — как устроен BOLT и что именно использует проект.
- [`docs/architecture/content-architecture.md`](docs/architecture/content-architecture.md) — загрузка корпуса, связи, поиск.
- [`docs/architecture/application-architecture.md`](docs/architecture/application-architecture.md) — маршруты, шаблоны, сборка.
- [`docs/development/local-environment.md`](docs/development/local-environment.md) — установка с нуля и типичные проблемы.
- [`docs/development/coding-guidelines.md`](docs/development/coding-guidelines.md) — соглашения кода.
- [`docs/development/testing.md`](docs/development/testing.md) — PHPUnit и `TestClient`.
- [`docs/development/deployment.md`](docs/development/deployment.md) — сборка и переменные окружения.
- [`docs/development/fonts.md`](docs/development/fonts.md) — шрифты и подмножества.
- [`docs/design/ui-kit.md`](docs/design/ui-kit.md) — дизайн-система и компоненты.

## Лицензии

**Текст энциклопедии** распространяется по лицензии
[Creative Commons Attribution-ShareAlike 4.0 International](https://creativecommons.org/licenses/by-sa/4.0/)
(CC BY-SA 4.0). Это означает: можно копировать, распространять и перерабатывать материал,
в том числе в коммерческих целях, при условии указания авторства и сохранения той же
лицензии для производных работ. В Frontmatter каждого документа это зафиксировано полем
`license: CC-BY-SA-4.0`, а условия — в [`content/LICENSE`](content/LICENSE).

**Код** (PHP, TypeScript, SCSS, конфиги, схемы, скрипты) — MIT; это указано в
`composer.json` и `package.json`, полный текст — в [`LICENSE`](LICENSE).

**Шрифты** Inter и Source Serif 4 — SIL Open Font License 1.1, подмножества лежат
локально в `public/fonts/`. Подробности — в [`docs/development/fonts.md`](docs/development/fonts.md).

## Как участвовать

Прочитайте [`CONTRIBUTING.md`](CONTRIBUTING.md): там порядок добавления документа,
требования к источникам, правила Markdown и процесс PR. Участие регулируется
[`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md); об уязвимостях сообщайте по
[`SECURITY.md`](SECURITY.md), а не через публичный issue.

Минимальный цикл правки:

```bash
ddev composer content   # корпус валиден?
ddev composer check     # маршруты и иконки на месте?
ddev composer test      # инварианты корпуса и страниц
ddev composer repo      # гигиена репозитория
ddev exec 'bun run build'
```
