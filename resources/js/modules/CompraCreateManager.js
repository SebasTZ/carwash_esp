/**
 * CompraCreateManager
 * Inicializa el formulario de compra usando datos serializados desde Blade.
 */
import { readJsonScript } from '@utils/json-script';

class CompraCreateManager {
    constructor() {
        this.containerId = 'formCompraContainer';
        this.payloadId = 'compra-form-data';

        this.CompraForm = window.CarWash?.CompraForm;
        this.FormValidator = window.CarWash?.FormValidator;

        if (!document.getElementById(this.containerId)) {
            return;
        }

        if (!this.CompraForm) {
            console.warn('[CompraCreateManager] CompraForm no está disponible en window.CarWash');
            return;
        }

        this.init();
    }

    init() {
        const payload = this.getPayload();
        if (!payload) {
            return;
        }

        new this.CompraForm({
            elementId: this.containerId,
            productos: payload.productos || [],
            proveedores: payload.proveedores || [],
            comprobantes: payload.comprobantes || [],
            impuesto: payload.impuesto ?? 18,
            old: payload.old || {},
            errors: payload.errors || [],
            action: payload.action || '',
            method: payload.method || 'POST',
            onFormReady: (form) => {
                if (!this.FormValidator) {
                    return;
                }

                new this.FormValidator(form, {
                    validateOnInput: false,
                });
            },
        });
    }

    getPayload() {
        return readJsonScript(this.payloadId, null, 'CompraCreateManager');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.compraCreateManager = new CompraCreateManager();
});

export default CompraCreateManager;
