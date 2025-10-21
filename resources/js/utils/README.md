# 📦 Utilidades Frontend - CarWash ESP

## 🎯 Descripción

Este directorio contiene módulos de utilidades reutilizables para el frontend de CarWash ESP, diseñados para mejorar la mantenibilidad, reducir código duplicado y estandarizar funcionalidades comunes.

## 📁 Estructura

```
utils/
├── notifications.js     → Manejo de notificaciones (SweetAlert2)
├── validators.js        → Validaciones de formularios
├── formatters.js        → Formateo de datos (moneda, fechas, etc.)
├── bootstrap-init.js    → Inicialización de componentes Bootstrap
└── lazy-loader.js       → Carga diferida de recursos
```

## 🚀 Uso Rápido

### Desde código Blade (JavaScript inline)

Todas las utilidades están disponibles globalmente en `window.CarWash`:

```javascript
// Notificaciones
CarWash.showSuccess("Venta registrada correctamente");
CarWash.showError("Error al procesar la solicitud");
CarWash.showWarning("Stock bajo en este producto");

// Confirmaciones
const confirmed = await CarWash.showDeleteConfirm("este producto");
if (confirmed) {
    // Proceder con la eliminación
}

// Validaciones
const stockValidation = CarWash.validateStock(cantidad, stock, false);
if (!stockValidation.valid) {
    CarWash.showError(stockValidation.message);
    return;
}

// Formateo
const precio = CarWash.formatCurrency(125.5); // "S/ 125.50"
const fecha = CarWash.formatDate(new Date()); // "21/10/2025"
```

### Desde módulos ES6

```javascript
import { showSuccess, showError } from "@utils/notifications";
import { validateStock, validatePrecio } from "@utils/validators";
import { formatCurrency, formatDate } from "@utils/formatters";

// Usar directamente
showSuccess("Operación exitosa");
const validation = validateStock(10, 5, false);
const precio = formatCurrency(99.99);
```

## 📚 Documentación Detallada

### 1. Notifications (notifications.js)

#### Toasts

```javascript
// Toast de éxito (5 segundos)
CarWash.showSuccess("Guardado exitosamente");

// Toast de error (6 segundos)
CarWash.showError("No se pudo completar la operación");

// Toast de advertencia
CarWash.showWarning("Revisa los datos ingresados");

// Modal informativo
CarWash.showInfo("Recuerda guardar tus cambios");
```

#### Confirmaciones

```javascript
// Confirmación genérica
const result = await CarWash.showConfirm(
    "¿Continuar?",
    "Esta acción modificará los datos",
    "Sí, continuar",
    "Cancelar"
);

// Confirmación para eliminar
const deleteConfirmed = await CarWash.showDeleteConfirm("este producto");
if (deleteConfirmed) {
    // Ejecutar eliminación
}
```

#### Estados de Carga

```javascript
// Mostrar loading
CarWash.showLoading("Procesando venta...");

// Ocultar loading
CarWash.hideLoading();

// Loading en botón
const button = document.getElementById("btnGuardar");
CarWash.setButtonLoading(button, true); // Mostrar
// ... hacer operación async
CarWash.setButtonLoading(button, false); // Ocultar
```

#### Validación de Campos

```javascript
// Mostrar error en campo
const campo = document.getElementById("cantidad");
CarWash.showFieldError(campo, "La cantidad debe ser mayor a 0");

// Limpiar error
CarWash.clearFieldError(campo);

// Limpiar todos los errores de un formulario
const form = document.getElementById("miFormulario");
CarWash.clearFormErrors(form);
```

---

### 2. Validators (validators.js)

#### Validación de Stock

```javascript
const validation = CarWash.validateStock(
    cantidad, // 10
    stock, // 5
    esServicio // false
);

if (!validation.valid) {
    CarWash.showError(validation.message);
    // "Stock insuficiente. Disponible: 5"
}
```

#### Validación de Precios

```javascript
const validation = CarWash.validatePrecio(precio, minimoPermitido);
if (!validation.valid) {
    CarWash.showError(validation.message);
}
```

#### Validación de Descuentos

```javascript
const validation = CarWash.validateDescuento(
    descuento, // 100
    precioUnitario, // 50
    cantidad // 1
);

if (!validation.valid) {
    CarWash.showError(validation.message);
    // "El descuento no puede superar el subtotal (S/ 50.00)"
}
```

#### Validación de Fechas

```javascript
// Fecha individual
const validation = CarWash.validateFecha("2025-10-21");

// Rango de fechas
const validation = CarWash.validateRangoFechas(
    fechaInicio, // '2025-10-01'
    fechaFin // '2025-10-21'
);
```

#### Validación de Documentos (Perú)

```javascript
// DNI (8 dígitos)
const validation = CarWash.validateDNI("12345678");

// RUC (11 dígitos)
const validation = CarWash.validateRUC("20123456789");

// Placa vehicular
const validation = CarWash.validatePlaca("ABC-123");

// Teléfono
const validation = CarWash.validateTelefono("987654321");

// Email
const validation = CarWash.validateEmail("user@example.com");
```

#### Validación de Tablas

```javascript
// Verificar que una tabla tenga al menos una fila
const validation = CarWash.validateTableNotEmpty("tabla_detalle");
if (!validation.valid) {
    CarWash.showError("Debe agregar al menos un producto");
}
```

#### Sanitización

```javascript
// Prevenir XSS
const safeString = CarWash.sanitizeString(userInput);
```

---

### 3. Formatters (formatters.js)

#### Formateo de Moneda

```javascript
const precio = CarWash.formatCurrency(125.5);
// "S/ 125.50"

const numero = CarWash.formatNumber(1234.567, 2);
// "1,234.57"
```

#### Formateo de Fechas

```javascript
const fecha = new Date();

// DD/MM/YYYY
CarWash.formatDate(fecha);
// "21/10/2025"

// DD/MM/YYYY HH:mm
CarWash.formatDateTime(fecha);
// "21/10/2025 14:30"

// YYYY-MM-DD (para inputs)
CarWash.formatDateInput(fecha);
// "2025-10-21"

// Relativo
CarWash.formatRelativeTime(fecha);
// "Hace 2 horas"
```

#### Formateo de Documentos

```javascript
// RUC
CarWash.formatRUC("20123456789");
// "20-12345678-9"

// Teléfono
CarWash.formatTelefono("987654321");
// "987 654 321"

// Placa
CarWash.formatPlaca("ABC123");
// "ABC-123"
```

#### Otros Formateos

```javascript
// Porcentaje
CarWash.formatPercentage(18.5);
// "18.50%"

// Capitalizar
CarWash.capitalize("juan pérez");
// "Juan Pérez"

// Truncar texto
CarWash.truncateText("Texto muy largo...", 10);
// "Texto muy ..."

// Tamaño de archivo
CarWash.formatFileSize(1536);
// "1.50 KB"

// Número a palabras (para comprobantes)
CarWash.numberToWords(125.5);
// "CIENTO VEINTICINCO CON 50/100 SOLES"
```

---

### 4. Bootstrap Init (bootstrap-init.js)

#### Inicialización de Componentes

```javascript
// Tooltips
CarWash.initTooltips();

// Popovers
CarWash.initPopovers();

// Bootstrap Select
CarWash.initBootstrapSelect(".selectpicker", {
    liveSearch: true,
    size: 7,
});

// DataTables
CarWash.initDataTable("miTabla", {
    perPage: 10,
    searchable: true,
});
```

#### Manejo de Modales

```javascript
// Abrir modal
CarWash.showBsModal("miModal");

// Cerrar modal
CarWash.hideBsModal("miModal");
```

#### Bootstrap Select Avanzado

```javascript
// Refrescar después de cambios
CarWash.refreshBootstrapSelect("#producto_id");

// Cambiar valor programáticamente
CarWash.setBootstrapSelectValue("#lavador_id", "5");

// Deshabilitar/habilitar
CarWash.toggleBootstrapSelect("#categoria_id", true); // disabled
```

#### Validación de Formularios

```javascript
const form = document.getElementById("formVenta");

// Habilitar validación Bootstrap
CarWash.initFormValidation(form);

// Limpiar validación
CarWash.clearFormValidation(form);
```

---

### 5. Lazy Loader (lazy-loader.js)

#### Lazy Loading de Imágenes

```html
<!-- En tu HTML -->
<img data-src="imagen-grande.jpg" alt="Descripción" class="img-fluid" />
```

```javascript
// Se inicializa automáticamente al cargar el DOM
// O manualmente:
CarWash.initLazyImages("img[data-src]");
```

#### Lazy Loading de Scripts

```javascript
// Cargar jQuery de manera diferida (si no está en CDN)
await CarWash.lazyLoadScript("/js/jquery.min.js", {
    async: true,
});

// Cargar CSS de forma diferida
await CarWash.lazyLoadCSS("/css/extra-styles.css");
```

#### Debounce y Throttle

```javascript
// Debounce para búsquedas
const buscarProducto = CarWash.debounce((query) => {
    // Hacer búsqueda
}, 300);

input.addEventListener("input", (e) => {
    buscarProducto(e.target.value);
});

// Throttle para scroll
const handleScroll = CarWash.throttle(() => {
    // Procesar scroll
}, 100);

window.addEventListener("scroll", handleScroll);
```

---

## 🔧 Migración Gradual

### Reemplazar código existente

**Antes:**

```javascript
Swal.fire({
    icon: "success",
    title: "Guardado",
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
});
```

**Después:**

```javascript
CarWash.showSuccess("Guardado", 5000);
```

---

**Antes:**

```javascript
let cantidad = parseInt($("#cantidad").val());
if (!cantidad || cantidad <= 0) {
    Swal.fire({
        icon: "error",
        text: "Debe ingresar una cantidad válida",
    });
    return;
}
```

**Después:**

```javascript
const cantidad = parseInt($("#cantidad").val());
if (!CarWash.isPositive(cantidad)) {
    CarWash.showError("Debe ingresar una cantidad válida");
    return;
}
```

---

## 🎨 Auto-formateo de Inputs

Agrega atributos `data-*` para formateo automático:

```html
<!-- Auto-formateo de moneda al perder foco -->
<input type="text" data-currency name="precio" class="form-control" />

<!-- Auto-formateo de placa -->
<input type="text" data-placa name="placa" class="form-control" />

<!-- Prevenir doble submit -->
<form data-prevent-double-submit>
    <button type="submit">Guardar</button>
</form>
```

---

## 🐛 Debugging

Todas las utilidades registran información en la consola:

```
🚀 CarWash ESP - Frontend inicializado
✅ Componentes Bootstrap inicializados
✅ Utilidades globales cargadas y disponibles en window.CarWash
```

Para debug en desarrollo, las utilidades están disponibles en la consola:

```javascript
// En DevTools Console
CarWash.showSuccess("Test");
CarWash.validateStock(10, 5, false);
CarWash.formatCurrency(99.99);
```

---

## 📖 Ejemplos Prácticos

### Ejemplo 1: Agregar Producto con Validación

```javascript
function agregarProducto() {
    const cantidad = parseInt($("#cantidad").val());
    const stock = parseInt($("#stock").val());
    const precio = parseFloat($("#precio").val());
    const descuento = parseFloat($("#descuento").val()) || 0;
    const esServicio = $("#servicio_lavado").is(":checked");

    // Validar cantidad
    if (!CarWash.isPositive(cantidad)) {
        CarWash.showError("Ingrese una cantidad válida");
        return;
    }

    // Validar stock
    const stockValidation = CarWash.validateStock(cantidad, stock, esServicio);
    if (!stockValidation.valid) {
        CarWash.showError(stockValidation.message);
        return;
    }

    // Validar precio
    const precioValidation = CarWash.validatePrecio(precio, 0);
    if (!precioValidation.valid) {
        CarWash.showError(precioValidation.message);
        return;
    }

    // Validar descuento
    const descuentoValidation = CarWash.validateDescuento(
        descuento,
        precio,
        cantidad
    );
    if (!descuentoValidation.valid) {
        CarWash.showError(descuentoValidation.message);
        return;
    }

    // Todo OK, agregar a tabla
    const subtotal = precio * cantidad - descuento;
    // ... resto de la lógica

    CarWash.showSuccess("Producto agregado");
}
```

### Ejemplo 2: Eliminar con Confirmación

```javascript
async function eliminarProducto(id) {
    const confirmed = await CarWash.showDeleteConfirm("este producto");

    if (confirmed) {
        $(`#fila${id}`).remove();
        recalcularTotales();
        CarWash.showSuccess("Producto eliminado");
    }
}
```

### Ejemplo 3: Guardar Venta

```javascript
async function guardarVenta(e) {
    e.preventDefault();

    const form = document.getElementById("form-venta");
    const button = document.getElementById("btn-guardar");

    // Validar tabla
    const tableValidation = CarWash.validateTableNotEmpty("tabla_detalle");
    if (!tableValidation.valid) {
        CarWash.showError(tableValidation.message);
        return;
    }

    // Validar formulario
    const formValidation = CarWash.validateForm(form);
    if (!formValidation.valid) {
        formValidation.errors.forEach((error) => {
            CarWash.showError(error);
        });
        return;
    }

    // Mostrar loading
    CarWash.setButtonLoading(button, true);

    try {
        const response = await axios.post("/ventas", {
            // ... datos
        });

        CarWash.showSuccess("Venta registrada correctamente");
        setTimeout(() => {
            window.location.href = "/ventas";
        }, 1500);
    } catch (error) {
        // El interceptor de axios ya muestra el error
        CarWash.setButtonLoading(button, false);
    }
}
```

---

## 🚀 Próximos Pasos

1. Migrar progresivamente el código inline de las vistas a estos módulos
2. Crear módulos específicos como `VentaManager.js`
3. Implementar tests unitarios
4. Agregar más validaciones según necesidades del negocio

---

**Mantenido por:** Equipo de Desarrollo CarWash ESP  
**Última actualización:** Octubre 2025
