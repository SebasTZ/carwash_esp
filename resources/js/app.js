import './bootstrap';
import $ from 'jquery';
import Swal from 'sweetalert2';
import Alpine from 'alpinejs';
import { getCsrfToken, withCsrfHeader } from './utils/csrf';
window.$ = $;
window.jQuery = $;
window.Swal = Swal;
window.Alpine = Alpine;

// Inicializar window.Laravel para compatibilidad con componentes legacy
window.Laravel = {
    csrfToken: getCsrfToken(),
};
// Bootstrap Select se carga vía módulos ES cuando una vista lo requiere.
// import 'bootstrap-select/dist/js/bootstrap-select.min.js';

/**
 * CarWash ESP - Frontend Application
 * Entry point principal de la aplicación
 */

// ========================================
// Importar utilidades globales
// ========================================
import * as Notifications from './utils/notifications';
import * as Validators from './utils/validators';
import * as Formatters from './utils/formatters';
import * as BootstrapInit from './utils/bootstrap-init';
import * as LazyLoader from './utils/lazy-loader';
import * as Dom from './utils/dom';

// ========================================
// Importar componentes modernos
// ========================================
import DynamicTable from './components/tables/DynamicTable.js';
import CompraForm from './modules/CompraForm.js';
import AutoSave from './components/forms/AutoSave.js';
import FormValidator from './components/forms/FormValidator.js';
import LavadorTableManager from './components/tables/LavadorTableManager.js';
import LavadorFormManager from './components/forms/LavadorFormManager.js';
import LavadorEditFormManager from './components/forms/LavadorEditFormManager.js';
import './modules/LegacyInlineMigration.js';

// ========================================
// Exportar utilidades al objeto window
// para uso en código inline existente (migración gradual)
// ========================================
window.CarWash = {
    // Notificaciones
    showSuccess: Notifications.showSuccess,
    showError: Notifications.showError,
    showWarning: Notifications.showWarning,
    showInfo: Notifications.showInfo,
    showConfirm: Notifications.showConfirm,
    showDeleteConfirm: Notifications.showDeleteConfirm,
    showLoading: Notifications.showLoading,
    hideLoading: Notifications.hideLoading,
    showModal: Notifications.showModal,
    setButtonLoading: Notifications.setButtonLoading,
    showFieldError: Notifications.showFieldError,
    clearFieldError: Notifications.clearFieldError,
    clearFormErrors: Notifications.clearFormErrors,
    
    // Validaciones
    validateStock: Validators.validateStock,
    validatePrecio: Validators.validatePrecio,
    validateDescuento: Validators.validateDescuento,
    validateFecha: Validators.validateFecha,
    validateRangoFechas: Validators.validateRangoFechas,
    validateEmail: Validators.validateEmail,
    validateRUC: Validators.validateRUC,
    validateDNI: Validators.validateDNI,
    validatePlaca: Validators.validatePlaca,
    validateTelefono: Validators.validateTelefono,
    validateTableNotEmpty: Validators.validateTableNotEmpty,
    validateForm: Validators.validateForm,
    validateRequired: Validators.validateRequired,
    sanitizeString: Validators.sanitizeString,
    isPositive: Validators.isPositive,
    isNonNegative: Validators.isNonNegative,
    isInRange: Validators.isInRange,
    
    // Formateo
    formatCurrency: Formatters.formatCurrency,
    formatNumber: Formatters.formatNumber,
    formatDate: Formatters.formatDate,
    formatDateTime: Formatters.formatDateTime,
    formatDateInput: Formatters.formatDateInput,
    formatRelativeTime: Formatters.formatRelativeTime,
    formatPercentage: Formatters.formatPercentage,
    formatRUC: Formatters.formatRUC,
    formatTelefono: Formatters.formatTelefono,
    capitalize: Formatters.capitalize,
    formatFileSize: Formatters.formatFileSize,
    formatPlaca: Formatters.formatPlaca,
    parseCurrency: Formatters.parseCurrency,
    
    // Bootstrap
    initTooltips: BootstrapInit.initTooltips,
    initPopovers: BootstrapInit.initPopovers,
    initBootstrapSelect: BootstrapInit.initBootstrapSelect,
    refreshBootstrapSelect: BootstrapInit.refreshBootstrapSelect,
    setBootstrapSelectValue: BootstrapInit.setBootstrapSelectValue,
    toggleBootstrapSelect: BootstrapInit.toggleBootstrapSelect,
    initDataTable: BootstrapInit.initDataTable,
    showBsModal: BootstrapInit.showModal,
    hideBsModal: BootstrapInit.hideModal,
    showTab: BootstrapInit.showTab,
    toggleCollapse: BootstrapInit.toggleCollapse,
    initFormValidation: BootstrapInit.initFormValidation,
    clearFormValidation: BootstrapInit.clearFormValidation,
    
    // Lazy Loading
    initLazyImages: LazyLoader.initLazyImages,
    initLazyIframes: LazyLoader.initLazyIframes,
    preloadImage: LazyLoader.preloadImage,

    // DOM helpers
    query: Dom.query,
    queryAll: Dom.queryAll,
    on: Dom.on,
    getValue: Dom.getValue,
    setValue: Dom.setValue,
    getSelectedText: Dom.getSelectedText,
    setHtml: Dom.setHtml,
    appendHTML: Dom.appendHTML,
    clearHTML: Dom.clearHTML,
    removeElement: Dom.removeElement,
    showElement: Dom.showElement,
    hideElement: Dom.hideElement,
    setRequired: Dom.setRequired,
    setDisabled: Dom.setDisabled,
    focusElement: Dom.focusElement,
    isChecked: Dom.isChecked,
    
    // Componentes modernos
    DynamicTable: DynamicTable,
    AutoSave: AutoSave,
    FormValidator: FormValidator,
    CompraForm: CompraForm,
    // Lavadores
    LavadorTableManager: LavadorTableManager,
    LavadorFormManager: LavadorFormManager,
    LavadorEditFormManager: LavadorEditFormManager,

    openActionModal: ({
        modalId,
        title = null,
        titleId = null,
        bodyId = null,
        formId = null,
        methodInputId = null,
        confirmButtonId = null,
        message = '¿Desea continuar con esta acción?',
        action = '#',
        method = 'POST',
        confirmText = 'Confirmar',
        confirmClass = 'btn btn-danger',
    }) => {
        if (!modalId || !window.bootstrap?.Modal) {
            return;
        }

        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            return;
        }

        const modalTitle = document.getElementById(titleId || `${modalId}Label`);
        const modalBody = document.getElementById(bodyId || `${modalId}Body`);
        const formElement = document.getElementById(formId || `${modalId}Form`);
        const methodInput = document.getElementById(methodInputId || `${modalId}Method`) || formElement?.querySelector('input[name="_method"]');
        const confirmButton = document.getElementById(confirmButtonId || `${modalId}ConfirmButton`);

        if (title && modalTitle) {
            modalTitle.textContent = title;
        }

        if (modalBody) {
            modalBody.textContent = message;
        }

        if (formElement) {
            formElement.action = action;
        }

        if (methodInput) {
            methodInput.value = String(method || 'POST').toUpperCase();
        }

        if (confirmButton) {
            confirmButton.textContent = confirmText;
            confirmButton.className = confirmClass;
        }

        const modal = new window.bootstrap.Modal(modalElement);
        modal.show();
    },
};

// ========================================
// Alpine.js — stores globales
// ========================================
Alpine.store('notifications', {
    items: [],
    add(type, message) {
        const id = Date.now();
        this.items.push({ id, type, message });
        setTimeout(() => this.remove(id), 4000);
    },
    remove(id) {
        this.items = this.items.filter(n => n.id !== id);
    },
});

// ========================================
// Inicialización global de la aplicación
// ========================================
document.addEventListener('DOMContentLoaded', () => {
    const confirmModalElement = document.getElementById('globalConfirmModal');
    const confirmModalMessage = document.getElementById('globalConfirmModalMessage');
    const confirmModalTitle = document.getElementById('globalConfirmModalLabel');
    const confirmModalAccept = document.getElementById('globalConfirmModalAccept');
    let pendingConfirmAction = null;

    if (confirmModalElement && confirmModalAccept && window.bootstrap?.Modal) {
        const confirmModal = new window.bootstrap.Modal(confirmModalElement);

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-confirm]');
            if (!trigger) {
                return;
            }

            const form = trigger.closest('form');
            const href = trigger.getAttribute('href');
            if (!form && !href) {
                return;
            }

            event.preventDefault();

            const message = trigger.getAttribute('data-confirm') || '¿Desea continuar con esta acción?';
            const title = trigger.getAttribute('data-confirm-title') || 'Confirmar acción';
            const confirmText = trigger.getAttribute('data-confirm-confirm-text') || 'Confirmar';
            const confirmClass = trigger.getAttribute('data-confirm-confirm-class') || 'btn btn-danger';

            confirmModalTitle.textContent = title;
            confirmModalMessage.textContent = message;
            confirmModalAccept.textContent = confirmText;
            confirmModalAccept.className = confirmClass;

            pendingConfirmAction = () => {
                if (form) {
                    form.submit();
                    return;
                }

                if (href) {
                    window.location.href = href;
                }
            };

            confirmModal.show();
        });

        confirmModalAccept.addEventListener('click', () => {
            if (typeof pendingConfirmAction === 'function') {
                pendingConfirmAction();
                pendingConfirmAction = null;
            }
        });

        confirmModalElement.addEventListener('hidden.bs.modal', () => {
            pendingConfirmAction = null;
        });
    }

    // Los componentes Bootstrap se inicializan automáticamente
    // desde bootstrap-init.js
    
    // Configuración global de Axios
    if (window.axios) {
        // Interceptor para mostrar loading en requests
        window.axios.interceptors.request.use(
            config => {
                // Agregar token CSRF si existe
                config.headers = withCsrfHeader(config.headers || {});
                return config;
            },
            error => {
                return Promise.reject(error);
            }
        );
        
        // Interceptor para manejar errores globales
        window.axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response) {
                    switch (error.response.status) {
                        case 401:
                            Notifications.showError('Sesión expirada. Por favor, inicia sesión nuevamente.');
                            setTimeout(() => {
                                window.location.href = '/login';
                            }, 2000);
                            break;
                        case 403:
                            Notifications.showError('No tienes permisos para realizar esta acción.');
                            break;
                        case 404:
                            Notifications.showError('Recurso no encontrado.');
                            break;
                        case 422:
                            // Errores de validación (se manejan en cada componente)
                            break;
                        case 500:
                            Notifications.showError('Error interno del servidor. Por favor, contacta al administrador.');
                            break;
                        default:
                            Notifications.showError('Ocurrió un error. Por favor, intenta nuevamente.');
                    }
                } else if (error.request) {
                    Notifications.showError('No se pudo conectar con el servidor. Verifica tu conexión a internet.');
                } else {
                    Notifications.showError('Error al procesar la solicitud.');
                }
                
                return Promise.reject(error);
            }
        );
    }
    
    // Prevenir doble submit en formularios
    const forms = document.querySelectorAll('form[data-prevent-double-submit]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                Notifications.setButtonLoading(submitButton, true);
            }
        });
    });
    
    // Auto-formateo de inputs de moneda
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    currencyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const value = Formatters.parseCurrency(this.value);
            this.value = Formatters.formatNumber(value, 2);
        });
    });
    
    // Auto-formateo de placas
    const placaInputs = document.querySelectorAll('input[data-placa]');
    placaInputs.forEach(input => {
        input.addEventListener('blur', function() {
            this.value = Formatters.formatPlaca(this.value);
        });
    });
    
    // Auto-validación de campos numéricos
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            const min = parseFloat(this.min);
            const max = parseFloat(this.max);
            const value = parseFloat(this.value);

            if (!isNaN(min) && value < min) {
                Notifications.showFieldError(this, `El valor mínimo es ${min}`);
            } else if (!isNaN(max) && value > max) {
                Notifications.showFieldError(this, `El valor máximo es ${max}`);
            } else {
                Notifications.clearFieldError(this);
            }
        });
    });

    window._alpineStarted = true;
    Alpine.start();
});

// ========================================
// Manejo de errores globales
// ========================================
window.addEventListener('error', (event) => {
    console.error('Error global capturado:', event.error);
    // En producción, podrías enviar esto a un servicio de logging
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Promise rechazada no manejada:', event.reason);
    // En producción, podrías enviar esto a un servicio de logging
});

// ========================================
// Exportar para uso en módulos ES6
// ========================================
export {
    Notifications,
    Validators,
    Formatters,
    BootstrapInit,
    LazyLoader,
};
