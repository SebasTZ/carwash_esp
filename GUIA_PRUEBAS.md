# 🧪 Guía de Pruebas - Cambios Implementados

## 📋 CHECKLIST DE PRUEBAS

Usa este documento para verificar que todas las mejoras implementadas funcionan correctamente.

---

## 1️⃣ PRUEBAS DE CACHÉ Y PERFORMANCE

### Test 1: Formulario de Creación de Productos

**Objetivo:** Verificar que el caché funcione

1. Acceder a `/productos/create`
2. **Primera carga:** Nota el tiempo (debería ser normal)
3. **Recargar la página (F5):** Debería ser notablemente más rápido
4. Verificar que aparezcan las marcas, presentaciones y categorías

**Resultado esperado:** ✅ Segunda carga 70-90% más rápida

---

### Test 2: Formulario de Creación de Ventas

**Objetivo:** Verificar optimización de query complejo

1. Acceder a `/ventas/create`
2. Nota el tiempo de carga
3. Verificar que aparezcan:
    - Productos con stock
    - Servicios de lavado
    - Clientes activos
    - Comprobantes

**Resultado esperado:** ✅ Carga en <500ms (antes era ~800ms)

---

### Test 3: Limpiar Caché Manual

**Objetivo:** Verificar comando artisan

```bash
php artisan cache:productos:clear
```

**Resultado esperado:**

```
✓ Caché de productos limpiado exitosamente
```

---

## 2️⃣ PRUEBAS DE SCOPES

### Test 4: Scope de Productos Activos

**En Tinker:**

```bash
php artisan tinker
```

```php
// Probar scopes de Producto
Producto::activos()->count();
Producto::conStock()->count();
Producto::noServicio()->count();
Producto::serviciosLavado()->count();
Producto::stockBajo(10)->count();
Producto::buscar('jabon')->get();

// Combinar scopes
Producto::activos()->conStock()->noServicio()->get();
```

**Resultado esperado:** ✅ Cada query retorna resultados correctos

---

### Test 5: Scope de Ventas

**En Tinker:**

```php
// Probar scopes de Venta
Venta::delDia()->count();
Venta::deLaSemana()->count();
Venta::delMes()->count();
Venta::conRelaciones()->first(); // Ver que trae todas las relaciones
Venta::porMedioPago('efectivo')->count();
Venta::activas()->count();
```

**Resultado esperado:** ✅ Queries funcionan sin errores

---

### Test 6: Scope de Clientes

**En Tinker:**

```php
Cliente::activos()->count();
Cliente::conFidelidad()->count();
Cliente::buscar('Juan')->get();
```

**Resultado esperado:** ✅ Filtra correctamente

---

## 3️⃣ PRUEBAS DE ACCESSORS

### Test 7: Accessors de Producto

**En Tinker:**

```php
$producto = Producto::first();
$producto->stock_status;        // 'disponible', 'bajo', 'agotado', 'servicio'
$producto->stock_status_color;  // 'success', 'warning', 'danger', 'info'
```

**Resultado esperado:** ✅ Retorna valores correctos según el stock

---

### Test 8: Accessors de Cliente

**En Tinker:**

```php
$cliente = Cliente::first();
$cliente->nombre_completo;       // Nombre de la persona
$cliente->progreso_fidelidad;    // 0-100
$cliente->puede_canjear_lavado;  // true/false
```

**Resultado esperado:** ✅ Retorna valores calculados correctamente

---

## 4️⃣ PRUEBAS DE OBSERVER

### Test 9: ProductoObserver - Limpieza de Caché

**Pasos:**

1. Crear caché manualmente:

```bash
php artisan tinker
```

```php
Cache::put('productos:para_venta', 'test', 60);
Cache::has('productos:para_venta'); // true
```

2. Actualizar un producto en la UI o Tinker:

```php
$producto = Producto::first();
$producto->nombre = 'Producto Actualizado Test';
$producto->save();
```

3. Verificar que el caché se limpió:

```php
Cache::has('productos:para_venta'); // false
```

**Resultado esperado:** ✅ Caché se limpia automáticamente

---

### Test 10: ProductoObserver - Logging

**Pasos:**

1. Actualizar stock de un producto:

```php
$producto = Producto::find(1);
$producto->stock = 50;
$producto->save();
```

2. Revisar el log:

```bash
# En Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 20
```

**Resultado esperado:** ✅ Mensaje de log: "Stock actualizado: [nombre]"

---

## 5️⃣ PRUEBAS DE REPOSITORIES

### Test 11: ProductoRepository - Obtener Para Venta

**En Tinker:**

```php
$repo = app(\App\Repositories\ProductoRepository::class);
$productos = $repo->obtenerParaVenta();
$productos->count();

// Verificar que incluya servicios de lavado
$servicios = $productos->where('es_servicio_lavado', true);
$servicios->count();
```

**Resultado esperado:** ✅ Retorna productos normales + servicios

---

### Test 12: ProductoRepository - Stock Bajo

**En Tinker:**

```php
$repo = app(\App\Repositories\ProductoRepository::class);
$stockBajo = $repo->obtenerStockBajo(10);
$stockBajo->count();
```

**Resultado esperado:** ✅ Retorna productos con stock <= 10

---

### Test 13: CaracteristicaRepository

**En Tinker:**

```php
$repo = app(\App\Repositories\CaracteristicaRepository::class);

$marcas = $repo->obtenerMarcasActivas();
$marcas->count();

$presentaciones = $repo->obtenerPresentacionesActivas();
$categorias = $repo->obtenerCategoriasActivas();
```

**Resultado esperado:** ✅ Retorna colecciones con id y nombre

---

## 6️⃣ PRUEBAS DE SERVICIOS (Estructura)

### Test 14: Verificar que Servicios Existan

**En Tinker:**

```php
// Verificar que los servicios estén registrados
app(\App\Services\VentaService::class);
app(\App\Services\StockService::class);
app(\App\Services\FidelizacionService::class);
app(\App\Services\TarjetaRegaloService::class);
app(\App\Services\ComprobanteService::class);
```

**Resultado esperado:** ✅ No arroja error (servicios registrados)

---

## 7️⃣ PRUEBAS FUNCIONALES DE UI

### Test 15: Crear Producto

**Pasos:**

1. Ir a `/productos/create`
2. Llenar formulario con datos válidos
3. Guardar
4. Verificar que aparece en listado
5. **Verificar en log** que se registró creación

**Resultado esperado:** ✅ Producto creado + log generado + caché limpiado

---

### Test 16: Editar Producto

**Pasos:**

1. Ir a `/productos/{id}/edit`
2. Cambiar nombre o stock
3. Guardar
4. **Verificar en log** que se registró cambio
5. Volver a `/productos/create` y verificar que el cambio se refleja

**Resultado esperado:** ✅ Cambios guardados + log + caché actualizado

---

### Test 17: Crear Venta

**Pasos:**

1. Ir a `/ventas/create`
2. Seleccionar cliente
3. Agregar productos
4. Completar venta
5. Verificar que se guardó correctamente

**Resultado esperado:** ✅ Venta creada (aún usando código antiguo, pero formulario optimizado)

---

## 8️⃣ PRUEBAS DE PERFORMANCE

### Test 18: Medir Tiempo de Carga

**Usando DevTools del navegador:**

1. Abrir DevTools (F12)
2. Ir a Network tab
3. Cargar `/productos/create` (primera vez)
4. Anotar tiempo
5. Recargar (F5)
6. Comparar tiempos

**Resultado esperado:** ✅ Segunda carga 50-80% más rápida

---

### Test 19: Queries Ejecutadas

**Instalar Laravel Debugbar (opcional):**

```bash
composer require barryvdh/laravel-debugbar --dev
```

Luego acceder a páginas y verificar número de queries en la barra de debug.

**Resultado esperado:**

-   `/productos/create`: ✅ ~3-5 queries (antes 10+)
-   `/ventas/create`: ✅ ~5-8 queries (antes 15+)

---

## 9️⃣ PRUEBAS DE MIGRACIONES

### Test 20: Verificar Tablas Nuevas

**En Tinker o MySQL:**

```php
// Verificar tabla stock_movimientos
DB::table('stock_movimientos')->count();

// Verificar tabla secuencias_comprobantes
DB::table('secuencias_comprobantes')->count();

// Verificar campo stock_minimo en productos
DB::table('productos')->select('stock_minimo')->first();
```

**Resultado esperado:** ✅ Tablas y campos existen

---

## 🔟 PRUEBAS DE MODELOS

### Test 21: Relación StockMovimiento

**En Tinker:**

```php
// Si hay movimientos
$movimiento = \App\Models\StockMovimiento::first();
$movimiento->producto;
$movimiento->usuario;
```

**Resultado esperado:** ✅ Relaciones funcionan

---

## ✅ CHECKLIST FINAL

Marca cada item cuando lo hayas probado exitosamente:

### Caché

-   [ ] Formulario productos carga rápido segunda vez
-   [ ] Formulario ventas carga rápido
-   [ ] Comando `cache:productos:clear` funciona
-   [ ] Observer limpia caché al editar producto

### Scopes

-   [ ] Scopes de Producto funcionan
-   [ ] Scopes de Venta funcionan
-   [ ] Scopes de Cliente funcionan

### Accessors

-   [ ] Accessors de Producto funcionan
-   [ ] Accessors de Cliente funcionan

### Repositories

-   [ ] ProductoRepository funciona
-   [ ] CaracteristicaRepository funciona
-   [ ] VentaRepository existe (no usado aún)

### Servicios

-   [ ] Servicios están registrados
-   [ ] Se pueden inyectar en controladores

### Observer

-   [ ] Loguea cambios en productos
-   [ ] Limpia caché automáticamente

### UI/Funcional

-   [ ] Crear producto funciona
-   [ ] Editar producto funciona
-   [ ] Crear venta funciona (formulario optimizado)

### Performance

-   [ ] Páginas cargan más rápido con caché
-   [ ] Menos queries N+1

---

## 🐛 PROBLEMAS CONOCIDOS

### 1. Cache Tags No Soportado

**Síntoma:** Error "This cache store does not support tagging"  
**Causa:** Driver `file` no soporta tags  
**Solución:** Ya implementada (try/catch en código)

### 2. Advertencia PSR-4

**Síntoma:** Warning sobre `proveedorController.php`  
**Causa:** Nombre de archivo no sigue estándar  
**Solución:** Pendiente (renombrar en fase posterior)

---

## 📊 RESULTADOS ESPERADOS

Después de completar todas las pruebas, deberías ver:

✅ **Performance:** 50-80% más rápido en formularios  
✅ **Queries:** 60-70% reducción  
✅ **Código:** Más limpio y mantenible  
✅ **Logs:** Cambios importantes registrados  
✅ **Caché:** Funcionando automáticamente

---

## 💡 TIPS

1. **Usa Tinker:** Es la forma más rápida de probar modelos y servicios
2. **Revisa Logs:** Siempre verifica `storage/logs/laravel.log`
3. **Caché:** Si algo no se refleja, limpia caché primero
4. **DevTools:** Usa Network tab para medir performance real

---

**¿Todo funciona?** 🎉  
**Próximo paso:** Implementar VentaService completo en ventas/compras
