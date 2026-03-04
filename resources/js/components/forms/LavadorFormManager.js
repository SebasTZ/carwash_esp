// LavadorFormManager.js
import FormValidator from './FormValidator';

export default class LavadorFormManager {
    constructor(formElement) {
        this.validator = new FormValidator(formElement, {
            rules: {
                nombre: {
                    required: true,
                    minLength: 3,
                    maxLength: 100,
                },
                dni: {
                    required: true,
                    minLength: 8,
                    maxLength: 8,
                    pattern: /^\d+$/,
                },
                telefono: {
                    pattern: /^\d{9}$/,
                },
                estado: {
                    required: true,
                },
            },
            messages: {
                nombre: {
                    required: 'El nombre es obligatorio',
                    minLength: 'El nombre debe tener al menos 3 caracteres',
                    maxLength: 'El nombre no puede exceder 100 caracteres',
                },
                dni: {
                    required: 'El DNI es obligatorio',
                    minLength: 'El DNI debe tener 8 dígitos',
                    maxLength: 'El DNI debe tener 8 dígitos',
                    pattern: 'El DNI debe contener solo números',
                },
                telefono: {
                    pattern: 'El teléfono debe tener un formato válido (9 dígitos)',
                },
                estado: {
                    required: 'Debe seleccionar un estado',
                },
            },
            validateOnBlur: true,
            validateOnInput: false,
            showErrors: true,
        });
    }
}
