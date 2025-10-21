# 🎯 FASE 3: COMPONENTES REUTILIZABLES Y MODERNIZACIÓN

**Fecha de inicio:** 21 de Octubre, 2025  
**Estado:** 📋 EN PLANIFICACIÓN  
**Objetivo:** Crear biblioteca de componentes reutilizables y migrar de jQuery a JavaScript moderno

---

## 📊 RESUMEN EJECUTIVO

### Objetivos de Fase 3

✅ **Completado antes:**
- **Fase 1:** 5 módulos utilitarios (2,500 líneas) - validators, formatters, notifications, dom-utils, api
- **Fase 2:** 4 managers con patrón State/Manager (1,975 líneas) - Venta, Compra, Lavados, Estacionamiento

🎯 **Fase 3 - Metas:**
1. **Extraer componentes comunes** identificados en Fase 2
2. **Migrar jQuery a Vanilla JS** progresivamente
3. **Evaluar framework ligero** (Alpine.js o Petite-Vue) para reactividad
4. **Establecer biblioteca de componentes** con API consistente
5. **Mejorar DX (Developer Experience)** con documentación y ejemplos

### Beneficios Esperados

| Métrica | Actual | Objetivo Fase 3 | Mejora |
|---------|--------|-----------------|--------|
| **Líneas duplicadas** | ~400 líneas | 0 líneas | -100% |
| **jQuery dependencia** | 100% en managers | 20% (solo Bootstrap Select) | -80% |
| **Bundle size** | 23.52 KB (7.81 KB gzip) | ~30 KB (10 KB gzip) | +2.2 KB (aceptable) |
| **Tiempo de desarrollo** | N/A | -50% para nuevas vistas | +100% velocidad |
| **Mantenibilidad** | Media | Alta | 📈 Mejora significativa |

---

## 🔍 ANÁLISIS DE PATRONES COMUNES

### Patrones Identificados en Fase 2

#### 1. **Gestión de Tablas Dinámicas** (VentaManager, CompraManager)

**Patrón común:**
```javascript
// Repetido en VentaManager.js y CompraManager.js
agregarFilaTabla(producto) {
    const fila = $(`
        <tr id="fila${producto.indice}">
            <td>${producto.nombre}</td>
            <td class="text-center">${producto.cantidad}</td>
            <td class="text-end">${formatCurrency(producto.precio)}</td>
            <td class="text-end">${formatCurrency(producto.subtotal)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" 
                        onclick="eliminarProducto(${producto.indice})">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>
    `);
    $('#tabla_detalle tbody').append(fila);
}
```

**Uso:**
- VentaManager: tabla de productos con precio/descuento
- CompraManager: tabla de productos con precioCompra/precioVenta
- Potencial: Lavados, Estacionamiento

**Componente propuesto:** `TableManager`

---

#### 2. **Persistencia localStorage** (VentaManager, CompraManager)

**Patrón común:**
```javascript
// Repetido en ambos managers con claves diferentes
guardarEnLocalStorage() {
    try {
        const estado = {
            productos: this.productos,
            // ... más propiedades
        };
        localStorage.setItem('venta_borrador', JSON.stringify(estado)); // o 'compra_borrador'
    } catch (error) {
        console.warn('No se pudo guardar en localStorage:', error);
    }
}

cargarDesdeLocalStorage() {
    try {
        const guardado = localStorage.getItem('venta_borrador');
        if (guardado) {
            const estado = JSON.parse(guardado);
            // Restaurar estado
        }
    } catch (error) {
        console.warn('No se pudo cargar desde localStorage:', error);
    }
}
```

**Componente propuesto:** `LocalStorageManager` (utility class)

---

#### 3. **Confirmaciones con SweetAlert2** (Todos los managers)

**Patrón común:**
```javascript
// Usado en VentaManager, CompraManager, EstacionamientoManager
const confirmado = await showConfirm(
    '¿Desea cancelar la venta?',
    'Se perderán todos los datos ingresados',
    'warning'
);

if (confirmado.isConfirmed) {
    // Acción
}
```

**Uso actual:**
- Cancelar venta/compra
- Eliminar producto de tabla
- Finalizar estacionamiento
- Completar lavado

**Componente propuesto:** Ya existe en `notifications.js`, pero mejorar API

---

#### 4. **Actualización de Totales** (VentaManager, CompraManager)

**Patrón común:**
```javascript
actualizarTotales() {
    const totales = this.state.calcularTotales();
    
    $('#sumas').val(totales.sumas.toFixed(2));
    $('#igv').val(totales.igv.toFixed(2));
    $('#total').val(totales.total.toFixed(2));
    
    // Actualizar input hidden para enviar form
    $('#input_sumas').val(totales.sumas);
    $('#input_igv').val(totales.igv);
    $('#input_total').val(totales.total);
}
```

**Componente propuesto:** `TotalesCalculator` (integrado en TableManager)

---

#### 5. **Filtros AJAX** (LavadosManager)

**Patrón común:**
```javascript
async aplicarFiltros() {
    try {
        this.mostrarLoading();
        
        const params = this.state.obtenerParametrosURL();
        const response = await axios.get('/control/lavados', { params });
        
        this.actualizarContenido(response.data.html);
        this.state.actualizarHistorial();
        
        this.ocultarLoading();
    } catch (error) {
        showError('Error al cargar lavados');
    }
}
```

**Uso futuro:**
- Filtros de productos
- Filtros de clientes
- Búsquedas generales

**Componente propuesto:** `AjaxFilterManager`

---

#### 6. **Validación de Formularios** (Todos los managers)

**Patrón común:**
```javascript
validarFormulario() {
    // Validar que haya productos
    if (!validateTableNotEmpty('#tabla_detalle')) {
        showError('Debe agregar al menos un producto');
        return false;
    }
    
    // Validar campos requeridos
    if (!validateRequiredFields(['#cliente_id', '#comprobante_id'])) {
        showError('Complete los campos requeridos');
        return false;
    }
    
    return true;
}
```

**Componente propuesto:** `FormValidator` (extension de validators.js)

---

#### 7. **Modales de Confirmación Bootstrap** (Múltiples vistas)

**Patrón común en Blade:**
```php
<!-- Repetido en categorias, marcas, presentaciones, roles, users, proveedores -->
<div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mensaje de Confirmación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar el registro?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <form action="{{ route('...destroy', $item->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>
```

**Componente propuesto:** `ConfirmModal` (Blade Component) o migrar a SweetAlert2

---

## 📦 BIBLIOTECA DE COMPONENTES PROPUESTA

### Estructura de archivos

```
resources/js/
├── components/               # NUEVO - Componentes reutilizables
│   ├── tables/
│   │   ├── DynamicTable.js      # Tabla dinámica con CRUD inline
│   │   ├── TableRow.js          # Fila de tabla reutilizable
│   │   └── TablePagination.js   # Paginación AJAX
│   ├── forms/
│   │   ├── FormValidator.js     # Validación de formularios
│   │   ├── AutoSave.js          # Auto-guardado en localStorage
│   │   └── SelectSearch.js      # Select con búsqueda (reemplazo Bootstrap Select)
│   ├── modals/
│   │   ├── ConfirmDialog.js     # Diálogos de confirmación
│   │   ├── FormModal.js         # Modal con formulario
│   │   └── AlertModal.js        # Alertas personalizadas
│   ├── filters/
│   │   ├── AjaxFilter.js        # Filtros AJAX genéricos
│   │   └── FilterState.js       # Estado de filtros
│   └── ui/
│       ├── LoadingSpinner.js    # Indicadores de carga
│       ├── Toast.js             # Notificaciones toast
│       └── Tooltip.js           # Tooltips dinámicos
├── utils/                    # EXISTENTE - Fase 1
│   ├── validators.js
│   ├── formatters.js
│   ├── notifications.js
│   ├── dom-utils.js
│   └── api.js
├── modules/                  # EXISTENTE - Fase 2
│   ├── VentaManager.js
│   ├── CompraManager.js
│   ├── LavadosManager.js
│   └── EstacionamientoManager.js
└── core/                     # NUEVO - Núcleo del framework
    ├── Component.js          # Clase base para componentes
    ├── EventBus.js           # Sistema de eventos
    └── Store.js              # State management global
```

---

## 🎨 DISEÑO DE COMPONENTES

### 1. **DynamicTable Component**

**Propósito:** Gestionar tablas dinámicas con agregar/editar/eliminar

**API:**
```javascript
import { DynamicTable } from '@/components/tables/DynamicTable';

const tabla = new DynamicTable({
    selector: '#tabla_detalle',
    columns: [
        { field: 'nombre', label: 'Producto', align: 'left' },
        { field: 'cantidad', label: 'Cantidad', align: 'center' },
        { field: 'precio', label: 'Precio', align: 'right', format: 'currency' },
        { field: 'subtotal', label: 'Subtotal', align: 'right', format: 'currency' }
    ],
    actions: [
        {
            icon: 'fa-trash',
            class: 'btn-danger',
            callback: (row) => this.eliminarProducto(row.id)
        }
    ],
    emptyMessage: 'No hay productos agregados',
    onRowAdded: (row) => this.actualizarTotales(),
    onRowRemoved: (id) => this.actualizarTotales()
});

// Usar
tabla.addRow({ id: 1, nombre: 'Shampoo', cantidad: 2, precio: 15.00, subtotal: 30.00 });
tabla.removeRow(1);
tabla.clear();
tabla.getData(); // Array de todas las filas
```

**Características:**
- ✅ Renderizado eficiente con DocumentFragment
- ✅ Eventos personalizados (onRowAdded, onRowRemoved, onRowUpdated)
- ✅ Formateo automático (currency, date, number)
- ✅ Validación de datos antes de agregar
- ✅ Manejo de estado interno
- ✅ Accesibilidad (ARIA labels)

---

### 2. **AutoSave Component**

**Propósito:** Auto-guardado en localStorage con recuperación

**API:**
```javascript
import { AutoSave } from '@/components/forms/AutoSave';

const autoSave = new AutoSave({
    key: 'venta_borrador',
    interval: 30000, // 30 segundos
    getData: () => ({
        productos: this.state.productos,
        cliente_id: $('#cliente_id').val(),
        // ... más campos
    }),
    onSave: (data) => console.log('Guardado automático:', data),
    onRestore: (data) => {
        // Restaurar estado
        this.state.productos = data.productos;
        // ... restaurar más campos
    },
    confirmRestore: true // Mostrar confirmación antes de restaurar
});

// Controlar
autoSave.start();
autoSave.stop();
autoSave.save(); // Guardar manualmente
autoSave.restore(); // Restaurar manualmente
autoSave.clear(); // Limpiar borrador
```

**Características:**
- ✅ Auto-guardado configurable
- ✅ Confirmación antes de restaurar
- ✅ Notificación de guardado exitoso
- ✅ Manejo de errores de quota exceeded
- ✅ Versionado de datos (migración de esquemas)

---

### 3. **AjaxFilter Component**

**Propósito:** Filtros AJAX con sincronización de URL

**API:**
```javascript
import { AjaxFilter } from '@/components/filters/AjaxFilter';

const filtros = new AjaxFilter({
    url: '/control/lavados',
    container: '#contenido-lavados',
    filters: {
        lavador_id: { selector: '#lavador_id', type: 'select' },
        estado: { selector: '#estado', type: 'select' },
        fecha: { selector: '#fecha', type: 'date' },
        search: { selector: '#search', type: 'text', debounce: 500 }
    },
    syncUrl: true, // Sincronizar con URL
    historyPush: true, // Agregar a historial
    loadingSelector: '.loading-spinner',
    onBeforeLoad: (params) => console.log('Cargando con:', params),
    onLoad: (html) => this.reinitTooltips(),
    onError: (error) => showError('Error al cargar')
});

// Controlar
filtros.apply(); // Aplicar filtros actuales
filtros.reset(); // Resetear a valores iniciales
filtros.getParams(); // Obtener parámetros actuales
```

**Características:**
- ✅ Debounce para inputs de texto
- ✅ Sincronización con URL y history API
- ✅ Loading states automáticos
- ✅ Cache de respuestas
- ✅ Cancelación de requests previos

---

### 4. **FormValidator Component**

**Propósito:** Validación de formularios con feedback visual

**API:**
```javascript
import { FormValidator } from '@/components/forms/FormValidator';

const validator = new FormValidator('#form-venta', {
    rules: {
        cliente_id: {
            required: true,
            message: 'Debe seleccionar un cliente'
        },
        comprobante_id: {
            required: true,
            message: 'Debe seleccionar un comprobante'
        },
        numero_comprobante: {
            required: true,
            pattern: /^[A-Z0-9]{3,15}$/,
            message: 'Formato inválido (Ej: F001-00001)'
        }
    },
    customRules: {
        hasProducts: () => {
            return this.state.productos.length > 0;
        }
    },
    onValidate: (isValid, errors) => {
        if (!isValid) {
            showError(errors[0].message);
        }
    },
    realtime: true // Validar en tiempo real
});

// Usar
if (validator.validate()) {
    // Enviar formulario
}

validator.reset(); // Limpiar errores
validator.setErrors({ cliente_id: 'Error custom' }); // Errores manuales
```

**Características:**
- ✅ Validación en tiempo real
- ✅ Feedback visual (clases Bootstrap)
- ✅ Reglas customizables
- ✅ Integración con validators.js de Fase 1
- ✅ Mensajes de error personalizables

---

### 5. **SelectSearch Component** (Reemplazo jQuery Bootstrap Select)

**Propósito:** Select con búsqueda sin jQuery

**API:**
```javascript
import { SelectSearch } from '@/components/forms/SelectSearch';

const productSelect = new SelectSearch('#producto_id', {
    searchPlaceholder: 'Buscar producto...',
    noResultsText: 'No se encontraron productos',
    liveSearch: true,
    showSubtext: true, // Mostrar data-subtext
    onChange: (value, text) => {
        console.log('Producto seleccionado:', value, text);
    },
    ajax: {
        url: '/api/productos/search',
        delay: 300,
        processResults: (data) => data.map(p => ({
            value: p.id,
            text: p.nombre,
            subtext: `Stock: ${p.stock}`
        }))
    }
});

// Controlar
productSelect.setValue(5);
productSelect.refresh(); // Refrescar opciones
productSelect.destroy();
```

**Características:**
- ✅ Búsqueda local y AJAX
- ✅ Accesibilidad completa
- ✅ Estilo compatible con Bootstrap
- ✅ Sin dependencia de jQuery
- ✅ Soporte para grupos de opciones

---

## 🔧 MIGRACIÓN DE JQUERY A VANILLA JS

### Estrategia de Migración

#### Fase 3.1: Análisis de dependencias jQuery

**jQuery usado actualmente:**
```javascript
// VentaManager.js - 47 ocurrencias
$('#selector')              // 15 veces - Selección
$('#campo').val()           // 12 veces - Get/Set valores
$('#tabla tbody').append()  // 5 veces - Manipulación DOM
$.ajax() / axios            // 0 veces - Ya migrado a axios
$('#select').on('change')   // 8 veces - Event listeners
$('option:selected').text() // 7 veces - Traversing
```

**Prioridad de migración:**
1. 🔴 **Alta:** Selección de elementos (`$('#selector')`)
2. 🟡 **Media:** Get/Set valores (`val()`, `text()`, `html()`)
3. 🟢 **Baja:** Event listeners (`on()`) - Funciona bien
4. ⚪ **No migrar:** Bootstrap Select (hasta encontrar reemplazo)

#### Fase 3.2: Crear helpers de migración

**Archivo:** `resources/js/utils/dom-helpers.js` (ACTUALIZAR)

```javascript
/**
 * Helpers de migración jQuery → Vanilla JS
 */

// Selección (reemplazo de $())
export const $ = (selector) => {
    if (selector.startsWith('#')) {
        return document.getElementById(selector.slice(1));
    }
    return document.querySelector(selector);
};

export const $$ = (selector) => document.querySelectorAll(selector);

// Get/Set valores
export const val = (element, value = undefined) => {
    if (typeof element === 'string') {
        element = $(element);
    }
    
    if (value === undefined) {
        return element.value;
    }
    
    element.value = value;
    return element;
};

// Append
export const append = (parent, child) => {
    if (typeof parent === 'string') {
        parent = $(parent);
    }
    
    if (typeof child === 'string') {
        parent.insertAdjacentHTML('beforeend', child);
    } else {
        parent.appendChild(child);
    }
};

// Event delegation
export const on = (selector, event, callback) => {
    document.addEventListener(event, (e) => {
        if (e.target.matches(selector)) {
            callback.call(e.target, e);
        }
    });
};

// ... más helpers
```

**Uso en managers:**
```javascript
// Antes (jQuery)
$('#producto_id').val(5);
$('#tabla tbody').append(fila);

// Después (Vanilla JS con helpers)
import { $, val, append } from '@/utils/dom-helpers';

val('#producto_id', 5);
append('#tabla tbody', fila);
```

#### Fase 3.3: Migración progresiva

**Orden de migración:**
1. ✅ **Semana 1:** VentaManager (705 líneas)
2. ✅ **Semana 2:** CompraManager (559 líneas)
3. ✅ **Semana 3:** LavadosManager (343 líneas)
4. ✅ **Semana 4:** EstacionamientoManager (368 líneas)
5. ⏳ **Semana 5:** SelectSearch component (reemplazo Bootstrap Select)

**Métricas esperadas:**
- Reducción de bundle: -30 KB (si removemos jQuery del CDN)
- Tiempo de migración: 1 hora por manager
- Riesgo: Bajo (manteniendo tests)

---

## 🌐 EVALUACIÓN DE FRAMEWORKS LIGEROS

### Opción 1: **Alpine.js** (Recomendado)

**Pros:**
- ✅ Sintaxis declarativa similar a Vue.js
- ✅ Solo 15 KB gzipped
- ✅ Integración perfecta con Laravel/Blade
- ✅ No requiere build step (puede usarse desde CDN)
- ✅ Curva de aprendizaje baja

**Contras:**
- ❌ Menos maduro que Vue/React
- ❌ Comunidad más pequeña
- ❌ Menos plugins/extensiones

**Ejemplo de uso:**
```html
<!-- Blade view -->
<div x-data="ventaForm()">
    <select x-model="productoId" @change="agregarProducto()">
        <!-- opciones -->
    </select>
    
    <table>
        <template x-for="producto in productos" :key="producto.id">
            <tr>
                <td x-text="producto.nombre"></td>
                <td x-text="formatCurrency(producto.precio)"></td>
                <td>
                    <button @click="eliminar(producto.id)">Eliminar</button>
                </td>
            </tr>
        </template>
    </table>
    
    <div>
        <strong>Total:</strong>
        <span x-text="formatCurrency(total)"></span>
    </div>
</div>

<script>
function ventaForm() {
    return {
        productoId: null,
        productos: [],
        
        get total() {
            return this.productos.reduce((sum, p) => sum + p.subtotal, 0);
        },
        
        agregarProducto() {
            // lógica
        },
        
        eliminar(id) {
            this.productos = this.productos.filter(p => p.id !== id);
        }
    };
}
</script>
```

**Integración con arquitectura actual:**
- Usar Alpine.js para vistas simples (index, CRUD básicos)
- Mantener managers para lógica compleja (ventas, compras)
- Alpine maneja UI reactiva, managers manejan business logic

---

### Opción 2: **Petite-Vue**

**Pros:**
- ✅ API idéntica a Vue 3
- ✅ Solo 6 KB gzipped (más ligero que Alpine)
- ✅ Optimizado para progressive enhancement
- ✅ Sin build step requerido

**Contras:**
- ❌ Menos features que Vue completo
- ❌ Documentación limitada
- ❌ No tiene ecosystem de plugins

**Ejemplo:**
```html
<div v-scope="{ count: 0 }">
    <button @click="count++">Incrementar</button>
    <p>{{ count }}</p>
</div>

<script type="module">
import { createApp } from 'https://unpkg.com/petite-vue?module';
createApp().mount();
</script>
```

---

### Opción 3: **Mantener arquitectura actual + Web Components**

**Pros:**
- ✅ Estándar web nativo
- ✅ Sin dependencias adicionales
- ✅ Encapsulación perfecta
- ✅ Reutilizable en cualquier framework

**Contras:**
- ❌ Más verboso que frameworks
- ❌ Sin reactividad built-in
- ❌ Soporte limitado en IE11 (no es problema en 2025)

**Ejemplo:**
```javascript
// components/DynamicTable.js
class DynamicTable extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
    }
    
    connectedCallback() {
        this.render();
    }
    
    render() {
        this.shadowRoot.innerHTML = `
            <style>
                table { width: 100%; }
                /* ... estilos */
            </style>
            <table>
                <thead>
                    <slot name="header"></slot>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
        `;
    }
    
    addRow(data) {
        // lógica
    }
}

customElements.define('dynamic-table', DynamicTable);
```

**Uso en Blade:**
```html
<dynamic-table>
    <tr slot="header">
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio</th>
    </tr>
</dynamic-table>
```

---

### Recomendación Final

**🏆 Alpine.js** para Fase 3

**Justificación:**
1. Balance perfecto entre simplicidad y features
2. Integración natural con Laravel/Blade
3. No rompe arquitectura actual (complementa managers)
4. Comunidad activa y documentación excelente
5. Usado por Laravel Livewire (ecosistema familiar)

**Plan de adopción:**
- **Corto plazo:** Usar Alpine en vistas index (categorías, marcas, roles)
- **Mediano plazo:** Migrar componentes simples (modales, tooltips)
- **Largo plazo:** Evaluar si reemplazar managers por Alpine (opcional)

---

## 📋 PLAN DE IMPLEMENTACIÓN

### Sprint 1: Componentes de Tablas (Semana 1-2)

**Objetivos:**
- [x] Diseñar API de DynamicTable
- [ ] Implementar DynamicTable component
- [ ] Implementar TableRow component
- [ ] Migrar VentaManager a usar DynamicTable
- [ ] Migrar CompraManager a usar DynamicTable
- [ ] Tests unitarios de DynamicTable

**Entregables:**
- `components/tables/DynamicTable.js` (300 líneas)
- `components/tables/TableRow.js` (100 líneas)
- VentaManager refactorizado (-150 líneas)
- CompraManager refactorizado (-120 líneas)
- Tests: `DynamicTable.test.js` (20 casos)

---

### Sprint 2: Persistencia y AutoSave (Semana 3)

**Objetivos:**
- [ ] Implementar AutoSave component
- [ ] Implementar LocalStorageManager utility
- [ ] Migrar lógica de localStorage de managers
- [ ] Agregar versionado de esquemas
- [ ] Agregar compresión de datos (LZ-String)

**Entregables:**
- `components/forms/AutoSave.js` (200 líneas)
- `utils/storage.js` (150 líneas)
- VentaManager refactorizado (-50 líneas)
- CompraManager refactorizado (-50 líneas)

---

### Sprint 3: Filtros AJAX (Semana 4)

**Objetivos:**
- [ ] Implementar AjaxFilter component
- [ ] Implementar FilterState class
- [ ] Migrar LavadosManager a usar AjaxFilter
- [ ] Agregar cache de respuestas
- [ ] Implementar cancelación de requests

**Entregables:**
- `components/filters/AjaxFilter.js` (250 líneas)
- `components/filters/FilterState.js` (100 líneas)
- LavadosManager refactorizado (-100 líneas)

---

### Sprint 4: Validación de Formularios (Semana 5)

**Objetivos:**
- [ ] Implementar FormValidator component
- [ ] Extender validators.js con nuevas reglas
- [ ] Integrar con managers existentes
- [ ] Agregar validación en tiempo real
- [ ] Feedback visual (Bootstrap classes)

**Entregables:**
- `components/forms/FormValidator.js` (300 líneas)
- validators.js extendido (+20 reglas)
- Todos los managers refactorizados

---

### Sprint 5: Migración jQuery (Semana 6-7)

**Objetivos:**
- [ ] Crear dom-helpers.js completo
- [ ] Migrar VentaManager a Vanilla JS
- [ ] Migrar CompraManager a Vanilla JS
- [ ] Migrar LavadosManager a Vanilla JS
- [ ] Migrar EstacionamientoManager a Vanilla JS
- [ ] Tests de regresión

**Entregables:**
- `utils/dom-helpers.js` actualizado (400 líneas)
- 4 managers migrados (-47 ocurrencias jQuery)
- Tests E2E verificando funcionalidad

---

### Sprint 6: SelectSearch Component (Semana 8)

**Objetivos:**
- [ ] Implementar SelectSearch component
- [ ] Reemplazar Bootstrap Select en ventas
- [ ] Reemplazar Bootstrap Select en compras
- [ ] Agregar soporte para AJAX
- [ ] Styling compatible con Bootstrap

**Entregables:**
- `components/forms/SelectSearch.js` (500 líneas)
- CSS module para estilos (100 líneas)
- Ventas y compras sin jQuery
- **Remover jQuery del proyecto** 🎉

---

### Sprint 7: Alpine.js Integration (Semana 9-10)

**Objetivos:**
- [ ] Evaluar Alpine.js con POC
- [ ] Migrar vista index de categorías
- [ ] Migrar vista index de marcas
- [ ] Crear guía de uso de Alpine
- [ ] Documentar patrones recomendados

**Entregables:**
- Alpine.js integrado en proyecto
- 2 vistas migradas a Alpine
- Documentación: `ALPINE_GUIDE.md`
- Ejemplos de uso

---

### Sprint 8: Documentación y Testing (Semana 11-12)

**Objetivos:**
- [ ] Documentar todos los componentes
- [ ] Crear Storybook o similar para componentes
- [ ] Tests E2E completos (Playwright)
- [ ] Tests unitarios (Vitest)
- [ ] Benchmarks de performance

**Entregables:**
- `COMPONENTS_API.md` (documentación completa)
- Storybook con ejemplos interactivos
- 50+ tests E2E
- 100+ tests unitarios
- Reporte de performance

---

## 📊 MÉTRICAS DE ÉXITO

### KPIs Fase 3

| Métrica | Línea Base | Objetivo | Medición |
|---------|-----------|----------|----------|
| **Código duplicado** | 400 líneas | 0 líneas | SonarQube |
| **jQuery dependencia** | 100% | 0% | Bundle analysis |
| **Bundle size (gzip)** | 7.81 KB | < 12 KB | Vite build |
| **Test coverage** | 0% | > 80% | Vitest |
| **Tiempo de desarrollo** | - | -50% | Tracking manual |
| **Lighthouse score** | - | > 95 | Chrome DevTools |

### Criterios de Aceptación

✅ **Completado cuando:**
1. 0 líneas de código duplicado entre managers
2. jQuery completamente removido (excepto si Alpine no se adopta)
3. 8 componentes reutilizables funcionando
4. 100+ tests automatizados pasando
5. Bundle size < 12 KB gzipped
6. Documentación completa de componentes
7. 2+ vistas migradas a Alpine.js (si se adopta)

---

## 🎓 GUÍAS DE DESARROLLO

### Crear un Nuevo Componente

```javascript
// 1. Crear archivo en components/
// components/ui/MyComponent.js

/**
 * @class MyComponent
 * @description Descripción del componente
 * @example
 * const comp = new MyComponent({
 *   selector: '#element',
 *   option: 'value'
 * });
 */
export class MyComponent {
    /**
     * @param {Object} options - Configuración
     * @param {string} options.selector - Selector CSS
     */
    constructor(options = {}) {
        this.options = {
            // Valores por defecto
            ...options
        };
        
        this.element = document.querySelector(this.options.selector);
        
        if (!this.element) {
            throw new Error(`Element not found: ${this.options.selector}`);
        }
        
        this.init();
    }
    
    /**
     * Inicializar componente
     * @private
     */
    init() {
        this.attachEvents();
        this.render();
    }
    
    /**
     * Adjuntar event listeners
     * @private
     */
    attachEvents() {
        // Event listeners
    }
    
    /**
     * Renderizar componente
     * @private
     */
    render() {
        // Renderizado
    }
    
    /**
     * Destruir componente y limpiar eventos
     * @public
     */
    destroy() {
        // Cleanup
    }
}

// 2. Exportar desde index
// components/index.js
export { MyComponent } from './ui/MyComponent';

// 3. Documentar en COMPONENTS_API.md

// 4. Crear tests
// tests/unit/MyComponent.test.js
import { describe, it, expect } from 'vitest';
import { MyComponent } from '@/components/ui/MyComponent';

describe('MyComponent', () => {
    it('should initialize correctly', () => {
        // Test
    });
});
```

---

## 🔄 TRANSICIÓN DE ARQUITECTURAS

### Actual (Post-Fase 2)

```
Blade View
    ↓
Manager (State + UI Logic)
    ↓
Utils (validators, formatters, notifications)
    ↓
Backend (Laravel API)
```

### Fase 3 - Opción A (Solo Componentes)

```
Blade View
    ↓
Manager (Business Logic)
    ↓
Components (UI Logic) ← NUEVO
    ↓
Utils (helpers)
    ↓
Backend
```

### Fase 3 - Opción B (Alpine.js)

```
Blade View (Alpine directives) ← Reactividad
    ↓
Manager (Business Logic)
    ↓
Components (Widgets complejos)
    ↓
Utils
    ↓
Backend
```

**Recomendación:** Opción B para nuevas vistas, mantener Opción A para vistas complejas

---

## 📚 RECURSOS Y REFERENCIAS

### Documentación Oficial

- **Alpine.js:** https://alpinejs.dev/
- **Petite-Vue:** https://github.com/vuejs/petite-vue
- **Web Components:** https://developer.mozilla.org/en-US/docs/Web/Web_Components
- **Vite:** https://vitejs.dev/guide/
- **Vitest:** https://vitest.dev/guide/

### Inspiración

- **Laravel Breeze + Alpine:** https://github.com/laravel/breeze
- **Shoelace (Web Components):** https://shoelace.style/
- **Headless UI:** https://headlessui.com/

---

## ⏭️ PRÓXIMOS PASOS

### Inmediatos (Esta semana)

1. ✅ **Crear este documento FASE_3_PLAN.md**
2. [ ] **Revisar y aprobar plan con stakeholders**
3. [ ] **Setup ambiente de testing:**
   - Instalar Vitest: `npm install -D vitest`
   - Configurar `vitest.config.js`
   - Crear carpeta `tests/unit/`
4. [ ] **Crear estructura de carpetas components/**
5. [ ] **Empezar Sprint 1: DynamicTable component**

### Siguientes 2 semanas

- [ ] Implementar DynamicTable
- [ ] Implementar AutoSave
- [ ] Migrar VentaManager y CompraManager a usar componentes
- [ ] Primeros 20 tests unitarios

### Mes 1 completo

- [ ] Todos los componentes core implementados
- [ ] jQuery migrado al 50%
- [ ] 50+ tests automatizados
- [ ] Documentación de componentes actualizada

---

## 🎯 CONCLUSIÓN

**Fase 3** es la evolución natural después del éxito de Fase 1 y Fase 2. Nos enfocamos en:

1. 📦 **DRY (Don't Repeat Yourself):** Eliminar código duplicado
2. 🧩 **Componentes reutilizables:** Biblioteca consistente
3. 🚀 **Modernización:** Eliminar dependencias legacy (jQuery)
4. 🎨 **DX (Developer Experience):** Más fácil desarrollar nuevas features
5. 📈 **Escalabilidad:** Base sólida para futuro crecimiento

**Impacto esperado:**
- ⏱️ **Desarrollo 50% más rápido** para nuevas vistas
- 🐛 **Menos bugs** por código centralizado y testeado
- 🎓 **Onboarding más fácil** con documentación clara
- 📊 **Mejor performance** con bundle optimizado

---

**Estado:** 📋 Pendiente de aprobación  
**Próxima reunión:** TBD  
**Autor:** GitHub Copilot Assistant  
**Fecha:** 21 de Octubre, 2025
