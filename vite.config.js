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
				scan: boltScan({ projectRoot: __dirname })
			})
		]
	};
});
