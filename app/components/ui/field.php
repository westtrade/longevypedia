<?php

declare(strict_types=1);

/**
 * Поле формы.
 *
 * Проектный компонент: `components::ui/field`.
 *
 * Почему не `bolt://ui-kit/field`: встроенное поле умеет только input и textarea,
 * не поддерживает select, description, обязательность и состояния выбора файла
 * и диапазона. BOLT не расширяется снаружи, поэтому поле живёт в проекте, а
 * `bolt://ui-kit/form` (CSRF, htmx, id формы) по-прежнему оборачивает его.
 *
 * Одно поле — один контрол. Подпись, описание и ошибка связаны с контролом
 * через `for`/`aria-describedby`/`aria-invalid`, поэтому скринридер читает
 * причину ошибки, а не только красную рамку.
 *
 * @param string            $name        имя поля, оно же основа id
 * @param string            $label       подпись
 * @param string            $type        text | search | email | password | url | tel | number | date | textarea | select | file | range
 * @param string|int|float  $value
 * @param string            $placeholder
 * @param string            $description подсказка под подписью
 * @param string            $error       текст ошибки; включает состояние ошибки
 * @param bool              $required
 * @param bool              $disabled
 * @param bool              $readonly
 * @param string|null       $id          по умолчанию field-<name>
 * @param int               $rows        высота textarea
 * @param array             $options     для select: value => label
 * @param string|null       $suffix      единица измерения после поля
 * @param mixed             $element     Closure для своего контрола
 * @param array             $attrs
 * @param list<string>      $describedBy дополнительные id для aria-describedby
 *                                        (счётчик подсказок, статус запроса)
 * @param bool              $labelHidden подпись только для скринридера
 */

$name ??= '';
$label ??= '';
$type ??= 'text';
$value ??= '';
$placeholder ??= '';
$description ??= '';
$error ??= '';
$required ??= false;
$disabled ??= false;
$readonly ??= false;
$id ??= 'field-' . $name;
$rows ??= 5;
$options ??= [];
$suffix ??= null;
$element ??= null;
$attrs ??= [];
$describedBy ??= [];
$labelHidden ??= false;

$id = (string) $id;
$hasError = (string) $error !== '';
$descriptionId = $id . '-description';
$errorId = $id . '-error';

// Порядок важен: сначала внешние id (их передал вызывающий), затем
// собственные описание и ошибка — скринридер читает в этом же порядке.
$describedByIds = array_values(array_filter(array_merge(
	array_map(static fn ($value): string => (string) $value, (array) $describedBy),
	[
		$description !== '' ? $descriptionId : null,
		$hasError ? $errorId : null,
	],
)));

$controlAttrs = array_merge($attrs, [
	'id' => $id,
	'name' => $name,
	'class' => clsx('ui-field__input', [
		'ui-field__input--error' => $hasError,
	]),
	'aria-invalid' => $hasError ? 'true' : null,
	'aria-describedby' => $describedByIds === [] ? null : implode(' ', $describedByIds),
	'required' => $required ?: null,
	'disabled' => $disabled ?: null,
	'readonly' => $readonly ?: null,
	'placeholder' => $placeholder !== '' ? $placeholder : null,
]);
?>
<div class="ui-field<?= $hasError ? ' ui-field--error' : '' ?><?= $disabled ? ' ui-field--disabled' : '' ?>">
	<label class="ui-field__label<?= $labelHidden ? ' visually-hidden' : '' ?>" for="<?= $this->e($id) ?>">
		<?= $this->e((string) $label) ?>
		<?php if ($required): ?>
			<span class="ui-field__required" aria-hidden="true">*</span>
		<?php endif; ?>
	</label>

	<?php if ($description !== ''): ?>
		<p class="ui-field__description" id="<?= $this->e($descriptionId) ?>"><?= $this->e((string) $description) ?></p>
	<?php endif; ?>

	<div class="ui-field__control">
		<?php if ($element !== null): ?>
			<?php render($element, ['id' => $id, 'name' => $name, 'attrs' => $controlAttrs]); ?>
		<?php elseif ($type === 'textarea'): ?>
			<textarea<?= attrs($controlAttrs) ?> rows="<?= (int) $rows ?>"><?= $this->e((string) $value) ?></textarea>
		<?php elseif ($type === 'select'): ?>
			<select<?= attrs($controlAttrs) ?>>
				<?php foreach ($options as $optionValue => $optionLabel): ?>
					<option value="<?= $this->e((string) $optionValue) ?>"<?= (string) $optionValue === (string) $value ? ' selected' : '' ?>>
						<?= $this->e((string) $optionLabel) ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php elseif ($type === 'range'): ?>
			<input<?= attrs(array_merge($controlAttrs, ['type' => 'range', 'value' => (string) $value])) ?>>
		<?php elseif ($type === 'file'): ?>
			<input<?= attrs(array_merge($controlAttrs, ['type' => 'file'])) ?>>
		<?php else: ?>
			<input<?= attrs(array_merge($controlAttrs, ['type' => $type, 'value' => (string) $value])) ?>>
		<?php endif; ?>

		<?php if ($suffix !== null): ?>
			<span class="ui-field__suffix" aria-hidden="true"><?= $this->e((string) $suffix) ?></span>
		<?php endif; ?>
	</div>

	<?php if ($hasError): ?>
		<p class="ui-field__error" id="<?= $this->e($errorId) ?>"><?= $this->e((string) $error) ?></p>
	<?php endif; ?>
</div>

<style lang="scss">
	.ui-field {
		display: flex;
		flex-direction: column;
		gap: var(--space-2);
		min-width: 0;
	}

	.ui-field__label {
		color: var(--color-text);
		font-size: var(--text-sm);
		font-weight: var(--weight-medium);
		line-height: var(--leading-snug);
	}

	.ui-field__required {
		margin-inline-start: 0.15em;
		color: var(--color-danger);
	}

	.ui-field__description {
		color: var(--color-text-muted);
		font-size: var(--text-xs);
		line-height: var(--leading-normal);
	}

	.ui-field__control {
		position: relative;
		display: flex;
		align-items: center;
	}

	/* Граница контрола — 3:1 к фону: иначе поле не видно без цвета заливки. */
	.ui-field__input {
		width: 100%;
		/* Высота настраивается снаружи переменной, а не параметром PHP:
		   компактный вариант нужен шапке, но структура поля от этого не меняется. */
		min-height: var(--ui-field-input-height, 2.5rem);
		padding: var(--space-2) var(--space-3);
		border: 1px solid var(--color-control-border);
		border-radius: var(--radius-sm);
		background: var(--color-surface);
		color: var(--color-text);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
		font-variant-numeric: tabular-nums;
		transition:
			border-color 120ms ease,
			box-shadow 120ms ease;
	}

	.ui-field__input::placeholder {
		color: var(--color-text-subtle);
	}

	.ui-field__input:hover:not(:disabled) {
		border-color: var(--color-text-muted);
	}

	.ui-field__input:focus {
		outline: none;
		border-color: var(--color-brand);
		box-shadow: var(--focus-ring-inset);
	}

	.ui-field__input:disabled {
		background: var(--color-surface-alt);
		color: var(--color-text-muted);
		cursor: not-allowed;
	}

	.ui-field__input[readonly] {
		background: var(--color-surface-alt);
	}

	.ui-field__input--error {
		border-color: var(--color-danger);
	}

	.ui-field__input--error:focus {
		box-shadow: inset 0 0 0 2px var(--color-danger);
	}

	textarea.ui-field__input {
		min-height: 7rem;
		resize: vertical;
	}

	select.ui-field__input {
		appearance: none;
		padding-inline-end: var(--space-8);
		background-image: linear-gradient(45deg, transparent 50%, currentColor 50%),
			linear-gradient(135deg, currentColor 50%, transparent 50%);
		background-position:
			calc(100% - 1.15rem) calc(50% + 1px),
			calc(100% - 0.85rem) calc(50% + 1px);
		background-size: 0.3rem 0.3rem;
		background-repeat: no-repeat;
	}

	input[type='file'].ui-field__input {
		padding: var(--space-2);
		font-size: var(--text-xs);
	}

	input[type='file'].ui-field__input::file-selector-button {
		margin-inline-end: var(--space-3);
		padding: var(--space-1) var(--space-3);
		border: 1px solid var(--color-border-strong);
		border-radius: var(--radius-sm);
		background: var(--color-surface-alt);
		color: var(--color-text);
		font: inherit;
		cursor: pointer;
	}

	input[type='range'].ui-field__input {
		min-height: 1.5rem;
		padding: 0;
		border: 0;
		background: transparent;
		accent-color: var(--color-brand);
	}

	.ui-field__suffix {
		position: absolute;
		inset-inline-end: var(--space-3);
		color: var(--color-text-muted);
		font-size: var(--text-xs);
		pointer-events: none;
	}

	.ui-field__error {
		display: flex;
		gap: var(--space-1);
		color: var(--color-danger-text);
		font-size: var(--text-xs);
		line-height: var(--leading-normal);
	}

	.ui-field__error::before {
		content: '!';
		display: inline-grid;
		place-items: center;
		width: 1rem;
		height: 1rem;
		flex: none;
		border-radius: var(--radius-full);
		background: var(--color-danger-soft);
		color: var(--color-danger-text);
		font-weight: var(--weight-bold);
	}

	.ui-field--disabled .ui-field__label,
	.ui-field--disabled .ui-field__description {
		color: var(--color-text-muted);
	}
</style>
