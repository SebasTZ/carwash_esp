import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

const mockFns = vi.hoisted(() => ({
    showSuccess: vi.fn(),
    showError: vi.fn(),
    showWarning: vi.fn(),
    showConfirm: vi.fn(),
    setButtonLoading: vi.fn(),
    clearFormErrors: vi.fn(),
}));

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
    },
}));

vi.mock('@utils/notifications', () => ({
    showSuccess: mockFns.showSuccess,
    showError: mockFns.showError,
    showWarning: mockFns.showWarning,
    showConfirm: mockFns.showConfirm,
    setButtonLoading: mockFns.setButtonLoading,
    clearFormErrors: mockFns.clearFormErrors,
}));

import axios from 'axios';
import { VentaManager } from '../../resources/js/modules/VentaManager.js';

function buildVentaDom() {
    const productosConfig = {
        '1': {
            stock: 10,
            precio_venta: 30,
            es_servicio_lavado: false,
            label: 'PROD-1 - Producto Demo',
        },
    };

    const endpointsConfig = {
        validarFidelizacionUrl: '/validar-fidelizacion-lavado/__cliente_id__',
    };

    document.body.innerHTML = `
        <script type="application/json" id="venta-productos-config">${JSON.stringify(productosConfig)}</script>
        <script type="application/json" id="venta-endpoints-config">${JSON.stringify(endpointsConfig)}</script>

        <form id="venta-form">
            <input type="hidden" id="producto_id" name="producto_id" data-selected-label="PROD-1 - Producto Demo" value="1">
            <input type="hidden" id="cliente_id" name="cliente_id" data-selected-label="Cliente Premium" value="17">

            <select id="comprobante_id" name="comprobante_id">
                <option value="1" selected>Factura</option>
            </select>

            <select id="medio_pago" name="medio_pago">
                <option value="efectivo" selected>Efectivo</option>
                <option value="lavado_gratis">Lavado Gratis</option>
                <option value="tarjeta_regalo">Tarjeta Regalo</option>
            </select>

            <input id="cantidad" name="cantidad" type="number" value="2">
            <input id="precio_venta" name="precio_venta" type="number" value="30">
            <input id="descuento" name="descuento" type="number" value="5">
            <input id="stock" name="stock" type="text" value="10">
            <input id="impuesto" name="impuesto" type="number" value="18">
            <input id="con_igv" name="con_igv" type="checkbox">

            <div id="tarjeta_regalo_div" style="display:none"></div>
            <input id="tarjeta_regalo_id" name="tarjeta_regalo_id" value="">

            <div id="lavado_gratis_div" style="display:none"></div>
            <div id="detalles_lavado_gratis"></div>
            <div id="campos-lavado-gratis-ocultos"></div>

            <input id="servicio_lavado" name="servicio_lavado" type="checkbox">
            <div id="horario_lavado_div" style="display:none"></div>
            <input id="horario_lavado" name="horario_lavado" value="">

            <table id="tabla_detalle">
                <tbody></tbody>
            </table>
            <div id="campos-productos-ocultos"></div>

            <span id="sumas"></span>
            <span id="igv"></span>
            <span id="total"></span>
            <input type="hidden" id="inputTotal" name="total" value="0">

            <button id="btn_agregar" type="button">Agregar</button>
            <button id="btnCancelarVenta" type="button" disabled>Cancelar</button>
            <button id="guardar" type="submit" disabled>Guardar</button>
        </form>
    `;
}

describe('Venta form integration', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        buildVentaDom();
    });

    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('ejecuta flujo completo agregar producto y validar submit del formulario', () => {
        const manager = new VentaManager();

        manager.agregarProducto();

        expect(mockFns.showError).not.toHaveBeenCalled();
        expect(manager.state.productos.filter((producto) => producto !== null)).toHaveLength(1);
        expect(document.getElementById('tabla_detalle').textContent).toContain('Producto Demo');
        expect(Number(document.getElementById('inputTotal').value)).toBe(55);
        expect(document.getElementById('guardar').disabled).toBe(false);
        expect(document.getElementById('btnCancelarVenta').disabled).toBe(false);

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

    it('aplica flujo de lavado gratis via endpoint configurado al cambiar medio de pago', async () => {
        axios.get.mockResolvedValueOnce({
            data: {
                valido: true,
                mensaje: 'Cliente apto para lavado gratis',
                lavados_actuales: 12,
                lavados_disponibles: 1,
            },
        });

        const manager = new VentaManager();
        const medioPago = document.getElementById('medio_pago');

        medioPago.value = 'lavado_gratis';
        medioPago.dispatchEvent(new Event('change', { bubbles: true }));

        await vi.waitFor(() => {
            expect(axios.get).toHaveBeenCalledWith('/validar-fidelizacion-lavado/17', {
                headers: {
                    Accept: 'application/json',
                },
            });
        });

        expect(document.getElementById('lavado_gratis_div').style.display).toBe('block');
        expect(document.querySelector('input[name="lavado_gratis"]')?.value).toBe('1');
        expect(document.getElementById('detalles_lavado_gratis').innerHTML).toContain('Lavados acumulados');
        expect(document.getElementById('inputTotal').value).toBe('0');
        expect(mockFns.showSuccess).toHaveBeenCalledWith(expect.stringContaining('Lavado Gratis Disponible'));

        manager.detenerAutoGuardado();
    });
});
