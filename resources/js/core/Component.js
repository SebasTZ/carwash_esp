/**
 * @fileoverview Component - Clase base para todos los componentes reutilizables
 * @module core/Component
 */

/**
 * Clase base abstracta para componentes UI
 * Proporciona lifecycle methods y funcionalidad común
 * 
 * @abstract
 * @class Component
 * @example
 * class MyComponent extends Component {
 *   init() {
 *     this.render();
 *   }
 *   
 *   render() {
 *     this.element.innerHTML = '<div>Hello</div>';
 *   }
 * }
 */
export class Component {
    /**
     * @param {Object} options - Configuración del componente
     * @param {string} options.selector - Selector CSS del elemento contenedor
     * @param {HTMLElement} [options.element] - Elemento DOM alternativo
     * @throws {Error} Si no se encuentra el elemento
     */
    constructor(options = {}) {
        this.options = {
            selector: null,
            element: null,
            ...options
        };
        
        // Obtener elemento del DOM
        if (this.options.element) {
            this.element = this.options.element;
        } else if (this.options.selector) {
            this.element = document.querySelector(this.options.selector);
        }
        
        if (!this.element) {
            throw new Error(
                `Component: Element not found (selector: ${this.options.selector})`
            );
        }
        
        // Estado del componente
        this._mounted = false;
        this._destroyed = false;
        
        // Listeners para cleanup
        this._eventListeners = [];
        
        // Auto-inicializar
        this.mount();
    }
    
    /**
     * Lifecycle: Montaje del componente
     * @private
     */
    mount() {
        if (this._mounted) {
            console.warn('Component already mounted');
            return;
        }
        
        this.beforeMount();
        this.init();
        this._mounted = true;
        this.mounted();
    }
    
    /**
     * Hook: Antes de montar
     * Override en subclases para lógica pre-montaje
     * @protected
     */
    beforeMount() {
        // Override en subclases
    }
    
    /**
     * Hook: Inicialización principal
     * DEBE ser implementado por subclases
     * @abstract
     * @protected
     */
    init() {
        throw new Error('Component.init() must be implemented by subclass');
    }
    
    /**
     * Hook: Después de montar
     * Override en subclases para lógica post-montaje
     * @protected
     */
    mounted() {
        // Override en subclases
    }
    
    /**
     * Añadir event listener con auto-cleanup
     * @param {EventTarget} target - Elemento target
     * @param {string} event - Nombre del evento
     * @param {Function} handler - Handler function
     * @param {Object} options - Opciones del event listener
     * @protected
     */
    addEventListener(target, event, handler, options = {}) {
        target.addEventListener(event, handler, options);
        
        // Guardar para cleanup
        this._eventListeners.push({
            target,
            event,
            handler,
            options
        });
    }
    
    /**
     * Emitir evento custom desde el componente
     * @param {string} eventName - Nombre del evento
     * @param {*} detail - Datos del evento
     * @protected
     */
    emit(eventName, detail = {}) {
        const event = new CustomEvent(eventName, {
            detail,
            bubbles: true,
            cancelable: true
        });
        
        this.element.dispatchEvent(event);
    }
    
    /**
     * Actualizar opciones del componente
     * @param {Object} newOptions - Nuevas opciones
     * @public
     */
    setOptions(newOptions) {
        this.options = {
            ...this.options,
            ...newOptions
        };
        
        this.update();
    }
    
    /**
     * Hook: Actualizar componente después de cambio de opciones
     * Override en subclases
     * @protected
     */
    update() {
        // Override en subclases
    }
    
    /**
     * Verificar si el componente está montado
     * @returns {boolean}
     * @public
     */
    isMounted() {
        return this._mounted;
    }
    
    /**
     * Verificar si el componente está destruido
     * @returns {boolean}
     * @public
     */
    isDestroyed() {
        return this._destroyed;
    }
    
    /**
     * Lifecycle: Destrucción del componente
     * Limpia event listeners y libera recursos
     * @public
     */
    destroy() {
        if (this._destroyed) {
            console.warn('Component already destroyed');
            return;
        }
        
        this.beforeDestroy();
        
        // Limpiar event listeners
        this._eventListeners.forEach(({ target, event, handler, options }) => {
            target.removeEventListener(event, handler, options);
        });
        this._eventListeners = [];
        
        this._destroyed = true;
        this._mounted = false;
        
        this.destroyed();
    }
    
    /**
     * Hook: Antes de destruir
     * Override en subclases para cleanup personalizado
     * @protected
     */
    beforeDestroy() {
        // Override en subclases
    }
    
    /**
     * Hook: Después de destruir
     * Override en subclases
     * @protected
     */
    destroyed() {
        // Override en subclases
    }
    
    /**
     * Helper: Encontrar elemento dentro del componente
     * @param {string} selector - Selector CSS
     * @returns {HTMLElement|null}
     * @protected
     */
    find(selector) {
        return this.element.querySelector(selector);
    }
    
    /**
     * Helper: Encontrar múltiples elementos dentro del componente
     * @param {string} selector - Selector CSS
     * @returns {NodeList}
     * @protected
     */
    findAll(selector) {
        return this.element.querySelectorAll(selector);
    }
    
    /**
     * Helper: Agregar clase CSS al elemento
     * @param {...string} classes - Clases a agregar
     * @public
     */
    addClass(...classes) {
        this.element.classList.add(...classes);
    }
    
    /**
     * Helper: Remover clase CSS del elemento
     * @param {...string} classes - Clases a remover
     * @public
     */
    removeClass(...classes) {
        this.element.classList.remove(...classes);
    }
    
    /**
     * Helper: Toggle clase CSS en el elemento
     * @param {string} className - Clase a toggle
     * @param {boolean} [force] - Forzar add (true) o remove (false)
     * @public
     */
    toggleClass(className, force) {
        this.element.classList.toggle(className, force);
    }
    
    /**
     * Helper: Verificar si tiene clase CSS
     * @param {string} className - Clase a verificar
     * @returns {boolean}
     * @public
     */
    hasClass(className) {
        return this.element.classList.contains(className);
    }
}

/**
 * Exportar por defecto
 */
export default Component;
