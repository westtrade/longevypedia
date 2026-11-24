/**
 * Подсказки поиска: закрытие по Escape и по клику вне списка.
 *
 * Attachment: разметку и содержимое подсказок рисует сервер (HTMX-фрагмент),
 * здесь только поведение, которого нет в CSS. Показ и скрытие пустого списка
 * делает CSS через `:empty`, поэтому JS не трогает видимость напрямую.
 */
export function initSearchSuggest(): void {
	const lists = document.querySelectorAll<HTMLElement>('[data-search-suggest]');

	if (lists.length === 0) {
		return;
	}

	document.addEventListener('keydown', (event: KeyboardEvent) => {
		if (event.key !== 'Escape') {
			return;
		}

		lists.forEach((list) => {
			list.replaceChildren();
		});
	});

	document.addEventListener('click', (event: MouseEvent) => {
		const target = event.target;

		if (!(target instanceof Node)) {
			return;
		}

		lists.forEach((list) => {
			const form = list.closest('form');

			// Клик внутри своей формы (в том числе по подсказке) список не закрывает:
			// ссылка ещё должна успеть сработать.
			if (form !== null && form.contains(target)) {
				return;
			}

			list.replaceChildren();
		});
	});
}
