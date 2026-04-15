import { appendHTML, clearHTML } from '@utils/dom';

const DEFAULT_EMPTY_ROW_HTML = `
    <tr>
        <th></th>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
`;

export function hasDetailItems(productos = []) {
    return productos.some((producto) => producto !== null);
}

export function startDraftAutoSave(state, intervalMs = 30000) {
    return setInterval(() => {
        if (hasDetailItems(state.productos)) {
            state.guardarEnLocalStorage();
        }
    }, intervalMs);
}

export function stopDraftAutoSave(intervalId) {
    if (intervalId) {
        clearInterval(intervalId);
    }
}

export function restoreDraftTableRows({
    productos = [],
    tableBodySelector = '#tabla_detalle tbody',
    addRow,
}) {
    if (typeof addRow !== 'function') {
        return;
    }

    clearHTML(tableBodySelector);

    productos.forEach((producto) => {
        if (producto !== null) {
            addRow(producto);
        }
    });
}

export function resetTransactionTable({
    tableBodySelector = '#tabla_detalle tbody',
    hiddenFieldSelectors = [],
    emptyRowHtml = DEFAULT_EMPTY_ROW_HTML,
}) {
    clearHTML(tableBodySelector);
    appendHTML(tableBodySelector, emptyRowHtml);

    hiddenFieldSelectors.forEach((selector) => {
        clearHTML(selector);
    });
}
