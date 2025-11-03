import './bootstrap';
import $ from 'jquery';
window.$ = $;
window.jQuery = $;
// Bootstrap Select se carga desde CDN en create.blade.php para evitar doble inicialización
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
    truncateText: Formatters.truncateText,
    formatPlaca: Formatters.formatPlaca,
    numberToWords: Formatters.numberToWords,
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
    lazyLoadModule: LazyLoader.lazyLoadModule,
    lazyLoadCSS: LazyLoader.lazyLoadCSS,
    lazyLoadScript: LazyLoader.lazyLoadScript,
    preloadImage: LazyLoader.preloadImage,
    preloadImages: LazyLoader.preloadImages,
    debounce: LazyLoader.debounce,
    throttle: LazyLoader.throttle,
    
    // Componentes modernos
    DynamicTable: DynamicTable,
    AutoSave: AutoSave,
    FormValidator: FormValidator,
    CompraForm: CompraForm,
    // Lavadores
    LavadorTableManager: LavadorTableManager,
    LavadorFormManager: LavadorFormManager,
    LavadorEditFormManager: LavadorEditFormManager,
};

// ========================================
// Inicialización global de la aplicación
// ========================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 CarWash ESP - Frontend inicializado');
    
    // Los componentes Bootstrap se inicializan automáticamente
    // desde bootstrap-init.js
    
    // Configuración global de Axios
    if (window.axios) {
        // Interceptor para mostrar loading en requests
        window.axios.interceptors.request.use(
            config => {
                // Agregar token CSRF si existe
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    config.headers['X-CSRF-TOKEN'] = token.content;
                }
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
    
    console.log('✅ Utilidades globales cargadas y disponibles en window.CarWash');
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
