# 🚀 FASE 2 EN PROGRESO - Refactorización de Vistas

**Fecha de inicio:** 21 de Octubre, 2025  
**Estado:** ⏳ EN PROGRESO  
**Primera vista migrada:** `venta/create.blade.php`

---

## 📋 Resumen Ejecutivo

La Fase 2 se centra en **extraer todo el código JavaScript inline de las vistas Blade** y migrar la lógica a módulos ES6 reutilizables y mantenibles.

### ✅ Objetivos de la Fase 2

1. **Eliminar código duplicado** - Extraer ~300 líneas de JS inline por vista
2. **Arquitectura modular** - Crear clases JavaScript con responsabilidades claras
3. **Persistencia de datos** - Implementar localStorage para borradores
4. **Mejor UX** - Agregar confirmaciones, validaciones en tiempo real, auto-guardado
5. **Mantenibilidad** - Código testeable, documentado y fácil de modificar

---

## 🎯 Vista Migrada: venta/create.blade.php

### Análisis Inicial

**Código inline original:**
- **335 líneas de JavaScript** embebidas en la vista
- 10 funciones globales: `agregarProducto()`, `eliminarProducto()`, `recalcularIGV()`, etc.
- Validaciones manuales repetidas
- Manipulación directa del DOM
- Sin persistencia de datos
- Sin confirmaciones para acciones destructivas

### ✨ Solución Implementada: VentaManager.js

**Ubicación:** `resources/js/modules/VentaManager.js`  
**Tamaño:** 705 líneas (incluyendo documentación JSDoc)  
**Arquitectura:** 2 clases principales

#### Clase `VentaState`

Maneja el estado completo de la venta:

```javascript
class VentaState {
    constructor() {
        this.productos = [];
        this.contador = 0;
        this.impuesto = 18;
        this.sumas = 0;
        this.igv = 0;
        this.total = 0;
    }
    
    // Métodos principales:
    // - agregarProducto()
    // - eliminarProducto()
    // - calcularTotales()
    // - recalcularIGV()
    // - guardarEnLocalStorage()
    // - cargarDesdeLocalStorage()
}
```

**Ventajas:**
- Estado centralizado y predecible
- Fácil de testear (funciones puras)
- Persistencia automática en localStorage

#### Clase `VentaManager`

Coordina la interacción entre UI y estado:

```javascript
export class VentaManager {
    constructor() {
        this.state = new VentaState();
        this.init();
    }
    
    // Métodos principales:
    // - setupEventListeners()
    // - agregarProducto() - Con validaciones usando validators.js
    // - eliminarProducto() - Con confirmación async
    // - actualizarTotales()
    // - cancelarVenta() - Con confirmación
    // - validarAntesDeGuardar()
    // - intentarRecuperarBorrador()
    // - iniciarAutoGuardado() - Cada 30 segundos
}
```

**Ventajas:**
- Separación de responsabilidades
- Usa las utilidades de Fase 1 (validators.js, formatters.js, notifications.js)
- Confirma acciones destructivas con SweetAlert2
- Auto-guardado periódico

---

## 🔄 Funcionalidades Migradas

### ✅ Agregar Producto

**Antes (inline):**
```javascript
function agregarProducto() {
    // 60 líneas de código
    // Validaciones manuales con if/else
    // Mensajes hardcodeados
    // Sin reutilización
}
```

**Después (VentaManager):**
```javascript
agregarProducto() {
    // Validaciones con validators.js
    const stockValidation = validateStock(cantidad, stock, esServicioLavado);
    if (!stockValidation.valid) {
        showError(stockValidation.message);
        return;
    }
    
    // Agregar al estado
    const producto = this.state.agregarProducto(...);
    
    // Actualizar UI
    this.agregarFilaTabla(producto);
    this.actualizarTotales();
    
    // Persistencia
    this.state.guardarEnLocalStorage();
    
    showSuccess('Producto agregado correctamente');
}
```

**Mejoras:**
- ✅ Validaciones reutilizables
- ✅ Mensajes centralizados
- ✅ Auto-guardado en localStorage
- ✅ Notificaciones consistentes
- ✅ Código 50% más corto

---

### ✅ Eliminar Producto

**Antes:**
```javascript
function eliminarProducto(indice) {
    // Elimina directamente sin confirmar
    // Código imperativo
}
```

**Después:**
```javascript
async eliminarProducto(indice) {
    const confirmado = await showConfirm(
        '¿Eliminar producto?',
        'Esta acción no se puede deshacer'
    );
    
    if (!confirmado) return;
    
    this.state.eliminarProducto(indice);
    $(`#fila${indice}`).remove();
    this.actualizarTotales();
    this.state.guardarEnLocalStorage();
    
    showSuccess('Producto eliminado');
}
```

**Mejoras:**
- ✅ Confirmación antes de eliminar
- ✅ Async/await para mejor UX
- ✅ Actualización automática de totales

---

### ✅ Cancelar Venta

**Antes:**
```javascript
function cancelarVenta() {
    // Cancela directamente sin confirmar
    // Código repetitivo
}
```

**Después:**
```javascript
async cancelarVenta() {
    const confirmado = await showConfirm(
        '¿Cancelar venta?',
        'Se perderán todos los productos agregados'
    );
    
    if (!confirmado) return;
    
    this.state.limpiar();
    this.state.limpiarLocalStorage();
    // ... limpiar UI
    
    showSuccess('Venta cancelada');
}
```

**Mejoras:**
- ✅ Confirmación antes de cancelar
- ✅ Limpia localStorage automáticamente

---

### 🆕 Recuperación de Borrador

**Funcionalidad nueva:**

```javascript
async intentarRecuperarBorrador() {
    const hayBorrador = this.state.cargarDesdeLocalStorage();
    
    if (!hayBorrador) return;
    
    const recuperar = await showConfirm(
        '¿Recuperar venta anterior?',
        'Se encontró una venta sin completar. ¿Deseas recuperarla?'
    );
    
    if (recuperar) {
        this.recuperarBorrador();
    }
}
```

**Beneficios:**
- ✅ No se pierde información si se cierra accidentalmente
- ✅ Experiencia de usuario mejorada
- ✅ Opción de recuperar o empezar de nuevo

---

### 🆕 Auto-guardado Periódico

**Funcionalidad nueva:**

```javascript
iniciarAutoGuardado() {
    this.autoGuardarInterval = setInterval(() => {
        const hayProductos = this.state.productos.some(p => p !== null);
        if (hayProductos) {
            this.state.guardarEnLocalStorage();
            console.log('💾 Auto-guardado realizado');
        }
    }, 30000); // 30 segundos
}
```

**Beneficios:**
- ✅ Guardado automático cada 30 segundos
- ✅ Solo guarda si hay productos
- ✅ Log en consola para debugging

---

## 📦 Integración con Utilidades (Fase 1)

El `VentaManager` aprovecha **todas** las utilidades creadas en la Fase 1:

### De `notifications.js`:
```javascript
import { 
    showSuccess,      // ✅ Mensajes de éxito
    showError,        // ✅ Mensajes de error
    showConfirm,      // ✅ Confirmaciones async
    setButtonLoading  // ✅ Loading en botones
} from '@utils/notifications';
```

### De `validators.js`:
```javascript
import { 
    validateStock,          // ✅ Validar stock vs cantidad
    validatePrecio,         // ✅ Validar precio > 0
    validateDescuento,      // ✅ Validar descuento <= subtotal
    isPositive,             // ✅ Verificar positivo
    isInteger,              // ✅ Verificar entero
    validateTableNotEmpty   // ✅ Validar tabla con productos
} from '@utils/validators';
```

### De `formatters.js`:
```javascript
import { 
    formatCurrency,   // ✅ Formatear S/ 125.50
    parseCurrency     // ✅ Parsear "S/ 125.50" → 125.50
} from '@utils/formatters';
```

**Resultado:** Código limpio, reutilizable y fácil de mantener.

---

## ⚙️ Cambios en Configuración

### vite.config.js

```javascript
input: [
    'resources/css/app.css', 
    'resources/js/app.js',
    'resources/js/modules/VentaManager.js',  // ← Nuevo entry point
],

// ...

manualChunks: {
    'vendor-core': ['axios', 'lodash'],
    'utils': [/* utilidades de Fase 1 */],
    'modules': [
        './resources/js/modules/VentaManager.js',  // ← Nuevo chunk
    ],
},
```

**Resultado del build:**
```
✓ 62 modules transformed
public/build/assets/VentaManager.e67b0234.js    7.69 KB / gzip: 2.40 KiB
public/build/assets/modules.b0c3cd21.js         0.00 KB / gzip: 0.02 KiB
public/build/assets/utils.57cb95f7.js           15.08 KB / gzip: 4.91 KiB
public/build/assets/vendor-core.8a569419.js     102.62 KB / gzip: 37.07 KiB
```

---

### venta/create.blade.php

**Antes:**
```blade
@push('js')
<script>
    // 335 líneas de código inline
</script>
@endpush
```

**Después:**
```blade
@push('js')
{{-- Cargar jQuery y Bootstrap Select desde CDN (temporal) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

{{-- Cargar módulo VentaManager --}}
@vite(['resources/js/modules/VentaManager.js'])
@endpush
```

**Reducción:** De 335 líneas a **5 líneas** (-98.5%)

---

## 📊 Métricas de Impacto

### Antes vs Después

| Métrica                     | Antes         | Después       | Mejora    |
|-----------------------------|---------------|---------------|-----------|
| Líneas de código inline     | 335           | 5             | -98.5%    |
| Funciones globales          | 10            | 0             | -100%     |
| Validaciones reutilizables  | 0             | 8             | +∞        |
| Confirmaciones              | 0             | 3             | +∞        |
| Persistencia (localStorage) | No            | Sí            | ✅        |
| Auto-guardado               | No            | Sí (30s)      | ✅        |
| Recuperación de borrador    | No            | Sí            | ✅        |
| Formato de moneda           | Manual        | Automático    | ✅        |
| Tests posibles              | Difícil       | Fácil         | ✅        |

---

## 🧪 Funcionalidades a Probar

### Checklist de Testing

#### ✅ Agregar Producto
- [ ] Seleccionar producto del dropdown
- [ ] Ingresar cantidad válida
- [ ] Validar cantidad > stock (debe mostrar error)
- [ ] Ingresar descuento válido
- [ ] Validar descuento > subtotal (debe mostrar error)
- [ ] Producto se agrega a la tabla correctamente
- [ ] Totales se calculan correctamente
- [ ] Mensaje de éxito se muestra

#### ✅ Eliminar Producto
- [ ] Hacer clic en botón eliminar
- [ ] Modal de confirmación aparece
- [ ] Cancelar no elimina el producto
- [ ] Confirmar elimina el producto
- [ ] Totales se recalculan
- [ ] Mensaje de éxito se muestra

#### ✅ Calcular Totales
- [ ] Sumas se calculan correctamente
- [ ] IGV se calcula solo en Facturas con checkbox marcado
- [ ] Total = Sumas + IGV
- [ ] Cambiar tipo de comprobante recalcula IGV
- [ ] Cambiar porcentaje de IGV recalcula total

#### ✅ Persistencia localStorage
- [ ] Agregar productos y refrescar página
- [ ] Modal de recuperación aparece
- [ ] Recuperar restaura los productos
- [ ] "Nueva venta" limpia el borrador
- [ ] Auto-guardado funciona cada 30 segundos

#### ✅ Cancelar Venta
- [ ] Hacer clic en "Cancelar Venta"
- [ ] Modal de confirmación aparece
- [ ] Confirmar limpia tabla y totales
- [ ] localStorage se limpia

#### ✅ Guardar Venta
- [ ] Validar tabla vacía (debe mostrar error)
- [ ] Validar servicio de lavado sin horario (debe mostrar error)
- [ ] Botón muestra loading durante guardado
- [ ] localStorage se limpia después de guardar

---

## 🐛 Problemas Conocidos

### ⚠️ jQuery Dependency

**Problema:** El módulo sigue dependiendo de jQuery ($) porque Bootstrap Select lo requiere.

**Solución temporal:** Cargar jQuery desde CDN en la vista.

**Solución futura (Fase 3):**
- Migrar Bootstrap Select a una alternativa vanilla JS (ej: Choices.js)
- O crear wrapper que cargue jQuery solo cuando sea necesario

---

### ⚠️ Bootstrap Select desde CDN

**Problema:** Bootstrap Select se carga desde CDN en lugar de npm.

**Solución temporal:** CDN funcionando correctamente.

**Solución futura:**
- Instalar Bootstrap Select vía npm
- Importarlo en el módulo
- Eliminar CDN de la vista

---

## 🎯 Vista Migrada: compra/create.blade.php

### Análisis Inicial

**Código inline original:**
- **237 líneas de JavaScript** embebidas en la vista
- 12 funciones globales: `agregarProducto()`, `eliminarProducto()`, `recalcularIGV()`, `limpiarCampos()`, etc.
- Validaciones manuales (precio_compra vs precio_venta)
- Manipulación directa del DOM
- Sin persistencia de datos
- Sin confirmaciones para acciones destructivas

### ✨ Solución Implementada: CompraManager.js

**Ubicación:** `resources/js/modules/CompraManager.js`  
**Tamaño:** 559 líneas (incluyendo documentación JSDoc)  
**Arquitectura:** 2 clases principales (patrón similar a VentaManager)

#### Clase `CompraState`

Maneja el estado completo de la compra:

```javascript
class CompraState {
    constructor() {
        this.productos = [];
        this.contador = 0;
        this.impuesto = 18;
        this.sumas = 0;
        this.igv = 0;
        this.total = 0;
    }
    
    // Métodos principales:
    // - agregarProducto(id, nombre, cantidad, precioCompra, precioVenta)
    // - eliminarProducto(indice)
    // - calcularTotales()
    // - recalcularIGV()
    // - guardarEnLocalStorage() → 'compra_borrador'
    // - cargarDesdeLocalStorage()
}
```

**Diferencias clave con VentaState:**
- Maneja `precioCompra` y `precioVenta` (en lugar de precio + descuento)
- No valida stock (compras agregan inventario)
- localStorage usa clave diferente: `'compra_borrador'`

#### Clase `CompraManager`

Coordina la interacción entre UI y estado:

```javascript
export class CompraManager {
    constructor() {
        this.state = new CompraState();
        this.init();
    }
    
    // Métodos principales:
    // - setupEventListeners()
    // - agregarProducto() - Validaciones específicas de compras
    // - eliminarProducto() - Con confirmación async
    // - actualizarTotales()
    // - cancelarCompra() - Con confirmación
    // - validarAntesDeGuardar()
    // - intentarRecuperarBorrador()
    // - iniciarAutoGuardado() - Cada 30 segundos
}
```

**Características especiales de compras:**
- ✅ Valida `precioVenta >= precioCompra` (warning si precioVenta < precioCompra)
- ✅ No valida stock (compras incrementan inventario)
- ✅ Calcula subtotal basado en `cantidad * precioCompra`

---

### 📊 Métricas de Migración - Compras

| Métrica | Antes | Después | Cambio |
|---------|-------|---------|--------|
| Líneas totales vista | ~468 líneas | 231 líneas | -50.6% |
| JavaScript inline | 237 líneas | 0 líneas | **-100%** |
| Funciones globales | 12 | 0 | -12 |
| Módulos creados | 0 | 1 (CompraManager.js) | +1 |
| Líneas CompraManager | 0 | 559 líneas | +559 |
| Bundle size | N/A | 6.37 KB | N/A |
| Gzipped | N/A | 2.05 KB | N/A |

**Comparación con VentaManager:**
- CompraManager: 559 líneas vs VentaManager: 705 líneas (-20.7%)
- CompraManager bundle: 6.37 KB vs VentaManager: 7.69 KB (-17.2%)
- Lógica más simple: no descuentos, no validación de stock

---

### ✨ Funcionalidades Nuevas - Compras

#### 1. Validación Precio Compra vs Precio Venta

**Implementación:**

```javascript
async agregarProducto() {
    // ... validaciones básicas
    
    const precioCompra = parseFloat($('#precio_compra').val());
    const precioVenta = parseFloat($('#precio_venta').val());
    
    // Warning si precioVenta < precioCompra (posible pérdida)
    if (precioVenta < precioCompra) {
        const continuar = await showConfirm(
            '⚠️ Advertencia de Precio',
            'El precio de venta es menor al precio de compra. ¿Deseas continuar?',
            'warning'
        );
        
        if (!continuar) return;
    }
    
    // Agregar producto si todo OK
}
```

**Beneficios:**
- ✅ Previene errores de captura de precios
- ✅ Alerta al usuario de posibles pérdidas
- ✅ No bloquea (es warning, no error)

---

#### 2. Persistencia en localStorage

**Implementación:**

```javascript
guardarEnLocalStorage() {
    const data = {
        productos: this.productos,
        contador: this.contador,
        totales: {
            sumas: this.sumas,
            igv: this.igv,
            total: this.total
        },
        timestamp: new Date().toISOString()
    };
    
    localStorage.setItem('compra_borrador', JSON.stringify(data));
}
```

**Clave diferente:** `'compra_borrador'` vs `'venta_borrador'` para evitar conflictos.

---

#### 3. Auto-guardado y Recuperación

**Misma funcionalidad que VentaManager:**
- ✅ Auto-guardado cada 30 segundos
- ✅ Recuperación al cargar página
- ✅ Confirmación para recuperar o descartar

---

### 🔧 Configuración Vite

**Actualizado `vite.config.js`:**

```javascript
input: [
    'resources/css/app.css', 
    'resources/js/app.js',
    // Módulos de páginas específicas
    'resources/js/modules/VentaManager.js',
    'resources/js/modules/CompraManager.js',  // ⬅️ AGREGADO
],

// ...

manualChunks: {
    // ...
    'modules': [
        './resources/js/modules/VentaManager.js',
        './resources/js/modules/CompraManager.js',  // ⬅️ AGREGADO
    ],
}
```

**Build exitoso:**
```
public/build/assets/CompraManager.7576c162.js    6.37 KiB / gzip: 2.05 KiB
```

---

### 🧪 Testing Sugerido - Compras

#### Escenario 1: Agregar productos con precios válidos
1. Seleccionar producto
2. Ingresar cantidad (positivo, entero)
3. Ingresar precio_compra > 0
4. Ingresar precio_venta >= precio_compra
5. Click "Agregar"
6. ✅ Producto agregado a tabla
7. ✅ Totales calculados correctamente

#### Escenario 2: Warning cuando precioVenta < precioCompra
1. Seleccionar producto
2. Ingresar precio_compra = 100
3. Ingresar precio_venta = 80 (menor)
4. Click "Agregar"
5. ✅ Modal de confirmación aparece
6. Confirmar o cancelar
7. ✅ Comportamiento según elección

#### Escenario 3: Persistencia en localStorage
1. Agregar 2-3 productos
2. Cerrar pestaña/navegador
3. Abrir página de nuevo
4. ✅ Modal de recuperación aparece
5. Aceptar recuperar
6. ✅ Productos y totales restaurados

#### Escenario 4: Auto-guardado
1. Agregar productos
2. Esperar 30+ segundos
3. Abrir DevTools → Application → localStorage
4. ✅ Verificar clave `compra_borrador` actualizada
5. ✅ Timestamp actualizado

#### Escenario 5: Cancelar compra
1. Agregar productos
2. Click "Cancelar"
3. ✅ Confirmación aparece
4. Confirmar
5. ✅ Tabla vacía
6. ✅ Totales en 0
7. ✅ localStorage limpio

---

### 📦 Integración con Utilidades (Fase 1)

**Mismo patrón que VentaManager:**

```javascript
// notifications.js
import { showSuccess, showError, showConfirm } from '@utils/notifications';

// validators.js
import { validatePrecio, validateCantidad, isPositive, isInteger } from '@utils/validators';

// formatters.js
import { formatCurrency, round } from '@utils/formatters';
```

**Validadores específicos usados:**
- `validatePrecio()` - Para precio_compra y precio_venta
- `isPositive()` - Verificar valores > 0
- `isInteger()` - Verificar cantidad entera
- `round()` - Redondear a 2 decimales

---

## 📊 Resumen de Fase 2 - Estado Actual

### ✅ Vistas Completadas (2/4)

1. **venta/create.blade.php** → VentaManager.js
   - 705 líneas módulo
   - 7.69 KB bundle (2.40 KB gzipped)
   - 98.5% reducción inline JS
   
2. **compra/create.blade.php** → CompraManager.js
   - 559 líneas módulo
   - 6.37 KB bundle (2.05 KB gzipped)
   - 50.6% reducción total vista

### ⏳ Vistas Pendientes (2/4)

3. **control/lavados.blade.php** → LavadosManager.js
   - Filtros AJAX (sin page reload)
   - Lazy loading tabla
   - Real-time updates

4. **estacionamiento/index.blade.php** → EstacionamientoManager.js
   - AJAX disponibilidad
   - WebSockets opcional

### 📈 Métricas Acumuladas

| Métrica | Total |
|---------|-------|
| Managers creados | 2 |
| Líneas JS inline eliminadas | 567 líneas |
| Bundle size total modules | 14.06 KB |
| Gzipped total | 4.45 KB |
| Vistas refactorizadas | 2 |
| Nuevas funcionalidades | 6 |

---

## 🎯 Próximos Pasos

### Tareas Pendientes en esta Vista

1. **Testing manual exhaustivo** ✅ Prioridad Alta
   - Probar todos los escenarios listados arriba
   - Verificar en Chrome DevTools que no hay errores
   - Comparar comportamiento con versión anterior

2. **Eliminar dependencia de jQuery** ⏳ Prioridad Media
   - Migrar a vanilla JS o
   - Crear wrapper para cargar jQuery dinámicamente

3. **Tests automatizados** ⏳ Prioridad Media
   - Setup de Vitest para tests unitarios
   - Tests para `VentaState` (funciones puras)
   - Tests E2E con Playwright

---

### Siguientes Vistas a Migrar

#### 1. compra/create.blade.php
- Lógica similar a ventas
- Reutilizar `VentaManager` como base
- Crear `CompraManager.js` con misma estructura

#### 2. control/lavados.blade.php
- Convertir filtros de página reload a AJAX
- Lazy loading de tabla de resultados
- Estado en localStorage

#### 3. estacionamiento/index.blade.php
- AJAX para actualizar disponibilidad
- WebSockets para tiempo real (opcional)

---

## 📚 Documentación Actualizada

### Archivos Modificados

```
d:\Sebas GOREHCO\carwash_esp\
├── resources/
│   ├── js/
│   │   └── modules/
│   │       └── VentaManager.js         [NUEVO] 705 líneas
│   └── views/
│       └── venta/
│           └── create.blade.php        [MODIFICADO] -330 líneas
├── vite.config.js                      [MODIFICADO] +6 líneas
└── FASE_2_PROGRESO.md                  [NUEVO] Este archivo
```

### Documentación Relacionada

- `FASE_1_COMPLETADA.md` - Utilidades creadas
- `resources/js/utils/README.md` - Documentación de utilidades
- `EJEMPLO_MIGRACION.md` - Ejemplos de migración

---

## 💡 Lecciones Aprendidas

### ✅ Lo que Funcionó Bien

1. **Reutilización de Fase 1**: Las utilidades se integraron perfectamente
2. **Arquitectura de clases**: Separar `State` y `Manager` fue una buena decisión
3. **localStorage**: Fácil de implementar y gran impacto en UX
4. **Async/await**: Confirmaciones con Promises hacen el código más limpio

### ⚠️ Desafíos Encontrados

1. **jQuery dependency**: Difícil eliminar por Bootstrap Select
2. **Testing**: Sin framework de testing aún configurado
3. **Code splitting**: Generó chunk vacío "modules" (no crítico)

### 📖 Recomendaciones

1. **Migrar vista por vista**: No intentar migrar todo a la vez
2. **Probar exhaustivamente**: Comparar con versión anterior
3. **Documentar todo**: JSDoc ayuda mucho durante desarrollo
4. **Usar las utilidades**: No reinventar la rueda, reutilizar Fase 1

---

## 🎉 Conclusión Parcial

**Vistas migradas exitosamente:** `venta/create.blade.php` y `compra/create.blade.php` (2/4)

**Resultados acumulados:**
- ✅ 567 líneas de código inline eliminadas
- ✅ Arquitectura modular y testeable (2 managers)
- ✅ 6 funcionalidades nuevas (confirmaciones, persistencia, auto-guardado, validaciones)
- ✅ Integración completa con utilidades de Fase 1
- ✅ Build exitoso para ambos managers (14.06 KB total, 4.45 KB gzipped)
- ✅ Patrón State/Manager establecido para siguientes vistas

**Progreso Fase 2:** 50% completado (2 de 4 vistas)

**Próximo milestone:** Testing manual de compras y migrar `control/lavados.blade.php`

---

**Actualizado:** 21 de Octubre, 2025  
**Por:** Equipo de Desarrollo CarWash ESP  
**Estado:** ⏳ En progreso - 2 vistas completadas de 4
