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
    isPositive,
    isInteger,
    validateTableNotEmpty
} from '@utils/validators';

import { 
    formatCurrency,
    parseCurrency 
} from '@utils/formatters';

/**
 * Estado de la compra
 */
class CompraState {
    constructor() {
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
        
        // Calcular IGV (siempre se aplica en compras)
        this.recalcularIGV();
        
        return {
            sumas: this.round(this.sumas),
            igv: this.round(this.igv),
            total: this.round(this.total)
        };
    }

    /**
     * Recalcula el IGV basado en el tipo de comprobante
     */
    recalcularIGV() {
        const tipoComprobanteText = $('#comprobante_id option:selected').text();
        const porcentajeIGV = parseFloat($('#impuesto').val()) || 18;
        
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
        try {
            const estado = {
                productos: this.productos,
                contador: this.contador,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('compra_borrador', JSON.stringify(estado));
        } catch (error) {
            console.warn('No se pudo guardar en localStorage:', error);
        }
    }

    /**
     * Carga el estado desde localStorage
     */
    cargarDesdeLocalStorage() {
        try {
            const guardado = localStorage.getItem('compra_borrador');
            if (guardado) {
                const estado = JSON.parse(guardado);
                this.productos = estado.productos || [];
                this.contador = estado.contador || 0;
                return true;
            }
        } catch (error) {
            console.warn('No se pudo cargar desde localStorage:', error);
        }
        return false;
    }

    /**
     * Limpia el borrador de localStorage
     */
    limpiarLocalStorage() {
        try {
            localStorage.removeItem('compra_borrador');
        } catch (error) {
            console.warn('No se pudo limpiar localStorage:', error);
        }
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

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Botón agregar producto
        $('#btn_agregar').on('click', () => this.agregarProducto());

        // Cambio de comprobante
        $('#comprobante_id').on('change', () => this.actualizarTotales());

        // Cambio de impuesto personalizado
        $('#impuesto').on('change', () => this.actualizarTotales());

        // Botón cancelar compra
        $('#btnCancelarCompra').on('click', () => this.cancelarCompra());

        // Validación antes de guardar
        $('#guardar').on('click', (event) => this.validarAntesDeGuardar(event));
    }

    /**
     * Agrega un producto al detalle de compra
     */
    agregarProducto() {
        // Obtener valores
        const idProducto = $('#producto_id').val();
        
        if (!idProducto) {
            showError('Debe seleccionar un producto');
            return;
        }

        const nombreProducto = $('#producto_id option:selected').text();
        const cantidad = parseInt($('#cantidad').val());
        const precioCompra = parseFloat($('#precio_compra').val());
        const precioVenta = parseFloat($('#precio_venta').val());

        // Validar cantidad
        if (!isInteger(cantidad) || !isPositive(cantidad)) {
            showError('La cantidad debe ser un número entero positivo');
            $('#cantidad').focus();
            return;
        }

        // Validar precio de compra
        const precioCompraValidation = validatePrecio(precioCompra, 0);
        if (!precioCompraValidation.valid) {
            showError(precioCompraValidation.message);
            $('#precio_compra').focus();
            return;
        }

        // Validar precio de venta
        const precioVentaValidation = validatePrecio(precioVenta, 0);
        if (!precioVentaValidation.valid) {
            showError(precioVentaValidation.message);
            $('#precio_venta').focus();
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

        $('#tabla_detalle tbody').append(fila);

        // Event listener para eliminar
        $(`[data-eliminar="${producto.indice}"]`).on('click', () => {
            this.eliminarProducto(producto.indice);
        });
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
        $(`#fila${indice}`).remove();

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

        $('#sumas').html(formatCurrency(totales.sumas));
        $('#igv').html(formatCurrency(totales.igv));
        $('#total').html(formatCurrency(totales.total));
        $('#inputTotal').val(totales.total);
    }

    /**
     * Limpia los campos del formulario de producto
     */
    limpiarCampos() {
        $('#cantidad').val('');
        $('#precio_compra').val('');
        $('#precio_venta').val('');
        $('#producto_id').val('').selectpicker('refresh');
    }

    /**
     * Habilita/deshabilita botones según el estado
     */
    actualizarEstadoBotones() {
        const hayProductos = this.state.productos.some(p => p !== null);
        
        $('#guardar').prop('disabled', !hayProductos);
        $('#btnCancelarCompra').prop('disabled', !hayProductos);
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

        // Limpiar tabla
        $('#tabla_detalle tbody').empty();
        
        // Agregar fila vacía
        const filaVacia = `
            <tr>
                <th></th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        `;
        $('#tabla_detalle tbody').append(filaVacia);

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
        }
    }

    /**
     * Recupera el borrador y lo muestra en la UI
     */
    recuperarBorrador() {
        // Limpiar tabla actual
        $('#tabla_detalle tbody').empty();

        // Agregar cada producto del borrador
        this.state.productos.forEach((producto) => {
            if (producto !== null) {
                this.agregarFilaTabla(producto);
            }
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
        // Auto-guardar cada 30 segundos
        this.autoGuardarInterval = setInterval(() => {
            const hayProductos = this.state.productos.some(p => p !== null);
            if (hayProductos) {
                this.state.guardarEnLocalStorage();
                console.log('💾 Auto-guardado de compra realizado');
            }
        }, 30000); // 30 segundos
    }

    /**
     * Detiene el auto-guardado
     */
    detenerAutoGuardado() {
        if (this.autoGuardarInterval) {
            clearInterval(this.autoGuardarInterval);
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Solo inicializar si estamos en la página de crear compra
    if (document.getElementById('btn_agregar') && window.location.pathname.includes('/compras/')) {
        console.log('[CompraManager] Inicializando módulo de compra...');
        window.compraManager = new CompraManager();
        console.log('🚀 CompraManager inicializado');
    }
});

// Exportar también para módulos ES6
export default CompraManager;
