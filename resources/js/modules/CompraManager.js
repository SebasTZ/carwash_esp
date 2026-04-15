/**
 * CompraManager - Módulo para gestionar el proceso de compras
 * Basado en VentaManager.js, adaptado para el flujo de compras
 */

import { 
    showSuccess, 
    showError, 
    showWarning, 
    showConfirm,
    setButtonLoading,
    clearFormErrors 
} from '@utils/notifications';

import { 
    validatePrecio,
    validateRequired,
    isPositive,
    isInteger,
    validateTableNotEmpty
} from '@utils/validators';

import { 
    formatCurrency,
    parseCurrency 
} from '@utils/formatters';

import {
    refreshBootstrapSelect,
    setBootstrapSelectValue,
} from '@utils/bootstrap-init';

import {
    on,
    getValue,
    setValue,
    getSelectedText,
    setHtml,
    appendHTML,
    removeElement,
    setDisabled,
    focusElement,
    query,
} from '@utils/dom';
import { safeHandler } from '@utils/safe-handler';
import DraftStorage from './shared/DraftStorage';
import {
    hasDetailItems,
    resetTransactionTable,
    restoreDraftTableRows,
    startDraftAutoSave,
    stopDraftAutoSave,
} from './shared/TransactionDraftHelpers';

/**
 * Estado de la compra
 */
class CompraState {
    constructor() {
        this.draftStorage = new DraftStorage('compra_borrador');
        this.productos = [];
        this.contador = 0;
        this.impuesto = 18; // IGV estándar en Perú
        this.sumas = 0;
        this.igv = 0;
        this.total = 0;
    }

    /**
     * Redondea un número a 2 decimales
     */
    round(num) {
        return Math.round(num * 100) / 100;
    }

    /**
     * Calcula los totales de la compra
     */
    calcularTotales() {
        // Calcular sumas
        this.sumas = this.productos.reduce((sum, producto) => {
            return sum + (producto ? producto.subtotal : 0);
        }, 0);

        const tipoComprobanteText = getSelectedText('#comprobante_id');
        const porcentajeIGV = parseFloat(getValue('#impuesto')) || 18;
        
        // Calcular IGV (siempre se aplica en compras)
        this.recalcularIGV(tipoComprobanteText, porcentajeIGV);
        
        return {
            sumas: this.round(this.sumas),
            igv: this.round(this.igv),
            total: this.round(this.total)
        };
    }

    /**
     * Recalcula el IGV basado en el tipo de comprobante
     */
    recalcularIGV(tipoComprobanteText, porcentajeIGV) {
        // En compras, el IGV se aplica si es Factura
        if (tipoComprobanteText === 'Factura') {
            this.igv = this.round(this.sumas / 100 * porcentajeIGV);
        } else {
            this.igv = 0;
        }
        
        this.total = this.round(this.sumas + this.igv);
    }

    /**
     * Agrega un producto al detalle de compra
     */
    agregarProducto(idProducto, nombreProducto, cantidad, precioCompra, precioVenta) {
        const subtotal = this.round(cantidad * precioCompra);
        
        const producto = {
            id: idProducto,
            nombre: nombreProducto,
            cantidad: cantidad,
            precioCompra: precioCompra,
            precioVenta: precioVenta,
            subtotal: subtotal,
            indice: this.contador
        };
        
        this.productos[this.contador] = producto;
        this.contador++;
        
        return producto;
    }

    /**
     * Elimina un producto del detalle
     */
    eliminarProducto(indice) {
        if (this.productos[indice]) {
            this.productos[indice] = null;
        }
    }

    /**
     * Limpia el estado de la compra
     */
    limpiar() {
        this.productos = [];
        this.contador = 0;
        this.sumas = 0;
        this.igv = 0;
        this.total = 0;
    }

    /**
     * Guarda el estado en localStorage
     */
    guardarEnLocalStorage() {
        this.draftStorage.save({
            productos: this.productos,
            contador: this.contador,
        });
    }

    /**
     * Carga el estado desde localStorage
     */
    cargarDesdeLocalStorage() {
        const estado = this.draftStorage.load();
        if (estado) {
            this.productos = estado.productos || [];
            this.contador = estado.contador || 0;
            return true;
        }

        return false;
    }

    /**
     * Limpia el borrador de localStorage
     */
    limpiarLocalStorage() {
        this.draftStorage.clear();
    }
}

/**
 * Clase principal para manejar la compra
 */
export class CompraManager {
    constructor() {
        this.state = new CompraState();
        this.autoGuardarInterval = null;
        this.init();
    }

    /**
     * Inicializa el manager
     */
    init() {
        this.setupEventListeners();
        this.intentarRecuperarBorrador();
        this.iniciarAutoGuardado();
    }

    refreshSelectpicker(selector) {
        refreshBootstrapSelect(selector);
    }

    setSelectpickerValue(selector, value) {
        setValue(selector, value);
        setBootstrapSelectValue(selector, value);
        this.refreshSelectpicker(selector);
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Botón agregar producto
        on('#btn_agregar', 'click', safeHandler(
            () => this.agregarProducto(),
            { message: 'No se pudo agregar el producto a la compra.' }
        ));

        // Cambio de comprobante
        on('#comprobante_id', 'change', () => this.actualizarTotales());

        // Cambio de impuesto personalizado
        on('#impuesto', 'change', () => this.actualizarTotales());

        // Botón cancelar compra
        on('#btnCancelarCompra', 'click', safeHandler(
            () => this.cancelarCompra(),
            { message: 'No se pudo cancelar la compra actual.' }
        ));

        // Validación antes de guardar
        on('#guardar', 'click', safeHandler(
            (event) => this.validarAntesDeGuardar(event),
            { message: 'No se pudo validar la compra antes de guardar.' }
        ));
    }

    /**
     * Agrega un producto al detalle de compra
     */
    agregarProducto() {
        // Obtener valores
        const idProducto = getValue('#producto_id');

        const requiredProducto = validateRequired(idProducto, 'Producto', 'Debe seleccionar un producto');
        if (!requiredProducto.valid) {
            showError(requiredProducto.message);
            return;
        }

        const nombreProducto = getSelectedText('#producto_id');
        const cantidad = parseInt(getValue('#cantidad'));
        const precioCompra = parseFloat(getValue('#precio_compra'));
        const precioVenta = parseFloat(getValue('#precio_venta'));

        // Validar cantidad
        if (!isInteger(cantidad) || !isPositive(cantidad)) {
            showError('La cantidad debe ser un número entero positivo');
            focusElement('#cantidad');
            return;
        }

        // Validar precio de compra
        const precioCompraValidation = validatePrecio(precioCompra, 0);
        if (!precioCompraValidation.valid) {
            showError(precioCompraValidation.message);
            focusElement('#precio_compra');
            return;
        }

        // Validar precio de venta
        const precioVentaValidation = validatePrecio(precioVenta, 0);
        if (!precioVentaValidation.valid) {
            showError(precioVentaValidation.message);
            focusElement('#precio_venta');
            return;
        }

        // Validar que precio de venta >= precio de compra
        if (precioVenta < precioCompra) {
            showWarning('El precio de venta es menor al precio de compra. Esto generará pérdidas.');
            // No bloqueamos, solo advertimos
        }

        // Agregar al estado
        const producto = this.state.agregarProducto(
            idProducto,
            nombreProducto,
            cantidad,
            precioCompra,
            precioVenta
        );

        // Agregar fila a la tabla
        this.agregarFilaTabla(producto);

        // Actualizar totales
        this.actualizarTotales();

        // Limpiar campos
        this.limpiarCampos();

        // Guardar en localStorage
        this.state.guardarEnLocalStorage();

        // Habilitar/deshabilitar botones
        this.actualizarEstadoBotones();

        showSuccess('Producto agregado correctamente');
    }

    /**
     * Agrega una fila a la tabla de detalle
     */
    agregarFilaTabla(producto) {
        const fila = `
            <tr id="fila${producto.indice}" data-indice="${producto.indice}">
                <th>${producto.indice + 1}</th>
                <td>
                    <input type="hidden" name="arrayidproducto[]" value="${producto.id}">
                    ${producto.nombre}
                </td>
                <td>
                    <input type="hidden" name="arraycantidad[]" value="${producto.cantidad}">
                    ${producto.cantidad}
                </td>
                <td>
                    <input type="hidden" name="arraypreciocompra[]" value="${producto.precioCompra}">
                    ${formatCurrency(producto.precioCompra)}
                </td>
                <td>
                    <input type="hidden" name="arrayprecioventa[]" value="${producto.precioVenta}">
                    ${formatCurrency(producto.precioVenta)}
                </td>
                <td>${formatCurrency(producto.subtotal)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" type="button" data-eliminar="${producto.indice}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        appendHTML('#tabla_detalle tbody', fila);

        // Event listener para eliminar
        const deleteButton = query(`[data-eliminar="${producto.indice}"]`);
        if (deleteButton) {
            deleteButton.addEventListener('click', () => {
                this.eliminarProducto(producto.indice);
            });
        }
    }

    /**
     * Elimina un producto del detalle
     */
    async eliminarProducto(indice) {
        const confirmado = await showConfirm(
            '¿Eliminar producto?',
            'Esta acción no se puede deshacer',
            'Sí, eliminar',
            'Cancelar'
        );

        if (!confirmado) return;

        // Eliminar del estado
        this.state.eliminarProducto(indice);

        // Eliminar fila de la tabla
        removeElement(`#fila${indice}`);

        // Actualizar totales
        this.actualizarTotales();

        // Guardar en localStorage
        this.state.guardarEnLocalStorage();

        // Actualizar botones
        this.actualizarEstadoBotones();

        showSuccess('Producto eliminado');
    }

    /**
     * Actualiza los totales mostrados
     */
    actualizarTotales() {
        const totales = this.state.calcularTotales();

        setHtml('#sumas', formatCurrency(totales.sumas));
        setHtml('#igv', formatCurrency(totales.igv));
        setHtml('#total', formatCurrency(totales.total));
        setValue('#inputTotal', totales.total);
    }

    /**
     * Limpia los campos del formulario de producto
     */
    limpiarCampos() {
        setValue('#cantidad', '');
        setValue('#precio_compra', '');
        setValue('#precio_venta', '');
        this.setSelectpickerValue('#producto_id', '');
    }

    /**
     * Habilita/deshabilita botones según el estado
     */
    actualizarEstadoBotones() {
        const hayProductos = hasDetailItems(this.state.productos);
        
        setDisabled('#guardar', !hayProductos);
        setDisabled('#btnCancelarCompra', !hayProductos);
    }

    /**
     * Cancela la compra y limpia todo
     */
    async cancelarCompra() {
        const confirmado = await showConfirm(
            '¿Cancelar compra?',
            'Se perderán todos los productos agregados',
            'Sí, cancelar',
            'No'
        );

        if (!confirmado) return;

        resetTransactionTable({
            tableBodySelector: '#tabla_detalle tbody',
        });

        // Limpiar estado
        this.state.limpiar();
        this.state.limpiarLocalStorage();

        // Actualizar UI
        this.actualizarTotales();
        this.limpiarCampos();
        this.actualizarEstadoBotones();

        showSuccess('Compra cancelada');
    }

    /**
     * Valida antes de guardar la compra
     */
    validarAntesDeGuardar(event) {
        // Validar que haya productos
        const validacionTabla = validateTableNotEmpty('tabla_detalle');
        if (!validacionTabla.valid) {
            event.preventDefault();
            showError('Debe agregar al menos un producto');
            return false;
        }

        // Mostrar loading en el botón
        const btnGuardar = document.getElementById('guardar');
        setButtonLoading(btnGuardar, true);

        // Limpiar localStorage después de guardar
        setTimeout(() => {
            this.state.limpiarLocalStorage();
        }, 1000);

        return true;
    }

    /**
     * Intenta recuperar un borrador guardado
     */
    async intentarRecuperarBorrador() {
        const hayBorrador = this.state.cargarDesdeLocalStorage();
        
        if (!hayBorrador) return;

        const recuperar = await showConfirm(
            '¿Recuperar compra anterior?',
            'Se encontró una compra sin completar. ¿Deseas recuperarla?',
            'Sí, recuperar',
            'No, empezar nueva compra'
        );

        if (recuperar) {
            this.recuperarBorrador();
        } else {
            this.state.limpiarLocalStorage();
            this.state.limpiar();
            resetTransactionTable({
                tableBodySelector: '#tabla_detalle tbody',
            });
            this.actualizarTotales();
            this.limpiarCampos();
            this.actualizarEstadoBotones();
        }
    }

    /**
     * Recupera el borrador y lo muestra en la UI
     */
    recuperarBorrador() {
        restoreDraftTableRows({
            productos: this.state.productos,
            tableBodySelector: '#tabla_detalle tbody',
            addRow: (producto) => {
                this.agregarFilaTabla(producto);
            },
        });

        // Actualizar totales y botones
        this.actualizarTotales();
        this.actualizarEstadoBotones();

        showSuccess('Compra recuperada correctamente');
    }

    /**
     * Inicia el auto-guardado periódico
     */
    iniciarAutoGuardado() {
        this.autoGuardarInterval = startDraftAutoSave(this.state);
    }

    /**
     * Detiene el auto-guardado
     */
    detenerAutoGuardado() {
        stopDraftAutoSave(this.autoGuardarInterval);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Solo inicializar si estamos en la página de crear compra
    if (document.getElementById('btn_agregar') && window.location.pathname.includes('/compras/')) {
        window.compraManager = new CompraManager();
    }
});

// Exportar también para módulos ES6
export default CompraManager;
