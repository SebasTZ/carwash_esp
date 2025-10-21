/**
 * VentaManager - Módulo para gestionar el proceso de ventas
 * Reemplaza el código inline de resources/views/venta/create.blade.php
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
    validateStock, 
    validatePrecio, 
    validateDescuento,
    isPositive,
    isInteger,
    validateTableNotEmpty
} from '@utils/validators';

import { 
    formatCurrency,
    parseCurrency 
} from '@utils/formatters';

/**
 * Estado de la venta
 */
class VentaState {
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
     * Calcula los totales de la venta
     */
    calcularTotales() {
        // Calcular sumas
        this.sumas = this.productos.reduce((sum, producto) => {
            return sum + (producto ? producto.subtotal : 0);
        }, 0);
        
        // Calcular IGV
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
        const incluirIGV = $('#con_igv').is(':checked');
        const porcentajeIGV = parseFloat($('#impuesto').val()) || 18;
        
        if (tipoComprobanteText === 'Factura' && incluirIGV) {
            this.igv = this.round(this.sumas / 100 * porcentajeIGV);
        } else {
            this.igv = 0;
        }
        
        this.total = this.round(this.sumas + this.igv);
    }

    /**
     * Agrega un producto al detalle de venta
     */
    agregarProducto(idProducto, nombreProducto, cantidad, precioVenta, descuento, esServicio) {
        const subtotal = this.round(cantidad * precioVenta - descuento);
        
        const producto = {
            id: idProducto,
            nombre: nombreProducto,
            cantidad: cantidad,
            precioVenta: precioVenta,
            descuento: descuento,
            subtotal: subtotal,
            esServicio: esServicio,
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
     * Limpia el estado de la venta
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
            localStorage.setItem('venta_borrador', JSON.stringify(estado));
        } catch (error) {
            console.warn('No se pudo guardar en localStorage:', error);
        }
    }

    /**
     * Carga el estado desde localStorage
     */
    cargarDesdeLocalStorage() {
        try {
            const guardado = localStorage.getItem('venta_borrador');
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
            localStorage.removeItem('venta_borrador');
        } catch (error) {
            console.warn('No se pudo limpiar localStorage:', error);
        }
    }
}

/**
 * Clase principal para manejar la venta
 */
export class VentaManager {
    constructor() {
        this.state = new VentaState();
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

        // Cambio de producto
        $('#producto_id').on('change', () => this.mostrarValoresProducto());

        // Cambio de comprobante o checkbox IGV
        $('#comprobante_id, #con_igv').on('change', () => this.actualizarTotales());

        // Cambio de impuesto personalizado
        $('#impuesto').on('change', () => this.actualizarTotales());

        // Botón cancelar venta
        $('#btnCancelarVenta').on('click', () => this.cancelarVenta());

        // Validación antes de guardar
        $('#guardar').on('click', (event) => this.validarAntesDeGuardar(event));

        // Cambio de medio de pago
        $('#medio_pago').on('change', () => this.manejarCambioMedioPago());

        // Checkbox servicio de lavado
        $('#servicio_lavado').on('change', () => this.toggleHorarioLavado());
    }

    /**
     * Muestra los valores del producto seleccionado
     */
    mostrarValoresProducto() {
        const productoValue = $('#producto_id').val();
        if (!productoValue) return;

        const [idProducto, stock, precioVenta, esServicio] = productoValue.split('-');
        const esServicioLavado = esServicio === '1';

        // Mostrar stock
        if (esServicioLavado) {
            $('#stock').val('∞'); // Símbolo de infinito para servicios
        } else {
            $('#stock').val(stock);
        }

        // Mostrar precio
        $('#precio_venta').val(precioVenta);
    }

    /**
     * Agrega un producto al detalle de venta
     */
    agregarProducto() {
        // Obtener valores
        const productoValue = $('#producto_id').val();
        
        if (!productoValue) {
            showError('Debe seleccionar un producto');
            return;
        }

        const [idProducto, stock, , esServicio] = productoValue.split('-');
        const nombreProducto = $('#producto_id option:selected').text();
        const cantidad = parseInt($('#cantidad').val());
        const precioVenta = parseFloat($('#precio_venta').val());
        const descuento = parseFloat($('#descuento').val()) || 0;
        const esServicioLavado = esServicio === '1';

        // Validar cantidad
        if (!isInteger(cantidad) || !isPositive(cantidad)) {
            showError('La cantidad debe ser un número entero positivo');
            $('#cantidad').focus();
            return;
        }

        // Validar stock (solo para productos, no servicios)
        const stockValidation = validateStock(cantidad, stock, esServicioLavado);
        if (!stockValidation.valid) {
            showError(stockValidation.message);
            $('#cantidad').focus();
            return;
        }

        // Validar precio
        const precioValidation = validatePrecio(precioVenta, 0);
        if (!precioValidation.valid) {
            showError(precioValidation.message);
            $('#precio_venta').focus();
            return;
        }

        // Validar descuento
        const descuentoValidation = validateDescuento(descuento, precioVenta, cantidad);
        if (!descuentoValidation.valid) {
            showError(descuentoValidation.message);
            $('#descuento').focus();
            return;
        }

        // Agregar al estado
        const producto = this.state.agregarProducto(
            idProducto,
            nombreProducto,
            cantidad,
            precioVenta,
            descuento,
            esServicioLavado
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
                    <input type="hidden" name="arrayprecioventa[]" value="${producto.precioVenta}">
                    ${formatCurrency(producto.precioVenta)}
                </td>
                <td>
                    <input type="hidden" name="arraydescuento[]" value="${producto.descuento}">
                    ${formatCurrency(producto.descuento)}
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
        $('#descuento').val('');
        $('#producto_id').val('').selectpicker('refresh');
        $('#stock').val('');
        $('#precio_venta').val('');
    }

    /**
     * Habilita/deshabilita botones según el estado
     */
    actualizarEstadoBotones() {
        const hayProductos = this.state.productos.some(p => p !== null);
        
        $('#guardar').prop('disabled', !hayProductos);
        $('#btnCancelarVenta').prop('disabled', !hayProductos);
    }

    /**
     * Cancela la venta y limpia todo
     */
    async cancelarVenta() {
        const confirmado = await showConfirm(
            '¿Cancelar venta?',
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

        showSuccess('Venta cancelada');
    }

    /**
     * Valida antes de guardar la venta
     */
    validarAntesDeGuardar(event) {
        // Validar que haya productos
        const validacionTabla = validateTableNotEmpty('tabla_detalle');
        if (!validacionTabla.valid) {
            event.preventDefault();
            showError('Debe agregar al menos un producto');
            return false;
        }

        // Validar servicio de lavado y horario
        const servicioLavado = $('#servicio_lavado').is(':checked');
        const horarioLavado = $('#horario_lavado').val();

        if (servicioLavado && !horarioLavado) {
            event.preventDefault();
            showError('Debe ingresar el horario estimado de culminación del lavado');
            $('#horario_lavado').focus();
            return false;
        }

        // Si hay servicio de lavado, copiar al campo hidden
        if (servicioLavado) {
            $('#horario_lavado_hidden').val(horarioLavado);
        } else {
            $('#horario_lavado_hidden').val('');
        }

        // Mostrar loading en el botón
        const btnGuardar = document.getElementById('guardar');
        setButtonLoading(btnGuardar, true);

        // Limpiar localStorage después de guardar (se hará en el backend)
        setTimeout(() => {
            this.state.limpiarLocalStorage();
        }, 1000);

        return true;
    }

    /**
     * Maneja el cambio de medio de pago
     */
    manejarCambioMedioPago() {
        const medioPago = $('#medio_pago').val();

        // Tarjeta regalo
        if (medioPago === 'tarjeta_regalo') {
            $('#tarjeta_regalo_div').show();
            $('#tarjeta_regalo_codigo').attr('required', true);
        } else {
            $('#tarjeta_regalo_div').hide();
            $('#tarjeta_regalo_codigo').removeAttr('required').val('');
        }

        // Tarjeta crédito
        if (medioPago === 'tarjeta_credito') {
            $('#tarjeta_credito_div').show();
        } else {
            $('#tarjeta_credito_div').hide();
            $('#tarjeta_credito').val('');
        }

        // Lavado gratis
        if (medioPago === 'lavado_gratis') {
            $('#lavado_gratis_div').show();
        } else {
            $('#lavado_gratis_div').hide();
        }
    }

    /**
     * Toggle del campo horario de lavado
     */
    toggleHorarioLavado() {
        if ($('#servicio_lavado').is(':checked')) {
            $('#horario_lavado_div').show();
            $('#horario_lavado').attr('required', true);
        } else {
            $('#horario_lavado_div').hide();
            $('#horario_lavado').removeAttr('required').val('');
        }
    }

    /**
     * Intenta recuperar un borrador guardado
     */
    async intentarRecuperarBorrador() {
        const hayBorrador = this.state.cargarDesdeLocalStorage();
        
        if (!hayBorrador) return;

        const recuperar = await showConfirm(
            '¿Recuperar venta anterior?',
            'Se encontró una venta sin completar. ¿Deseas recuperarla?',
            'Sí, recuperar',
            'No, empezar nueva venta'
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

        showSuccess('Venta recuperada correctamente');
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
                console.log('💾 Auto-guardado realizado');
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
    // Solo inicializar si estamos en la página de crear venta
    if (document.getElementById('btn_agregar')) {
        window.ventaManager = new VentaManager();
        console.log('🚀 VentaManager inicializado');
    }
});

// Exportar también para módulos ES6
export default VentaManager;
