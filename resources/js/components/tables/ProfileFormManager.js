// ProfileFormManager.js
// Componente para gestionar el formulario de perfil de usuario

import FormValidator from '../forms/FormValidator';

export default class ProfileFormManager {
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

    enableFields() {
        // Permite editar los campos del perfil
        ['name', 'email', 'password'].forEach(id => {
            const input = this.form.querySelector(`#${id}`);
            if (input) input.removeAttribute('disabled');
        });
        const submitBtn = this.form.querySelector('input[type="submit"]');
        if (submitBtn) submitBtn.removeAttribute('disabled');
    }
}
