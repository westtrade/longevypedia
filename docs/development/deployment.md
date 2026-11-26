# Развёртывание

Longevypedia — обычное PHP-приложение с файловым контентом: деплой сводится к доставке
файлов, сборке фронтенда и настройке docroot. Базы данных, миграций и очередей нет.

Рабочий способ выкладки — `./scripts/deploy` (бандл `bolt-deploy`, FTP), см. раздел 1а.
Сборка здесь же, на машине разработчика: на сервере нет ни bun, ни node_modules, ни
composer — уезжает готовое дерево вместе с `vendor/` и `public/build/`.

## 1. Что должно оказаться на сервере

```
<app>/                      корень проекта
├── app/                    страницы, компоненты, фрагменты, иконки
├── content/                корпус (Markdown)
├── schemas/                JSON Schema
├── taxonomy/               topics.php
├── config.php              конфиг
├── public/                 фронт-контроллер и статика (в docroot через .htaccess)
│   ├── index.php           фронт-контроллер
│   ├── fonts/              woff2
│   └── build/              результат bun run build (обязателен)
├── src/                    PHP-классы проекта
├── vendor/                 composer-зависимости, включая bsti/bolt
├── var/                    записываемый каталог (спрайт, кеш PHPUnit)
└── .bolt/client/generated/ генерируется при сборке (в рантайме не нужно)
```

Важная особенность: `bsti/bolt` подключён path-репозиторием `../bolt` с
`symlink: true`. Локально `vendor/bsti/bolt` — симлинк на соседний каталог, и на сервере
его быть не может. Выкладка это решает: `bolt-deploy` обходит проект через `find -L` и
раскрывает симлинк, поэтому BOLT уезжает обычными файлами внутри `vendor/bsti/bolt`.
Рядом с проектом на сервере ничего держать не нужно.

Если вы всё-таки ставите проект на сервер через `git clone` (а не выкладкой), соседний
клон `bolt/` обязателен — иначе `composer install` создаст висячий симлинк и приложение
не загрузится.

Что должно быть на сервере вне файлов проекта:

- интерпретатор PHP **8.4** для домена (на Джино — колонка «Интерпретатор» в настройках
  хостинга; `.envrc` с `load_prefix` на версию веб-обработчика не влияет);
- `var/` доступен на запись процессу PHP — BOLT пишет туда спрайт иконок при первом
  запросе к `/assets/sprite.svg`;
- `.env` с `APP_ENV=production` и `BOLT_DEBUG=0` (см. раздел 3).

## 1а. Выкладка на Джино

Хостинг, SSH-ключ и пароли — в файле `ДОСТУП-К-ХОСТИНГУ.md` в корне каталога проектов,
рядом с репозиторием (он не коммитится никуда: в нём пароли).

```bash
cp deploy.env.example deploy.env    # один раз, дальше файл живёт локально
$EDITOR deploy.env                  # хост, FTP-логин, пароль, адрес сайта

./scripts/deploy --dry-run          # что уедет, без сети
./scripts/deploy                    # сборка Vite, загрузка, проверка
./scripts/deploy --verify-only      # только проверка живого сайта
```

`deploy.env` в `.gitignore`: там пароль. На сервер он тоже не попадает — `bolt-deploy`
считает его служебным файлом.

Что делает `./scripts/deploy`:

1. `vite build` (кроме `--dry-run` и `--verify-only`);
2. сравнивает файлы с манифестом `var/deploy.manifest` (размер + mtime) и грузит только
   изменившееся — пачками по 200 файлов, одним соединением `curl`, пассивным режимом;
   первый прогон заливает всё (около 4,7 тыс. файлов, 17 МБ, большую часть занимают
   `vendor/bsti/bolt` и `vendor/phpunit`);
3. `DEPLOY_WRITE_ENV=1` кладёт на сервер `.env` из трёх строк `APP_ENV`, `BOLT_DEBUG`,
   `APP_URL`;
4. удаляет с сервера то, что было в прежнем манифесте, но исчезло локально;
5. проверяет `DEPLOY_PAGES` на 200, а `DEPLOY_DENY` — на 404/403, и только после этого
   обновляет манифест.

Раскладка на сервере повторяет рабочее дерево: корень домена = корень проекта,
фронт-контроллер в `public/`. Разводит их `.htaccess` из репозитория:

```apache
# существующий файл из public/ — по чистому URL (/build/…, /fonts/…)
RewriteCond %{DOCUMENT_ROOT}/public%{REQUEST_URI} -f
RewriteRule ^(.*)$ public/$1 [L]

# всё остальное — фронт-контроллер
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ public/index.php [L]
```

Три ошибки, на которых эта схема ломается:

- **редирект с `/public/…` на чистый URL.** Внутренняя подстановка `public/index.php`
  проходит правила заново, и такой редирект ловит сам себя — сайт отдаёт вечный 301;
- **условие `%{DOCUMENT_ROOT}/public$1 -f`.** Группа из шаблона правила внутрь
  `RewriteCond` не подставляется; нужно `%{REQUEST_URI}`, иначе статика отдаёт 404;
- **`Options -Indexes` без `RedirectMatch 404`.** Корень проекта совпадает с docroot,
  поэтому `config.php`, `vendor/`, `content/` и `var/` открылись бы по HTTP. Список
  закрытых путей — в `.htaccess`; `DEPLOY_DENY` проверяет его после каждой выкладки.

Проверка после первой выкладки:

```bash
for u in / /catalog /research /about /ui-kit /search /compounds/rapamycin/ /nonexistent-page; do
	printf '%-30s %s\n' "$u" "$(curl -s -o /dev/null -w '%{http_code}' "https://<домен>$u")"
done
# 200 … 200, последний — 404

for u in /config.php /vendor/autoload.php /var/ /content/en /schemas /taxonomy; do
	printf '%-25s %s\n' "$u" "$(curl -s -o /dev/null -w '%{http_code}' "https://<домен>$u")"
done
# 404 или 403
```

TLS и HTTPS-редирект включаются в панели на уровне домена (`requestDomainSSLCert` →
`enableDomainHTTPSRedirect`), а не в коде. До выпуска сертификата домен отдаётся с
сертификатом родительской зоны, и браузер ругается
`ERR_CERT_COMMON_NAME_INVALID` — `curl` при этом отвечает 200, так что проверять надо
именно браузером.

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
php vendor/bin/phpunit               # OK (125 tests, 679 assertions)

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
- [ ] docroot — корень проекта, а `.htaccess` разводит чистые URL по `public/`
      (плоская раскладка «содержимое public/ в корень» ломает пути к манифесту Vite,
      шрифтам и спрайту);
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
- **Нет миграций и очередей.** Деплой — пофайловая загрузка по FTP с манифестом. Он не
  атомарен: между загрузкой `public/index.php` и нового `app/` сайт может на секунду
  оказаться в промежуточном состоянии. Для текущего объёма (4,7 тыс. файлов, 17 МБ)
  это секунды, но при частых релизах нужен приём «залить в новый каталог и
  переключить симлинк».
- **Манифест живёт на машине, а не на сервере.** `var/deploy.manifest` описывает, что
  уже доставлено; если выложить с другой машины, первый прогон зальёт всё заново.
- **Внешние запросы отсутствуют.** Шрифты локальные, аналитики нет, CDN нет — это
  осознанно (приватность и предсказуемость).
- **BOLT — соседний репозиторий.** Обновление BOLT — отдельный деплой; зафиксируйте
  коммит BOLT, чтобы прод не поехал на чужой правке.

## 8. Откат

1. Вернуть предыдущий коммит проекта (`git checkout <sha>`), при необходимости — коммит
   BOLT.
2. `bun install --frozen-lockfile`, если менялись зависимости.
3. `bun run build` — манифест и хеши должны соответствовать коду.
4. `./scripts/deploy` — выкладка заливает изменившееся и удаляет то, что исчезло
   локально (по манифесту `var/deploy.manifest`).
5. `./scripts/deploy --verify-only` — открыть главную и страницу документа.

Контент откатывается обычным revert'ом: он в Git, отдельных снапшотов нет. Если
сломался только контент, `bun run build` не нужен — достаточно вернуть `content/`
и выложить.

Отдельный случай — когда сломался сам `vendor/`: `git checkout <sha>` не вернёт
зависимости, их надо переустановить (`bun install`, `composer install`), иначе на
сервер уедет смесь версий.

## 9. Связанные документы

- [`local-environment.md`](local-environment.md) — как это же устроено локально.
- [`testing.md`](testing.md) — что именно проверяют гейты.
- [`fonts.md`](fonts.md) — шрифты как статика.
- [`../architecture/application-architecture.md`](../architecture/application-architecture.md) — маршруты и сборка.
