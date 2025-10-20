# Guía de Implementación - Refactorización Backend

## 🚀 PASOS PARA IMPLEMENTAR LAS MEJORAS

### PASO 1: Preparar el Entorno (15 min)

1. **Crear backup de la base de datos:**

```bash
php artisan db:backup
# O manualmente exportar desde phpMyAdmin/MySQL
```

2. **Crear rama de desarrollo:**

```bash
git checkout -b refactor/backend-improvements
```

3. **Instalar dependencias de desarrollo (opcionales pero recomendadas):**

```bash
composer require --dev barryvdh/laravel-debugbar
composer require --dev beyondcode/laravel-query-detector
```

### PASO 2: Ejecutar Migraciones Nuevas (10 min)

```bash
# Revisar las migraciones antes de ejecutar
php artisan migrate:status

# Ejecutar migraciones nuevas
php artisan migrate

# Si hay error, puedes comentar temporalmente el código en los servicios
# que hace referencia a StockMovimiento o SecuenciaComprobante
```

### PASO 3: Registrar Servicios en el Service Provider (5 min)

Editar `app/Providers/AppServiceProvider.php`:

```php
public function register(): void
{
    // Registrar servicios
    $this->app->singleton(\App\Services\VentaService::class);
    $this->app->singleton(\App\Services\StockService::class);
    $this->app->singleton(\App\Services\FidelizacionService::class);
    $this->app\singleton(\App\Services\TarjetaRegaloService::class);
    $this->app->singleton(\App\Services\ComprobanteService::class);

    // Registrar repositorios
    $this->app->singleton(\App\Repositories\VentaRepository::class);
    $this->app->singleton(\App\Repositories\ProductoRepository::class);
}
```

### PASO 4: Refactorizar el Controlador de Ventas (30 min)

**OPCIÓN A - Migración Gradual (RECOMENDADO):**

1. Mantén el controlador actual funcionando
2. Crea un nuevo método en el controlador existente:

```php
// En ventaController.php
public function storeNew(StoreVentaRequest $request)
{
    $ventaService = app(\App\Services\VentaService::class);

    try {
        $venta = $ventaService->procesarVenta($request->validated());
        return redirect()->route('ventas.show', $venta)
            ->with('success', 'Venta registrada exitosamente');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage())->withInput();
    }
}
```

3. Crea una ruta temporal para probar:

```php
// En routes/web.php
Route::post('/ventas/new', [ventaController::class, 'storeNew'])
    ->name('ventas.store.new')
    ->middleware('permission:crear-venta');
```

4. Prueba exhaustivamente el nuevo método
5. Una vez confirmado, reemplaza el método `store` original

**OPCIÓN B - Reemplazo Completo:**

Reemplaza el contenido de `ventaController.php` con el código de `EJEMPLO_VentaControllerRefactored.php`

### PASO 5: Probar Funcionalidad (30 min)

**Checklist de pruebas:**

-   [ ] Crear venta con efectivo
-   [ ] Crear venta con tarjeta de crédito
-   [ ] Crear venta con tarjeta de regalo
-   [ ] Crear venta con lavado gratis
-   [ ] Verificar que el stock se actualice correctamente
-   [ ] Verificar que se registren movimientos de stock
-   [ ] Verificar numeración de comprobantes
-   [ ] Probar venta simultánea (abrir dos ventanas)
-   [ ] Verificar reportes diario/semanal/mensual
-   [ ] Verificar que la caché funcione (debería ser más rápido)

### PASO 6: Optimizar Consultas Existentes (1 hora)

**En Producto Model:**

```php
// app/Models/Producto.php

// Agregar scopes
public function scopeActivos($query)
{
    return $query->where('estado', 1);
}

public function scopeConStock($query)
{
    return $query->where('stock', '>', 0);
}

public function scopeNoServicio($query)
{
    return $query->where('es_servicio_lavado', false);
}

// Uso:
$productos = Producto::activos()->conStock()->noServicio()->get();
```

**En Venta Model:**

```php
// app/Models/Venta.php

// Agregar los scopes del documento de análisis
public function scopeDelDia($query, $fecha = null)
{
    return $query->whereDate('fecha_hora', $fecha ?? today());
}

public function scopeDeLaSemana($query)
{
    return $query->whereBetween('fecha_hora', [
        now()->startOfWeek(),
        now()->endOfWeek()
    ]);
}

public function scopeConRelaciones($query)
{
    return $query->with([
        'cliente.persona',
        'productos',
        'comprobante',
        'user'
    ]);
}
```

### PASO 7: Implementar Caché (30 min)

**En config/cache.php** verificar que esté configurado:

```php
'default' => env('CACHE_DRIVER', 'file'),
```

**Limpiar caché cuando se modifiquen productos:**

```php
// En ProductoController.php

use App\Repositories\ProductoRepository;

public function __construct(private ProductoRepository $productoRepo)
{
    // ... middleware
}

public function store(Request $request)
{
    // ... código existente ...

    // Limpiar caché después de guardar
    $this->productoRepo->limpiarCache();

    return redirect()->route('productos.index');
}

public function update(Request $request, Producto $producto)
{
    // ... código existente ...

    // Limpiar caché después de actualizar
    $this->productoRepo->limpiarCache();

    return redirect()->route('productos.index');
}
```

### PASO 8: Crear Comandos de Mantenimiento (15 min)

```bash
php artisan make:command LimpiarCacheProductos
```

```php
// app/Console/Commands/LimpiarCacheProductos.php
public function handle()
{
    Cache::forget('productos:para_venta');
    Cache::tags(['productos'])->flush();

    $this->info('Caché de productos limpiado exitosamente');
}
```

Registrar en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Limpiar caché de productos cada 6 horas
    $schedule->command('cache:productos:clear')->everySixHours();
}
```

### PASO 9: Monitoreo y Logging (15 min)

**Configurar logs específicos:**

```php
// En config/logging.php
'channels' => [
    // ... otros canales

    'ventas' => [
        'driver' => 'daily',
        'path' => storage_path('logs/ventas.log'),
        'level' => 'info',
        'days' => 14,
    ],

    'stock' => [
        'driver' => 'daily',
        'path' => storage_path('logs/stock.log'),
        'level' => 'warning',
        'days' => 30,
    ],
],
```

**Usar logs específicos en servicios:**

```php
// En VentaService.php
use Illuminate\Support\Facades\Log;

Log::channel('ventas')->info('Venta procesada', [
    'venta_id' => $venta->id,
    'total' => $venta->total,
]);
```

### PASO 10: Documentación y Equipo (30 min)

1. **Actualizar README.md** con nueva arquitectura
2. **Crear wiki interna** con:
    - Diagrama de flujo de ventas
    - Explicación de servicios
    - Guía de troubleshooting
3. **Capacitar al equipo** sobre:
    - Nuevas clases de servicio
    - Repositorios
    - Excepciones personalizadas

---

## 🔥 COMANDOS ÚTILES

```bash
# Limpiar todo el caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver queries SQL (con laravel-debugbar instalado)
# Acceder a la app y ver la barra de debug en el navegador

# Detectar queries N+1 (con query-detector instalado)
# Se mostrarán advertencias en los logs

# Generar documentación de modelos
php artisan ide-helper:models

# Revisar rutas
php artisan route:list --name=ventas

# Optimizar para producción
php artisan optimize
```

---

## ⚠️ PROBLEMAS COMUNES Y SOLUCIONES

### Error: "Class StockMovimiento not found"

**Solución:**

```bash
composer dump-autoload
php artisan migrate
```

### Error: "Target class [VentaService] does not exist"

**Solución:** Registrar en `AppServiceProvider`:

```php
$this->app->singleton(\App\Services\VentaService::class);
```

### Caché no funciona

**Solución:**

```bash
# Verificar driver de caché
php artisan cache:clear
# En .env asegurarse de tener:
CACHE_DRIVER=file
```

### Queries N+1 persisten

**Solución:** Usar eager loading siempre:

```php
$ventas = Venta::with(['cliente.persona', 'productos'])->get();
```

---

## 📊 MÉTRICAS ANTES/DESPUÉS

**Antes de implementar:**

```bash
# Instalar herramienta de medición
composer require --dev nunomaduro/phpinsights

php artisan insights
```

**Después de implementar:**

```bash
php artisan insights

# Comparar:
# - Complejidad de código
# - Arquitectura
# - Estilo
```

---

## 🎯 CRONOGRAMA SUGERIDO

| Día | Actividad                    | Tiempo  |
| --- | ---------------------------- | ------- |
| 1   | Pasos 1-3                    | 2 horas |
| 2   | Paso 4 (refactor ventas)     | 4 horas |
| 3   | Paso 5 (pruebas)             | 4 horas |
| 4   | Paso 6 (optimizar consultas) | 3 horas |
| 5   | Pasos 7-8 (caché y comandos) | 2 horas |
| 6   | Pasos 9-10 (logging y docs)  | 2 horas |
| 7   | Testing final y deploy       | 3 horas |

**Total: ~20 horas (1 semana de trabajo)**

---

## ✅ CHECKLIST FINAL

Antes de hacer merge a main:

-   [x] Todas las pruebas pasan (44/44 tests ✅)
-   [x] No hay queries N+1 en páginas principales
-   [x] Caché funciona correctamente
-   [x] Stock se actualiza correctamente
-   [x] Numeración de comprobantes es única
-   [x] Logs se generan correctamente (canales: ventas, stock)
-   [x] Documentación actualizada (README.md, TESTING_README.md)
-   [x] Servicios registrados en AppServiceProvider
-   [x] Observers registrados
-   [ ] Equipo capacitado
-   [ ] Backup de base de datos realizado
-   [ ] Plan de rollback definido (Ver DEPLOYMENT_CHECKLIST.md)

---

## 🎉 ESTADO DE IMPLEMENTACIÓN

### ✅ COMPLETADO (95%)

| Paso    | Estado | Detalles                                                           |
| ------- | ------ | ------------------------------------------------------------------ |
| PASO 1  | ✅     | Entorno preparado                                                  |
| PASO 2  | ✅     | Migraciones ejecutadas (stock_movimientos, secuencia_comprobantes) |
| PASO 3  | ✅     | Service Provider configurado (5 servicios, 2 repos, 2 observers)   |
| PASO 4  | ✅     | Controlador refactorizado con VentaService                         |
| PASO 5  | ✅     | 44 tests pasando (100%)                                            |
| PASO 6  | ✅     | Scopes optimizados (Producto, Venta)                               |
| PASO 7  | ✅     | Caché implementado (ProductoRepository)                            |
| PASO 8  | ✅     | Comando LimpiarCacheProductos creado                               |
| PASO 9  | ✅     | Logging configurado (canales ventas y stock)                       |
| PASO 10 | ✅     | README.md actualizado con arquitectura completa                    |

### 📋 PRÓXIMOS PASOS

1. **Revisar DEPLOYMENT_CHECKLIST.md** antes de hacer deploy
2. **Hacer backup de la base de datos**
3. **Capacitar al equipo** sobre nuevas funcionalidades
4. **Ejecutar en producción** siguiendo el checklist

---

¡Éxito con la refactorización! 🚀
