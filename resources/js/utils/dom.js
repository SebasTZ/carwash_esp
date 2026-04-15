/**
 * Utilidades DOM ligeras para reducir dependencia de jQuery en módulos legacy.
 */

export function query(selector, root = document) {
    return root.querySelector(selector);
}

export function queryAll(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
}

export function on(targetOrSelector, eventName, handler, root = document) {
    const elements = typeof targetOrSelector === 'string'
        ? queryAll(targetOrSelector, root)
        : (targetOrSelector ? [targetOrSelector] : []);

    elements.forEach((element) => {
        element.addEventListener(eventName, handler);
    });

    return elements.length > 0;
}

export function getValue(selector, root = document) {
    const element = query(selector, root);
    return element ? element.value : '';
}

export function setValue(selector, value, root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.value = value;
}

export function getSelectedText(selector, root = document) {
    const element = query(selector, root);
    if (!element) return '';

    // Native <select> element
    if (element.tagName === 'SELECT') {
        return element.options[element.selectedIndex]?.text || '';
    }

    // x-select-search: label persistida en data attribute del input hidden
    if (element.dataset?.selectedLabel) {
        return element.dataset.selectedLabel;
    }

    // x-select-search: hidden/text input — read label from Alpine component data
    const wrapper = element.closest('[x-data]');
    if (wrapper && window.Alpine) {
        try {
            const data = window.Alpine.$data(wrapper);
            if (data && 'selectedLabel' in data) return data.selectedLabel || '';
        } catch {}
    }

    return '';
}

/**
 * Programmatically set the value and label of an x-select-search component.
 * Falls back gracefully for native <select> elements.
 * @param {string} name  — field name attribute
 * @param {string} value — value to select
 * @param {string} [label] — display label (auto-resolved from options if omitted)
 */
export function setSelectSearchValue(name, value, label = null) {
    const isSelector = /^#|^\.|\[|\s/.test(name);
    const input = isSelector
        ? document.querySelector(name)
        : document.querySelector(`[name="${CSS.escape(name)}"]`);
    if (!input) return;

    // Native <select>: just set value
    if (input.tagName === 'SELECT') {
        input.value = value;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        return;
    }

    // x-select-search wrapper
    const wrapper = input.closest('[x-data]');
    if (!wrapper || !window.Alpine) return;

    try {
        const data = window.Alpine.$data(wrapper);
        if (!data) return;

        const resolved = label
            ?? data.options?.find(o => String(o.value) === String(value))?.label
            ?? '';

        data.selected = value;
        data.selectedLabel = resolved;
        data.hasError = false;
        input.dataset.selectedLabel = resolved;

        // Notify other listeners
        input.dispatchEvent(new Event('change', { bubbles: true }));
    } catch {}
}

export function setHtml(selector, html, root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.innerHTML = html;
}

export function appendHTML(selector, html, root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.insertAdjacentHTML('beforeend', html);
}

export function clearHTML(selector, root = document) {
    setHtml(selector, '', root);
}

export function removeElement(selector, root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.remove();
}

export function showElement(selector, display = 'block', root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.style.display = display;
}

export function hideElement(selector, root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.style.display = 'none';
}

export function setRequired(selector, required = true, root = document) {
    const element = query(selector, root);
    if (!element) return;

    if (required) {
        element.setAttribute('required', 'required');
    } else {
        element.removeAttribute('required');
    }
}

export function setDisabled(selector, disabled = true, root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.disabled = disabled;
}

export function focusElement(selector, root = document) {
    const element = query(selector, root);
    if (!element) return;
    element.focus();
}

export function isChecked(selector, root = document) {
    const element = query(selector, root);
    return !!element?.checked;
}
