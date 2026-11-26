/**
 * Точка входа Longevypedia.
 *
 * Порядок импортов задаёт каскад:
 *
 * 1. `styles/main.scss` — слои и базовое оформление проекта.
 * 2. `generated/main` — barrel, который собирает плагин `component-assets` из
 *    `<style lang="scss">` и `<script lang="ts">` шаблонов (компоненты Bolt
 *    и встроенный ui-kit). Файл генерируется, руками не правится, лежит в
 *    `.bolt/` и в Git не попадает.
 * 3. Поведение страниц — attachments, каждое на свою готовую разметку.
 */

// Оформление идёт первым: `@layer` объявляется до того, как BOLT добавит
// свои слои, иначе `bolt.base` окажется последним и перебьёт компоненты.
import './styles/main.scss';

import '../../.bolt/client/generated/main/index';

// htmx 4 — серверные фрагменты. Импорт пакетом, а не вендорингом: версия
// фиксируется в package.json, а `htmx.org` кладёт себя в `window.htmx`.
import 'htmx.org';

import { initHoverMenus } from './hover-menu';
import { initTheme } from './theme';
import { initCatalogFilters } from './catalog-filters';
import { initSearchSuggest } from './search-suggest';
import { initCopyButtons } from './copy-code';

/**
 * Всё поведение страницы разом.
 *
 * Модули идемпотентны: каждый помнит, на какие узлы уже повесил обработчики
 * (`WeakSet`) или какие слушатели документа уже поставил. Поэтому функцию
 * можно звать повторно — новые узлы получат поведение, старые не получат
 * второго.
 */
function initInteractivity(): void {
	initHoverMenus();
	initTheme();
	initCatalogFilters();
	initSearchSuggest();
	initCopyButtons();
}

initInteractivity();

/**
 * HTMX подменяет часть страницы, и вместе с разметкой приходят новые узлы —
 * без обработчиков. Пока в проекте нет `hx-boost`, подменяются только фрагменты
 * (результаты каталога, подсказки поиска), но полагаться на это нельзя: с
 * `hx-boost` так приезжает и шапка, а значит переключатель темы, меню и кнопка
 * копирования. Один и тот же обработчик закрывает оба случая.
 *
 * Событие то же, что слушает `catalog-filters.ts`, — `htmx:after:swap`.
 */
document.addEventListener('htmx:after:swap', initInteractivity);
