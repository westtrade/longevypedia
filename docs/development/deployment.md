# Развёртывание

Longevypedia — обычное PHP-приложение с файловым контентом: деплой сводится к checkout,
`composer install`, сборке фронтенда и настройке docroot. Базы данных, миграций и
очередей нет.

## 1. Что должно оказаться на сервере

```
<app>/                      корень проекта
├── app/                    страницы, компоненты, фрагменты, иконки
├── content/                корпус (Markdown)
├── schemas/                JSON Schema
├── taxonomy/               topics.php
├── config.php              конфиг
├── public/                 единственный docroot
│   ├── index.php           фронт-контроллер
│   ├── fonts/              woff2
│   └── build/              результат bun run build (обязателен)
├── src/                    PHP-классы проекта
├── vendor/                 composer-зависимости, включая bsti/bolt
├── var/                    записываемый каталог (спрайт, кеш PHPUnit)
└── .bolt/client/generated/ генерируется при сборке (в рантайме не нужно)
```

Важная особенность: `bsti/bolt` подключён path-репозиторием `../bolt` с
`symlink: true`. Поэтому на сервере рядом с проектом должен лежать клон BOLT — либо
`vendor/bsti/bolt` нужно доставлять иначе (вендорить, менять репозиторий на VCS). Без
соседнего `bolt/` `composer install` создаст висячий симлинк, и приложение не
загрузится.

Минимальная последовательность:

```bash
git clone <longevypedia> app
git clone <bolt> bolt          # рядом, на одном уровне с app/
cd app
composer install --no-dev --optimize-autoloader
bun install --frozen-lockfile
bun run build
```

`bun install` и `bun run build` должны выполняться в том же окружении, где потом
отдаётся статика (одинаковые платформенные бинарники `sass-embedded`/`esbuild`), поэтому
не собирайте на macOS для Linux-сервера.

## 2. Сборка фронтенда

```bash
bun run build
```

Что происходит:

1. `vite.config.js` читает порт и режим через `boltVitePort()`/`boltScan()`.
2. Плагин `componentAssets` обходит `components/`, `pages/`, `fragments/`, извлекает
   `<style lang="scss">` и `<script lang="ts">` и генерирует
   `.bolt/client/generated/main/index.ts` (+ манифест генераций).
3. Vite собирает `app/client/main.ts` (вход из `rollupOptions.input`), включая
   сгенерированный barrel.
4. Результат — `public/build/` с хешированными именами и манифестом
   `public/build/.vite/manifest.json`.

`base` в production — `/build/`, `copyPublicDir: false` (статика из `public/` не
копируется). Манифест обязателен: именно из него `vite_assets()` берёт имена файлов.

Проверка:

```bash
test -f public/build/.vite/manifest.json && echo OK
ls public/build/assets | head
```

Если манифеста нет, страницы отдадутся без CSS/JS, а в лог попадёт предупреждение
`точки входа "app/client/main.ts" нет в манифесте Vite`. Не игнорируйте его.

## 3. Переменные окружения

| Переменная | Значение в проде | Что делает |
| --- | --- | --- |
| `APP_ENV` | `production` | определяет режим Vite; включает production-ветку `vite_assets()` |
| `BOLT_DEBUG` | `false` | убирает блок `Debug details` со стеком из страницы ошибки |
| `VITE_DEV_SERVER_URL` | **не задавать** | явный адрес dev-сервера; в проде должен отсутствовать |
| `VITE_DEV` | **не задавать** | принудительный dev-режим; в проде выключен |
| `VITE_MANIFEST_PATH` | не обязательно | путь к манифесту, если он не в `public/build/.vite/manifest.json` |
| `VITE_PORT` | не задавать | порт dev-сервера, если в конфиге нет `vite.port` |
| `APP_REPOSITORY` | URL репозитория | ссылки «source» и «history» на странице документа |
| `APP_BRANCH` | `main` | ветка в тех же ссылках |

Порядок определения режима (`bolt/src/Support/vite.php`):

1. явный `config['vite.dev']` или `VITE_DEV`;
2. `APP_ENV=production|prod` → production;
3. `APP_ENV=development|dev|local` → dev;
4. наличие `DDEV_PRIMARY_URL_WITHOUT_PORT` → dev;
5. иначе production + предупреждение в диагностику.

Отсюда практическое правило: **на проде задайте `APP_ENV=production` явно**. Если не
задать ничего, BOLT тоже выберет production, но напишет предупреждение — а если на
сервере случайно останется `DDEV_PRIMARY_URL_WITHOUT_PORT`, сайт начнёт искать Vite на
localhost.

`APP_REPOSITORY` и `APP_BRANCH` влияют только на ссылки в шапке документа; их отсутствие
не ломает сайт, но ссылки будут вести на дефолтный GitHub-адрес.

## 4. Как отдавать статику

- Docroot — только `public/`. `config.php`, `content/`, `schemas/`, `var/`, `vendor/` и
  `.bolt/` не должны быть доступны по HTTP.
- `public/index.php` — единственная точка входа PHP; остальные запросы уходят в него
  через try_files/rewrite.
- `/build/*` — файлы из манифеста, имена с хешем; их можно кешировать надолго.
- `/fonts/*.woff2` — локальные шрифты, тоже статика.
- `/assets/sprite.svg` — маршрут PHP (спрайт иконок), отдаётся с ETag и 304; не
  пытайтесь отдать его как обычный файл, его содержимое генерируется.
- Своей конфигурации кеширования в репозитории нет: заголовки `Cache-Control` для
  статики задаёт веб-сервер. Для `/build/` разумно `immutable`, для HTML — `no-cache`.

## 5. Почему прод не должен зависеть от Vite dev-сервера

В dev-режиме `vite_assets()` печатает `<script src="{dev}/@vite/client">` и модули с
dev-сервера. Если на проде останется `VITE_DEV_SERVER_URL=https://longevypedia.localhost:5176`,
браузер будет ломиться на порт 5176, которого на сервере нет: страница «загрузится», но
без стилей и с ошибками в консоли.

Признаки:

- в HTML есть `@vite/client` или `:5176`;
- в логах предупреждения Vite;
- ассеты 404.

Лечение: убрать `VITE_DEV_SERVER_URL`/`VITE_DEV`, выставить `APP_ENV=production`,
пересобрать и проверить, что в HTML появились ссылки `/build/assets/...`.

Dev-сервер в проде не нужен и не должен запускаться: он не умеет отдавать production-сборку
и требует Node-процесс рядом с PHP.

## 6. Чеклист перед релизом

```bash
# 1. Контент
php scripts/content-check.php        # «Контент в порядке.» и код 0

# 2. Маршруты, бандлы, иконки
php vendor/bin/bolt check            # OK: 1 бандл(ов), 26 маршрут(ов)

# 3. Тесты
php vendor/bin/phpunit               # OK (116 tests, 599 assertions)

# 4. Гигиена репозитория
php scripts/repo-check.php           # «Репозиторий в порядке.»

# 5. Сборка
bun run build
test -f public/build/.vite/manifest.json

# 6. Режим
echo "$APP_ENV"                      # production
echo "${VITE_DEV_SERVER_URL:-unset}" # unset
```

Дополнительно:

- [ ] `BOLT_DEBUG=false` (иначе стек исключения виден посетителям);
- [ ] docroot указывает на `public/`, а не на корень проекта;
- [ ] `var/` доступен на запись процессу PHP (спрайт и кеш);
- [ ] `content/` смонтирован только для чтения;
- [ ] HTTPS и корректный `Host`; при работе за прокси заданы `app.allowed_hosts` и
      `app.trusted_proxies` в `config.php`;
- [ ] в HTML главной есть `/build/assets/main-*.css` и `main-*.js`;
- [ ] `/compounds/rapamycin/` открывается, шрифты грузятся с `/fonts/`, спрайт — с
      `/assets/sprite.svg`;
- [ ] 404 отдаёт страницу ошибки, а не пустой экран;
- [ ] ссылки «source»/«history» ведут в правильный репозиторий и ветку.

Быстрая проверка без браузера:

```bash
curl -sS -o /dev/null -w '%{http_code}\n' https://example.org/
curl -sS https://example.org/ | grep -o '/build/assets/main-[^"]*' | head
curl -sS -o /dev/null -w '%{http_code}\n' https://example.org/does-not-exist   # 404
```

## 7. Ограничения эксплуатации

- **Нет CDN и внешнего кеша.** Статика отдаётся тем же сервером; при росте трафика
  понадобится прокси/CDN перед `public/`.
- **Нет персистентного кеша контента.** Каждый запрос парсит корпус и рендерит Markdown.
  Для текущего объёма это быстро; для десятков тысяч документов нужен кеш (см.
  `../architecture/content-architecture.md`).
- **Поиск в памяти.** Индекс строится на запрос; внешний движок не подключён.
- **Контент только для чтения.** Сайт не пишет в `content/`; редактирование — через Git.
  Права на запись нужны только `var/`.
- **Один узел.** Нет распределённых сессий и блокировок; горизонтальное масштабирование
  упрётся в файловую систему (общий `content/` и `var/`).
- **Нет миграций и очередей.** Деплой — атомарная замена каталога или `git pull` +
  сборка.
- **Внешние запросы отсутствуют.** Шрифты локальные, аналитики нет, CDN нет — это
  осознанно (приватность и предсказуемость).
- **BOLT — соседний репозиторий.** Обновление BOLT — отдельный деплой; зафиксируйте
  коммит BOLT, чтобы прод не поехал на чужой правке.

## 8. Откат

1. Вернуть предыдущий коммит проекта (`git checkout <sha>`), при необходимости — коммит
   BOLT.
2. `composer install --no-dev` (если менялись зависимости).
3. `bun run build` — манифест и хеши должны соответствовать коду.
4. Проверить главную и страницу документа.

Контент откатывается обычным revert'ом: он в Git, отдельных снапшотов нет. Если
сломался только контент, `bun run build` не нужен — достаточно вернуть `content/`.

## 9. Связанные документы

- [`local-environment.md`](local-environment.md) — как это же устроено локально.
- [`testing.md`](testing.md) — что именно проверяют гейты.
- [`fonts.md`](fonts.md) — шрифты как статика.
- [`../architecture/application-architecture.md`](../architecture/application-architecture.md) — маршруты и сборка.
