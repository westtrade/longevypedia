/**
 * Копирование исходника: подтверждение, что нажатие сработало.
 *
 * Буфер обмена невидим, поэтому без смены состояния кнопка ничего не сообщает:
 * пользователь не знает, скопировался код или нет. Здесь кнопка на две секунды
 * меняет подпись, цвет состояния и пишет сообщение в `role="status"` рядом —
 * смену подписи самой кнопки скринридер может не прочитать.
 *
 * Attachment: разметку и текст рисует сервер (`components::ui-kit/snippet`),
 * модуль работает только с `[data-copy-text]`.
 */

const COPIED_MS = 2000;

type State = 'idle' | 'copied' | 'error';

/**
 * Запись в буфер обмена.
 *
 * `navigator.clipboard` есть только в защищённом контексте (HTTPS или
 * localhost). По http в локальной сети он недоступен, поэтому остаётся
 * старый путь через скрытое поле и `execCommand`.
 */
async function writeClipboard(text: string): Promise<boolean> {
	if (navigator.clipboard !== undefined) {
		try {
			await navigator.clipboard.writeText(text);

			return true;
		} catch {
			// Разрешение не выдано или API недоступен — пробуем запасной путь.
		}
	}

	const field = document.createElement('textarea');
	field.value = text;
	field.setAttribute('readonly', '');
	field.style.position = 'fixed';
	field.style.top = '-1000px';
	document.body.append(field);
	field.select();

	let copied = false;

	try {
		copied = document.execCommand('copy');
	} catch {
		copied = false;
	}

	field.remove();

	return copied;
}

function setState(button: HTMLButtonElement, state: State): void {
	const labels: Record<State, string> = {
		idle: button.dataset.copyLabelIdle ?? '',
		copied: button.dataset.copyLabelCopied ?? '',
		error: button.dataset.copyLabelError ?? '',
	};

	const label = button.querySelector<HTMLElement>('.ui-button__label');

	if (label !== null) {
		label.textContent = labels[state];
	}

	if (state === 'idle') {
		delete button.dataset.copyState;
	} else {
		button.dataset.copyState = state;
	}

	const statusId = button.dataset.copyStatus ?? '';
	const status = statusId === '' ? null : document.getElementById(statusId);

	if (status !== null) {
		status.textContent = state === 'copied'
			? 'Код скопирован в буфер обмена'
			: (state === 'error' ? 'Не удалось скопировать код' : '');
	}
}

export function initCopyButtons(): void {
	const buttons = document.querySelectorAll<HTMLButtonElement>('[data-copy-text]');

	if (buttons.length === 0) {
		return;
	}

	buttons.forEach((button) => {
		let timer: number | undefined;

		button.addEventListener('click', () => {
			void (async () => {
				const copied = await writeClipboard(button.dataset.copyText ?? '');

				window.clearTimeout(timer);
				setState(button, copied ? 'copied' : 'error');
				timer = window.setTimeout(() => {
					setState(button, 'idle');
				}, COPIED_MS);
			})();
		});
	});
}
