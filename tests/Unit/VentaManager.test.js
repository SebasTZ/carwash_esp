import { describe, it, expect, beforeEach, vi } from 'vitest';

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
    },
}));

vi.mock('@utils/notifications', () => ({
    showSuccess: vi.fn(),
    showError: vi.fn(),
    showWarning: vi.fn(),
    showConfirm: vi.fn(),
    setButtonLoading: vi.fn(),
}));

import axios from 'axios';
import * as notifications from '@utils/notifications';
import { VentaManager } from '../../resources/js/modules/VentaManager.js';

describe('VentaManager endpoint contracts', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
    });

    it('obtenerUrlValidacionFidelizacion reemplaza placeholder con cliente', () => {
        const context = {
            endpointsConfig: {
                validarFidelizacionUrl: '/validar-fidelizacion-lavado/__cliente_id__',
            },
        };

        const url = VentaManager.prototype.obtenerUrlValidacionFidelizacion.call(context, 25);

        expect(url).toBe('/validar-fidelizacion-lavado/25');
    });

    it('obtenerUrlValidacionFidelizacion codifica cliente en URL', () => {
        const context = {
            endpointsConfig: {
                validarFidelizacionUrl: '/validar-fidelizacion-lavado/__cliente_id__',
            },
        };

        const url = VentaManager.prototype.obtenerUrlValidacionFidelizacion.call(context, 'AB 10');

        expect(url).toBe('/validar-fidelizacion-lavado/AB%2010');
    });

    it('obtenerUrlValidacionFidelizacion retorna null cuando falta configuracion', () => {
        const context = {
            endpointsConfig: {},
        };

        const url = VentaManager.prototype.obtenerUrlValidacionFidelizacion.call(context, 25);

        expect(url).toBeNull();
    });

    it('validarFidelizacionLavado revierte medio de pago y muestra error si endpoint falta', async () => {
        const setSelectFieldValue = vi.fn();
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        const context = {
            endpointsConfig: {},
            obtenerUrlValidacionFidelizacion: VentaManager.prototype.obtenerUrlValidacionFidelizacion,
            setSelectFieldValue,
            actualizarEstadoBotones: vi.fn(),
        };

        await VentaManager.prototype.validarFidelizacionLavado.call(context, 17);

        expect(axios.get).not.toHaveBeenCalled();
        expect(notifications.showError).toHaveBeenCalledWith(
            expect.stringContaining('No se encontró la configuración de endpoint para validar fidelización')
        );
        expect(setSelectFieldValue).toHaveBeenCalledWith('#medio_pago', 'efectivo');

        consoleSpy.mockRestore();
    });

    it('validarFidelizacionLavado muestra advertencia cuando cliente no tiene puntos suficientes', async () => {
        axios.get.mockResolvedValueOnce({
            data: {
                valido: false,
                mensaje: 'No alcanzó el mínimo',
                lavados_actuales: 4,
                lavados_necesarios: 10,
            },
        });

        const setSelectFieldValue = vi.fn();
        const context = {
            endpointsConfig: {
                validarFidelizacionUrl: '/validar-fidelizacion-lavado/__cliente_id__',
            },
            obtenerUrlValidacionFidelizacion: VentaManager.prototype.obtenerUrlValidacionFidelizacion,
            setSelectFieldValue,
            actualizarEstadoBotones: vi.fn(),
        };

        await VentaManager.prototype.validarFidelizacionLavado.call(context, 17);

        expect(axios.get).toHaveBeenCalledWith('/validar-fidelizacion-lavado/17', {
            headers: {
                Accept: 'application/json',
            },
        });
        expect(notifications.showWarning).toHaveBeenCalledWith(expect.stringContaining('Lavados Insuficientes'));
        expect(setSelectFieldValue).toHaveBeenCalledWith('#medio_pago', 'efectivo');
    });

    it('validarFidelizacionLavado configura estado de lavado gratis cuando la validacion es exitosa', async () => {
        axios.get.mockResolvedValueOnce({
            data: {
                valido: true,
                mensaje: 'Cliente apto para lavado gratis',
                lavados_actuales: 11,
                lavados_disponibles: 1,
            },
        });

        document.body.innerHTML = `
            <select id="cliente_id">
                <option value="17" selected>Cliente Premium</option>
            </select>
            <div id="lavado_gratis_div" style="display:none"></div>
            <div id="detalles_lavado_gratis"></div>
            <div id="campos-lavado-gratis-ocultos"></div>
            <input id="inputTotal" value="15">
            <span id="total">S/ 15.00</span>
            <span id="igv">S/ 2.70</span>
        `;

        const context = {
            endpointsConfig: {
                validarFidelizacionUrl: '/validar-fidelizacion-lavado/__cliente_id__',
            },
            obtenerUrlValidacionFidelizacion: VentaManager.prototype.obtenerUrlValidacionFidelizacion,
            setSelectFieldValue: vi.fn(),
            actualizarEstadoBotones: vi.fn(),
        };

        await VentaManager.prototype.validarFidelizacionLavado.call(context, 17);

        expect(notifications.showSuccess).toHaveBeenCalledWith(expect.stringContaining('Lavado Gratis Disponible'));
        expect(document.getElementById('lavado_gratis_div').style.display).toBe('block');
        expect(document.querySelector('input[name="lavado_gratis"]')?.value).toBe('1');
        expect(document.getElementById('inputTotal').value).toBe('0');
        expect(document.getElementById('detalles_lavado_gratis').innerHTML).toContain('Lavados acumulados');
        expect(document.getElementById('detalles_lavado_gratis').innerHTML).toContain('11');
        expect(context.actualizarEstadoBotones).toHaveBeenCalled();
    });
});
