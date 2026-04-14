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
    if (!element || element.tagName !== 'SELECT') {
        return '';
    }

    return element.options[element.selectedIndex]?.text || '';
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
