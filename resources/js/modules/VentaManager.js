/**
 * VentaManager - Módulo para gestionar el proceso de ventas
 * Reemplaza el código inline de resources/views/venta/create.blade.php
 */

import axios from 'axios';

import { 
    showSuccess, 
    showError, 
    showWarning, 
    showConfirm,
    setButtonLoading 
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
    formatCurrency
} from '@utils/formatters';

import {
    on,
    getValue,
    setValue,
    getSelectedText,
    setSelectSearchValue,
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
import { readJsonScript } from '@utils/json-script';
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
 * Estado de la venta
 */
class VentaState {
    constructor() {
        this.draftStorage = new DraftStorage('venta_borrador');
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
 * Clase principal para manejar la venta
 */
export class VentaManager {
    constructor() {
        this.state = new VentaState();
        this.autoGuardarInterval = null;
        this.productosConfig = readJsonScript('venta-productos-config', {}, 'VentaManager');
        this.livewireSelectHandler = null;
        this.init();
    }

    /**
     * Inicializa el manager
     */
    init() {
        this.setupLivewireSelectBridge();
        this.setupEventListeners();
        this.intentarRecuperarBorrador();
        this.iniciarAutoGuardado();
    }

    setupLivewireSelectBridge() {
        if (this.livewireSelectHandler) {
            return;
        }

        this.livewireSelectHandler = (event) => {
            const detail = event?.detail;
            if (!detail || typeof detail !== 'object') {
                return;
            }

            const field = String(detail.field || '').trim();
            if (!field) {
                return;
            }

            const selector = `#${field}`;
            const input = query(selector);
            if (!input) {
                return;
            }

            const value = detail.value ?? '';
            const label = detail.label ?? '';

            setValue(selector, value);
            input.dataset.selectedLabel = label;

            if (field === 'producto_id') {
                const selectedId = String(value || '').trim();
                const config = detail.config && typeof detail.config === 'object' ? detail.config : null;

                if (selectedId && config) {
                    this.productosConfig[selectedId] = {
                        stock: Number(config.stock ?? 0),
                        precio_venta: Number(config.precio_venta ?? 0),
                        es_servicio_lavado: this.normalizarBooleano(config.es_servicio_lavado),
                        label: String(config.label || label || ''),
                    };
                }
            }

            input.dispatchEvent(new Event('change', { bubbles: true }));
        };

        window.addEventListener('venta-select-updated', this.livewireSelectHandler);
    }

    setSelectFieldValue(selector, value, label = null) {
        const element = query(selector);
        if (!element) {
            return;
        }

        if (element.tagName === 'SELECT') {
            setValue(selector, value);
            element.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }

        if (element.dataset?.livewireSelect === '1') {
            setValue(selector, value || '');
            element.dataset.selectedLabel = label || '';
            element.dispatchEvent(new Event('change', { bubbles: true }));

            window.dispatchEvent(new CustomEvent('venta-livewire-select-sync', {
                detail: {
                    field: String(element.id || selector).replace(/^#/, ''),
                    value: value || '',
                    label: label || '',
                },
            }));

            return;
        }

        setSelectSearchValue(selector, value, label);
    }

    normalizarBooleano(valor) {
        return valor === true || valor === 1 || valor === '1';
    }

    obtenerProductoSeleccionado() {
        const productoId = String(getValue('#producto_id') || '').trim();
        if (!productoId) {
            return null;
        }

        const config = this.productosConfig?.[productoId];
        if (!config) {
            return null;
        }

        return {
            id: productoId,
            nombre: config.label || getSelectedText('#producto_id'),
            stock: Number(config.stock ?? 0),
            precioVenta: Number(config.precio_venta ?? 0),
            esServicioLavado: this.normalizarBooleano(config.es_servicio_lavado),
        };
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Botón agregar producto
        on('#btn_agregar', 'click', safeHandler(
            () => this.agregarProducto(),
            { message: 'No se pudo agregar el producto a la venta.' }
        ));

        // Cambio de producto
        on('#producto_id', 'change', safeHandler(
            () => this.mostrarValoresProducto(),
            { message: 'No se pudo cargar la información del producto.' }
        ));

        // Cambio de comprobante o checkbox IGV
        on('#comprobante_id, #con_igv', 'change', safeHandler(
            () => this.actualizarTotales(),
            { message: 'No se pudo recalcular los totales de la venta.' }
        ));

        // Cambio de impuesto personalizado
        on('#impuesto', 'change', safeHandler(
            () => this.actualizarTotales(),
            { message: 'No se pudo aplicar el impuesto de la venta.' }
        ));

        // Botón cancelar venta
        on('#btnCancelarVenta', 'click', safeHandler(
            () => this.cancelarVenta(),
            { message: 'No se pudo cancelar la venta actual.' }
        ));

        // Validación antes de guardar - Interceptar el submit del formulario
        on('#venta-form', 'submit', safeHandler(
            (event) => {
                const resultado = this.validarAntesDeGuardar(event);
                if (!resultado) {
                    event.preventDefault();
                    return false;
                }

                return true;
            },
            { message: 'No se pudo validar la venta antes de guardar.' }
        ));

        // Cambio de medio de pago
        on('#medio_pago', 'change', safeHandler(
            () => this.manejarCambioMedioPago(),
            { message: 'No se pudo validar el método de pago seleccionado.' }
        ));

        // Cambio de cliente - validar fidelización si ya está seleccionado "Lavado Gratis"
        on('#cliente_id', 'change', safeHandler(
            () => this.validarClienteConLavadoGratis(),
            { message: 'No se pudo validar la fidelización del cliente.' }
        ));

        // Checkbox servicio de lavado
        on('#servicio_lavado', 'change', safeHandler(
            () => this.toggleHorarioLavado(),
            { message: 'No se pudo actualizar el horario del servicio de lavado.' }
        ));
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
        const producto = this.obtenerProductoSeleccionado();
        if (!producto) {
            setValue('#stock', '');
            setValue('#precio_venta', '');
            return;
        }

        // Mostrar stock
        if (producto.esServicioLavado) {
            setValue('#stock', '∞'); // Símbolo de infinito para servicios
        } else {
            setValue('#stock', producto.stock);
        }

        // Mostrar precio
        setValue('#precio_venta', producto.precioVenta);
    }

    /**
     * Agrega un producto al detalle de venta
     */
    agregarProducto() {
        const productoSeleccionado = this.obtenerProductoSeleccionado();
        const requiredProducto = validateRequired(productoSeleccionado?.id, 'Producto', 'Debe seleccionar un producto');
        if (!requiredProducto.valid) {
            showError(requiredProducto.message);
            return;
        }

        const {
            id: idProducto,
            nombre: nombreProducto,
            stock,
            precioVenta: precioBase,
            esServicioLavado,
        } = productoSeleccionado;

        const cantidad = parseInt(getValue('#cantidad'));
        const precioInput = parseFloat(getValue('#precio_venta'));
        const precioVenta = Number.isFinite(precioInput) ? precioInput : precioBase;
        const descuento = parseFloat(getValue('#descuento')) || 0;

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
        this.setSelectFieldValue('#producto_id', '', '');
        setValue('#stock', '');
        setValue('#precio_venta', '');
    }

    /**
     * Habilita/deshabilita botones según el estado
     */
    actualizarEstadoBotones() {
        const hayProductos = hasDetailItems(this.state.productos);
        
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

        resetTransactionTable({
            tableBodySelector: '#tabla_detalle tbody',
            hiddenFieldSelectors: ['#campos-productos-ocultos'],
        });

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
        const servicioLavado = isChecked('#servicio_lavado');
        const horarioLavado = getValue('#horario_lavado');

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
                this.setSelectFieldValue('#medio_pago', 'efectivo');
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
            const response = await axios.get(`/validar-fidelizacion-lavado/${clienteId}`, {
                headers: {
                    Accept: 'application/json',
                },
            });

            const data = response.data;

            if (!data.valido) {
                // No tiene suficientes lavados, mostrar advertencia detallada
                const titulo = '⚠️ Lavados Insuficientes';
                const mensaje = `${data.mensaje}\n\n📊 Progreso: ${data.lavados_actuales}/${data.lavados_necesarios} lavados`;
                
                showWarning(`${titulo}: ${mensaje}`);
                
                // Revertir el select al valor anterior
                this.setSelectFieldValue('#medio_pago', 'efectivo');
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
            
            // Asegurar que el botón de guardar esté habilitado
            this.actualizarEstadoBotones();
        } catch (error) {
            console.error('Error validando fidelización:', error);
            showError('No se pudo validar los puntos de fidelización: ' + error.message);
            this.setSelectFieldValue('#medio_pago', 'efectivo');
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
            // Limpiar localStorage
            this.state.limpiarLocalStorage();
            // Limpiar estado
            this.state.limpiar();
            // Limpiar UI
            resetTransactionTable({
                tableBodySelector: '#tabla_detalle tbody',
                hiddenFieldSelectors: ['#campos-productos-ocultos'],
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

        showSuccess('Venta recuperada correctamente');
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
    // Solo inicializar si estamos en la página de crear venta
    if (document.getElementById('btn_agregar')) {
        window.ventaManager = new VentaManager();
    }
});

// Exportar también para módulos ES6
export default VentaManager;
