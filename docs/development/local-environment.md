# Локальное окружение с нуля

Инструкция для машины, на которой ещё нет ни проекта, ни зависимостей. Всё, что нужно
на хосте, — Docker, DDEV и git. PHP, Composer, Bun и Node на хосте не требуются: они
живут внутри контейнера.

## 1. Что понадобится

| Компонент | Зачем | Проверка |
| --- | --- | --- |
| Docker (Engine или Desktop) | контейнеры DDEV | `docker info` |
| DDEV | окружение проекта | `ddev version` |
| git | два репозитория | `git --version` |
| Свободные порты | 80/443 (сайт), 5175/5176 (Vite) | `ss -ltn \| grep -E ':(80\|443\|5175\|5176)'` |

Docker должен быть запущен **до** `ddev start`, иначе DDEV не найдёт демон. На Linux
пользователь обычно добавляется в группу `docker`; на macOS/Windows достаточно
запущенного Docker Desktop.

Отдельно: DDEV пишет глобальные настройки и кеш в `~/.ddev`. Домашний каталог должен быть
доступен на запись — в средах с read-only home запускайте DDEV с записываемым `HOME`
(например, `HOME=/tmp/ddev-home ddev start`), иначе старт упадёт на записи
конфигурации.

## 2. Два клона рядом

Проект ожидает BOLT как соседний каталог: `composer.json` подключает его
path-репозиторием `../bolt` с `symlink: true`.

```bash
mkdir -p ~/work && cd ~/work

git clone https://github.com/westtrade/longevypedia.git
git clone <URL-репозитория-BOLT> bolt

ls -la
# bolt/  longevypedia/
```

Имя второго каталога должно быть именно `bolt` (или путь в `composer.json` нужно
поменять). Внутри контейнера эта связка работает благодаря
`.ddev/docker-compose.bolt.yaml`:

```yaml
services:
  web:
    volumes:
      - "${DDEV_APPROOT}/../bolt:/var/www/bolt:cached"
```

То есть `vendor/bsti/bolt` → `../../bolt` разрешается в `/var/www/bolt`, который смонтирован
с хоста. Если BOLT лежит в другом месте, симлинк внутри контейнера повиснет.

## 3. Первый запуск

```bash
cd ~/work/longevypedia
ddev start
```

При первом старте DDEV собирает образ веб-контейнера по
`.ddev/web-build/Dockerfile` и ставит Bun 1.4.2:

```dockerfile
ARG BASE_IMAGE
FROM $BASE_IMAGE
RUN npm install -g bun@1.4.2 && bun --version
```

Bun нужен внутри контейнера, потому что `bun install` и `bun run build` должны
выполняться в том же окружении, что и Vite (`sass-embedded`, `esbuild` и их платформенные
бинарники).

Дальше — зависимости и сборка:

```bash
ddev composer install
ddev exec 'bun install && bun run build'
```

Проверьте адрес:

```bash
ddev describe
```

В выводе будет `https://longevypedia.localhost`. Откройте его в браузере. Первый ответ
может быть медленным: PHP прогревает opcache, а Vite — зависимости.

`ddev config` запускать **не нужно**: `.ddev/config.yaml` уже в репозитории (имя
`longevypedia`, PHP 8.4, nginx-fpm, docroot `public`, MariaDB 11.8, Node 24,
`project_tld: localhost`). Если запустить `ddev config` в этом каталоге, он может
перезаписать настройки — не делайте этого без причины.

## 4. Проверка установки

```bash
ddev exec php -v
# PHP 8.4.x (cli) ...

ddev exec 'php vendor/bin/bolt check'
# OK: 1 бандл(ов), 26 маршрут(ов)
# иконок: 27 (источников: 3, режим: file)

ddev exec 'php scripts/content-check.php'
# Контент: /var/www/html/content
# Языки: en
# en: 10 документов
# ...
# Контент в порядке.
```

Проверка конкретной страницы без DDEV (быстрее для шаблонов и контента):

```bash
php vendor/bin/bolt serve --host=127.0.0.1 --port=8099
# в другом терминале
curl -sS http://127.0.0.1:8099/ | head -20
curl -sS http://127.0.0.1:8099/compounds/rapamycin/ | head -40
```

Для этого на хосте нужен PHP 8.2+ (и установленный `vendor/`, который появился после
`ddev composer install`). Встроенный сервер отдаёт статику из `public/`, остальное
передаёт в `public/index.php`. Если PHP на хосте нет — те же проверки через
`ddev exec`.

## 5. Ежедневная работа

| Задача | Команда |
| --- | --- |
| Поднять окружение | `ddev start` |
| Остановить | `ddev stop` |
| Перезапустить после правки `.ddev/*` | `ddev restart` |
| Логи веб-контейнера (включая Vite) | `ddev logs -s web` |
| Логи базы | `ddev logs -s db` |
| Shell в контейнере | `ddev ssh` |
| Проверка корпуса | `ddev composer content` |
| Маршруты, бандлы, иконки | `ddev composer check` |
| Тесты | `ddev composer test` |
| Гигиена репозитория | `ddev composer repo` |
| Сборка фронтенда | `ddev exec 'bun run build'` |
| Генерация страниц UI-кита | `ddev exec 'php scripts/ui-kit-pages.php'` |

Vite поднимается автоматически как демон DDEV:

```yaml
web_extra_daemons:
  - name: vite
    command: "CHOKIDAR_USEPOLLING=1 CHOKIDAR_INTERVAL=400 node_modules/.bin/vite --host 0.0.0.0"
    directory: /var/www/html
web_extra_exposed_ports:
  - name: vite
    container_port: 5176
    http_port: 5175
    https_port: 5176
```

Порты: внутри контейнера 5176, снаружи 5175 (http) и 5176 (https). Разные порты для
протоколов нужны потому, что роутер DDEV не умеет отдавать два протокола на одном порту.

`CHOKIDAR_USEPOLLING=1` и `CHOKIDAR_INTERVAL=400` обязательны: Vite следит за
`vendor/bsti/bolt` и сотнями PHP-шаблонов, а лимиты inotify заданы на хосте и из
контейнера не поднимаются. Без polling старт падает с `ENOSPC`.

## 6. Типичные проблемы

### `ddev start` падает с `ENOSPC` / `System limit for number of file watchers reached`

Это inotify. Проверьте, что в команде демона Vite остались
`CHOKIDAR_USEPOLLING=1 CHOKIDAR_INTERVAL=400`; не «оптимизируйте» их удаление.
Дополнительно можно уменьшить область наблюдения, но менять лимиты хоста из контейнера
нельзя. После правки `.ddev/config.yaml` — `ddev restart`.

### `~/.ddev` только для чтения

Симптом: DDEV не может записать глобальный конфиг или кеш, старт падает на
`permission denied`. Решение — запускать с записываемым `HOME`:

```bash
HOME=/tmp/ddev-home ddev start
```

или смонтировать `~/.ddev` как volume в контейнеризованной среде разработки.

### Порт 5176 или 5175 занят

Симптом: Vite не стартует, в логах `EADDRINUSE`. Порты заданы в двух местах, менять нужно
согласованно:

- `.ddev/config.yaml` → `web_extra_exposed_ports` (внешние порты);
- `config.php` → `vite.port` (или переменная `VITE_PORT`) — этот порт BOLT подставляет в
  `vite_assets()`.

После правки — `ddev restart`. Не меняйте только `vite.config.js`: порт там читается из
`boltVitePort()`, то есть из конфига проекта.

### `vendor/bsti/bolt` — битый симлинк

Симптом: 500 на автозагрузке, `ls -l vendor/bsti/bolt` показывает цель, которой нет.
Причины: BOLT склонирован не рядом, переименован или не смонтирован в контейнер.

```bash
ls -l vendor/bsti/bolt          # должно указывать на ../../bolt
ls -d ../bolt                   # каталог должен существовать
ddev composer install           # пересоздаст ссылку
ddev restart
```

### Изменил `.ddev/config.yaml`, ничего не поменялось

DDEV читает конфиг при старте. Нужен `ddev restart`; если менялся
`.ddev/web-build/Dockerfile`, образ пересобирается при следующем старте.

### Сайт открывается, но без стилей

Проверьте `public/build/.vite/manifest.json` и логи:

```bash
ls -l public/build/.vite/manifest.json
ddev logs -s web | grep -i vite
```

В dev-режиме стили идут с dev-сервера (`https://longevypedia.localhost:5176`), в
production — из манифеста. Если манифеста нет и `APP_ENV=production`, страница останется
без ассетов, а в лог попадёт предупреждение про отсутствующую точку входа.

### Ошибка 500 с пустой страницей

`BOLT_DEBUG` по умолчанию `true`, поэтому на странице ошибки должен быть блок
`Debug details` с классом исключения и стеком. Если его нет — смотрите
`ddev logs -s web` и `var/`. Частые причины: битый Frontmatter (но он даёт 404, а не
500), ошибка в шаблоне, отсутствующий компонент.

### Медленно

- Первый запрос после старта всегда медленнее: корпус парсится на каждый запрос, кеша
  между запросами нет.
- `xdebug_enabled: false` в конфиге; не включайте Xdebug без необходимости — он
  замедляет PHP в разы.
- Проверяйте страницы через `bolt serve` на хосте: это быстрее полного DDEV-стека.

## 7. Где смотреть логи

| Что | Где |
| --- | --- |
| PHP/nginx и демоны (Vite) | `ddev logs -s web` |
| База | `ddev logs -s db` |
| Ошибки PHP | `ddev logs -s web`, `var/` |
| Проверки контента | stdout `php scripts/content-check.php` |
| Маршруты и бандлы | stdout `php vendor/bin/bolt check`, `bolt routes` |
| Диагностика Vite | `ddev logs -s web`, сообщения `Diagnostics` в ответе/логе |

Диагностика BOLT не молчит: битый манифест, отсутствующая точка входа и неоднозначные
маршруты пишутся в лог с конкретным текстом. Ищите по `vite`, `manifest`, `маршрут`.

## 8. Что дальше

- [`coding-guidelines.md`](coding-guidelines.md) — соглашения кода.
- [`testing.md`](testing.md) — тесты и проверки.
- [`deployment.md`](deployment.md) — сборка и переменные окружения.
- [`../architecture/overview.md`](../architecture/overview.md) — как всё устроено.
