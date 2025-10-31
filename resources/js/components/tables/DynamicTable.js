/**
 * DynamicTable Component
 * 
 * Componente reutilizable para gestión de tablas dinámicas con todas las funcionalidades
 * comunes: CRUD de filas, formatters, búsqueda, acciones, eventos y más.
 * 
 * @example
 * const table = new DynamicTable('#myTable', {
 *   columns: [
 *     { key: 'id', label: '#' },
 *     { key: 'name', label: 'Nombre' },
 *     { key: 'price', label: 'Precio', formatter: 'currency' },
 *     { key: 'created_at', label: 'Fecha', formatter: 'date' }
 *   ],
 *   actions: [
 *     { label: 'Editar', class: 'btn-primary', callback: (row) => editRow(row) },
 *     { label: 'Eliminar', class: 'btn-danger', callback: (row) => deleteRow(row) }
 *   ],
 *   searchable: true,
 *   onRowAdded: (row) => console.log('Row added', row)
 * });
 * 
 * @version 1.0.0
 * @author GOREHCO Team
 */

import Component from '../../core/Component.js';

export default class DynamicTable extends Component {
    /**
     * Constructor del componente
     * @param {string|HTMLElement} selector - Selector CSS o elemento DOM de la tabla
     * @param {Object} options - Opciones de configuración
     */
    constructor(selector, options = {}) {
        // Opciones específicas de DynamicTable (ANTES de llamar a super)
        const tableOptions = {
            columns: [],              // Array de objetos {key, label, formatter, class}
            data: [],                 // Datos iniciales
            actions: [],              // Array de acciones {label, class, icon, callback}
            searchable: false,        // Habilitar búsqueda
            searchPlaceholder: 'Buscar...',
            emptyMessage: 'No hay datos disponibles',
            striped: true,           // Tabla rayada
            hover: true,             // Hover effect
            bordered: false,         // Bordes en celdas
            responsive: true,        // Contenedor responsive
            rowIdKey: 'id',          // Key para identificar filas únicas
            // Events
            onRowAdded: null,
            onRowRemoved: null,
            onRowUpdated: null,
            onSearch: null,
            // Formatters personalizados
            customFormatters: {},
            ...options
        };

        // Log de configuración inicial
        console.log('[DynamicTable] Opciones:', tableOptions);

        // Pasar selector correctamente a Component
        let componentOptions;
        let element = null;
        if (typeof selector === 'string') {
            element = document.querySelector(selector);
            if (!element) {
                console.error(`[DynamicTable] Error: No se encontró el elemento con selector '${selector}'. La tabla no se inicializará.`);
                return;
            }
            componentOptions = { element };
        } else {
            element = selector;
            if (!element) {
                console.error('[DynamicTable] Error: El elemento de la tabla es null o undefined. La tabla no se inicializará.');
                return;
            }
            componentOptions = { element };
        }

        super(componentOptions);

        // Asignar tableOptions DESPUÉS de super()
        this.tableOptions = tableOptions;
        this.data = [...this.tableOptions.data];
        this.filteredData = [...this.data];
        this.searchTerm = '';

        // Log de data inicial
        console.log('[DynamicTable] Data inicial:', this.data);

        // Ahora sí inicializar (después de que tableOptions esté listo)
        this.init();
    }

    /**
     * Override mount() para evitar que Component llame a init() automáticamente
     */
    mount() {
        // Component.mount() ya fue llamado por super()
        // No hacemos nada aquí, init() se llama manualmente en el constructor
    }

    /**
     * Inicialización del componente
     */
    init() {
        if (this.tableOptions.searchable) {
            this.renderSearchBar();
        }
        
        this.renderTable();
        this.attachEventListeners();
    }

    /**
     * Renderiza la barra de búsqueda
     */
    renderSearchBar() {
        const container = this.element.parentElement;
        const searchBar = document.createElement('div');
        searchBar.className = 'mb-3';
        searchBar.innerHTML = `
            <input 
                type="text" 
                class="form-control" 
                placeholder="${this.tableOptions.searchPlaceholder}"
                data-table-search
            >
        `;
        
        container.insertBefore(searchBar, this.element);
        
        // Attach search listener
        const searchInput = searchBar.querySelector('[data-table-search]');
        searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    }

    /**
     * Renderiza la tabla completa
     */
    renderTable() {
        // Crear estructura si no existe
        if (!this.element.querySelector('thead')) {
            this.element.innerHTML = `
                <thead></thead>
                <tbody></tbody>
            `;
        }

        // Aplicar clases Bootstrap
        this.element.classList.add('table');
        if (this.tableOptions.striped) this.element.classList.add('table-striped');
        if (this.tableOptions.hover) this.element.classList.add('table-hover');
        if (this.tableOptions.bordered) this.element.classList.add('table-bordered');

        // Wrapper responsive
        if (this.tableOptions.responsive && !this.element.closest('.table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            this.element.parentElement.insertBefore(wrapper, this.element);
            wrapper.appendChild(this.element);
        }

        // Log de renderizado de tabla
        console.log('[DynamicTable] Renderizando tabla. Data actual:', this.data);

        this.renderHeader();
        this.renderBody();
    }

    /**
     * Renderiza el header de la tabla
     */
    renderHeader() {
        const thead = this.element.querySelector('thead');
        const hasActions = this.tableOptions.actions.length > 0;
        
        thead.innerHTML = `
            <tr>
                ${this.tableOptions.columns.map(col => 
                    `<th class="${col.class || ''}">${col.label}</th>`
                ).join('')}
                ${hasActions ? '<th>Acciones</th>' : ''}
            </tr>
        `;
    }

    /**
     * Renderiza el body de la tabla
     */
    renderBody() {
        const tbody = this.element.querySelector('tbody');
        
        if (this.filteredData.length === 0) {
            console.log('[DynamicTable] No hay datos para mostrar. Mostrando tabla vacía.');
            tbody.innerHTML = `
                <tr>
                    <td colspan="${this.tableOptions.columns.length + (this.tableOptions.actions.length > 0 ? 1 : 0)}" 
                        class="text-center text-muted py-4">
                        ${this.tableOptions.emptyMessage}
                    </td>
                </tr>
            `;
            return;
        }

        // Renderizar filas usando innerHTML para celdas con HTML
        tbody.innerHTML = '';
        this.filteredData.forEach(row => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-row-id', row[this.tableOptions.rowIdKey]);
            this.tableOptions.columns.forEach(col => {
                const value = this.getNestedValue(row, col.key);
                const formattedValue = this.formatValue(value, col.formatter, row);
                const td = document.createElement('td');
                td.className = col.class || '';
                // Si el valor parece HTML (acciones, badges, etc.), usar innerHTML
                if (col.key === 'acciones' || /<.+>/.test(formattedValue)) {
                    td.innerHTML = formattedValue;
                } else {
                    td.textContent = formattedValue;
                }
                tr.appendChild(td);
            });
            // Acciones extra
            if (this.tableOptions.actions.length > 0) {
                const td = document.createElement('td');
                td.innerHTML = this.renderActions(row);
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        });
    }

    /**
     * Renderiza una fila
     * @param {Object} row - Datos de la fila
     * @returns {string} HTML de la fila
     */
    renderRow(row) {
        const rowId = row[this.tableOptions.rowIdKey];
        const hasActions = this.tableOptions.actions.length > 0;
        
        return `
            <tr data-row-id="${rowId}">
                ${this.tableOptions.columns.map(col => {
                    const value = this.getNestedValue(row, col.key);
                    const formattedValue = this.formatValue(value, col.formatter, row);
                    return `<td class="${col.class || ''}">${formattedValue}</td>`;
                }).join('')}
                ${hasActions ? `<td>${this.renderActions(row)}</td>` : ''}
            </tr>
        `;
    }

    /**
     * Renderiza botones de acción
     * @param {Object} row - Datos de la fila
     * @returns {string} HTML de acciones
     */
    renderActions(row) {
        return this.tableOptions.actions.map(action => {
            const icon = action.icon ? `<i class="${action.icon}"></i> ` : '';
            return `
                <button 
                    class="btn btn-sm ${action.class || 'btn-secondary'} me-1" 
                    data-action="${action.label}"
                    data-row-id="${row[this.tableOptions.rowIdKey]}"
                >
                    ${icon}${action.label}
                </button>
            `;
        }).join('');
    }

    /**
     * Formatea un valor según el formatter especificado
     * @param {*} value - Valor a formatear
     * @param {string} formatter - Tipo de formatter
     * @param {Object} row - Fila completa (para formatters complejos)
     * @returns {string} Valor formateado
     */
    formatValue(value, formatter, row) {
        if (value === null || value === undefined) return '-';

        // Si el formatter es una función, llamarla directamente
        if (typeof formatter === 'function') {
            return formatter(value, row);
        }

        // Formatters personalizados por nombre
        if (formatter && this.tableOptions.customFormatters[formatter]) {
            return this.tableOptions.customFormatters[formatter](value, row);
        }

        // Formatters built-in
        switch (formatter) {
            case 'currency':
                return this.formatCurrency(value);
            case 'date':
                return this.formatDate(value);
            case 'datetime':
                return this.formatDateTime(value);
            
            case 'status':
                return this.formatStatus(value);
            
            case 'boolean':
                return this.formatBoolean(value);
            
            case 'badge':
                return this.formatBadge(value);
            
            default:
                return this.escapeHtml(String(value));
        }
    }

    /**
     * Formatea valor como moneda
     */
    formatCurrency(value) {
        const num = parseFloat(value);
        if (isNaN(num)) return '-';
        return `S/ ${num.toFixed(2)}`;
    }

    /**
     * Formatea valor como fecha
     */
    formatDate(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (isNaN(date.getTime())) return value;
        return date.toLocaleDateString('es-PE', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }

    /**
     * Formatea valor como fecha y hora
     */
    formatDateTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (isNaN(date.getTime())) return value;
        return date.toLocaleString('es-PE', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Formatea valor como status badge
     */
    formatStatus(value) {
        const statusMap = {
            'activo': 'success',
            'active': 'success',
            'completado': 'success',
            'completed': 'success',
            'inactivo': 'secondary',
            'inactive': 'secondary',
            'pendiente': 'warning',
            'pending': 'warning',
            'cancelado': 'danger',
            'cancelled': 'danger'
        };
        
        const color = statusMap[value?.toLowerCase()] || 'secondary';
        return `<span class="badge bg-${color}">${this.escapeHtml(value)}</span>`;
    }

    /**
     * Formatea valor booleano
     */
    formatBoolean(value) {
        const isTrue = value === true || value === 1 || value === '1' || value === 'true';
        const icon = isTrue ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';
        const text = isTrue ? 'Sí' : 'No';
        return `<i class="bi ${icon}"></i> ${text}`;
    }

    /**
     * Formatea valor como badge
     */
    formatBadge(value) {
        return `<span class="badge bg-primary">${this.escapeHtml(value)}</span>`;
    }

    /**
     * Escapa HTML para prevenir XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Obtiene valor anidado de un objeto usando dot notation
     * @param {Object} obj - Objeto
     * @param {string} path - Path (ej: 'user.name')
     */
    getNestedValue(obj, path) {
        return path.split('.').reduce((current, prop) => 
            current?.[prop], obj
        );
    }

    /**
     * Maneja la búsqueda en la tabla
     */
    handleSearch(term) {
        this.searchTerm = term.toLowerCase();
        
        if (!term) {
            this.filteredData = [...this.data];
        } else {
            this.filteredData = this.data.filter(row => {
                return this.tableOptions.columns.some(col => {
                    const value = this.getNestedValue(row, col.key);
                    return String(value).toLowerCase().includes(this.searchTerm);
                });
            });
        }

        this.renderBody();

        if (this.tableOptions.onSearch) {
            this.tableOptions.onSearch(term, this.filteredData);
        }
    }

    /**
     * Adjunta event listeners
     */
    attachEventListeners() {
        // Event delegation para botones de acción
        this.element.addEventListener('click', (e) => {
            const button = e.target.closest('[data-action]');
            if (!button) return;

            const actionLabel = button.dataset.action;
            const rowId = button.dataset.rowId;
            const action = this.tableOptions.actions.find(a => a.label === actionLabel);
            
            if (action && action.callback) {
                const row = this.getRowById(rowId);
                action.callback(row, button);
            }
        });
    }

    /**
     * Agrega una fila a la tabla
     * @param {Object} row - Datos de la fila
     */
    addRow(row) {
        this.data.push(row);
        
        // Actualizar filteredData si cumple búsqueda
        if (!this.searchTerm || this.rowMatchesSearch(row)) {
            this.filteredData.push(row);
        }
        
        this.renderBody();

        if (this.tableOptions.onRowAdded) {
            this.tableOptions.onRowAdded(row);
        }
    }

    /**
     * Elimina una fila de la tabla
     * @param {string|number} rowId - ID de la fila
     */
    removeRow(rowId) {
        const index = this.data.findIndex(r => r[this.tableOptions.rowIdKey] == rowId);
        if (index === -1) return;

        const removedRow = this.data.splice(index, 1)[0];
        
        const filteredIndex = this.filteredData.findIndex(r => r[this.tableOptions.rowIdKey] == rowId);
        if (filteredIndex !== -1) {
            this.filteredData.splice(filteredIndex, 1);
        }
        
        this.renderBody();

        if (this.tableOptions.onRowRemoved) {
            this.tableOptions.onRowRemoved(removedRow);
        }
    }

    /**
     * Actualiza una fila existente
     * @param {string|number} rowId - ID de la fila
     * @param {Object} newData - Nuevos datos (parcial o completo)
     */
    updateRow(rowId, newData) {
        const index = this.data.findIndex(r => r[this.tableOptions.rowIdKey] == rowId);
        if (index === -1) return;

        this.data[index] = { ...this.data[index], ...newData };
        
        const filteredIndex = this.filteredData.findIndex(r => r[this.tableOptions.rowIdKey] == rowId);
        if (filteredIndex !== -1) {
            this.filteredData[filteredIndex] = { ...this.data[index] };
        }
        
        this.renderBody();

        if (this.tableOptions.onRowUpdated) {
            this.tableOptions.onRowUpdated(this.data[index]);
        }
    }

    /**
     * Limpia toda la tabla
     */
    clearTable() {
        this.data = [];
        this.filteredData = [];
        this.renderBody();
    }

    /**
     * Obtiene una fila por ID
     * @param {string|number} rowId - ID de la fila
     * @returns {Object|null}
     */
    getRowById(rowId) {
        return this.data.find(r => r[this.tableOptions.rowIdKey] == rowId) || null;
    }

    /**
     * Verifica si una fila coincide con el término de búsqueda actual
     */
    rowMatchesSearch(row) {
        if (!this.searchTerm) return true;
        
        return this.tableOptions.columns.some(col => {
            const value = this.getNestedValue(row, col.key);
            return String(value).toLowerCase().includes(this.searchTerm);
        });
    }

    /**
     * Obtiene todos los datos actuales
     * @returns {Array}
     */
    getData() {
        return [...this.data];
    }

    /**
     * Obtiene datos filtrados actuales
     * @returns {Array}
     */
    getFilteredData() {
        return [...this.filteredData];
    }

    /**
     * Reemplaza todos los datos
     * @param {Array} newData - Nuevos datos
     */
    setData(newData) {
        this.data = [...newData];
        this.filteredData = [...newData];
        if (this.searchTerm) {
            this.handleSearch(this.searchTerm);
        } else {
            this.renderBody();
        }
    }

    /**
     * Limpieza al destruir el componente
     */
    destroy() {
        // Limpiar búsqueda si existe
        const searchBar = this.element.parentElement?.querySelector('[data-table-search]');
        if (searchBar) {
            searchBar.parentElement.remove();
        }

        super.destroy();
    }
}
