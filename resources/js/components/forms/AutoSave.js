import Component from '@core/Component.js';

/**
 * AutoSave Component
 * 
 * Componente para auto-guardar formularios automáticamente con:
 * - Debouncing configurable
 * - Indicador visual de estado (guardando, guardado, error)
 * - Manejo de errores con reintentos
 * - Callbacks personalizables
 * - LocalStorage fallback opcional
 * 
 * @extends Component
 * 
 * @example
 * ```js
 * const autoSave = new AutoSave('#my-form', {
 *     saveCallback: async (formData) => {
 *         return await fetch('/api/save', {
 *             method: 'POST',
 *             body: JSON.stringify(formData)
 *         });
 *     },
 *     delay: 2000,
 *     onSaved: (response) => console.log('Guardado:', response),
 *     onError: (error) => console.error('Error:', error)
 * });
 * ```
 */
export default class AutoSave extends Component {
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
        
        // Opciones AutoSave
        this.autoSaveOptions = {
            saveCallback: null,          // Función async que recibe formData y retorna Promise
            delay: 3000,                 // Delay en ms antes de guardar (debouncing)
            enableLocalStorage: true,    // Guardar en localStorage como fallback
            storageKey: null,            // Key para localStorage (auto-generada si null)
            showIndicator: true,         // Mostrar indicador visual
            indicatorPosition: 'top-right', // Posición: 'top-right', 'top-left', 'bottom-right', 'bottom-left'
            maxRetries: 3,               // Intentos máximos en caso de error
            retryDelay: 1000,            // Delay entre reintentos
            excludeFields: [],           // Array de nombres de campos a excluir
            includeFields: [],           // Array de nombres de campos (si vacío, incluye todos)
            validateBeforeSave: null,    // Función de validación opcional antes de guardar
            // Eventos
            onSaving: null,              // Callback cuando inicia guardado
            onSaved: null,               // Callback cuando guardado exitoso
            onError: null,               // Callback en error
            onRestore: null,             // Callback cuando restaura desde localStorage
            ...options
        };

        // Validaciones
        if (!this.autoSaveOptions.saveCallback && !this.autoSaveOptions.enableLocalStorage) {
            throw new Error('AutoSave: Debe proporcionar saveCallback o habilitar localStorage');
        }

        // Estado interno
        this.saveTimer = null;
        this.isSaving = false;
        this.lastSavedData = null;
        this.retryCount = 0;
        this.indicator = null;
        
        // Generar storage key si no existe
        if (this.autoSaveOptions.enableLocalStorage && !this.autoSaveOptions.storageKey) {
            const formId = this.element.id || 'form';
            this.autoSaveOptions.storageKey = `autosave_${formId}_${window.location.pathname}`;
        }

        // Inicializar ahora que autoSaveOptions está listo
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
            throw new Error('AutoSave: El elemento debe ser un <form>');
        }

        // Crear indicador visual si está habilitado
        if (this.autoSaveOptions.showIndicator) {
            this.createIndicator();
        }

        // Restaurar datos desde localStorage si existe
        this.restoreFromStorage();

        // Attach event listeners
        this.attachListeners();

        console.log('AutoSave initialized:', {
            form: this.element.id || this.element.name,
            delay: this.autoSaveOptions.delay,
            localStorage: this.autoSaveOptions.enableLocalStorage
        });
    }

    /**
     * Crear indicador visual de estado
     */
    createIndicator() {
        this.indicator = document.createElement('div');
        this.indicator.className = 'autosave-indicator';
        this.indicator.style.cssText = this.getIndicatorStyles();
        this.indicator.innerHTML = '<span class="autosave-text"></span>';
        
        // Insertar en el form
        this.element.style.position = 'relative';
        this.element.appendChild(this.indicator);
        
        // Ocultar inicialmente
        this.hideIndicator();
    }

    /**
     * Estilos CSS del indicador según posición
     * @returns {string} CSS inline styles
     */
    getIndicatorStyles() {
        const baseStyles = `
            position: absolute;
            z-index: 1000;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            pointer-events: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        `;

        const positions = {
            'top-right': 'top: 10px; right: 10px;',
            'top-left': 'top: 10px; left: 10px;',
            'bottom-right': 'bottom: 10px; right: 10px;',
            'bottom-left': 'bottom: 10px; left: 10px;'
        };

        return baseStyles + (positions[this.autoSaveOptions.indicatorPosition] || positions['top-right']);
    }

    /**
     * Actualizar indicador visual
     * @param {string} status - Estado: 'saving', 'saved', 'error'
     * @param {string} [message] - Mensaje personalizado
     */
    updateIndicator(status, message = null) {
        if (!this.indicator) return;

        const states = {
            saving: {
                text: message || 'Guardando...',
                bg: '#0d6efd',
                color: '#fff',
                icon: '⏳'
            },
            saved: {
                text: message || 'Guardado',
                bg: '#198754',
                color: '#fff',
                icon: '✓'
            },
            error: {
                text: message || 'Error al guardar',
                bg: '#dc3545',
                color: '#fff',
                icon: '⚠'
            }
        };

        const state = states[status] || states.saved;
        
        this.indicator.style.backgroundColor = state.bg;
        this.indicator.style.color = state.color;
        this.indicator.style.display = 'block';
        this.indicator.querySelector('.autosave-text').innerHTML = 
            `${state.icon} ${state.text}`;

        // Auto-ocultar si es "saved"
        if (status === 'saved') {
            setTimeout(() => this.hideIndicator(), 2000);
        }
    }

    /**
     * Ocultar indicador
     */
    hideIndicator() {
        if (this.indicator) {
            this.indicator.style.display = 'none';
        }
    }

    /**
     * Attach event listeners a los campos del form
     */
    attachListeners() {
        // Eventos que disparan autosave
        const events = ['input', 'change', 'blur'];
        
        events.forEach(eventName => {
            this.element.addEventListener(eventName, (e) => {
                // Solo si el target es un input/select/textarea
                if (this.isFormField(e.target)) {
                    this.scheduleAutoSave();
                }
            });
        });

        // Prevenir submit accidental mientras guarda
        this.element.addEventListener('submit', (e) => {
            if (this.isSaving) {
                e.preventDefault();
                console.warn('AutoSave: Submit bloqueado, guardado en progreso');
            }
        });
    }

    /**
     * Verificar si el elemento es un campo de formulario válido
     * @param {HTMLElement} element
     * @returns {boolean}
     */
    isFormField(element) {
        const validTags = ['INPUT', 'SELECT', 'TEXTAREA'];
        const validTypes = ['text', 'email', 'number', 'tel', 'url', 'password', 
                           'search', 'date', 'time', 'datetime-local', 'month', 'week'];
        
        if (!validTags.includes(element.tagName)) return false;
        
        if (element.tagName === 'INPUT') {
            return validTypes.includes(element.type) || 
                   element.type === 'checkbox' || 
                   element.type === 'radio';
        }
        
        return true;
    }

    /**
     * Programar auto-guardado con debouncing
     */
    scheduleAutoSave() {
        // Cancelar timer anterior
        if (this.saveTimer) {
            clearTimeout(this.saveTimer);
        }

        // Programar nuevo guardado
        this.saveTimer = setTimeout(() => {
            this.performAutoSave();
        }, this.autoSaveOptions.delay);
    }

    /**
     * Ejecutar auto-guardado
     */
    async performAutoSave() {
        if (this.isSaving) {
            console.log('AutoSave: Ya hay un guardado en progreso');
            return;
        }

        const formData = this.getFormData();

        // Validar antes de guardar si hay función de validación
        if (this.autoSaveOptions.validateBeforeSave) {
            const isValid = await this.autoSaveOptions.validateBeforeSave(formData);
            if (!isValid) {
                console.log('AutoSave: Validación falló, no se guardará');
                return;
            }
        }

        // No guardar si los datos no han cambiado
        if (this.lastSavedData && 
            JSON.stringify(formData) === JSON.stringify(this.lastSavedData)) {
            console.log('AutoSave: Sin cambios desde último guardado');
            return;
        }

        this.isSaving = true;
        this.updateIndicator('saving');

        // Callback onSaving
        if (this.autoSaveOptions.onSaving) {
            this.autoSaveOptions.onSaving(formData);
        }

        try {
            let response = null;

            // Intentar guardar con callback si existe
            if (this.autoSaveOptions.saveCallback) {
                response = await this.autoSaveOptions.saveCallback(formData);
            }

            // Guardar en localStorage si está habilitado
            if (this.autoSaveOptions.enableLocalStorage) {
                this.saveToStorage(formData);
            }

            // Éxito
            this.lastSavedData = formData;
            this.retryCount = 0;
            this.updateIndicator('saved');

            // Callback onSaved
            if (this.autoSaveOptions.onSaved) {
                this.autoSaveOptions.onSaved(response, formData);
            }

        } catch (error) {
            console.error('AutoSave: Error al guardar:', error);
            
            // Reintentar si no se alcanzó el máximo
            if (this.retryCount < this.autoSaveOptions.maxRetries) {
                this.retryCount++;
                this.updateIndicator('error', `Error. Reintentando (${this.retryCount}/${this.autoSaveOptions.maxRetries})...`);
                
                setTimeout(() => {
                    this.performAutoSave();
                }, this.autoSaveOptions.retryDelay);
            } else {
                // Máximo de reintentos alcanzado
                this.updateIndicator('error', 'Error al guardar');
                this.retryCount = 0;

                // Callback onError
                if (this.autoSaveOptions.onError) {
                    this.autoSaveOptions.onError(error, formData);
                }
            }
        } finally {
            this.isSaving = false;
        }
    }

    /**
     * Obtener datos del formulario
     * @returns {Object} Datos del formulario
     */
    getFormData() {
        const formData = new FormData(this.element);
        const data = {};

        for (let [key, value] of formData.entries()) {
            // Excluir campos específicos
            if (this.autoSaveOptions.excludeFields.includes(key)) {
                continue;
            }

            // Solo incluir campos específicos si la lista no está vacía
            if (this.autoSaveOptions.includeFields.length > 0 && 
                !this.autoSaveOptions.includeFields.includes(key)) {
                continue;
            }

            // Manejar checkboxes y múltiples valores
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
     * Guardar en localStorage
     * @param {Object} data - Datos a guardar
     */
    saveToStorage(data) {
        try {
            const storageData = {
                data,
                timestamp: Date.now(),
                url: window.location.href
            };
            localStorage.setItem(
                this.autoSaveOptions.storageKey, 
                JSON.stringify(storageData)
            );
        } catch (error) {
            console.error('AutoSave: Error guardando en localStorage:', error);
        }
    }

    /**
     * Restaurar desde localStorage
     */
    restoreFromStorage() {
        if (!this.autoSaveOptions.enableLocalStorage) return;

        try {
            const stored = localStorage.getItem(this.autoSaveOptions.storageKey);
            if (!stored) return;

            const { data, timestamp, url } = JSON.parse(stored);
            
            // Verificar que sea de la misma URL
            if (url !== window.location.href) return;

            // Verificar que no sea muy antiguo (max 24h)
            const hoursSinceStore = (Date.now() - timestamp) / (1000 * 60 * 60);
            if (hoursSinceStore > 24) {
                this.clearStorage();
                return;
            }

            // Restaurar valores
            Object.entries(data).forEach(([key, value]) => {
                const field = this.element.elements[key];
                if (field) {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = value === field.value;
                    } else {
                        field.value = value;
                    }
                }
            });

            console.log('AutoSave: Datos restaurados desde localStorage');
            
            // Callback onRestore
            if (this.autoSaveOptions.onRestore) {
                this.autoSaveOptions.onRestore(data);
            }

            // Mostrar indicador
            if (this.autoSaveOptions.showIndicator) {
                this.updateIndicator('saved', 'Datos restaurados');
            }

        } catch (error) {
            console.error('AutoSave: Error restaurando desde localStorage:', error);
        }
    }

    /**
     * Limpiar datos de localStorage
     */
    clearStorage() {
        if (this.autoSaveOptions.enableLocalStorage) {
            localStorage.removeItem(this.autoSaveOptions.storageKey);
            console.log('AutoSave: localStorage limpiado');
        }
    }

    /**
     * Forzar guardado inmediato (sin debouncing)
     * @returns {Promise}
     */
    async forceSave() {
        if (this.saveTimer) {
            clearTimeout(this.saveTimer);
        }
        return await this.performAutoSave();
    }

    /**
     * Pausar auto-guardado
     */
    pause() {
        if (this.saveTimer) {
            clearTimeout(this.saveTimer);
            this.saveTimer = null;
        }
        console.log('AutoSave: Pausado');
    }

    /**
     * Reanudar auto-guardado
     */
    resume() {
        console.log('AutoSave: Reanudado');
        // El próximo cambio en el form lo activará automáticamente
    }

    /**
     * Destruir componente y limpiar event listeners
     */
    destroy() {
        if (this.saveTimer) {
            clearTimeout(this.saveTimer);
        }
        
        if (this.indicator) {
            this.indicator.remove();
        }

        // Component.destroy() limpia los listeners
        super.destroy();
    }
}
