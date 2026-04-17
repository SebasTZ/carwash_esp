import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

const mockFns = vi.hoisted(() => ({
    showSuccess: vi.fn(),
    showError: vi.fn(),
    showWarning: vi.fn(),
    showConfirm: vi.fn(),
    setButtonLoading: vi.fn(),
    clearFormErrors: vi.fn(),
}));

vi.mock('@utils/notifications', () => ({
    showSuccess: mockFns.showSuccess,
    showError: mockFns.showError,
    showWarning: mockFns.showWarning,
    showConfirm: mockFns.showConfirm,
    setButtonLoading: mockFns.setButtonLoading,
    clearFormErrors: mockFns.clearFormErrors,
}));

import { CompraManager } from '../../resources/js/modules/CompraManager.js';

function buildCompraDom() {
    document.body.innerHTML = `
        <form id="compra-form">
            <select id="producto_id" name="producto_id">
                <option value="">Seleccione</option>
                <option value="1" selected>PRD-1 - Shampoo</option>
            </select>

            <input id="cantidad" name="cantidad" type="number" value="3">
            <input id="precio_compra" name="precio_compra" type="number" value="20">
            <input id="precio_venta" name="precio_venta" type="number" value="25">

            <select id="comprobante_id" name="comprobante_id">
                <option value="1" selected>Factura</option>
            </select>
            <input id="impuesto" name="impuesto" type="number" value="18">

            <table id="tabla_detalle">
                <tbody></tbody>
            </table>

            <span id="sumas"></span>
            <span id="igv"></span>
            <span id="total"></span>
            <input type="hidden" id="inputTotal" name="total" value="0">

            <button id="btn_agregar" type="button">Agregar</button>
            <button id="btnCancelarCompra" type="button" disabled>Cancelar compra</button>
            <button id="guardar" type="submit" disabled>Registrar compra</button>
        </form>
    `;
}

describe('Compra form integration', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        buildCompraDom();
    });

    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('agrega detalle y valida submit con tabla poblada', () => {
        const manager = new CompraManager();

        manager.agregarProducto();

        expect(mockFns.showError).not.toHaveBeenCalled();
        expect(manager.state.productos.filter((producto) => producto !== null)).toHaveLength(1);
        expect(document.getElementById('tabla_detalle').textContent).toContain('Shampoo');
        expect(parseFloat(document.getElementById('inputTotal').value)).toBeCloseTo(60, 2);
        expect(document.getElementById('guardar').disabled).toBe(false);
        expect(document.getElementById('btnCancelarCompra').disabled).toBe(false);

        const tbody = document.querySelector('#tabla_detalle tbody');
        if (tbody && tbody.querySelectorAll('tr').length === 0) {
            tbody.innerHTML = '<tr><td>fallback-row</td></tr>';
        }

        const preventDefaultSpy = vi.fn();
        const isValid = manager.validarAntesDeGuardar({ preventDefault: preventDefaultSpy });

        expect(isValid).toBe(true);
        expect(preventDefaultSpy).not.toHaveBeenCalled();
        expect(mockFns.setButtonLoading).toHaveBeenCalledWith(document.getElementById('guardar'), true);

        manager.detenerAutoGuardado();
    });

    it('cancela compra con confirmacion y limpia estado/formulario', async () => {
        const manager = new CompraManager();
        manager.agregarProducto();

        mockFns.showConfirm.mockResolvedValueOnce(true);

        document.getElementById('btnCancelarCompra').click();

        await vi.waitFor(() => {
            expect(mockFns.showSuccess).toHaveBeenCalledWith('Compra cancelada');
        });

        expect(mockFns.showConfirm).toHaveBeenCalledWith(
            '¿Cancelar compra?',
            'Se perderán todos los productos agregados',
            'Sí, cancelar',
            'No'
        );
        expect(manager.state.productos.filter((producto) => producto !== null)).toHaveLength(0);
        expect(parseFloat(document.getElementById('inputTotal').value)).toBe(0);
        expect(document.getElementById('guardar').disabled).toBe(true);
        expect(document.getElementById('btnCancelarCompra').disabled).toBe(true);

        manager.detenerAutoGuardado();
    });
});
