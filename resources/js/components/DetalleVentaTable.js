/**
 * DetalleVentaTable - Componente para mostrar el detalle de productos de una venta
 * Se utiliza en la vista show.blade.php
 */

export class DetalleVentaTable {
    /**
     * Constructor
     * @param {Object} options - Opciones de configuración
     */
    constructor(options = {}) {
        this.el = options.el || '#venta-detalle-table';
        this.productos = options.productos || [];
        this.impuesto = options.impuesto || 0;
        this.servicio_lavado = options.servicio_lavado || false;
        this.horario_lavado = options.horario_lavado || null;
        this.total = options.total || 0;
        this.container = typeof this.el === 'string' ? document.querySelector(this.el) : this.el;
        this.render();
    }

    /**
     * Renderiza la tabla de detalle
     */
    render() {
        if (!this.container) return;

        // Si no hay productos pero SÍ es servicio de lavado
        if (!this.productos.length && this.servicio_lavado) {
            this.renderServicioLavado();
            return;
        }

        // Si no hay productos y NO es servicio de lavado
        if (!this.productos.length) {
            this.container.innerHTML = '<div class="alert alert-warning">No hay productos en esta venta.</div>';
            return;
        }

        // Si hay productos, renderizar tabla normal
        this.renderProductos();
    }

    /**
     * Renderiza la tabla de productos normales
     */
    renderProductos() {
        const table = document.createElement('table');
        table.className = 'table table-striped table-hover';

        // Crear encabezado
        const thead = document.createElement('thead');
        const theadRow = document.createElement('tr');
        theadRow.className = 'table-dark';
        const headers = ['Código', 'Producto', 'Cantidad', 'Precio Unitario', 'Descuento', 'Subtotal'];
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header;
            th.className = 'text-center';
            theadRow.appendChild(th);
        });
        thead.appendChild(theadRow);
        table.appendChild(thead);

        // Crear cuerpo
        const tbody = document.createElement('tbody');
        let subtotalGral = 0;

        this.productos.forEach((producto) => {
            const fila = document.createElement('tr');
            const cantidad = producto.pivot?.cantidad || 0;
            const precioVenta = producto.pivot?.precio_venta || 0;
            const descuento = producto.pivot?.descuento || 0;
            const subtotal = (cantidad * precioVenta) - descuento;
            subtotalGral += subtotal;

            const celdas = [
                producto.codigo || '',
                producto.nombre || '',
                cantidad.toFixed(2),
                this.formatCurrency(precioVenta),
                this.formatCurrency(descuento),
                this.formatCurrency(subtotal)
            ];

            celdas.forEach((celda, index) => {
                const td = document.createElement('td');
                td.textContent = celda;
                if (index >= 2) td.className = 'text-right';
                fila.appendChild(td);
            });
            tbody.appendChild(fila);
        });

        table.appendChild(tbody);

        // Crear pie de tabla
        const tfoot = document.createElement('tfoot');
        const totalesRow = document.createElement('tr');
        totalesRow.className = 'table-info';

        const tdEmpty = document.createElement('td');
        tdEmpty.colSpan = 5;
        tdEmpty.className = 'text-end fw-bold';
        tdEmpty.textContent = 'Sumas:';
        totalesRow.appendChild(tdEmpty);

        const tdSubtotal = document.createElement('td');
        tdSubtotal.className = 'text-right fw-bold';
        tdSubtotal.textContent = this.formatCurrency(subtotalGral);
        totalesRow.appendChild(tdSubtotal);

        tfoot.appendChild(totalesRow);

        // Fila IGV
        const igvRow = document.createElement('tr');
        igvRow.className = 'table-info';
        const tdIgvEmpty = document.createElement('td');
        tdIgvEmpty.colSpan = 5;
        tdIgvEmpty.className = 'text-end fw-bold';
        tdIgvEmpty.textContent = 'IGV (18%):';
        igvRow.appendChild(tdIgvEmpty);

        const tdIgv = document.createElement('td');
        const montoIgv = this.impuesto || 0;
        tdIgv.className = 'text-right fw-bold';
        tdIgv.textContent = this.formatCurrency(montoIgv);
        igvRow.appendChild(tdIgv);
        tfoot.appendChild(igvRow);

        // Fila Total
        const totalRow = document.createElement('tr');
        totalRow.className = 'table-success';
        const tdTotalEmpty = document.createElement('td');
        tdTotalEmpty.colSpan = 5;
        tdTotalEmpty.className = 'text-end fw-bold';
        tdTotalEmpty.textContent = 'Total:';
        totalRow.appendChild(tdTotalEmpty);

        const tdTotal = document.createElement('td');
        const total = subtotalGral + montoIgv;
        tdTotal.className = 'text-right fw-bold';
        tdTotal.textContent = this.formatCurrency(total);
        totalRow.appendChild(tdTotal);
        tfoot.appendChild(totalRow);

        table.appendChild(tfoot);

        this.container.innerHTML = '';
        this.container.appendChild(table);
    }

    /**
     * Renderiza la información de un servicio de lavado
     */
    renderServicioLavado() {
        const html = `
            <div class="alert alert-info">
                <h5 class="mb-3"><i class="fa-solid fa-bath"></i> Servicio de Lavado</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Tipo:</strong> Servicio de Lavado
                    </div>
                    <div class="col-md-6">
                        <strong>Hora de Fin:</strong> ${this.horario_lavado ? new Date(this.horario_lavado).toLocaleString('es-ES') : 'Sin especificar'}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total:</strong> ${this.formatCurrency(this.total)}
                    </div>
                </div>
            </div>
        `;
        this.container.innerHTML = html;
    }

    /**
     * Formatea un número como moneda
     * @param {number} value - Valor a formatear
     * @returns {string} Valor formateado
     */
    formatCurrency(value) {
        return 'S/. ' + parseFloat(value).toFixed(2);
    }

    /**
     * Método estático para inicialización desde Blade
     * @param {Object} options - Opciones
     */
    static init(options) {
        return new DetalleVentaTable(options);
    }
}

// Exportar como default
export default DetalleVentaTable;
window.DetalleVentaTable = DetalleVentaTable;
