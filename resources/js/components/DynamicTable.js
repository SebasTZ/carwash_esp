/**
 * DynamicTable - Componente para renderizar tablas dinámicas
 * Soporta datos complejos, formateo y acciones
 */
class DynamicTable {
    constructor({ el, columns = [], data = [], actions = {}, pagination = false, onRender = null }) {
        this.container = typeof el === 'string' ? document.querySelector(el) : el;
        this.columns = columns;
        this.data = Array.isArray(data) ? data : data.data || [];
        this.actions = actions;
        this.pagination = pagination;
        this.onRender = onRender;
        
        if (!this.container) {
            console.error('DynamicTable: No se encontró el contenedor:', el);
            return;
        }
        
        this.render();
    }

    /**
     * Obtiene el valor de una propiedad anidada
     */
    getNestedValue(obj, path) {
        if (!path || !obj) return '';
        return path.split('.').reduce((acc, part) => acc?.[part] ?? '', obj);
    }

    /**
     * Formatea un valor según su tipo
     */
    formatValue(value, column = {}) {
        if (!value && value !== 0 && value !== false) return '-';
        
        // Manejo especial para comprobante
        if (column.field === 'comprobante' && typeof value === 'object') {
            return `<strong>${value.tipo_comprobante}</strong><br><small class="text-muted">${value.numero_comprobante || '-'}</small>`;
        }

        // Manejo especial para cliente
        if (column.field === 'cliente' && typeof value === 'object') {
            return `<strong>${value.persona?.razon_social || '-'}</strong><br><small class="text-muted">${value.persona?.tipo_persona || ''}</small>`;
        }

        // Manejo especial para fecha_hora
        if (column.field === 'fecha_hora' && value) {
            try {
                const date = new Date(value);
                const fecha = date.toLocaleDateString('es-ES');
                const hora = date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                return `<strong>${fecha}</strong><br><small>${hora}</small>`;
            } catch (e) {
                return String(value);
            }
        }

        // Manejo especial para vendedor
        if (column.field === 'vendedor' && typeof value === 'object') {
            return value.name || '-';
        }

        // Manejo especial para método de pago
        if (column.field === 'medio_pago') {
            const mapeo = {
                'efectivo': 'Efectivo',
                'tarjeta_credito': 'Tarjeta de Crédito',
                'tarjeta_regalo': 'Tarjeta de Regalo',
                'lavado_gratis': 'Lavado Gratis (Fidelidad)',
                'billetera_digital': 'Billetera Digital'
            };
            return mapeo[value] || value;
        }

        // Manejo especial para servicio_lavado
        if (column.field === 'servicio_lavado') {
            // Convertir a booleano si es string
            const isTruthy = value === true || value === 1 || value === '1' || value === 'true';
            return isTruthy ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>';
        }

        // Manejo especial para tarjeta de regalo
        if (column.field === 'tarjeta_regalo') {
            const isTruthy = value === true || value === 1 || value === '1' || value === 'true';
            return isTruthy ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>';
        }

        // Manejo especial para lavado_gratis
        if (column.field === 'lavado_gratis') {
            const isTruthy = value === true || value === 1 || value === '1' || value === 'true';
            return isTruthy ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>';
        }

        // Manejo especial para horario_lavado
        if (column.field === 'horario_lavado' && value) {
            try {
                const date = new Date(value);
                return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return String(value);
            }
        }

        // Valor por defecto
        return String(value);
    }

    /**
     * Genera las acciones para una fila
     */
    generateActions(row) {
        let html = '<div class="btn-group btn-group-sm" role="group">';
        
        if (this.actions.show && this.actions.show.can) {
            const url = this.actions.show.url ? this.actions.show.url(row) : '#';
            html += `<a href="${url}" class="btn btn-primary btn-sm" title="${this.actions.show.label}">
                <i class="fas fa-eye"></i>
            </a>`;
        }
        
        if (this.actions.delete && this.actions.delete.can) {
            const url = this.actions.delete.url ? this.actions.delete.url(row) : '#';
            html += `<form action="${url}" method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger btn-sm" title="${this.actions.delete.label}" 
                    onclick="return confirm('¿Está seguro?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>`;
        }
        
        html += '</div>';
        return html;
    }

    render() {
        if (!this.container) return;
        
        if (!this.data.length) {
            this.container.innerHTML = '<div class="alert alert-info">No hay datos para mostrar.</div>';
            return;
        }

        const table = document.createElement('table');
        table.className = 'table table-striped table-hover';

        // Crear encabezados
        const thead = document.createElement('thead');
        const theadRow = document.createElement('tr');
        theadRow.className = 'table-dark';
        
        this.columns.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col.label || col.field || col;
            th.className = 'text-center';
            theadRow.appendChild(th);
        });
        
        thead.appendChild(theadRow);
        table.appendChild(thead);

        // Crear cuerpo
        const tbody = document.createElement('tbody');
        
        this.data.forEach((row, index) => {
            const tr = document.createElement('tr');
            
            this.columns.forEach(col => {
                const td = document.createElement('td');
                const fieldName = col.field || col;
                
                if (fieldName === 'acciones') {
                    td.innerHTML = this.generateActions(row);
                } else {
                    const value = this.getNestedValue(row, fieldName);
                    const formatted = this.formatValue(value, col);
                    td.innerHTML = formatted;
                }
                
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        this.container.innerHTML = '';
        this.container.appendChild(table);

        // Llamar callback si existe
        if (this.onRender) {
            this.onRender(this.data);
        }
    }

    /**
     * Método estático para inicialización
     */
    static init(options) {
        return new DynamicTable(options);
    }
}

// Exportar como default y asignar a window para acceso global
export default DynamicTable;
window.DynamicTable = DynamicTable;
