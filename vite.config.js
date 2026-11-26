import { defineConfig } from 'vite';
import path from 'path';
import { fileURLToPath } from 'url';

import componentAssets from './vendor/bsti/bolt/vite/plugins/component-assets/index.ts';
import { boltScan, boltVitePort } from './vendor/bsti/bolt/vite/plugins/component-assets/discovery.ts';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Адрес проекта в ddev без порта: из него собирается origin dev-сервера,
// чтобы браузер грузил модули с того же хоста, что и страницу.
const ddevOrigin = process.env.DDEV_PRIMARY_URL_WITHOUT_PORT;

/**
 * Сборка Longevypedia.
 *
 * Порт не задан константой: его читает `boltVitePort()` — тот же механизм,
 * что и у PHP (`vite_port()`: конфиг → `VITE_PORT` → 5176). Одно значение на
 * обе стороны, поэтому ссылки в dev-режиме не расходятся с тем, что слушает
 * Vite.
 *
 * Точка входа одна — `app/client/main.ts`. Точки входа бандлов добавляет
 * `boltEntries()`; у нас один системный бандл `_app`, и он объявлен в конфиге
 * проекта (`vite.entries`).
 */
export default defineConfig(({ command }) => {
	const port = boltVitePort({ projectRoot: __dirname });

	return {
		base: command === 'build' ? '/build/' : '/',

		// Точки перехода — SCSS-переменные в app/client/styles/_breakpoints.scss.
		// Медиазапрос не читает CSS-переменные, а стили компонентов компилируются
		// из .bolt/client/generated/ отдельными файлами, где относительный @use не
		// нашёл бы этот файл. Поэтому подставляем его в каждую единицу компиляции
		// через loadPaths + additionalData: значения остаются в одном месте.
		css: {
			preprocessorOptions: {
				scss: {
					loadPaths: [path.resolve(__dirname, 'app/client/styles')],
					additionalData: '@use "breakpoints" as *;\n'
				}
			}
		},

		build: {
			manifest: true,
			copyPublicDir: false,
			outDir: 'public/build',
			emptyOutDir: true,
			rollupOptions: {
				input: {
					main: path.resolve(__dirname, 'app/client/main.ts')
				}
			}
		},

		server: {
			host: '0.0.0.0',
			port,
			strictPort: true,
			origin: ddevOrigin ? `${ddevOrigin}:${port}` : `http://localhost:${port}`,
			cors: {
				origin: '*'
			},
			watch: {
				ignored: [
					'**/node_modules/**',
					'**/vendor/**',
					'**/public/build/**',
					'**/var/**',
					'**/.git/**',
					'**/.ddev/**'
				]
			},
			fs: {
				// Пакет Bolt лежит по симлинку vendor/bsti/bolt → ../bolt,
				// то есть вне корня проекта: без явного разрешения Vite
				// откажется отдавать из него модули плагина.
				allow: ['.', 'vendor/bsti/bolt']
			}
		},

		plugins: [
			componentAssets({
				// Скрытые генерации Bolt: стили и скрипты, вырезанные из
				// шаблонов. Каталог в .gitignore, руками не правится.
				outputDir: '.bolt/client',
				// `uiKit: false` — встроенный набор Bolt в сборку не идёт.
				// Причина не в размере, хотя размер и показателен: единственный
				// скрипт набора (`ui-kit/modal.php`) импортирует Alpine, и через
				// barrel тот попадал в бандл целиком — 69 КБ из 96, при том
				// что `Alpine.start()` в проекте не вызывается ни разу.
				// Стили набора тоже лишние: у проекта свой набор (`app/
				// components/ui`), а классы Bolt (`.btn`, `.modal`, `.card`)
				// в разметке не встречаются. Компоненты набора при этом
				// остаются доступны как PHP: `bolt://ui-kit/icon` — это
				// разметка без ассетов, и она работает как раньше.
				scan: boltScan({ projectRoot: __dirname, uiKit: false })
			})
		]
	};
});
