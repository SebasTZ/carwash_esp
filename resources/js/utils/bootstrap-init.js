/**
 * Módulo de Inicialización de Bootstrap
 * Inicializa componentes Bootstrap y plugins relacionados
 */

/**
 * Inicializa todos los tooltips de la página
 * @param {string} selector - Selector CSS para tooltips (default: '[data-bs-toggle="tooltip"]')
 */
export function initTooltips(selector = '[data-bs-toggle="tooltip"]') {
    const tooltipTriggerList = document.querySelectorAll(selector);
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    return tooltipList;
}

/**
 * Inicializa todos los popovers de la página
 * @param {string} selector - Selector CSS para popovers (default: '[data-bs-toggle="popover"]')
 */
export function initPopovers(selector = '[data-bs-toggle="popover"]') {
    const popoverTriggerList = document.querySelectorAll(selector);
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    return popoverList;
}

/**
 * Inicializa Bootstrap Select en elementos select
 * @param {string} selector - Selector CSS (default: '.selectpicker')
 * @param {Object} options - Opciones para bootstrap-select
 */
export function initBootstrapSelect(selector = '.selectpicker', options = {}) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.selectpicker === 'undefined') {
        console.warn('Bootstrap Select no está disponible');
        return;
    }
    
    const defaultOptions = {
        liveSearch: true,
        size: 7,
        style: 'btn-light',
        styleBase: 'form-control',
        noneResultsText: 'No hay resultados para {0}',
        noneSelectedText: 'Seleccione una opción',
        selectAllText: 'Seleccionar todo',
        deselectAllText: 'Deseleccionar todo',
        countSelectedText: function(numSelected, numTotal) {
            return (numSelected == 1) ? '{0} elemento seleccionado' : '{0} elementos seleccionados';
        }
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    jQuery(selector).selectpicker(finalOptions);
}

/**
 * Inicializa DataTables en una tabla
 * @param {string} tableId - ID de la tabla (sin #)
 * @param {Object} options - Opciones para DataTables
 */
export function initDataTable(tableId, options = {}) {
    if (typeof simpleDatatables === 'undefined') {
        console.warn('Simple DataTables no está disponible');
        return;
    }
    
    const table = document.getElementById(tableId);
    if (!table) {
        console.warn(`Tabla #${tableId} no encontrada`);
        return;
    }
    
    const defaultOptions = {
        searchable: true,
        perPage: 10,
        perPageSelect: [5, 10, 25, 50, 100],
        labels: {
            placeholder: 'Buscar...',
            perPage: 'registros por página',
            noRows: 'No se encontraron registros',
            info: 'Mostrando {start} a {end} de {rows} registros',
        },
        layout: {
            top: '{select}{search}',
            bottom: '{info}{pager}'
        }
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return new simpleDatatables.DataTable(table, finalOptions);
}

/**
 * Inicializa modales de Bootstrap
 * @param {string} selector - Selector CSS para modales
 */
export function initModals(selector = '.modal') {
    const modalElements = document.querySelectorAll(selector);
    const modals = [...modalElements].map(modalEl => {
        return new bootstrap.Modal(modalEl);
    });
    
    return modals;
}

/**
 * Inicializa tabs de Bootstrap
 * @param {string} selector - Selector CSS para tabs
 */
export function initTabs(selector = '[data-bs-toggle="tab"]') {
    const tabElements = document.querySelectorAll(selector);
    const tabs = [...tabElements].map(tabEl => {
        return new bootstrap.Tab(tabEl);
    });
    
    return tabs;
}

/**
 * Inicializa collapse/accordion de Bootstrap
 * @param {string} selector - Selector CSS para collapse
 */
export function initCollapses(selector = '[data-bs-toggle="collapse"]') {
    const collapseElements = document.querySelectorAll(selector);
    const collapses = [...collapseElements].map(collapseEl => {
        return new bootstrap.Collapse(collapseEl, { toggle: false });
    });
    
    return collapses;
}

/**
 * Inicializa dropdowns de Bootstrap
 * @param {string} selector - Selector CSS para dropdowns
 */
export function initDropdowns(selector = '[data-bs-toggle="dropdown"]') {
    const dropdownElements = document.querySelectorAll(selector);
    const dropdowns = [...dropdownElements].map(dropdownEl => {
        return new bootstrap.Dropdown(dropdownEl);
    });
    
    return dropdowns;
}

/**
 * Inicializa todos los componentes Bootstrap de una vez
 */
export function initAllBootstrapComponents() {
    initTooltips();
    initPopovers();
    initModals();
    initTabs();
    initDropdowns();
    
    console.log('✅ Componentes Bootstrap inicializados');
}

/**
 * Reinicializa Bootstrap Select después de cambios dinámicos
 * @param {string} selector - Selector del select a reinicializar
 */
export function refreshBootstrapSelect(selector) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.selectpicker === 'undefined') {
        return;
    }
    
    jQuery(selector).selectpicker('refresh');
}

/**
 * Actualiza el valor de un Bootstrap Select
 * @param {string} selector - Selector del select
 * @param {string|Array} value - Valor(es) a seleccionar
 */
export function setBootstrapSelectValue(selector, value) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.selectpicker === 'undefined') {
        return;
    }
    
    jQuery(selector).selectpicker('val', value);
}

/**
 * Abre un modal de Bootstrap
 * @param {string} modalId - ID del modal (sin #)
 */
export function showModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;
    
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

/**
 * Cierra un modal de Bootstrap
 * @param {string} modalId - ID del modal (sin #)
 */
export function hideModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;
    
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}

/**
 * Abre un tab específico
 * @param {string} tabId - ID del tab (sin #)
 */
export function showTab(tabId) {
    const tabEl = document.getElementById(tabId);
    if (!tabEl) return;
    
    const tab = new bootstrap.Tab(tabEl);
    tab.show();
}

/**
 * Abre/cierra un collapse
 * @param {string} collapseId - ID del collapse (sin #)
 * @param {boolean} show - true para abrir, false para cerrar
 */
export function toggleCollapse(collapseId, show = true) {
    const collapseEl = document.getElementById(collapseId);
    if (!collapseEl) return;
    
    const collapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
    
    if (show) {
        collapse.show();
    } else {
        collapse.hide();
    }
}

/**
 * Inicializa validación visual de formularios Bootstrap
 * @param {HTMLFormElement} form - Formulario a validar
 */
export function initFormValidation(form) {
    if (!form) return;
    
    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
}

/**
 * Limpia la validación de un formulario Bootstrap
 * @param {HTMLFormElement} form - Formulario a limpiar
 */
export function clearFormValidation(form) {
    if (!form) return;
    
    form.classList.remove('was-validated');
    
    const invalidFields = form.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    const validFields = form.querySelectorAll('.is-valid');
    validFields.forEach(field => {
        field.classList.remove('is-valid');
    });
}

/**
 * Agrega un evento de actualización automática a un Bootstrap Select
 * cuando su contenedor cambia
 * @param {string} selectSelector - Selector del select
 * @param {string} containerSelector - Selector del contenedor a observar
 */
export function autoRefreshSelectOnChange(selectSelector, containerSelector) {
    if (typeof MutationObserver === 'undefined') return;
    
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    const observer = new MutationObserver(() => {
        refreshBootstrapSelect(selectSelector);
    });
    
    observer.observe(container, {
        childList: true,
        subtree: true
    });
    
    return observer;
}

/**
 * Deshabilita/habilita un Bootstrap Select
 * @param {string} selector - Selector del select
 * @param {boolean} disabled - true para deshabilitar
 */
export function toggleBootstrapSelect(selector, disabled = true) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.selectpicker === 'undefined') {
        return;
    }
    
    jQuery(selector).prop('disabled', disabled);
    refreshBootstrapSelect(selector);
}

/**
 * Inicialización automática al cargar el DOM
 */
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar componentes básicos automáticamente
    initAllBootstrapComponents();
    
    // NO inicializar Bootstrap Select automáticamente porque cada vista lo inicializa manualmente
    // para evitar doble inicialización
    // if (document.querySelector('.selectpicker')) {
    //     initBootstrapSelect();
    // }
});
