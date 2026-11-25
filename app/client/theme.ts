/**
 * Переключатель темы.
 *
 * Attachment к готовой разметке: список состояний рисует шаблон
 * (`components::ui/theme-toggle`), модуль только меняет `data-theme` на
 * `<html>`, отмечает выбранный пункт и запоминает выбор. Цвета выбирает CSS
 * (`light-dark()`), поэтому скрипт не знает ни одного цвета — и не может
 * разойтись с оформлением.
 *
 * Три состояния, как в системных настройках: `auto` (по системе), `light`,
 * `dark`. Начальное значение ставит инлайновый скрипт в `<head>` — до первой
 * отрисовки, иначе страница мигнёт светлым на тёмной системе.
 */

const STORAGE_KEY = 'longevypedia.theme';

export type Theme = 'auto' | 'light' | 'dark';

const LABELS: Record<Theme, string> = {
	auto: 'system',
	light: 'light',
	dark: 'dark',
};

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

	for (const switcher of document.querySelectorAll<HTMLElement>('[data-theme-switch]')) {
		// `data-theme-state` показывает текущее состояние в триггере: значок,
		// подпись для скринридера и галочку у выбранного пункта.
		switcher.dataset.themeState = theme;

		const summary = switcher.querySelector<HTMLElement>('summary');

		if (summary !== null) {
			summary.setAttribute('aria-label', `Colour theme: ${LABELS[theme]}`);
		}

		for (const option of switcher.querySelectorAll<HTMLElement>('[data-theme-choice]')) {
			option.setAttribute('aria-pressed', option.dataset.themeChoice === theme ? 'true' : 'false');
		}
	}
}

function close(switcher: HTMLDetailsElement): void {
	switcher.open = false;
	switcher.querySelector<HTMLElement>('summary')?.focus();
}

export function initTheme(): void {
	const switchers = document.querySelectorAll<HTMLDetailsElement>('[data-theme-switch]');

	if (switchers.length === 0) {
		return;
	}

	apply(currentTheme());

	for (const switcher of switchers) {
		for (const option of switcher.querySelectorAll<HTMLElement>('[data-theme-choice]')) {
			option.addEventListener('click', () => {
				apply((option.dataset.themeChoice as Theme) ?? 'auto');
				// Выбор сделан — список закрывается и возвращает фокус на триггер,
				// иначе он остаётся висеть поверх страницы.
				close(switcher);
			});
		}

		switcher.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && switcher.open) {
				event.preventDefault();
				close(switcher);
			}
		});

		document.addEventListener('click', (event) => {
			const target = event.target as Node | null;

			if (switcher.open && target !== null && !switcher.contains(target)) {
				switcher.open = false;
			}
		});
	}
}
