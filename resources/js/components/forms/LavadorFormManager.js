// LavadorFormManager.js
import FormValidator from './FormValidator';

export default class LavadorFormManager {
    constructor(formElement) {
        const validationRules = {
            nombre: [
                { type: 'required', message: 'El nombre es obligatorio' },
                { type: 'minLength', value: 3, message: 'El nombre debe tener al menos 3 caracteres' },
                { type: 'maxLength', value: 100, message: 'El nombre no puede exceder 100 caracteres' }
            ],
            dni: [
                { type: 'required', message: 'El DNI es obligatorio' },
                { type: 'digits', message: 'El DNI debe contener solo números' },
                { type: 'minLength', value: 8, message: 'El DNI debe tener 8 dígitos' },
                { type: 'maxLength', value: 8, message: 'El DNI debe tener 8 dígitos' }
            ],
            telefono: [
                { type: 'phone', message: 'El teléfono debe tener un formato válido (9 dígitos)' }
            ],
            estado: [
                { type: 'required', message: 'Debe seleccionar un estado' }
            ]
        };

        this.validator = new FormValidator(formElement, validationRules, {
            validateOnBlur: true,
            validateOnInput: false,
            showErrors: true
        });

        formElement.addEventListener('submit', (e) => {
            const valid = this.validator.validate();
            if (!valid) {
                e.preventDefault();
                Object.keys(validationRules).forEach(field => {
                    const input = formElement.querySelector(`[name="${field}"]`);
                    if (input) {
                        const error = this.validator.getError(field);
                        const feedback = input.nextElementSibling;
                        if (error && feedback) {
                            feedback.textContent = error;
                            input.classList.add('is-invalid');
                        } else if (feedback) {
                            feedback.textContent = '';
                            input.classList.remove('is-invalid');
                        }
                    }
                });
            } else {
                e.preventDefault();
                Object.keys(validationRules).forEach(field => {
                    const input = formElement.querySelector(`[name="${field}"]`);
                    const feedback = input?.nextElementSibling;
                    if (feedback) feedback.textContent = '';
                    if (input) input.classList.remove('is-invalid');
                });
                formElement.submit();
            }
        });
    }
}
