import Component from '@core/Component.js';

/**
 * FormValidator Component
 * 
 * Sistema de validación de formularios con:
 * - Reglas predefinidas (required, email, minLength, maxLength, pattern, etc.)
 * - Validadores custom
 * - Validación en tiempo real o al submit
 * - Mensajes de error personalizables
 * - Integración con Bootstrap 5
 * - Múltiples modos de validación
 * 
 * @extends Component
 * 
 * @example
 * ```js
 * const validator = new FormValidator('#my-form', {
 *     rules: {
 *         email: { required: true, email: true },
 *         password: { required: true, minLength: 8 },
 *         age: { required: true, min: 18, max: 100 }
 *     },
 *     messages: {
 *         email: { required: 'El email es obligatorio' }
 *     },
 *     onValid: (formData) => console.log('Form válido:', formData),
 *     onInvalid: (errors) => console.log('Errores:', errors)
 * });
 * ```
 */
export default class FormValidator extends Component {
    /**
     * Constructor
     * @param {string|HTMLElement} selector - Selector CSS o elemento form
     * @param {Object} options - Opciones de configuración
     */
    constructor(selector, options = {}) {
        const componentOptions = typeof selector === 'string' 
            ? { selector } 
            : { element: selector };
        
        super(componentOptions);
        
        // Opciones FormValidator
        this.validatorOptions = {
            rules: {},                   // Reglas de validación por campo
            messages: {},                // Mensajes custom por campo y regla
            validateOnBlur: true,        // Validar al perder foco
            validateOnInput: false,      // Validar mientras escribe
            validateOnSubmit: true,      // Validar al submit
            showErrors: true,            // Mostrar mensajes de error
            errorClass: 'is-invalid',    // Clase CSS para campos inválidos
            successClass: 'is-valid',    // Clase CSS para campos válidos
            errorElement: 'div',         // Elemento para mensajes de error
            errorElementClass: 'invalid-feedback', // Clase para mensajes
            scrollToError: true,         // Scroll al primer error
            focusOnError: true,          // Focus en primer campo con error
            submitButton: null,          // Selector del botón submit
            disableSubmitOnInvalid: true, // Deshabilitar submit si hay errores
            // Eventos
            onValid: null,               // Callback cuando form válido
            onInvalid: null,             // Callback cuando form inválido
            onFieldValid: null,          // Callback cuando campo válido
            onFieldInvalid: null,        // Callback cuando campo inválido
            // Custom validators
            customValidators: {},        // Validadores personalizados
            ...options
        };

        // Estado interno
        this.errors = {};
        this.fields = {};
        this.isValid = false;
        this.submitBtn = null;

        // Mensajes de error por defecto
        this.defaultMessages = {
            required: 'Este campo es obligatorio',
            email: 'Ingrese un email válido',
            url: 'Ingrese una URL válida',
            number: 'Ingrese un número válido',
            integer: 'Ingrese un número entero',
            digits: 'Ingrese solo dígitos',
            minLength: 'Mínimo {0} caracteres',
            maxLength: 'Máximo {0} caracteres',
            min: 'El valor mínimo es {0}',
            max: 'El valor máximo es {0}',
            pattern: 'Formato inválido',
            equal: 'Los campos no coinciden',
            date: 'Ingrese una fecha válida',
            time: 'Ingrese una hora válida',
            alphanumeric: 'Solo letras y números',
            alpha: 'Solo letras',
            phone: 'Ingrese un teléfono válido'
        };

        // Inicializar
        this.init();
    }

    /**
     * Override mount() para evitar que Component llame a init() automáticamente
     */
    mount() {
        // No hacer nada, init() se llama manualmente en el constructor
    }

    /**
     * Inicialización del componente
     */
    init() {
        // Validar que sea un form
        if (this.element.tagName !== 'FORM') {
            throw new Error('FormValidator: El elemento debe ser un <form>');
        }

        // Prevenir submit por defecto
        this.element.setAttribute('novalidate', 'novalidate');

        // Encontrar campos con reglas
        this.findFields();

        // Attach event listeners
        this.attachListeners();

        // Buscar botón submit
        if (this.validatorOptions.submitButton) {
            this.submitBtn = document.querySelector(this.validatorOptions.submitButton);
        } else {
            this.submitBtn = this.element.querySelector('[type="submit"]');
        }

        console.log('FormValidator initialized:', {
            form: this.element.id || this.element.name,
            fields: Object.keys(this.fields).length,
            validateOnBlur: this.validatorOptions.validateOnBlur,
            validateOnInput: this.validatorOptions.validateOnInput
        });
    }

    /**
     * Encontrar campos del formulario con reglas de validación
     */
    findFields() {
        Object.keys(this.validatorOptions.rules).forEach(fieldName => {
            const field = this.element.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.fields[fieldName] = {
                    element: field,
                    rules: this.validatorOptions.rules[fieldName],
                    messages: this.validatorOptions.messages[fieldName] || {},
                    errorElement: null
                };
                
                // Crear elemento para mensajes de error
                if (this.validatorOptions.showErrors) {
                    this.createErrorElement(fieldName);
                }
            }
        });
    }

    /**
     * Crear elemento para mostrar errores
     * @param {string} fieldName - Nombre del campo
     */
    createErrorElement(fieldName) {
        const field = this.fields[fieldName];
        const errorEl = document.createElement(this.validatorOptions.errorElement);
        errorEl.className = this.validatorOptions.errorElementClass;
        errorEl.style.display = 'none';
        
        // Insertar después del campo
        field.element.parentNode.insertBefore(errorEl, field.element.nextSibling);
        field.errorElement = errorEl;
    }

    /**
     * Attach event listeners
     */
    attachListeners() {
        // Validación al submit
        if (this.validatorOptions.validateOnSubmit) {
            this.element.addEventListener('submit', (e) => {
                e.preventDefault();
                this.validate();
            });
        }

        // Validación individual de campos
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName].element;

            // Validación al perder foco
            if (this.validatorOptions.validateOnBlur) {
                field.addEventListener('blur', () => {
                    this.validateField(fieldName);
                });
            }

            // Validación mientras escribe
            if (this.validatorOptions.validateOnInput) {
                field.addEventListener('input', () => {
                    this.validateField(fieldName);
                });
            }
        });
    }

    /**
     * Validar formulario completo
     * @returns {boolean} - true si válido, false si inválido
     */
    validate() {
        this.errors = {};
        this.isValid = true;

        // Validar todos los campos
        Object.keys(this.fields).forEach(fieldName => {
            if (!this.validateField(fieldName)) {
                this.isValid = false;
            }
        });

        // Callbacks
        if (this.isValid) {
            if (this.validatorOptions.onValid) {
                this.validatorOptions.onValid(this.getFormData());
            }
        } else {
            if (this.validatorOptions.onInvalid) {
                this.validatorOptions.onInvalid(this.errors);
            }

            // Scroll al primer error
            if (this.validatorOptions.scrollToError) {
                this.scrollToFirstError();
            }

            // Focus en primer error
            if (this.validatorOptions.focusOnError) {
                this.focusFirstError();
            }
        }

        // Deshabilitar botón submit si hay errores
        if (this.validatorOptions.disableSubmitOnInvalid && this.submitBtn) {
            this.submitBtn.disabled = !this.isValid;
        }

        return this.isValid;
    }

    /**
     * Validar un campo específico
     * @param {string} fieldName - Nombre del campo
     * @returns {boolean} - true si válido, false si inválido
     */
    validateField(fieldName) {
        const field = this.fields[fieldName];
        if (!field) return true;

        const value = this.getFieldValue(field.element);
        const rules = field.rules;
        const errors = [];

        // Ejecutar cada regla
        Object.keys(rules).forEach(ruleName => {
            const ruleValue = rules[ruleName];
            const validator = this.getValidator(ruleName);

            if (validator && !validator(value, ruleValue, field.element)) {
                const message = this.getErrorMessage(fieldName, ruleName, ruleValue);
                errors.push(message);
            }
        });

        // Actualizar estado del campo
        if (errors.length > 0) {
            this.errors[fieldName] = errors;
            this.markFieldInvalid(fieldName, errors[0]);
            
            if (this.validatorOptions.onFieldInvalid) {
                this.validatorOptions.onFieldInvalid(fieldName, errors);
            }
            
            return false;
        } else {
            delete this.errors[fieldName];
            this.markFieldValid(fieldName);
            
            if (this.validatorOptions.onFieldValid) {
                this.validatorOptions.onFieldValid(fieldName, value);
            }
            
            return true;
        }
    }

    /**
     * Obtener valor de un campo
     * @param {HTMLElement} field - Elemento del campo
     * @returns {string|boolean|Array} - Valor del campo
     */
    getFieldValue(field) {
        if (field.type === 'checkbox') {
            return field.checked;
        } else if (field.type === 'radio') {
            const checked = this.element.querySelector(`[name="${field.name}"]:checked`);
            return checked ? checked.value : '';
        } else if (field.multiple) {
            return Array.from(field.selectedOptions).map(opt => opt.value);
        }
        return field.value.trim();
    }

    /**
     * Obtener validador por nombre de regla
     * @param {string} ruleName - Nombre de la regla
     * @returns {Function} - Función validadora
     */
    getValidator(ruleName) {
        // Validadores custom
        if (this.validatorOptions.customValidators[ruleName]) {
            return this.validatorOptions.customValidators[ruleName];
        }

        // Validadores predefinidos
        const validators = {
            required: (value) => {
                if (typeof value === 'boolean') return value;
                if (Array.isArray(value)) return value.length > 0;
                return value !== '' && value !== null && value !== undefined;
            },

            email: (value) => {
                if (!value) return true;
                const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return pattern.test(value);
            },

            url: (value) => {
                if (!value) return true;
                try {
                    new URL(value);
                    return true;
                } catch {
                    return false;
                }
            },

            number: (value) => {
                if (!value) return true;
                return !isNaN(parseFloat(value)) && isFinite(value);
            },

            integer: (value) => {
                if (!value) return true;
                return Number.isInteger(Number(value));
            },

            digits: (value) => {
                if (!value) return true;
                return /^\d+$/.test(value);
            },

            minLength: (value, minLength) => {
                if (!value) return true;
                return value.length >= minLength;
            },

            maxLength: (value, maxLength) => {
                if (!value) return true;
                return value.length <= maxLength;
            },

            min: (value, min) => {
                if (!value) return true;
                return Number(value) >= min;
            },

            max: (value, max) => {
                if (!value) return true;
                return Number(value) <= max;
            },

            pattern: (value, pattern) => {
                if (!value) return true;
                const regex = typeof pattern === 'string' ? new RegExp(pattern) : pattern;
                return regex.test(value);
            },

            equal: (value, targetFieldName, field) => {
                const targetField = this.element.querySelector(`[name="${targetFieldName}"]`);
                return targetField && value === targetField.value;
            },

            date: (value) => {
                if (!value) return true;
                const date = new Date(value);
                return date instanceof Date && !isNaN(date);
            },

            time: (value) => {
                if (!value) return true;
                return /^([01]\d|2[0-3]):([0-5]\d)$/.test(value);
            },

            alphanumeric: (value) => {
                if (!value) return true;
                return /^[a-zA-Z0-9]+$/.test(value);
            },

            alpha: (value) => {
                if (!value) return true;
                return /^[a-zA-Z]+$/.test(value);
            },

            phone: (value) => {
                if (!value) return true;
                // Formato: +51 999 999 999 o variaciones
                return /^[\d\s\+\-\(\)]+$/.test(value) && value.replace(/\D/g, '').length >= 9;
            }
        };

        return validators[ruleName];
    }

    /**
     * Obtener mensaje de error
     * @param {string} fieldName - Nombre del campo
     * @param {string} ruleName - Nombre de la regla
     * @param {*} ruleValue - Valor de la regla
     * @returns {string} - Mensaje de error
     */
    getErrorMessage(fieldName, ruleName, ruleValue) {
        // Mensaje custom del campo
        if (this.fields[fieldName].messages[ruleName]) {
            return this.fields[fieldName].messages[ruleName];
        }

        // Mensaje por defecto
        let message = this.defaultMessages[ruleName] || 'Campo inválido';
        
        // Reemplazar placeholders
        message = message.replace('{0}', ruleValue);
        
        return message;
    }

    /**
     * Marcar campo como inválido
     * @param {string} fieldName - Nombre del campo
     * @param {string} message - Mensaje de error
     */
    markFieldInvalid(fieldName, message) {
        const field = this.fields[fieldName];
        
        // Aplicar clases
        field.element.classList.remove(this.validatorOptions.successClass);
        field.element.classList.add(this.validatorOptions.errorClass);

        // Mostrar mensaje
        if (this.validatorOptions.showErrors && field.errorElement) {
            field.errorElement.textContent = message;
            field.errorElement.style.display = 'block';
        }
    }

    /**
     * Marcar campo como válido
     * @param {string} fieldName - Nombre del campo
     */
    markFieldValid(fieldName) {
        const field = this.fields[fieldName];
        
        // Aplicar clases
        field.element.classList.remove(this.validatorOptions.errorClass);
        field.element.classList.add(this.validatorOptions.successClass);

        // Ocultar mensaje
        if (field.errorElement) {
            field.errorElement.style.display = 'none';
        }
    }

    /**
     * Scroll al primer error
     */
    scrollToFirstError() {
        const firstErrorField = Object.keys(this.errors)[0];
        if (firstErrorField && this.fields[firstErrorField]) {
            this.fields[firstErrorField].element.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    /**
     * Focus en primer campo con error
     */
    focusFirstError() {
        const firstErrorField = Object.keys(this.errors)[0];
        if (firstErrorField && this.fields[firstErrorField]) {
            this.fields[firstErrorField].element.focus();
        }
    }

    /**
     * Obtener datos del formulario
     * @returns {Object} - Datos del formulario
     */
    getFormData() {
        const formData = new FormData(this.element);
        const data = {};

        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }

        return data;
    }

    /**
     * Limpiar errores
     */
    clearErrors() {
        this.errors = {};
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName];
            field.element.classList.remove(
                this.validatorOptions.errorClass,
                this.validatorOptions.successClass
            );
            if (field.errorElement) {
                field.errorElement.style.display = 'none';
            }
        });
    }

    /**
     * Reset formulario
     */
    reset() {
        this.element.reset();
        this.clearErrors();
        this.isValid = false;
        
        if (this.submitBtn && this.validatorOptions.disableSubmitOnInvalid) {
            this.submitBtn.disabled = false;
        }
    }

    /**
     * Agregar regla de validación dinámicamente
     * @param {string} fieldName - Nombre del campo
     * @param {string} ruleName - Nombre de la regla
     * @param {*} ruleValue - Valor de la regla
     */
    addRule(fieldName, ruleName, ruleValue) {
        if (this.fields[fieldName]) {
            this.fields[fieldName].rules[ruleName] = ruleValue;
        }
    }

    /**
     * Remover regla de validación
     * @param {string} fieldName - Nombre del campo
     * @param {string} ruleName - Nombre de la regla
     */
    removeRule(fieldName, ruleName) {
        if (this.fields[fieldName]) {
            delete this.fields[fieldName].rules[ruleName];
        }
    }

    /**
     * Agregar validador custom
     * @param {string} name - Nombre del validador
     * @param {Function} validator - Función validadora
     */
    addValidator(name, validator) {
        this.validatorOptions.customValidators[name] = validator;
    }

    /**
     * Destruir componente
     */
    destroy() {
        this.clearErrors();
        super.destroy();
    }
}
