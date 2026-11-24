/**
 * Каталог: объявление результата после HTMX-фильтрации.
 *
 * Attachment: форма и список приходят с сервера, JS не строит разметку. Нужен
 * он ровно для одного — после подмены списка сообщить счётчик скринридеру и
 * вернуть прокрутку к началу результатов. Без этого фильтрация выглядит как
 * «ничего не произошло»: изменившийся список находится ниже видимой области.
 */
export function initCatalogFilters(): void {
	const root = document.documentElement;

	if (root.dataset.catalogFiltersBound === 'true') {
		return;
	}

	root.dataset.catalogFiltersBound = 'true';

	document.addEventListener('htmx:after:swap', (event: Event) => {
		const detail = (event as CustomEvent<{ elt?: Element }>).detail;
		const swapped = detail?.elt;

		if (!(swapped instanceof Element) || swapped.id !== 'catalog-results') {
			return;
		}

		const count = swapped.querySelector('.catalog-results__count')?.textContent?.trim() ?? '';
		const status = document.querySelector<HTMLElement>('[data-catalog-status]');

		if (status !== null) {
			status.textContent = count === '' ? 'No documents match these filters.' : count;
		}

		swapped.scrollIntoView({ block: 'start', behavior: 'smooth' });
	});
}
