/**
 * Раскрытие выпадающего меню по наведению.
 *
 * `<details>` открывается только кликом. В шапке это читается как лишний шаг:
 * чтобы увидеть три состояния темы, надо целиком нажать на кнопку-значок,
 * хотя меню ожидают при наведении — рядом стоит такая же кнопка-ссылка.
 *
 * Модуль открывает меню при наведении и НЕ закрывает его при уходе курсора.
 * Это осознанно: между триггером и панелью есть зазор, и закрытие «по уходу»
 * требует либо моста через зазор, либо задержки — оба варианта дают мигание
 * на пути к пункту. Закрытие здесь — осознанное действие: выбранный пункт
 * (это делает сам компонент), Escape или клик мимо.
 *
 * Разметка помечается `data-hover-menu` на `<details>`: поведение включается
 * только там, где нужно, а не у каждого раскрывающегося блока на странице.
 */

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

		// Наведение мышью. На сенсорном экране `pointerenter` приходит прямо
		// перед касанием, и открывать меню заранее не нужно: там работает клик.
		menu.addEventListener('pointerenter', (event) => {
			if (event.pointerType !== 'mouse') {
				return;
			}

			menu.open = true;
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
				menu.open = false;
			}
		});

		menu.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && menu.open) {
				event.preventDefault();
				menu.open = false;
				summary.focus();
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
