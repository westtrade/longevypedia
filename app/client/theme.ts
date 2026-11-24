/**
 * Переключатель темы.
 *
 * Attachment к готовой разметке: кнопку рисует шаблон, модуль только меняет
 * `data-theme` на `<html>` и запоминает выбор. Цвета выбирает CSS
 * (`light-dark()`), поэтому скрипт не знает ни одного цвета — и не может
 * разойтись с оформлением.
 *
 * Три состояния, как в системных настройках: `auto` (по системе), `light`,
 * `dark`. Начальное значение ставит инлайновый скрипт в `<head>` — до первой
 * отрисовки, иначе страница мигнёт светлым на тёмной системе.
 */

const STORAGE_KEY = 'longevypedia.theme';

export type Theme = 'auto' | 'light' | 'dark';

const ORDER: Theme[] = ['auto', 'light', 'dark'];

export function currentTheme(): Theme {
	const stored = document.documentElement.dataset.theme;

	return stored === 'light' || stored === 'dark' ? stored : 'auto';
}

function apply(theme: Theme): void {
	if (theme === 'auto') {
		delete document.documentElement.dataset.theme;
	} else {
		document.documentElement.dataset.theme = theme;
	}

	try {
		if (theme === 'auto') {
			localStorage.removeItem(STORAGE_KEY);
		} else {
			localStorage.setItem(STORAGE_KEY, theme);
		}
	} catch {
		// Приватный режим и запрет хранилища: тема просто не запомнится.
	}

	for (const button of document.querySelectorAll<HTMLButtonElement>('[data-theme-toggle]')) {
		button.setAttribute('aria-label', `Theme: ${theme === 'auto' ? 'system' : theme}. Change theme`);
		button.dataset.themeState = theme;
	}
}

export function initTheme(): void {
	const buttons = document.querySelectorAll<HTMLButtonElement>('[data-theme-toggle]');

	if (buttons.length === 0) {
		return;
	}

	apply(currentTheme());

	for (const button of buttons) {
		button.addEventListener('click', () => {
			const next = ORDER[(ORDER.indexOf(currentTheme()) + 1) % ORDER.length];
			apply(next);
		});
	}
}
