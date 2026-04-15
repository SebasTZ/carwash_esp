/**
 * VentaShowManager
 * Inicializa la tabla de detalle en la vista de detalle de venta.
 */

import DetalleVentaTable from '../components/DetalleVentaTable';
import { readJsonScript } from '@utils/json-script';

class VentaShowManager {
    constructor() {
        this.containerSelector = '#venta-detalle-table';
        this.dataElementId = 'venta-show-data';

        if (!document.querySelector(this.containerSelector)) {
            return;
        }

        this.init();
    }

    init() {
        const payload = this.getPayload();
        if (!payload) {
            return;
        }

        DetalleVentaTable.init({
            el: this.containerSelector,
            productos: payload.productos || [],
            impuesto: payload.impuesto || 0,
            servicio_lavado: Boolean(payload.servicio_lavado),
            horario_lavado: payload.horario_lavado || null,
            total: payload.total || 0,
        });
    }

    getPayload() {
        return readJsonScript(this.dataElementId, null, 'VentaShowManager');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.ventaShowManager = new VentaShowManager();
});

export default VentaShowManager;