/**
 * Точка входа Longevypedia.
 *
 * Порядок импортов задаёт каскад:
 *
 * 1. `generated/main` — barrel, который собирает плагин `component-assets` из
 *    `<style lang="scss">` и `<script lang="ts">` шаблонов (компоненты Bolt
 *    и встроенный ui-kit). Файл генерируется, руками не правится, лежит в
 *    `.bolt/` и в Git не попадает.
 * 2. `styles/main.scss` — оформление проекта. Идёт вне `@layer`, поэтому
 *    перекрывает библиотечные слои без `!important`.
 * 3. Поведение страниц — attachments, каждое на свою готовую разметку.
 */

import '../../.bolt/client/generated/main/index';

// htmx 4 — серверные фрагменты. Импорт пакетом, а не вендорингом: версия
// фиксируется в package.json, а `htmx.org` кладёт себя в `window.htmx`.
import 'htmx.org';

import './styles/main.scss';

import { initTheme } from './theme';
import { initCatalogFilters } from './catalog-filters';
import { initSearchSuggest } from './search-suggest';

initTheme();
initCatalogFilters();
initSearchSuggest();
