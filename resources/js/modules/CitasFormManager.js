/**
 * CitasFormManager
 * Centraliza la inicialización de validaciones para formularios de citas.
 */
class CitasFormManager {
    constructor() {
        this.FormValidator = window.CarWash?.FormValidator;

        if (!this.FormValidator) {
            console.warn('[CitasFormManager] FormValidator no está disponible en window.CarWash');
            return;
        }

        this.initCreateForm();
        this.initEditForm();
    }

    initCreateForm() {
        if (!document.getElementById('citaForm')) {
            return;
        }

        const validator = new this.FormValidator('#citaForm', {
            cliente_id: {
                required: { message: 'Debe seleccionar un cliente' },
            },
            fecha: {
                required: { message: 'La fecha es obligatoria' },
            },
            hora: {
                required: { message: 'La hora es obligatoria' },
            },
        });

        validator.init();
    }

    initEditForm() {
        if (!document.getElementById('citaEditForm')) {
            return;
        }

        const validator = new this.FormValidator('#citaEditForm', {
            fecha: {
                required: { message: 'La fecha es obligatoria' },
            },
            hora: {
                required: { message: 'La hora es obligatoria' },
            },
        });

        validator.init();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.citasFormManager = new CitasFormManager();
});

export default CitasFormManager;
