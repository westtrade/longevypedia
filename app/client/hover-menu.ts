/**
 * Раскрытие выпадающего меню по наведению — и закрытие, когда курсор ушёл.
 *
 * `<details>` открывается только кликом. В шапке это читается как лишний шаг:
 * чтобы увидеть три состояния темы, надо целиком нажать на кнопку-значок,
 * хотя меню ожидают при наведении — рядом стоит такая же кнопка-ссылка.
 *
 * Закрытие «по уходу» делается с задержкой. Между триггером и панелью есть
 * зазор (`inset-block-start: calc(100% + var(--space-2))`), и он не принадлежит
 * ни триггеру, ни панели: без задержки меню закрывалось бы ровно в тот момент,
 * когда курсор идёт к пункту, и список мигал бы на каждом движении. Пока
 * задержка идёт, `pointerenter` на самом `<details>` (он срабатывает и при
 * входе в потомка — то есть в панель) её отменяет.
 *
 * Разметка помечается `data-hover-menu` на `<details>`: поведение включается
 * только там, где нужно, а не у каждого раскрывающегося блока на странице.
 */

/**
 * Сколько ждать, прежде чем закрыть меню после ухода курсора. Достаточно,
 * чтобы пересечь зазор между триггером и панелью (8px), и мало, чтобы меню
 * не «висело» после того, как курсор ушёл на другую часть страницы.
 */
const CLOSE_DELAY_MS = 260;

export function initHoverMenus(): void {
	const menus = document.querySelectorAll<HTMLDetailsElement>('[data-hover-menu]');

	if (menus.length === 0) {
		return;
	}

	for (const menu of menus) {
		const summary = menu.querySelector<HTMLElement>('summary');

		if (summary === null) {
			continue;
		}

		let closeTimer: number | null = null;

		const cancelClose = (): void => {
			if (closeTimer !== null) {
				window.clearTimeout(closeTimer);
				closeTimer = null;
			}
		};

		const scheduleClose = (): void => {
			cancelClose();

			closeTimer = window.setTimeout(() => {
				closeTimer = null;
				menu.open = false;
			}, CLOSE_DELAY_MS);
		};

		// Наведение мышью. На сенсорном экране `pointerenter` приходит прямо
		// перед касанием, и открывать меню заранее не нужно: там работает клик.
		// Вход в панель — тоже вход в `<details>`, поэтому здесь же
		// отменяется отложенное закрытие.
		menu.addEventListener('pointerenter', (event) => {
			if (event.pointerType !== 'mouse') {
				return;
			}

			cancelClose();
			menu.open = true;
		});

		// Курсор ушёл с триггера и панели — закрываем, но не сразу: см.
		// CLOSE_DELAY_MS. На сенсорном экране `pointerleave` приходит после
		// касания и закрывал бы меню в момент выбора, поэтому только мышь.
		menu.addEventListener('pointerleave', (event) => {
			if (event.pointerType !== 'mouse' || !menu.open) {
				return;
			}

			scheduleClose();
		});

		// Клик по триггеру только открывает. Без этого нативное поведение
		// закрывало бы меню, уже открытое наведением: курсор над кнопкой,
		// нажатие — и список исчезает.
		summary.addEventListener('click', (event) => {
			event.preventDefault();
			menu.open = true;
		});

		// Пункт-ссылка: меню закрывается выбором, а не остаётся висеть поверх
		// страницы. Переход по ссылке всё равно случится — закрываем до него.
		menu.addEventListener('click', (event) => {
			const link = (event.target as Element | null)?.closest('a');

			if (link !== null && link !== undefined) {
				cancelClose();
				menu.open = false;
			}
		});

		menu.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && menu.open) {
				event.preventDefault();
				cancelClose();
				menu.open = false;
				summary.focus();
			}
		});

		// Закрытие по таймеру не должно сработать, если меню уже закрыли
		// другим способом, — иначе оно «мигнёт» открытым состоянием.
		menu.addEventListener('toggle', () => {
			if (!menu.open) {
				cancelClose();
			}
		});
	}

	document.addEventListener('click', (event) => {
		const target = event.target as Node | null;

		if (target === null) {
			return;
		}

		for (const menu of menus) {
			if (menu.open && !menu.contains(target)) {
				menu.open = false;
			}
		}
	});
}
