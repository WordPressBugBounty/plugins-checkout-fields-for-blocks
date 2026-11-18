const updateUploadField = (field, focusFileInput = false) => {
	const fileInput = field.querySelector('input[type="file"]');
	if (!fileInput) {
		return;
	}
	const hasReplacement = fileInput.files.length > 0;
	const removeFile = field.querySelector('[data-remove-file]');
	if (!removeFile) {
		return;
	}

	const filename = field.querySelector('[data-upload-filename]');
	const removeValue = field.querySelector('[data-remove-value]');
	const hasExistingFile = removeFile.dataset.hasExistingFile === '1';
	const removing = removeValue && hasExistingFile && !hasReplacement && removeValue.value === '1';
	filename.hidden = hasReplacement || removing;
	const hideRemove = removing || (!hasReplacement && !hasExistingFile);
	const removeHadFocus = removeFile.ownerDocument.activeElement === removeFile;
	const uploadRow = field.querySelector('[data-upload-row]');
	const uploadChoice = field.querySelector('[data-upload-choice]');
	uploadRow.hidden = !hasExistingFile || hasReplacement || removing;
	(hasReplacement ? uploadChoice : uploadRow).appendChild(removeFile);
	removeFile.hidden = hideRemove;
	if (removeValue) {
		removeValue.value = removing ? '1' : '';
	}
	if (focusFileInput && removeHadFocus) {
		(hideRemove ? fileInput : removeFile).focus();
	}
};

document.addEventListener('change', (event) => {
	if (event.target.type !== 'file') {
		return;
	}

	const field = event.target.closest('.cffb-order-fields__edit-field');
	if (!field) {
		return;
	}
	updateUploadField(field);
});

document.addEventListener('click', (event) => {
	const removeFile = event.target.closest('[data-remove-file]');
	if (removeFile) {
		const field = removeFile.closest('.cffb-order-fields__edit-field');
		const fileInput = field.querySelector('input[type="file"]');
		const removeValue = field.querySelector('[data-remove-value]');
		fileInput.value = '';
		if (removeValue && removeFile.dataset.hasExistingFile === '1') {
			removeValue.value = '1';
		}
		updateUploadField(field, true);
		return;
	}

	const toggle = event.target.closest('.cffb-order-fields__toggle');
	if (!toggle) {
		return;
	}

	const container = toggle.closest('.cffb-order-fields');
	const view = container.querySelector('.cffb-order-fields__view');
	const editor = container.querySelector('.cffb-order-fields__editor');
	const fieldset = editor.querySelector('fieldset');
	const mode = editor.querySelector('[name="cffb_order_fields_editing"]');
	const editing = toggle.getAttribute('aria-expanded') === 'true';

	if (editing) {
		// The rendered HTML carries the original values, so cancel restores defaults.
		fieldset.querySelectorAll('input, select, textarea').forEach((control) => {
			if (control.type === 'file' || control.hasAttribute('data-remove-value')) {
				control.value = '';
				return;
			}
			if (control.type === 'select-one' || control.type === 'select-multiple') {
				Array.from(control.options).forEach((option) => {
					option.selected = option.defaultSelected;
				});
				return;
			}
			control.value = control.defaultValue;
			if ('checked' in control) {
				control.checked = control.defaultChecked;
			}
		});
		fieldset.querySelectorAll('.cffb-order-fields__edit-field').forEach((field) => updateUploadField(field));
		fieldset.disabled = true;
		mode.disabled = true;
		editor.hidden = true;
		view.hidden = false;
		toggle.setAttribute('aria-expanded', 'false');
		toggle.textContent = toggle.dataset.editLabel;
		return;
	}

	mode.disabled = false;
	fieldset.disabled = false;
	editor.hidden = false;
	view.hidden = true;
	toggle.setAttribute('aria-expanded', 'true');
	toggle.textContent = toggle.dataset.cancelLabel;
});
