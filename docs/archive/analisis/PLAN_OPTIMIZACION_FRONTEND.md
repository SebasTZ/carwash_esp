# 🚀 PLAN DE OPTIMIZACIÓN FRONTEND - IMPLEMENTACIÓN PRÁCTICA

**Fecha:** 21 de Octubre, 2025  
**Versión:** 1.0  
**Prioridad:** Alta  
**Basado en:** ANALISIS_FRONTEND_COMPLETO.md + PLAN_PRUEBAS_FRONTEND.md

---

## 📋 ÍNDICE

1. [Estrategia de Optimización](#estrategia-de-optimización)
2. [Fase 1: Fundamentos (Quick Wins)](#fase-1-fundamentos-quick-wins)
3. [Fase 2: Refactorización de JavaScript](#fase-2-refactorización-de-javascript)
4. [Fase 3: Optimización de Performance](#fase-3-optimización-de-performance)
5. [Fase 4: Mejoras de UX](#fase-4-mejoras-de-ux)
6. [Fase 5: Testing Automatizado](#fase-5-testing-automatizado)
7. [Implementación Práctica Paso a Paso](#implementación-práctica-paso-a-paso)

---

## 🎯 ESTRATEGIA DE OPTIMIZACIÓN

### Principios Guía

1. **Backwards Compatible:** No romper funcionalidad existente
2. **Incremental:** Mejoras graduales, no reescritura completa
3. **Measurable:** Cada mejora debe tener métricas
4. **Pragmatic:** Priorizar impacto vs esfuerzo

### Priorización por Impacto/Esfuerzo

```
Alto Impacto, Bajo Esfuerzo (HACER PRIMERO) ⭐⭐⭐
├── Migrar assets a Vite
├── Extraer JS inline a módulos
├── Implementar bundle optimization
└── Lazy loading de librerías

Alto Impacto, Alto Esfuerzo (PLANIFICAR)
├── Refactorizar a componentes
├── Implementar gestión de estado
└── Testing E2E completo

Bajo Impacto, Bajo Esfuerzo (SI HAY TIEMPO)
├── Mejoras estéticas
└── Animaciones

Bajo Impacto, Alto Esfuerzo (EVITAR)
├── Reescritura completa a React/Vue
└── Over-engineering
```

---

## ⚡ FASE 1: FUNDAMENTOS (QUICK WINS)

**Duración:** 2-3 días  
**Impacto:** Alto  
**Riesgo:** Bajo

### 1.1 Migrar Assets a Vite

#### Paso 1: Actualizar package.json

```json
{
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "preview": "vite preview"
    },
    "devDependencies": {
        "axios": "^1.6.2",
        "laravel-vite-plugin": "^1.0.0",
        "lodash": "^4.17.21",
        "vite": "^5.0.0",
        "sass": "^1.69.5"
    },
    "dependencies": {
        "bootstrap": "^5.3.2",
        "bootstrap-select": "^1.14.0-beta3",
        "sweetalert2": "^11.10.1",
        "@popperjs/core": "^2.11.8"
    }
}
```

#### Paso 2: Instalar dependencias

```bash
npm install
```

#### Paso 3: Actualizar vite.config.js

```javascript
// vite.config.js
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",

                // Páginas específicas
                "resources/js/pages/ventas/create.js",
                "resources/js/pages/control/lavados.js",
                "resources/js/pages/estacionamiento/index.js",
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
            "@css": path.resolve(__dirname, "./resources/css"),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ["bootstrap", "@popperjs/core"],
                    utils: ["axios", "lodash"],
                    ui: ["sweetalert2", "bootstrap-select"],
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
    server: {
        hmr: {
            host: "localhost",
        },
    },
});
```

#### Paso 4: Actualizar resources/js/app.js

```javascript
// resources/js/app.js
import "./bootstrap";

// Importar Bootstrap
import "bootstrap";

// Importar estilos globales
import "../css/app.css";

// Utilidades globales
import { initTooltips, initPopovers } from "./utils/bootstrap-init";

// Inicializar componentes de Bootstrap
document.addEventListener("DOMContentLoaded", () => {
    initTooltips();
    initPopovers();
});
```

#### Paso 5: Crear bootstrap.js mejorado

```javascript
// resources/js/bootstrap.js
import _ from "lodash";
window._ = _;

import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// CSRF Token para Axios
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error("CSRF token not found");
}

// Interceptor para manejar errores globalmente
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            // CSRF token mismatch
            alert("Tu sesión ha expirado. Por favor, recarga la página.");
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
```

#### Paso 6: Actualizar layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistema de ventas de abarrotes" />
    <meta name="author" content="SakCode" />
    <title>Sistema ventas - @yield('title')</title>

    {{-- FontAwesome (mantener en CDN por ahora) --}}
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous" defer></script>

    @stack('css')

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sb-nav-fixed">
    <x-navigation-header />

    <div id="layoutSidenav">
        <x-navigation-menu />

        <div id="layoutSidenav_content">
            <main>
                @yield('content')
            </main>

            <x-footer />
        </div>
    </div>

    @stack('js')
</body>
</html>
```

### 1.2 Crear Utilidades Globales

#### Archivo: resources/js/utils/notifications.js

```javascript
// resources/js/utils/notifications.js
import Swal from "sweetalert2";

/**
 * Muestra un toast notification
 * @param {string} message - Mensaje a mostrar
 * @param {string} icon - Tipo: success, error, warning, info
 * @param {number} timer - Tiempo en ms (default: 4000)
 */
export function showToast(message, icon = "info", timer = 4000) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    return Toast.fire({
        icon: icon,
        title: message,
    });
}

/**
 * Muestra un diálogo de confirmación
 * @param {string} title - Título del diálogo
 * @param {string} text - Texto descriptivo
 * @param {string} confirmButtonText - Texto del botón confirmar
 */
export function showConfirmDialog(
    title = "¿Estás seguro?",
    text = "Esta acción no se puede deshacer",
    confirmButtonText = "Sí, continuar"
) {
    return Swal.fire({
        title: title,
        text: text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: confirmButtonText,
        cancelButtonText: "Cancelar",
    });
}

/**
 * Muestra un loading overlay
 * @param {string} message - Mensaje durante la carga
 */
export function showLoading(message = "Procesando...") {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
}

/**
 * Cierra el loading overlay
 */
export function hideLoading() {
    Swal.close();
}

// Exportación por defecto
export default {
    showToast,
    showConfirmDialog,
    showLoading,
    hideLoading,
};
```

#### Archivo: resources/js/utils/validators.js

```javascript
// resources/js/utils/validators.js

/**
 * Valida que un valor no esté vacío
 */
export function required(value) {
    if (typeof value === "string") {
        return value.trim() !== "";
    }
    return value !== null && value !== undefined && value !== "";
}

/**
 * Valida que sea un número positivo
 */
export function positiveNumber(value) {
    const num = Number(value);
    return !isNaN(num) && num > 0;
}

/**
 * Valida que sea un número entero positivo
 */
export function positiveInteger(value) {
    const num = Number(value);
    return Number.isInteger(num) && num > 0;
}

/**
 * Valida rango de stock
 */
export function validateStock(cantidad, stockDisponible, esServicio = false) {
    if (esServicio) {
        return { valid: true };
    }

    if (!positiveInteger(cantidad)) {
        return {
            valid: false,
            message: "La cantidad debe ser un número entero positivo",
        };
    }

    if (cantidad > stockDisponible) {
        return {
            valid: false,
            message: `La cantidad no puede superar el stock disponible (${stockDisponible})`,
        };
    }

    return { valid: true };
}

/**
 * Valida formato de placa vehicular
 */
export function validatePlaca(placa) {
    // Formato peruano: AAA-123 o AA-1234
    const regex = /^[A-Z]{2,3}-\d{3,4}$/;
    return regex.test(placa);
}

/**
 * Valida formato de email
 */
export function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Sanitiza HTML para prevenir XSS
 */
export function sanitizeHTML(str) {
    const temp = document.createElement("div");
    temp.textContent = str;
    return temp.innerHTML;
}

/**
 * Valida que una fecha sea válida
 */
export function validateDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

/**
 * Valida que fecha_fin > fecha_inicio
 */
export function validateDateRange(fechaInicio, fechaFin) {
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);

    if (!validateDate(fechaInicio) || !validateDate(fechaFin)) {
        return {
            valid: false,
            message: "Las fechas ingresadas no son válidas",
        };
    }

    if (fin < inicio) {
        return {
            valid: false,
            message: "La fecha de fin debe ser posterior a la fecha de inicio",
        };
    }

    return { valid: true };
}

export default {
    required,
    positiveNumber,
    positiveInteger,
    validateStock,
    validatePlaca,
    validateEmail,
    sanitizeHTML,
    validateDate,
    validateDateRange,
};
```

#### Archivo: resources/js/utils/formatters.js

```javascript
// resources/js/utils/formatters.js

/**
 * Formatea un número como moneda peruana
 * @param {number} amount - Cantidad a formatear
 * @returns {string} - Cantidad formateada (Ej: S/ 1,234.56)
 */
export function formatCurrency(amount) {
    return new Intl.NumberFormat("es-PE", {
        style: "currency",
        currency: "PEN",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

/**
 * Formatea un número con decimales
 * @param {number} num - Número a formatear
 * @param {number} decimals - Número de decimales (default: 2)
 * @returns {number} - Número redondeado
 */
export function round(num, decimals = 2) {
    const sign = num >= 0 ? 1 : -1;
    num = num * sign;

    if (decimals === 0) {
        return sign * Math.round(num);
    }

    num = num.toString().split("e");
    num = Math.round(
        +(num[0] + "e" + (num[1] ? +num[1] + decimals : decimals))
    );
    num = num.toString().split("e");

    return sign * +(num[0] + "e" + (num[1] ? +num[1] - decimals : -decimals));
}

/**
 * Formatea una fecha en formato legible
 * @param {string|Date} date - Fecha a formatear
 * @param {boolean} includeTime - Incluir hora (default: false)
 * @returns {string} - Fecha formateada
 */
export function formatDate(date, includeTime = false) {
    const d = new Date(date);

    const options = {
        year: "numeric",
        month: "long",
        day: "numeric",
    };

    if (includeTime) {
        options.hour = "2-digit";
        options.minute = "2-digit";
    }

    return d.toLocaleDateString("es-PE", options);
}

/**
 * Formatea un número de teléfono peruano
 * @param {string} phone - Número de teléfono
 * @returns {string} - Teléfono formateado
 */
export function formatPhone(phone) {
    // Formato: 999 999 999
    const cleaned = phone.replace(/\D/g, "");
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{3})$/);

    if (match) {
        return `${match[1]} ${match[2]} ${match[3]}`;
    }

    return phone;
}

/**
 * Capitaliza la primera letra de cada palabra
 * @param {string} str - String a capitalizar
 * @returns {string} - String capitalizado
 */
export function capitalize(str) {
    return str.replace(/\b\w/g, (l) => l.toUpperCase());
}

/**
 * Trunca un string a una longitud específica
 * @param {string} str - String a truncar
 * @param {number} length - Longitud máxima
 * @returns {string} - String truncado
 */
export function truncate(str, length = 50) {
    if (str.length <= length) return str;
    return str.substring(0, length) + "...";
}

export default {
    formatCurrency,
    round,
    formatDate,
    formatPhone,
    capitalize,
    truncate,
};
```

#### Archivo: resources/js/utils/bootstrap-init.js

```javascript
// resources/js/utils/bootstrap-init.js
import { Tooltip, Popover } from "bootstrap";

/**
 * Inicializa todos los tooltips de Bootstrap
 */
export function initTooltips() {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );

    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl);
    });
}

/**
 * Inicializa todos los popovers de Bootstrap
 */
export function initPopovers() {
    const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]')
    );

    popoverTriggerList.map(function (popoverTriggerEl) {
        return new Popover(popoverTriggerEl);
    });
}

/**
 * Destruye todos los tooltips activos
 */
export function destroyTooltips() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach((el) => {
        const tooltip = Tooltip.getInstance(el);
        if (tooltip) tooltip.dispose();
    });
}

export default {
    initTooltips,
    initPopovers,
    destroyTooltips,
};
```

---

## 🔧 FASE 2: REFACTORIZACIÓN DE JAVASCRIPT

**Duración:** 5-7 días  
**Impacto:** Muy Alto  
**Riesgo:** Medio

### 2.1 Módulo de Ventas

#### Paso 1: Crear clase VentaManager

```javascript
// resources/js/modules/VentaManager.js
import {
    showToast,
    showConfirmDialog,
    showLoading,
    hideLoading,
} from "@/utils/notifications";
import { validateStock, sanitizeHTML } from "@/utils/validators";
import { formatCurrency, round } from "@/utils/formatters";

export class VentaManager {
    constructor() {
        this.productos = [];
        this.contador = 0;
        this.impuestoPorDefecto = 18;

        this.init();
    }

    init() {
        this.bindEvents();
        this.updateUI();
    }

    bindEvents() {
        // Evento: Agregar producto
        document
            .getElementById("btn_agregar")
            ?.addEventListener("click", () => {
                this.agregarProducto();
            });

        // Evento: Cambio en producto seleccionado
        document
            .getElementById("producto_id")
            ?.addEventListener("change", () => {
                this.mostrarValoresProducto();
            });

        // Evento: Cambio en tipo de comprobante
        document
            .getElementById("comprobante_id")
            ?.addEventListener("change", () => {
                this.handleComprobanteChange();
            });

        // Evento: Checkbox IGV
        document.getElementById("con_igv")?.addEventListener("change", () => {
            this.recalcularTotales();
        });

        // Evento: Cambio en método de pago
        document
            .getElementById("medio_pago")
            ?.addEventListener("change", (e) => {
                this.handleMedioPagoChange(e.target.value);
            });

        // Evento: Servicio de lavado
        document
            .getElementById("servicio_lavado")
            ?.addEventListener("change", (e) => {
                this.toggleHorarioLavado(e.target.checked);
            });

        // Evento: Cancelar venta
        document
            .getElementById("btnCancelarVenta")
            ?.addEventListener("click", () => {
                this.cancelarVenta();
            });

        // Evento: Guardar venta
        document.getElementById("guardar")?.addEventListener("click", (e) => {
            if (!this.validarAntesDeGuardar()) {
                e.preventDefault();
            }
        });
    }

    mostrarValoresProducto() {
        const select = document.getElementById("producto_id");
        if (!select || !select.value) return;

        const [id, stock, precioVenta, esServicio] = select.value.split("-");

        const stockInput = document.getElementById("stock");
        const precioInput = document.getElementById("precio_venta");

        if (esServicio === "1") {
            stockInput.value = "∞";
        } else {
            stockInput.value = stock;
        }

        precioInput.value = precioVenta;
    }

    agregarProducto() {
        const select = document.getElementById("producto_id");
        if (!select || !select.value) {
            showToast("Debe seleccionar un producto", "error");
            return;
        }

        const [idProducto, stock, precioVenta, esServicio] =
            select.value.split("-");
        const nombreProducto = select.options[select.selectedIndex].text;
        const cantidad = parseInt(document.getElementById("cantidad").value);
        const precio = parseFloat(precioVenta);
        const descuento =
            parseFloat(document.getElementById("descuento").value) || 0;

        // Validaciones
        if (!cantidad || cantidad <= 0) {
            showToast("Debe ingresar una cantidad válida", "error");
            return;
        }

        if (esServicio !== "1") {
            const validation = validateStock(cantidad, parseInt(stock), false);
            if (!validation.valid) {
                showToast(validation.message, "error");
                return;
            }
        }

        // Calcular subtotal
        const subtotal = round(cantidad * precio - descuento);

        // Agregar producto
        const producto = {
            id: this.contador,
            productoId: idProducto,
            nombre: sanitizeHTML(nombreProducto),
            cantidad: cantidad,
            precioVenta: precio,
            descuento: descuento,
            subtotal: subtotal,
        };

        this.productos.push(producto);
        this.contador++;

        // Actualizar UI
        this.renderProductos();
        this.recalcularTotales();
        this.limpiarCamposProducto();

        showToast("Producto agregado correctamente", "success", 2000);
    }

    eliminarProducto(id) {
        showConfirmDialog(
            "¿Eliminar producto?",
            "Esta acción eliminará el producto de la lista",
            "Sí, eliminar"
        ).then((result) => {
            if (result.isConfirmed) {
                this.productos = this.productos.filter((p) => p.id !== id);
                this.renderProductos();
                this.recalcularTotales();
                showToast("Producto eliminado", "success", 2000);
            }
        });
    }

    renderProductos() {
        const tbody = document.querySelector("#tabla_detalle tbody");
        if (!tbody) return;

        if (this.productos.length === 0) {
            tbody.innerHTML = `
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
            return;
        }

        tbody.innerHTML = this.productos
            .map(
                (producto, index) => `
            <tr id="fila${producto.id}">
                <th>${index + 1}</th>
                <td>
                    <input type="hidden" name="arrayidproducto[]" value="${
                        producto.productoId
                    }">
                    ${producto.nombre}
                </td>
                <td>
                    <input type="hidden" name="arraycantidad[]" value="${
                        producto.cantidad
                    }">
                    ${producto.cantidad}
                </td>
                <td>
                    <input type="hidden" name="arrayprecioventa[]" value="${
                        producto.precioVenta
                    }">
                    ${formatCurrency(producto.precioVenta)}
                </td>
                <td>
                    <input type="hidden" name="arraydescuento[]" value="${
                        producto.descuento
                    }">
                    ${formatCurrency(producto.descuento)}
                </td>
                <td>${formatCurrency(producto.subtotal)}</td>
                <td>
                    <button 
                        class="btn btn-danger btn-sm" 
                        type="button" 
                        data-producto-id="${producto.id}"
                        aria-label="Eliminar producto"
                        title="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `
            )
            .join("");

        // Re-bind eventos de eliminar
        tbody.querySelectorAll("button[data-producto-id]").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const id = parseInt(e.currentTarget.dataset.productoId);
                this.eliminarProducto(id);
            });
        });
    }

    recalcularTotales() {
        const sumas = this.productos.reduce((acc, p) => acc + p.subtotal, 0);

        let igv = 0;
        const tipoComprobante =
            document.getElementById("comprobante_id")?.selectedOptions[0]?.text;
        const incluirIGV = document.getElementById("con_igv")?.checked;
        const porcentajeIGV =
            parseFloat(document.getElementById("impuesto")?.value) ||
            this.impuestoPorDefecto;

        if (tipoComprobante === "Factura" && incluirIGV) {
            igv = round(sumas * (porcentajeIGV / 100));
        }

        const total = round(sumas + igv);

        // Actualizar UI
        document.getElementById("sumas").textContent = formatCurrency(sumas);
        document.getElementById("igv").textContent = formatCurrency(igv);
        document.getElementById("total").textContent = formatCurrency(total);
        document.getElementById("inputTotal").value = total;

        this.updateUI();
    }

    handleComprobanteChange() {
        const tipoComprobante =
            document.getElementById("comprobante_id")?.selectedOptions[0]?.text;
        const incluirIGV = document.getElementById("con_igv")?.checked;
        const impuestoInput = document.getElementById("impuesto");

        if (tipoComprobante === "Factura" && incluirIGV) {
            impuestoInput.removeAttribute("readonly");
            if (impuestoInput.value == "0") {
                impuestoInput.value = this.impuestoPorDefecto;
            }
        } else {
            impuestoInput.setAttribute("readonly", true);
            impuestoInput.value = 0;
        }

        this.recalcularTotales();
    }

    handleMedioPagoChange(medioPago) {
        const tarjetaDiv = document.getElementById("tarjeta_regalo_div");
        const tarjetaInput = document.getElementById("tarjeta_regalo_codigo");
        const lavadoGratisDiv = document.getElementById("lavado_gratis_div");

        // Ocultar todos
        tarjetaDiv.style.display = "none";
        lavadoGratisDiv.style.display = "none";
        tarjetaInput.removeAttribute("required");
        tarjetaInput.value = "";

        // Mostrar según selección
        if (medioPago === "tarjeta_regalo") {
            tarjetaDiv.style.display = "block";
            tarjetaInput.setAttribute("required", "");
        } else if (medioPago === "lavado_gratis") {
            lavadoGratisDiv.style.display = "block";
        }
    }

    toggleHorarioLavado(checked) {
        const horarioDiv = document.getElementById("horario_lavado_div");
        const horarioInput = document.getElementById("horario_lavado");
        const horarioHidden = document.getElementById("horario_lavado_hidden");

        if (checked) {
            horarioDiv.style.display = "block";
        } else {
            horarioDiv.style.display = "none";
            horarioInput.value = "";
            horarioHidden.value = "";
        }
    }

    validarAntesDeGuardar() {
        if (this.productos.length === 0) {
            showToast("Debe agregar al menos un producto", "error");
            return false;
        }

        const servicioLavado =
            document.getElementById("servicio_lavado")?.checked;
        const horarioLavado = document.getElementById("horario_lavado")?.value;

        if (servicioLavado && !horarioLavado) {
            showToast("Debe ingresar el horario de lavado", "error");
            document.getElementById("horario_lavado").focus();
            return false;
        }

        if (servicioLavado) {
            document.getElementById("horario_lavado_hidden").value =
                horarioLavado;
        }

        // Mostrar loading
        showLoading("Guardando venta...");

        return true;
    }

    cancelarVenta() {
        this.productos = [];
        this.contador = 0;
        this.renderProductos();
        this.recalcularTotales();
        this.limpiarCamposProducto();
        document.getElementById("con_igv").checked = false;
        showToast("Venta cancelada", "info");
    }

    limpiarCamposProducto() {
        const select = document.getElementById("producto_id");
        if (select && typeof $(select).selectpicker === "function") {
            $(select).selectpicker("val", "");
        } else if (select) {
            select.value = "";
        }

        document.getElementById("cantidad").value = "";
        document.getElementById("precio_venta").value = "";
        document.getElementById("descuento").value = "";
        document.getElementById("stock").value = "";
    }

    updateUI() {
        const guardarBtn = document.getElementById("guardar");
        const cancelarBtn = document.getElementById("cancelar");

        if (this.productos.length === 0) {
            guardarBtn.style.display = "none";
            cancelarBtn.style.display = "none";
        } else {
            guardarBtn.style.display = "inline-block";
            cancelarBtn.style.display = "inline-block";
        }
    }
}

export default VentaManager;
```

#### Paso 2: Crear archivo de página

```javascript
// resources/js/pages/ventas/create.js
import VentaManager from "@/modules/VentaManager";
import "bootstrap-select";
import "bootstrap-select/dist/css/bootstrap-select.min.css";

document.addEventListener("DOMContentLoaded", () => {
    // Inicializar Bootstrap Select
    $(".selectpicker").selectpicker();

    // Inicializar gestor de ventas
    new VentaManager();
});
```

#### Paso 3: Actualizar vista Blade

```blade
{{-- resources/views/venta/create.blade.php --}}
@extends('layouts.app')

@section('title','Registrar Venta')

@section('content')
{{-- ... HTML existente ... --}}
@endsection

@push('js')
    @vite('resources/js/pages/ventas/create.js')
@endpush
```

---

## 📊 FASE 3: OPTIMIZACIÓN DE PERFORMANCE

**Duración:** 3-4 días  
**Impacto:** Alto  
**Riesgo:** Bajo

### 3.1 Lazy Loading de Componentes

```javascript
// resources/js/utils/lazy-loader.js

/**
 * Carga componentes de forma lazy
 */
export class LazyLoader {
    static async loadBootstrapSelect() {
        if (window.bootstrapSelectLoaded) return;

        const { default: $ } = await import("jquery");
        window.$ = window.jQuery = $;

        await import("bootstrap-select");
        await import("bootstrap-select/dist/css/bootstrap-select.min.css");

        window.bootstrapSelectLoaded = true;
        $(".selectpicker").selectpicker();
    }

    static async loadSweetAlert() {
        if (window.Swal) return window.Swal;

        const { default: Swal } = await import("sweetalert2");
        window.Swal = Swal;
        return Swal;
    }

    static async loadChart() {
        if (window.Chart) return window.Chart;

        const { default: Chart } = await import("chart.js/auto");
        window.Chart = Chart;
        return Chart;
    }
}

export default LazyLoader;
```

### 3.2 Code Splitting Avanzado

```javascript
// vite.config.js (actualizado)
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Vendor chunks
                    if (id.includes("node_modules")) {
                        if (id.includes("bootstrap")) {
                            return "vendor-bootstrap";
                        }
                        if (id.includes("sweetalert2")) {
                            return "vendor-sweetalert";
                        }
                        if (id.includes("axios")) {
                            return "vendor-axios";
                        }
                        return "vendor";
                    }

                    // Page chunks
                    if (id.includes("pages/ventas")) {
                        return "page-ventas";
                    }
                    if (id.includes("pages/control")) {
                        return "page-control";
                    }
                    if (id.includes("pages/estacionamiento")) {
                        return "page-estacionamiento";
                    }

                    // Utils chunk
                    if (id.includes("utils/")) {
                        return "utils";
                    }
                },
            },
        },
    },
});
```

### 3.3 Optimización de Imágenes

```bash
# Instalar dependencias
npm install -D vite-plugin-imagemin
```

```javascript
// vite.config.js
import viteImagemin from 'vite-plugin-imagemin';

export default defineConfig({
    plugins: [
        laravel({...}),
        viteImagemin({
            gifsicle: {
                optimizationLevel: 7,
                interlaced: false,
            },
            optipng: {
                optimizationLevel: 7,
            },
            mozjpeg: {
                quality: 80,
            },
            pngquant: {
                quality: [0.8, 0.9],
                speed: 4,
            },
            svgo: {
                plugins: [
                    {
                        name: 'removeViewBox',
                    },
                    {
                        name: 'removeEmptyAttrs',
                        active: false,
                    },
                ],
            },
        }),
    ],
});
```

---

## 🎨 FASE 4: MEJORAS DE UX

**Duración:** 4-5 días  
**Impacto:** Medio-Alto  
**Riesgo:** Bajo

### 4.1 Loading States

```javascript
// resources/js/components/LoadingButton.js

export class LoadingButton {
    constructor(button) {
        this.button = button;
        this.originalText = button.innerHTML;
        this.isLoading = false;
    }

    startLoading(text = "Procesando...") {
        if (this.isLoading) return;

        this.isLoading = true;
        this.button.disabled = true;
        this.button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            ${text}
        `;
    }

    stopLoading() {
        this.isLoading = false;
        this.button.disabled = false;
        this.button.innerHTML = this.originalText;
    }

    static create(selector) {
        const button = document.querySelector(selector);
        return button ? new LoadingButton(button) : null;
    }
}
```

### 4.2 Persistencia con LocalStorage

```javascript
// resources/js/utils/storage.js

export class VentaStorage {
    static STORAGE_KEY = "venta_draft";

    static save(productos, cliente, comprobante) {
        const data = {
            productos,
            cliente,
            comprobante,
            timestamp: Date.now(),
        };

        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
    }

    static load() {
        const data = localStorage.getItem(this.STORAGE_KEY);
        if (!data) return null;

        const parsed = JSON.parse(data);

        // Verificar que no sea muy antiguo (24 horas)
        if (Date.now() - parsed.timestamp > 24 * 60 * 60 * 1000) {
            this.clear();
            return null;
        }

        return parsed;
    }

    static clear() {
        localStorage.removeItem(this.STORAGE_KEY);
    }

    static hasDraft() {
        return localStorage.getItem(this.STORAGE_KEY) !== null;
    }
}
```

### 4.3 Filtros AJAX (Control de Lavados)

```javascript
// resources/js/pages/control/lavados.js
import axios from "axios";
import { showToast, showLoading, hideLoading } from "@/utils/notifications";

class ControlLavadosManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupAjaxFilters();
    }

    setupAjaxFilters() {
        const form = document.querySelector('form[action*="control.lavados"]');
        if (!form) return;

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            this.aplicarFiltros(new FormData(form));
        });
    }

    async aplicarFiltros(formData) {
        showLoading("Filtrando...");

        try {
            const params = new URLSearchParams(formData);
            const response = await axios.get(
                `/control/lavados?${params.toString()}`,
                {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                }
            );

            // Actualizar solo el contenido de lavados
            const container = document.querySelector("#lavados-container");
            if (container) {
                container.innerHTML = response.data.html;
            }

            // Actualizar URL sin recargar
            window.history.pushState({}, "", `?${params.toString()}`);

            hideLoading();
            showToast("Filtros aplicados", "success", 2000);
        } catch (error) {
            hideLoading();
            showToast("Error al aplicar filtros", "error");
            console.error(error);
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new ControlLavadosManager();
});
```

---

## 🧪 FASE 5: TESTING AUTOMATIZADO

**Duración:** 5-7 días  
**Impacto:** Muy Alto (largo plazo)  
**Riesgo:** Bajo

### 5.1 Setup de Playwright

```bash
npm install -D @playwright/test
npx playwright install
```

```javascript
// playwright.config.js
import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
    testDir: "./tests/e2e",
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    reporter: "html",

    use: {
        baseURL: "http://localhost:8000",
        trace: "on-first-retry",
        screenshot: "only-on-failure",
    },

    projects: [
        {
            name: "chromium",
            use: { ...devices["Desktop Chrome"] },
        },
        {
            name: "Mobile Chrome",
            use: { ...devices["Pixel 5"] },
        },
    ],

    webServer: {
        command: "php artisan serve",
        url: "http://localhost:8000",
        reuseExistingServer: !process.env.CI,
    },
});
```

### 5.2 Unit Tests con Vitest

```bash
npm install -D vitest @testing-library/dom
```

```javascript
// vitest.config.js
import { defineConfig } from "vitest/config";
import path from "path";

export default defineConfig({
    test: {
        globals: true,
        environment: "jsdom",
        setupFiles: ["./tests/setup.js"],
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        },
    },
});
```

```javascript
// tests/unit/validators.test.js
import { describe, it, expect } from "vitest";
import {
    positiveInteger,
    validateStock,
    validatePlaca,
} from "@/utils/validators";

describe("Validators", () => {
    describe("positiveInteger", () => {
        it("valida números enteros positivos", () => {
            expect(positiveInteger(5)).toBe(true);
            expect(positiveInteger(0)).toBe(false);
            expect(positiveInteger(-5)).toBe(false);
            expect(positiveInteger(5.5)).toBe(false);
        });
    });

    describe("validateStock", () => {
        it("valida stock para productos normales", () => {
            const result = validateStock(5, 10, false);
            expect(result.valid).toBe(true);
        });

        it("rechaza cantidad mayor al stock", () => {
            const result = validateStock(15, 10, false);
            expect(result.valid).toBe(false);
            expect(result.message).toContain("stock disponible");
        });

        it("permite cualquier cantidad para servicios", () => {
            const result = validateStock(100, 0, true);
            expect(result.valid).toBe(true);
        });
    });

    describe("validatePlaca", () => {
        it("valida placas peruanas válidas", () => {
            expect(validatePlaca("ABC-123")).toBe(true);
            expect(validatePlaca("AB-1234")).toBe(true);
        });

        it("rechaza placas inválidas", () => {
            expect(validatePlaca("ABCD-123")).toBe(false);
            expect(validatePlaca("ABC-12")).toBe(false);
            expect(validatePlaca("abc-123")).toBe(false);
        });
    });
});
```

---

## 📝 IMPLEMENTACIÓN PRÁCTICA PASO A PASO

### Día 1-2: Setup Inicial

```bash
# 1. Instalar dependencias
npm install

# 2. Compilar con Vite (modo desarrollo)
npm run dev

# 3. En otra terminal, servidor Laravel
php artisan serve

# 4. Verificar que carga correctamente
# http://localhost:8000/ventas/create
```

### Día 3-4: Refactorizar Ventas

1. Crear estructura de carpetas
2. Copiar código inline a `VentaManager.js`
3. Probar funcionalmente que todo funciona igual
4. Commit: "refactor: Extract venta logic to VentaManager module"

### Día 5-6: Utilidades y Helpers

1. Implementar `notifications.js`
2. Implementar `validators.js`
3. Implementar `formatters.js`
4. Actualizar `VentaManager` para usar utilidades
5. Commit: "feat: Add utility modules for notifications, validation and formatting"

### Día 7-8: Control de Lavados

1. Refactorizar control de lavados
2. Implementar filtros AJAX
3. Commit: "refactor: Modernize control lavados with AJAX filters"

### Día 9-10: Testing

1. Setup Playwright
2. Escribir tests E2E críticos
3. Setup Vitest
4. Escribir tests unitarios
5. Commit: "test: Add E2E and unit tests"

### Día 11-12: Performance

1. Configurar code splitting
2. Implementar lazy loading
3. Optimizar assets
4. Auditoría con Lighthouse
5. Commit: "perf: Optimize bundle size and loading performance"

### Día 13-14: Documentación y Cleanup

1. Documentar módulos JS
2. Actualizar README
3. Crear CHANGELOG
4. Pull Request final

---

## 📊 MÉTRICAS DE ÉXITO

### Antes de Optimización (Actual)

```
- Requests por página: ~10
- Tamaño total: ~560KB
- FCP: ~2.5s (estimado)
- LCP: ~3.5s (estimado)
- Tests automatizados: 0
- Código duplicado: ~40%
```

### Después de Optimización (Target)

```
- Requests por página: ~5
- Tamaño total: ~300KB
- FCP: <1.8s
- LCP: <2.5s
- Tests automatizados: >50
- Código duplicado: <10%
- Performance Score: >90
- Accessibility Score: >90
```

---

## ⚠️ RIESGOS Y MITIGACIÓN

| Riesgo                                  | Probabilidad | Impacto | Mitigación                             |
| --------------------------------------- | ------------ | ------- | -------------------------------------- |
| Romper funcionalidad existente          | Media        | Alto    | Tests E2E exhaustivos antes de deploy  |
| Problemas de compatibilidad navegadores | Baja         | Medio   | Usar Babel y browserslist              |
| Aumento de complejidad                  | Media        | Medio   | Documentación clara y código comentado |
| Resistencia del equipo                  | Media        | Bajo    | Capacitación y documentación           |

---

## 🎓 CAPACITACIÓN DEL EQUIPO

### Sesión 1: Vite y Módulos ES6 (2 horas)

-   ¿Qué es Vite y por qué lo usamos?
-   Importar y exportar módulos
-   Cómo debuggear con sourcemaps

### Sesión 2: Nueva Arquitectura (2 horas)

-   Estructura de carpetas
-   VentaManager y otros módulos
-   Utilidades compartidas

### Sesión 3: Testing (2 horas)

-   Escribir tests con Playwright
-   Ejecutar tests localmente
-   Interpretar resultados

---

## 📚 RECURSOS ADICIONALES

-   [Vite Documentation](https://vitejs.dev/)
-   [Playwright Documentation](https://playwright.dev/)
-   [Laravel Vite Plugin](https://github.com/laravel/vite-plugin)
-   [Bootstrap 5 Documentation](https://getbootstrap.com/)
-   [Web.dev Performance Guide](https://web.dev/performance/)

---

**Fecha de finalización esperada:** 14 días hábiles  
**ROI esperado:** 3-4 meses  
**Próximos pasos:** Comenzar con Fase 1 - Fundamentos

---

**Preparado por:** GitHub Copilot  
**Última actualización:** 21 de Octubre, 2025
