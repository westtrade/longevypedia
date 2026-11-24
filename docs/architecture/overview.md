# Архитектура: общая картина

Longevypedia — монолитное PHP-приложение на BOLT без базы данных. Корпус документов
лежит в Git как Markdown, читается с диска, индексируется в памяти и рендерится
шаблонами Plates. Клиентская часть собирается Vite; в браузере работают htmx для
фрагментов и небольшой набор TS-модулей.

Документ описывает слои и поток запроса. Детали — в соседних файлах:

- [`bolt-analysis.md`](bolt-analysis.md) — что даёт BOLT и где его границы;
- [`content-architecture.md`](content-architecture.md) — модель документа, загрузка, связи, поиск;
- [`application-architecture.md`](application-architecture.md) — маршруты, шаблоны, сборка, ошибки.

## Слои

```
content/en/<тип>/<slug>.md      Markdown + YAML Frontmatter (источник истины)
        │
        ├── Frontmatter          разбор YAML, даты остаются строками
        ├── SchemaValidator      opis/json-schema, схемы из schemas/
        ▼
ContentRepository                парсит, строит индексы, рендерит Markdown, считает backlinks
        │                        (лениво, один раз на запрос)
        ├── MarkdownRenderer     CommonMark + GFM + WikilinkExtension
        ├── NativeSearchEngine   обратный индекс в памяти (SearchEngine)
        ▼
Document                         неизменяемый объект: meta, html, toc, links, unresolved
        │
        ├── DocumentRoutes       urlpatterns: каталоги и страницы документов
        ├── app/pages/*          страницы: выбирают данные и вызывают layout
        ▼
Plates-шаблоны                   components:: (ui/, doc/, layout/, site/), app://, bolt://
        │
        ├── AssetExtractingEngine  вынимает <style lang="scss"> и <script lang="ts">
        ▼
HTML + /build/assets/*.css|js    Vite: generated barrel + app/client/main.ts
```

Отдельно от контента живёт оформление:

```
app/client/styles/_tokens.scss   роли: цвета, типографика, отступы, радиусы, раскладка
        ├── _base.scss           сброс, сетка страницы, фокус, примитивы
        ├── _prose.scss          оформление статьи (.prose)
        └── компоненты           <style lang="scss"> внутри app/components/**/*.php
```

## Кто за что отвечает

| Путь | Ответственность |
| --- | --- |
| `public/index.php` | фронт-контроллер: автозагрузка, `Application::boot()`, `Kernel::run()` |
| `config.php` | единственный конфиг: контент, маршруты, Vite, SEO, репозиторий |
| `src/Content/Frontmatter.php` | разбор YAML-блока, нормализация дат и переводов строк |
| `src/Content/Document.php` | неизменяемая модель документа и доступ к полям Frontmatter |
| `src/Content/DocumentTypes.php` | 13 типов: каталог, метка, группа, схема |
| `src/Content/RelationTypes.php` | 17 имён связей; 10 канонических рёбер и их обратные |
| `src/Content/DocumentRoutes.php` | генерация `urlpatterns` из типов и локалей |
| `src/Content/SchemaValidator.php` | валидация Frontmatter по JSON Schema |
| `src/Content/ContentRepository.php` | загрузка корпуса, индексы, поиск, связи, статистика |
| `src/Content/Markdown/*` | CommonMark, Wikilink-расширение, оглавление, unresolved-ссылки |
| `src/Search/*` | интерфейс `SearchEngine`, наивный движок, `SearchHit` |
| `src/Design/*` | токены из SCSS, расчёт контраста, список иконок |
| `app/pages/**` | страницы: маршрут → данные → `layout` + `start('page')` |
| `app/components/**` | компоненты: разметка, параметры, стили в том же файле |
| `app/fragments/**` | HTMX-фрагменты, отдаваемые без каркаса страницы |
| `schemas/*.schema.json` | контракт Frontmatter; правится вместе с контентом |
| `taxonomy/topics.php` | список тематик: слаг, метка, описание |
| `scripts/content-check.php` | гейт корпуса для локальной проверки и CI |
| `scripts/ui-kit-pages.php` | генератор страниц UI-набора и `data/ui-kit.php` |

## Поток запроса

1. **Точка входа.** `public/index.php` подключает `vendor/autoload.php`, вызывает
   `Bolt\Core\Application::boot(dirname(__DIR__))` и `(new Kernel($app))->run()`.
   `Application` находит `config.php`, поднимает бандлы и сервисы.
2. **Бандлы.** Каталог `app/` автоматически становится системным бандлом `_app` — в
   конфиге нет ключа `bundles`. Отдельного `app/bundle.php` не требуется. Порядок
   бандлов задаёт приоритет шаблонов; `_app` всегда выше остальных.
3. **Ядро выбирает обработчик** (`bolt/src/Core/Http/Kernel.php`):
   PHP-эндпоинт в `public/` → фрагменты `?_fragments=...` → страница (явные
   `urlpatterns`, затем файловая структура) → fallback → страница ошибки.
4. **Маршрут страницы.** Для документа срабатывает маршрут из
   `DocumentRoutes::definitions()`: `/compounds/<slug:slug>/` с `dir => 'document'`,
   `type => 'compound'`, `locale => 'en'`. Для главной, каталога, поиска, `/about`,
   `/demo`, `/ui-kit` работают файловые страницы `app/pages/<slug>/page.php`.
5. **Данные.** Страница вызывает `longevypedia_content()` — репозиторий из контейнера
   BOLT (`Runtime::app()->make('longevypedia.content', ...)`). Первое обращение запускает
   загрузку корпуса; дальше ответы берутся из памяти. Если документа нет,
   `app/pages/document/page.php` бросает `PageNotFoundException` → 404.
6. **Рендер.** Страница вызывает `$this->layout('components::layout/site-layout', [...])`,
   кладёт тело в `start('page')`, layout печатает `section('page')` и вставляет шапку,
   подвал, SEO-блок и `vite_assets(['app/client/main.ts'], '/build')`.
7. **Извлечение ассетов.** `AssetExtractingEngine` вырезает из готового HTML блоки
   `<style lang="scss">` и `<script lang="ts">` — они уже собраны Vite через
   сгенерированную точку входа. В ответе остаются только ссылки на `/build/...`.

## Почему нет базы данных

Корпус — это файлы в Git, и это осознанное решение, а не отсутствие времени:

- **История и авторство бесплатно.** Каждая правка — коммит с диффом; страница
  ссылается на файл и его историю. В БД для этого понадобились бы отдельные таблицы
  ревизий и UI для них.
- **Ревью — это PR.** Схемы, контент и код проверяются одним процессом; нет «редактора»
  с правами, которого надо администрировать.
- **Деплой — это checkout.** Нет миграций, дампов, восстановления и рассинхронизации
  между окружениями.
- **Чтение дешевле записи.** Сайт только читает: корпус парсится один раз на запрос,
  индексы строятся в памяти. При десятках тысяч документов это перестанет быть верным —
  см. ограничения ниже.

Следствия, которые надо учитывать:

- нет пользователей, комментариев, закладок и счётчика просмотров — «популярное»
  считается по числу связей в графе, а не по посещениям;
- нет персистентного кеша: каждый запрос парсит и рендерит корпус заново (в пределах
  запроса результат переиспользуется);
- поиск живёт в памяти процесса и не переживёт роста корпуса на порядок;
- контент нельзя править через веб-интерфейс.

## Где проходят границы

**Реализовано:** 13 типов документов, YAML Frontmatter с валидацией по схемам, Wikilinks
с разрешением и отчётом о незакрытых, типизированные связи и вычисляемые обратные
ссылки, каталог с фильтрами и HTMX-обновлением, поиск с весами полей и префиксами,
локали в модели и маршрутах, UI-набор с проверкой контраста, локальные шрифты,
страницы ошибок 404/405/500, CLI-проверки.

**Не реализовано:** база данных, аутентификация и редактирование через браузер,
комментарии, загрузка изображений, полнотекстовый поиск через внешний движок,
персистентный кеш, автоматический деплой, CI-workflow, вторые локали с переводом
корпуса. Каждый пункт — точка расширения, а не скрытая часть; см.
[`content-architecture.md`](content-architecture.md) и
[`../development/deployment.md`](../development/deployment.md).

## Связанные документы

- [`bolt-analysis.md`](bolt-analysis.md) — архитектура BOLT, используемые возможности, ограничения.
- [`content-architecture.md`](content-architecture.md) — три прохода загрузки, идентификаторы, поиск.
- [`application-architecture.md`](application-architecture.md) — таблица маршрутов, шаблоны, ошибки.
- [`../specifications/longevypedia-markdown.md`](../specifications/longevypedia-markdown.md) — формат файла.
- [`../development/local-environment.md`](../development/local-environment.md) — запуск.
