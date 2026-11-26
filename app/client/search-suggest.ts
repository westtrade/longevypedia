/**
 * Подсказки поиска: закрытие по Escape и по клику вне списка.
 *
 * Attachment: разметку и содержимое подсказок рисует сервер (HTMX-фрагмент),
 * здесь только поведение, которого нет в CSS. Показ и скрытие пустого списка
 * делает CSS через `:empty`, поэтому JS не трогает видимость напрямую.
 */
/**
 * Обработчики висят на документе, поэтому ставятся один раз: повторный вызов
 * после подмены фрагмента не должен удваивать их.
 */
let bound = false;

/** Списки спрашиваются в момент события, а не при инициализации. */
function clearLists(): void {
	for (const list of document.querySelectorAll<HTMLElement>('[data-search-suggest]')) {
		list.replaceChildren();
	}
}

/**
 * Можно звать повторно (в том числе после подмены фрагмента HTMX): сами
 * обработчики ставятся однажды, а списки ищутся заново на каждое событие.
 * Иначе подсказки, подставленные фрагментом, Escape уже не закрывал бы —
 * захваченный при инициализации список к тому моменту отсоединён от страницы.
 */
export function initSearchSuggest(): void {
	if (bound) {
		return;
	}

	bound = true;

	document.addEventListener('keydown', (event: KeyboardEvent) => {
		if (event.key !== 'Escape') {
			return;
		}

		clearLists();
	});

	document.addEventListener('click', (event: MouseEvent) => {
		const target = event.target;

		if (!(target instanceof Node)) {
			return;
		}

		for (const list of document.querySelectorAll<HTMLElement>('[data-search-suggest]')) {
			const form = list.closest('form');

			// Клик внутри своей формы (в том числе по подсказке) список не закрывает:
			// ссылка ещё должна успеть сработать.
			if (form !== null && form.contains(target)) {
				continue;
			}

			list.replaceChildren();
		}
	});
}
