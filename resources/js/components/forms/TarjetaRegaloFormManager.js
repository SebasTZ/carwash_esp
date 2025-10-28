// TarjetaRegaloFormManager.js
// Componente para gestionar el formulario de creación/edición de tarjetas de regalo

import FormValidator from './FormValidator';

export default class TarjetaRegaloFormManager {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        if (!this.form) return;
        this.validator = new FormValidator(this.form);
        this.initListeners();
    }

    initListeners() {
        if (!this.form) return;
        this.form.addEventListener('submit', (e) => {
            if (!this.validator.validate()) {
                e.preventDefault();
            }
        });
    }

    // Métodos adicionales para lógica específica de tarjetas de regalo
    setDefaultFechaVenta() {
        const fechaVenta = this.form.querySelector('#fecha_venta');
        if (fechaVenta) {
            fechaVenta.value = new Date().toISOString().slice(0, 10);
        }
    }

    setClienteOpcional() {
        const clienteSelect = this.form.querySelector('#cliente_id');
        if (clienteSelect) {
            clienteSelect.value = '';
        }
    }
}
