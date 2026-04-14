/**
 * VentaManager - Módulo para gestionar el proceso de ventas
 * Reemplaza el código inline de resources/views/venta/create.blade.php
 */

import 'bootstrap-select/dist/css/bootstrap-select.min.css';
import 'bootstrap-select/dist/js/bootstrap-select.min.js';

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
    initBootstrapSelect,
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
    clearHTML,
    removeElement,
    showElement,
    hideElement,
    setRequired,
    setDisabled,
    focusElement,
    isChecked,
    query,
} from '@utils/dom';

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

        const tipoComprobanteText = getSelectedText('#comprobante_id');
        const incluirIGV = isChecked('#con_igv');
        const porcentajeIGV = parseFloat(getValue('#impuesto')) || 0;
        
        // Calcular IGV
        this.recalcularIGV(tipoComprobanteText, incluirIGV, porcentajeIGV);
        
        return {
            sumas: this.round(this.sumas),
            igv: this.round(this.igv),
            total: this.round(this.total)
        };
    }

    /**
     * Recalcula el IGV basado en el tipo de comprobante
     */
    recalcularIGV(tipoComprobanteText, incluirIGV, porcentajeIGV) {
        if (tipoComprobanteText === 'Factura' && incluirIGV && porcentajeIGV > 0) {
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
    console.log('[VentaManager] Estado de venta limpiado');
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
        this.initBootstrapSelect();
        this.setupEventListeners();
        this.intentarRecuperarBorrador();
        this.iniciarAutoGuardado();
    }

    /**
     * Inicializa bootstrap-select en la vista de crear venta
     */
    initBootstrapSelect() {
        initBootstrapSelect('.selectpicker');
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
        on('#btn_agregar', 'click', () => this.agregarProducto());

        // Cambio de producto
        on('#producto_id', 'change', () => this.mostrarValoresProducto());

        // Cambio de comprobante o checkbox IGV
        on('#comprobante_id, #con_igv', 'change', () => this.actualizarTotales());

        // Cambio de impuesto personalizado
        on('#impuesto', 'change', () => this.actualizarTotales());

        // Botón cancelar venta
        on('#btnCancelarVenta', 'click', () => this.cancelarVenta());

        // Validación antes de guardar - Interceptar el submit del formulario
        on('#venta-form', 'submit', (event) => {
            console.log('[VentaManager] Submit del formulario detectado');
            const resultado = this.validarAntesDeGuardar(event);
            console.log('[VentaManager] Resultado validación:', resultado);
            if (!resultado) {
                event.preventDefault();
                console.log('[VentaManager] Submit cancelado por validación');
                return false;
            }
            console.log('[VentaManager] Permitiendo submit del formulario');
        });

        // Cambio de medio de pago
        on('#medio_pago', 'change', () => this.manejarCambioMedioPago());

        // Cambio de cliente - validar fidelización si ya está seleccionado "Lavado Gratis"
        on('#cliente_id', 'change', () => this.validarClienteConLavadoGratis());

        // Checkbox servicio de lavado
        on('#servicio_lavado', 'change', () => this.toggleHorarioLavado());
    }

    /**
     * Valida si el cliente cambió y ya tienen "Lavado Gratis" seleccionado
     */
    validarClienteConLavadoGratis() {
        if (getValue('#medio_pago') === 'lavado_gratis') {
            // Si ya tenían lavado gratis seleccionado y cambiaron de cliente,
            // validar si el nuevo cliente también tiene puntos
            const clienteId = getValue('#cliente_id');
            if (clienteId) {
                this.validarFidelizacionLavado(clienteId);
            }
        }
    }


    /**
     * Muestra los valores del producto seleccionado
     */
    mostrarValoresProducto() {
        const productoValue = getValue('#producto_id');
        if (!productoValue) return;

        const [idProducto, stock, precioVenta, esServicio] = productoValue.split('-');
        const esServicioLavado = esServicio === '1';

        // Mostrar stock
        if (esServicioLavado) {
            setValue('#stock', '∞'); // Símbolo de infinito para servicios
        } else {
            setValue('#stock', stock);
        }

        // Mostrar precio
        setValue('#precio_venta', precioVenta);
    }

    /**
     * Agrega un producto al detalle de venta
     */
    agregarProducto() {
        // Obtener valores
        const productoValue = getValue('#producto_id');

        const requiredProducto = validateRequired(productoValue, 'Producto', 'Debe seleccionar un producto');
        if (!requiredProducto.valid) {
            showError(requiredProducto.message);
            return;
        }

        const [idProducto, stock, , esServicio] = productoValue.split('-');
        const nombreProducto = getSelectedText('#producto_id');
        const cantidad = parseInt(getValue('#cantidad'));
        const precioVenta = parseFloat(getValue('#precio_venta'));
        const descuento = parseFloat(getValue('#descuento')) || 0;
        const esServicioLavado = esServicio === '1';

        // Validar cantidad
        if (!isInteger(cantidad) || !isPositive(cantidad)) {
            showError('La cantidad debe ser un número entero positivo');
            focusElement('#cantidad');
            return;
        }
        // Solo validar stock si NO es servicio de lavado
        if (!esServicioLavado) {
            const stockValidation = validateStock(cantidad, stock, esServicioLavado);
            if (!stockValidation.valid) {
                showError(stockValidation.message);
                focusElement('#cantidad');
                return;
            }
        }

        // Validar precio
        const precioValidation = validatePrecio(precioVenta, 0);
        if (!precioValidation.valid) {
            showError(precioValidation.message);
            focusElement('#precio_venta');
            return;
        }

        // Validar descuento
        const descuentoValidation = validateDescuento(descuento, precioVenta, cantidad);
        if (!descuentoValidation.valid) {
            showError(descuentoValidation.message);
            focusElement('#descuento');
            return;
        }

        // Agregar al estado
        const producto = this.state.agregarProducto(
            idProducto,
            nombreProducto,
            cantidad,
            precioVenta,
            descuento
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
                <td>${producto.nombre}</td>
                <td>${producto.cantidad}</td>
                <td>${formatCurrency(producto.precioVenta)}</td>
                <td>${formatCurrency(producto.descuento)}</td>
                <td>${formatCurrency(producto.subtotal)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" type="button" data-eliminar="${producto.indice}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        appendHTML('#tabla_detalle tbody', fila);

        // Agregar campos ocultos en contenedor especial (FUERA de la tabla)
        const camposOcultos = `
            <div data-producto-indice="${producto.indice}">
                <input type="hidden" name="arrayidproducto[]" value="${producto.id}">
                <input type="hidden" name="arraycantidad[]" value="${producto.cantidad}">
                <input type="hidden" name="arrayprecioventa[]" value="${producto.precioVenta}">
                <input type="hidden" name="arraydescuento[]" value="${producto.descuento}">
            </div>
        `;
        appendHTML('#campos-productos-ocultos', camposOcultos);

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

        // Eliminar campos ocultos correspondientes
        removeElement(`[data-producto-indice="${indice}"]`);

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

        // LOG para depuración
        console.log('--- [VentaManager] Actualizando totales ---');
        console.log('Productos:', this.state.productos.filter(p => p !== null));
        console.log('Sumas:', totales.sumas);
        console.log('IGV:', totales.igv);
        console.log('Total:', totales.total);
        console.log('Tipo comprobante:', getSelectedText('#comprobante_id'));
        console.log('Incluir IGV:', isChecked('#con_igv'));

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
        setValue('#descuento', '');
        setValue('#producto_id', '');
        this.refreshSelectpicker('#producto_id');
        setValue('#stock', '');
        setValue('#precio_venta', '');
    }

    /**
     * Habilita/deshabilita botones según el estado
     */
    actualizarEstadoBotones() {
        const hayProductos = this.state.productos.some(p => p !== null);
        
        setDisabled('#guardar', !hayProductos);
        setDisabled('#btnCancelarVenta', !hayProductos);
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
        clearHTML('#tabla_detalle tbody');
        
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
        appendHTML('#tabla_detalle tbody', filaVacia);

        // Limpiar campos ocultos
        clearHTML('#campos-productos-ocultos');

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
        console.log('[VentaManager] Iniciando validación antes de guardar');
        
        // DEBUG: Ver qué datos se van a enviar
        const ventaForm = query('#venta-form');
        const formData = ventaForm ? new FormData(ventaForm) : new FormData();
        console.log('[VentaManager] FormData completo:', Object.fromEntries(formData));
        
        // Validar que haya productos
        const validacionTabla = validateTableNotEmpty('tabla_detalle');
        console.log('[VentaManager] Validación tabla:', validacionTabla);
        if (!validacionTabla.valid) {
            event.preventDefault();
            showError('Debe agregar al menos un producto');
            return false;
        }

        // Validar servicio de lavado y horario
        const servicioLavado = isChecked('#servicio_lavado');
        const horarioLavado = getValue('#horario_lavado');
        console.log('[VentaManager] Servicio lavado:', servicioLavado, 'Horario:', horarioLavado);

        const requiredHorarioLavado = validateRequired(
            horarioLavado,
            'Horario estimado de culminación',
            'Debe ingresar el horario estimado de culminación del lavado'
        );

        if (servicioLavado && !requiredHorarioLavado.valid) {
            event.preventDefault();
            showError(requiredHorarioLavado.message);
            focusElement('#horario_lavado');
            return false;
        }

        console.log('[VentaManager] Validación exitosa, enviando formulario...');

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
        const medioPago = getValue('#medio_pago');

        // Tarjeta regalo
        if (medioPago === 'tarjeta_regalo') {
            showElement('#tarjeta_regalo_div');
            setRequired('#tarjeta_regalo_id', true);
        } else {
            hideElement('#tarjeta_regalo_div');
            setRequired('#tarjeta_regalo_id', false);
            setValue('#tarjeta_regalo_id', '');
        }

        // Lavado gratis
        if (medioPago === 'lavado_gratis') {
            // Validar si el cliente tiene suficientes puntos ANTES de permitir la selección
            const clienteId = getValue('#cliente_id');
            const requiredCliente = validateRequired(
                clienteId,
                'Cliente',
                'Debes seleccionar un cliente primero'
            );
            
            if (!requiredCliente.valid) {
                showError(requiredCliente.message);
                this.setSelectpickerValue('#medio_pago', 'efectivo');
                return;
            }

            // Hacer la validación al servidor
            this.validarFidelizacionLavado(clienteId);
        } else {
            hideElement('#lavado_gratis_div');
            // Remover el hidden input cuando se selecciona otro método de pago
            clearHTML('#campos-lavado-gratis-ocultos');
            // Recalcular totales al cambiar de lavado_gratis a otro método
            this.actualizarTotales();
        }
    }

    /**
     * Valida si el cliente tiene puntos de fidelización para lavado gratis
     */
    async validarFidelizacionLavado(clienteId) {
        try {
            const response = await fetch(`/validar-fidelizacion-lavado/${clienteId}`);
            
            // Verificar si la respuesta es OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Verificar si es JSON válido
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Respuesta no es JSON válido');
            }
            
            const data = await response.json();

            if (!data.valido) {
                // No tiene suficientes lavados, mostrar advertencia detallada
                const titulo = '⚠️ Lavados Insuficientes';
                const mensaje = `${data.mensaje}\n\n📊 Progreso: ${data.lavados_actuales}/${data.lavados_necesarios} lavados`;
                
                showWarning(`${titulo}: ${mensaje}`);
                
                // Revertir el select al valor anterior
                this.setSelectpickerValue('#medio_pago', 'efectivo');
                return;
            }

            // ✅ Tiene lavados suficientes, proceder
            showElement('#lavado_gratis_div');
            
            // Actualizar detalles en el div de lavado gratis
            const clienteNombre = getSelectedText('#cliente_id');
            const detallesHtml = `
                👤 <strong>${clienteNombre}</strong><br>
                📊 Lavados acumulados: <strong>${data.lavados_actuales}</strong><br>
                ✨ Lavados gratis disponibles: <strong>${data.lavados_disponibles}</strong>
            `;
            setHtml('#detalles_lavado_gratis', detallesHtml);
            
            // Mostrar mensaje de éxito con detalles
            const titulo = '✅ Lavado Gratis Disponible';
            const mensaje = `${data.mensaje}\n\n✨ El cliente podrá disfrutar de este lavado sin costo.`;
            
            showSuccess(`${titulo}: ${mensaje}`);
            
            // Agregar el hidden input solo cuando se selecciona lavado_gratis
            const container = document.getElementById('campos-lavado-gratis-ocultos');
            if (!document.querySelector('input[name="lavado_gratis"]')) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'lavado_gratis';
                hiddenInput.value = '1';
                container.appendChild(hiddenInput);
            }
            
            // Si es lavado gratis, el total debe ser 0 (es gratis)
            const totalZero = 0.00;
            setValue('#inputTotal', totalZero);
            setHtml('#total', formatCurrency(totalZero));
            setHtml('#igv', formatCurrency(0));
            
            console.log(`✅ Lavado Gratis seleccionado: ${data.mensaje}`);
            
            // Asegurar que el botón de guardar esté habilitado
            this.actualizarEstadoBotones();
        } catch (error) {
            console.error('Error validando fidelización:', error);
            showError('No se pudo validar los puntos de fidelización: ' + error.message);
            this.setSelectpickerValue('#medio_pago', 'efectivo');
        }
    }


    /**
     * Toggle del campo horario de lavado
     */
    toggleHorarioLavado() {
        if (isChecked('#servicio_lavado')) {
            showElement('#horario_lavado_div');
            setRequired('#horario_lavado', true);
        } else {
            hideElement('#horario_lavado_div');
            setRequired('#horario_lavado', false);
            setValue('#horario_lavado', '');
        }
    }

    /**
     * Intenta recuperar un borrador guardado
     */
    async intentarRecuperarBorrador() {
        const hayBorrador = this.state.cargarDesdeLocalStorage();
        console.log('[VentaManager] ¿Hay borrador en localStorage?', hayBorrador);
        if (!hayBorrador) return;

        const recuperar = await showConfirm(
            '¿Recuperar venta anterior?',
            'Se encontró una venta sin completar. ¿Deseas recuperarla?',
            'Sí, recuperar',
            'No, empezar nueva venta'
        );
        console.log('[VentaManager] Selección recuperación:', recuperar);
        if (recuperar) {
            this.recuperarBorrador();
        } else {
            // Limpiar localStorage
            this.state.limpiarLocalStorage();
            // Limpiar estado
            this.state.limpiar();
            // Limpiar UI
            clearHTML('#tabla_detalle tbody');
            this.actualizarTotales();
            this.limpiarCampos();
            this.actualizarEstadoBotones();
            console.log('[VentaManager] Borrador limpiado y venta reiniciada');
        }
    }    /**
     * Recupera el borrador y lo muestra en la UI
     */
    recuperarBorrador() {
        // Limpiar tabla actual
        clearHTML('#tabla_detalle tbody');

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
