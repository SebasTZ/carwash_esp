import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

const mockFns = vi.hoisted(() => ({
    hideModal: vi.fn(),
    showError: vi.fn(),
    showSuccess: vi.fn(),
}));

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
    },
}));

vi.mock('bootstrap', () => ({
    Modal: {
        getInstance: vi.fn(() => ({
            hide: mockFns.hideModal,
        })),
    },
}));

vi.mock('@utils/notifications', () => ({
    showError: mockFns.showError,
    showSuccess: mockFns.showSuccess,
}));

vi.mock('@utils/csrf', () => ({
    getCsrfToken: () => 'csrf-integration-token',
}));

import axios from 'axios';
import { EstacionamientoManager } from '../../resources/js/modules/EstacionamientoManager.js';

describe('Estacionamiento form integration', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        window.history.replaceState({}, '', '/estacionamiento');

        const endpoints = {
            indexUrl: '/estacionamiento',
            registrarSalidaUrl: '/estacionamiento/__estacionamiento__/registrar-salida',
        };

        document.body.innerHTML = `
            <script type="application/json" id="estacionamiento-endpoints-config">${JSON.stringify(endpoints)}</script>
            <div id="estacionamiento-table-wrapper"></div>

            <div class="modal" id="modalResumenSalida"></div>
            <form id="formRegistrarSalida" method="POST"></form>

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

    it('ejecuta flujo completo modal a submit exitoso con endpoint configurado', async () => {
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

        const showEvent = new Event('show.bs.modal');
        Object.defineProperty(showEvent, 'relatedTarget', {
            value: trigger,
            configurable: true,
        });

        modal.dispatchEvent(showEvent);

        expect(form.action).toContain('/estacionamiento/AB%2012/registrar-salida');
        expect(document.getElementById('resumen-placa').textContent).toBe('ABC-123');

        axios.post.mockResolvedValueOnce({ status: 200 });

        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));

        await vi.waitFor(() => {
            expect(axios.post).toHaveBeenCalledTimes(1);
        });

        expect(axios.post).toHaveBeenCalledWith(
            expect.stringContaining('/estacionamiento/AB%2012/registrar-salida'),
            { _token: 'csrf-integration-token' }
        );
        expect(mockFns.showSuccess).toHaveBeenCalledWith('Salida registrada correctamente');
        expect(mockFns.hideModal).toHaveBeenCalled();

        manager.destroy();
    });

    it('muestra error de backend cuando el submit falla', async () => {
        const manager = new EstacionamientoManager();
        const modal = document.getElementById('modalResumenSalida');
        const form = document.getElementById('formRegistrarSalida');
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        const trigger = document.createElement('button');
        trigger.setAttribute('data-id', '77');
        trigger.setAttribute('data-action', '/estacionamiento/77/registrar-salida');
        trigger.setAttribute('data-placa', 'ERR-777');
        trigger.setAttribute('data-cliente', 'Cliente Error');
        trigger.setAttribute('data-entrada', '17/04/2026 08:30');
        trigger.setAttribute('data-tarifa', '8');
        trigger.setAttribute('data-pagado', '0');

        const showEvent = new Event('show.bs.modal');
        Object.defineProperty(showEvent, 'relatedTarget', {
            value: trigger,
            configurable: true,
        });

        modal.dispatchEvent(showEvent);

        axios.post.mockRejectedValueOnce({
            response: {
                data: {
                    message: 'Salida inválida',
                },
            },
        });

        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));

        await vi.waitFor(() => {
            expect(mockFns.showError).toHaveBeenCalledWith('Salida inválida');
        });

        consoleSpy.mockRestore();
        manager.destroy();
    });
});
