import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
    },
}));

vi.mock('bootstrap', () => ({
    Modal: {
        getInstance: vi.fn(() => ({
            hide: vi.fn(),
        })),
    },
}));

vi.mock('@utils/notifications', () => ({
    showError: vi.fn(),
    showSuccess: vi.fn(),
}));

vi.mock('@utils/csrf', () => ({
    getCsrfToken: () => 'csrf-test-token',
}));

import axios from 'axios';
import * as notifications from '@utils/notifications';
import { EstacionamientoManager } from '../../resources/js/modules/EstacionamientoManager.js';

describe('EstacionamientoManager', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        window.history.replaceState({}, '', '/estacionamiento');

        const endpoints = {
            indexUrl: '/estacionamiento',
            registrarSalidaUrl: '/estacionamiento/__estacionamiento__/registrar-salida',
        };

        document.body.innerHTML = `
            <script type="application/json" id="estacionamiento-endpoints-config">${JSON.stringify(endpoints)}</script>
            <div id="estacionamiento-table-wrapper">
                <table class="table table-striped"><tbody><tr><td>old</td></tr></tbody></table>
            </div>

            <div class="modal" id="modalResumenSalida"></div>
            <form id="formRegistrarSalida"></form>

            <span id="resumen-placa"></span>
            <span id="resumen-cliente"></span>
            <span id="resumen-entrada"></span>
            <span id="resumen-salida"></span>
            <span id="resumen-tiempo"></span>
            <span id="resumen-tarifa"></span>
            <span id="resumen-subtotal"></span>
            <span id="resumen-pago-adelantado"></span>
            <span id="resumen-total"></span>
            <div id="resumen-pago-adelantado-div"></div>
        `;
    });

    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('usa data-action del backend para construir la accion de registrar salida', () => {
        const manager = new EstacionamientoManager();
        const modal = document.getElementById('modalResumenSalida');
        const form = document.getElementById('formRegistrarSalida');

        const trigger = document.createElement('button');
        trigger.setAttribute('data-id', '15');
        trigger.setAttribute('data-action', '/estacionamiento/15/registrar-salida');
        trigger.setAttribute('data-placa', 'ABC-123');
        trigger.setAttribute('data-cliente', 'Cliente Demo');
        trigger.setAttribute('data-entrada', '17/04/2026 08:30');
        trigger.setAttribute('data-tarifa', '8');
        trigger.setAttribute('data-pagado', '0');

        const event = new Event('show.bs.modal');
        Object.defineProperty(event, 'relatedTarget', {
            value: trigger,
            configurable: true,
        });

        modal.dispatchEvent(event);

        expect(form.action.endsWith('/estacionamiento/15/registrar-salida')).toBe(true);
        expect(notifications.showError).not.toHaveBeenCalled();

        manager.destroy();
    });

    it('usa endpoint configurado cuando el boton no incluye data-action', () => {
        const manager = new EstacionamientoManager();
        const modal = document.getElementById('modalResumenSalida');
        const form = document.getElementById('formRegistrarSalida');

        const trigger = document.createElement('button');
        trigger.setAttribute('data-id', 'AB 12');
        trigger.setAttribute('data-placa', 'ABC-123');
        trigger.setAttribute('data-cliente', 'Cliente Demo');
        trigger.setAttribute('data-entrada', '17/04/2026 08:30');
        trigger.setAttribute('data-tarifa', '8');
        trigger.setAttribute('data-pagado', '0');

        const event = new Event('show.bs.modal');
        Object.defineProperty(event, 'relatedTarget', {
            value: trigger,
            configurable: true,
        });

        modal.dispatchEvent(event);

        expect(form.action.endsWith('/estacionamiento/AB%2012/registrar-salida')).toBe(true);
        expect(notifications.showError).not.toHaveBeenCalled();

        manager.destroy();
    });

    it('muestra error cuando no existe ruta para registrar salida', () => {
        const manager = new EstacionamientoManager();
        manager.endpointsConfig = {};

        const modal = document.getElementById('modalResumenSalida');
        const form = document.getElementById('formRegistrarSalida');
        const initialAction = form.action;
        const trigger = document.createElement('button');

        trigger.setAttribute('data-id', '99');
        trigger.setAttribute('data-placa', 'XYZ-999');
        trigger.setAttribute('data-cliente', 'Cliente Sin Ruta');
        trigger.setAttribute('data-entrada', '17/04/2026 08:30');
        trigger.setAttribute('data-tarifa', '8');
        trigger.setAttribute('data-pagado', '0');

        const event = new Event('show.bs.modal');
        Object.defineProperty(event, 'relatedTarget', {
            value: trigger,
            configurable: true,
        });

        modal.dispatchEvent(event);

        expect(notifications.showError).toHaveBeenCalledWith('No se encontró la ruta para registrar la salida del vehículo.');
        expect(form.action).toBe(initialAction);

        manager.destroy();
    });

    it('refresca el wrapper de tabla cuando backend retorna html parcial', async () => {
        axios.get.mockResolvedValueOnce({
            data: {
                html: '<div id="estacionamiento-table-wrapper"><table class="table table-striped"><tbody><tr><td>nuevo</td></tr></tbody></table></div>',
            },
        });

        const manager = new EstacionamientoManager();
        await manager.refrescarTabla();

        const wrapper = document.getElementById('estacionamiento-table-wrapper');
        expect(wrapper.innerHTML).toContain('nuevo');
        expect(axios.get).toHaveBeenCalledTimes(1);

        manager.destroy();
    });

    it('refresca usando indexUrl configurado y query string actual', async () => {
        window.history.replaceState({}, '', '/estacionamiento?estado=ocupado&pagina=2');

        axios.get.mockResolvedValueOnce({
            data: {
                html: '<div id="estacionamiento-table-wrapper"><table class="table table-striped"><tbody><tr><td>query</td></tr></tbody></table></div>',
            },
        });

        const manager = new EstacionamientoManager();
        await manager.refrescarTabla();

        expect(axios.get).toHaveBeenCalledWith('/estacionamiento?estado=ocupado&pagina=2', expect.objectContaining({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        }));

        manager.destroy();
    });
});
