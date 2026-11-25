<?php

declare(strict_types=1);

/**
 * Флажок, радиокнопка и переключатель.
 *
 * Проектный компонент: `components::ui/choice`.
 *
 * Почему не `bolt://ui-kit/field-checkbox`: встроенный компонент умеет только
 * чекбокс, без описания, ошибки и без радио/переключателя. Один компонент на три
 * роли оправдан: разметка и стили совпадают на 90%, различается только контрол.
 *
 * @param string      $name
 * @param string      $type        checkbox | radio | switch
 * @param string      $label
 * @param string      $value
 * @param bool        $checked
 * @param string      $description
 * @param string      $error
 * @param bool        $disabled
 * @param bool        $required
 * @param string|null $id          по умолчанию choice-<name>(-<value>)
 * @param array       $attrs
 */

$name ??= '';
$type ??= 'checkbox';
$label ??= '';
$value ??= 'on';
$checked ??= false;
$description ??= '';
$error ??= '';
$disabled ??= false;
$required ??= false;
$id ??= 'choice-' . $name . ($type === 'radio' ? '-' . preg_replace('/[^a-z0-9]+/i', '-', (string) $value) : '');
$attrs ??= [];

$id = (string) $id;
$hasError = (string) $error !== '';
$descriptionId = $id . '-description';
$errorId = $id . '-error';

$describedBy = array_values(array_filter([
	$description !== '' ? $descriptionId : null,
	$hasError ? $errorId : null,
]));

$controlAttrs = array_merge($attrs, [
	'type' => $type === 'switch' ? 'checkbox' : $type,
	'id' => $id,
	'name' => $name,
	'value' => (string) $value,
	'checked' => $checked ?: null,
	'disabled' => $disabled ?: null,
	'required' => $required ?: null,
	'role' => $type === 'switch' ? 'switch' : null,
	'aria-invalid' => $hasError ? 'true' : null,
	'aria-describedby' => $describedBy === [] ? null : implode(' ', $describedBy),
]);
?>
<div class="ui-choice ui-choice--<?= $this->e((string) $type) ?><?= $hasError ? ' ui-choice--error' : '' ?>">
	<input<?= attrs($controlAttrs) ?> class="ui-choice__input">
	<label class="ui-choice__label" for="<?= $this->e($id) ?>">
		<?php /* Знак рисуется внутри контрола и центрируется сеткой: у
		         clip-path и абсолютных inset'ов центр «на глаз», и галочка
		         с точкой съезжают на пиксель-два. */ ?>
		<span class="ui-choice__control" aria-hidden="true">
			<?php if ($type === 'checkbox'): ?>
				<?= $this->insert('bolt://ui-kit/icon', ['name' => 'check', 'size' => 12, 'class' => 'ui-choice__tick']) ?>
			<?php elseif ($type === 'radio'): ?>
				<span class="ui-choice__dot"></span>
			<?php endif ?>
		</span>
		<span class="ui-choice__text">
			<span class="ui-choice__title"><?= $this->e((string) $label) ?></span>
			<?php if ($description !== ''): ?>
				<span class="ui-choice__description" id="<?= $this->e($descriptionId) ?>"><?= $this->e((string) $description) ?></span>
			<?php endif; ?>
		</span>
	</label>

	<?php if ($hasError): ?>
		<p class="ui-choice__error" id="<?= $this->e($errorId) ?>"><?= $this->e((string) $error) ?></p>
	<?php endif; ?>
</div>

<style lang="scss">
	.ui-choice {
		display: grid;
		gap: var(--space-1);
	}

	/* Нативный контрол остаётся в потоке и фокусируемым, но не рисуется:
	   вид даёт .ui-choice__control, состояние берётся из :checked/:focus-visible. */
	.ui-choice__input {
		position: absolute;
		width: 1px;
		height: 1px;
		opacity: 0;
		pointer-events: none;
	}

	.ui-choice__label {
		display: flex;
		align-items: flex-start;
		gap: var(--space-3);
		cursor: pointer;
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.ui-choice__control {
		position: relative;
		display: grid;
		place-items: center;
		flex: none;
		width: 1.125rem;
		height: 1.125rem;
		margin-top: 0.15em;
		border: 1px solid var(--color-control-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface);
		transition:
			background-color 120ms ease,
			border-color 120ms ease;
	}

	/* Знак лежит в контроле, а не рисуется clip-path'ом: так его центр задаёт
	   сетка контрола, и он не съезжает при смене размера шрифта. */
	.ui-choice__tick {
		color: var(--color-on-brand);
		opacity: 0;
		transition: opacity 120ms ease;
	}

	.ui-choice__input:checked + .ui-choice__label .ui-choice__tick {
		opacity: 1;
	}

	.ui-choice__input:checked + .ui-choice__label .ui-choice__control {
		border-color: var(--color-brand);
		background: var(--color-brand);
	}

	.ui-choice__input:focus-visible + .ui-choice__label .ui-choice__control {
		box-shadow: var(--focus-ring);
	}

	.ui-choice__input:disabled + .ui-choice__label {
		opacity: 0.55;
		cursor: not-allowed;
	}

	.ui-choice--radio .ui-choice__control {
		border-radius: var(--radius-full);
	}

	.ui-choice__dot {
		width: 0.5rem;
		height: 0.5rem;
		border-radius: var(--radius-full);
		background: var(--color-on-brand);
		opacity: 0;
		transition: opacity 120ms ease;
	}

	.ui-choice--radio .ui-choice__input:checked + .ui-choice__label .ui-choice__dot {
		opacity: 1;
	}

	.ui-choice__text {
		display: grid;
		gap: 0.1rem;
	}

	.ui-choice__title {
		color: var(--color-text);
	}

	.ui-choice__description {
		color: var(--color-text-muted);
		font-size: var(--text-xs);
	}

	.ui-choice__error {
		padding-inline-start: calc(1.125rem + var(--space-3));
		color: var(--color-danger-text);
		font-size: var(--text-xs);
	}

	/* Переключатель: трек вместо квадрата, состояние видно по положению. */
	.ui-choice--switch .ui-choice__control {
		width: 2.25rem;
		height: 1.25rem;
		margin-top: 0.1em;
		border-radius: var(--radius-full);
		background: var(--color-surface-alt);
	}

	/* Кружок центрируется по треку через 50%: при фиксированном `top` он
	   смещён на половину разницы высот трека и кружка. */
	.ui-choice--switch .ui-choice__control::before {
		content: '';
		position: absolute;
		top: 50%;
		left: 0.15rem;
		width: 0.85rem;
		height: 0.85rem;
		border-radius: var(--radius-full);
		background: var(--color-surface);
		box-shadow: var(--shadow-sm);
		transform: translateY(-50%);
		transition: transform 140ms ease;
	}

	.ui-choice--switch .ui-choice__input:checked + .ui-choice__label .ui-choice__control::after {
		content: none;
	}

	.ui-choice--switch .ui-choice__input:checked + .ui-choice__label .ui-choice__control::before {
		transform: translateY(-50%) translateX(1rem);
		background: var(--color-on-brand);
	}

	.ui-choice--error .ui-choice__control {
		border-color: var(--color-danger);
	}

	@media (prefers-reduced-motion: reduce) {
		.ui-choice--switch .ui-choice__control::before {
			transition: none;
		}
	}
</style>
