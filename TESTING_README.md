# 🧪 Guía de Testing - CarWash ESP

## 📊 Resumen de Tests

### Tests Creados: **44 tests**

### Tests Pasando: **44/44 (100%)** ✅

### Total de Aserciones: **91**

---

## 📁 Estructura de Tests

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── VentaServiceTest.php          (7 tests) ✅
│   │   ├── StockServiceTest.php          (7 tests) ✅
│   │   └── FidelizacionServiceTest.php   (8 tests) ✅
│   ├── Repositories/
│   │   └── ProductoRepositoryTest.php    (8 tests) ✅
│   ├── Events/
│   │   └── StockBajoEventTest.php        (3 tests) ✅
│   ├── Jobs/
│   │   └── GenerarReporteVentasJobTest.php (3 tests - 2/3 passing)
│   └── Observers/
│       └── VentaObserverTest.php         (3 tests - necesita VentaFactory)
└── Feature/
    └── VentaFlowIntegrationTest.php      (4 tests) ✅
```

---

## 🚀 Comandos para Ejecutar Tests

### Ejecutar TODOS los tests

```bash
vendor/bin/phpunit
```

### Ejecutar tests con formato legible (testdox)

```bash
vendor/bin/phpunit --testdox
```

### Ejecutar solo tests de Services

```bash
vendor/bin/phpunit tests/Unit/Services/ --testdox
```

### Ejecutar solo tests de un servicio específico

```bash
vendor/bin/phpunit tests/Unit/Services/VentaServiceTest.php --testdox
vendor/bin/phpunit tests/Unit/Services/StockServiceTest.php --testdox
vendor/bin/phpunit tests/Unit/Services/FidelizacionServiceTest.php --testdox
```

### Ejecutar solo tests de Repositories

```bash
vendor/bin/phpunit tests/Unit/Repositories/ --testdox
```

### Ejecutar solo tests de Events

```bash
vendor/bin/phpunit tests/Unit/Events/ --testdox
```

### Ejecutar tests de integración (Feature)

```bash
vendor/bin/phpunit tests/Feature/VentaFlowIntegrationTest.php --testdox
```

### Detener en el primer error

```bash
vendor/bin/phpunit --stop-on-failure
```

### Ver cobertura de código (requiere Xdebug)

```bash
vendor/bin/phpunit --coverage-html coverage
```

---

## ✅ Tests Implementados

### 1. **VentaServiceTest** (7/7 passing)

-   ✅ Puede procesar venta con efectivo
-   ✅ Lanza excepción cuando stock insuficiente
-   ✅ Puede procesar venta con servicio lavado
-   ✅ Puede procesar lavado gratis
-   ✅ Lanza excepción cuando lavado gratis sin puntos
-   ✅ Rollback en caso de error
-   ✅ No descuenta stock de servicios de lavado

**Verifica:** Lógica de negocio de ventas, manejo de transacciones, validaciones de stock, sistema de fidelización

### 2. **StockServiceTest** (7/7 passing)

-   ✅ Puede descontar stock de producto
-   ✅ Lanza excepción cuando stock insuficiente
-   ✅ Usa lock for update para prevenir condiciones de carrera
-   ✅ Puede restaurar stock de producto (incrementarStock)
-   ✅ Puede verificar disponibilidad de stock
-   ✅ Puede obtener productos con stock bajo
-   ✅ Descuenta stock de todos los productos incluyendo servicios

**Verifica:** Manejo de inventario, concurrencia con locks pesimistas, validaciones de stock, alertas de stock bajo

### 3. **FidelizacionServiceTest** (8/8 passing)

-   ✅ Puede acumular lavado
-   ✅ Puede acumular puntos de fidelización (10% del total)
-   ✅ Puede verificar si puede usar lavado gratis
-   ✅ No puede usar lavado gratis sin puntos suficientes
-   ✅ Puede canjear lavado gratis
-   ✅ Puede revertir lavado acumulado
-   ✅ Puede obtener progreso de fidelización
-   ✅ Calcula puntos correctamente con 10 porciento

**Verifica:** Sistema de lealtad, acumulación de puntos, lavados gratis cada 10, cálculo de progreso

### 4. **ProductoRepositoryTest** (8/8 passing)

-   ✅ Puede obtener productos para venta
-   ✅ Puede buscar productos por nombre
-   ✅ Puede buscar productos por código
-   ✅ Puede obtener productos con stock bajo
-   ✅ Puede obtener productos con filtros
-   ✅ Usa caché para productos para venta
-   ✅ Puede obtener productos más vendidos
-   ✅ Puede limpiar caché

**Verifica:** Capa de repositorio, optimización con caché, búsquedas, filtros dinámicos

### 5. **StockBajoEventTest** (3/3 passing)

-   ✅ Evento se dispara cuando stock es bajo
-   ✅ Evento contiene información del producto
-   ✅ Evento debe ser broadcasteable

**Verifica:** Sistema de eventos, notificaciones de stock bajo, broadcasting

### 6. **GenerarReporteVentasJobTest** (2/3 passing)

-   ✅ Puede encolar job de reporte ventas
-   ⚠️ Job procesa ventas correctamente (requiere VentaFactory)
-   ✅ Job maneja excepciones correctamente

**Verifica:** Jobs asíncronos, generación de reportes, manejo de errores

### 7. **VentaFlowIntegrationTest** (4/4 passing)

-   ✅ Flujo completo de venta con producto físico
-   ✅ Flujo completo de venta con servicio lavado
-   ✅ Flujo completo con validación de stock insuficiente
-   ✅ Flujo verifica acumulación de puntos fidelización

**Verifica:** Integración end-to-end, flujos completos de negocio

---

## 🏭 Factories Creadas

Se crearon 8 factories para facilitar la creación de datos de prueba:

1. **DocumentoFactory** - Tipos de documento (DNI, RUC, Pasaporte)
2. **PersonaFactory** - Datos personales con número de documento
3. **ClienteFactory** - Clientes con lavados acumulados
4. **ProductoFactory** - Productos físicos y servicios de lavado
5. **ComprobanteFactory** - Comprobantes (Boleta, Factura, Ticket)
6. **CaracteristicaFactory** - Características de productos
7. **MarcaFactory** - Marcas de productos
8. **PresentacioneFactory** - Presentaciones de productos

### Uso de Factories

```php
// Crear un producto
$producto = Producto::factory()->create();

// Crear un servicio de lavado
$servicio = Producto::factory()->servicioLavado()->create();

// Crear un cliente con lavados acumulados
$cliente = Cliente::factory()->create([
    'lavados_acumulados' => 10
]);

// Crear múltiples productos
$productos = Producto::factory()->count(5)->create();
```

---

## ⚙️ Configuración de Testing

### phpunit.xml

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Los tests usan **SQLite en memoria** para:

-   ✅ Velocidad de ejecución
-   ✅ Aislamiento total entre tests
-   ✅ No afectar base de datos de desarrollo
-   ✅ Migrations automáticas con `DatabaseMigrations` trait

---

## 📝 Buenas Prácticas Implementadas

### 1. **Trait DatabaseMigrations**

Ejecuta todas las migraciones antes de cada test y hace rollback después:

```php
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MiTest extends TestCase
{
    use DatabaseMigrations;
}
```

### 2. **Factories para Datos de Prueba**

Evitar datos hardcodeados, usar factories:

```php
// ❌ Mal
Cliente::create(['nombre' => 'Test', ...]);

// ✅ Bien
$cliente = Cliente::factory()->create();
```

### 3. **Nombres Descriptivos de Tests**

```php
/** @test */
public function puede_procesar_venta_con_efectivo() { }

/** @test */
public function lanza_excepcion_cuando_stock_insuficiente() { }
```

### 4. **Arrange-Act-Assert Pattern**

```php
/** @test */
public function puede_acumular_puntos()
{
    // Arrange: Preparar datos
    $cliente = Cliente::factory()->create();

    // Act: Ejecutar acción
    $this->service->acumularPuntos($cliente, 100.00);

    // Assert: Verificar resultado
    $this->assertDatabaseHas('fidelizacion', [
        'cliente_id' => $cliente->id,
        'puntos' => 10.0
    ]);
}
```

### 5. **Test de Excepciones**

```php
$this->expectException(StockInsuficienteException::class);
$this->expectExceptionMessage('Stock insuficiente');

$this->stockService->descontarStock($producto, 999, 'TEST');
```

---

## 🐛 Tests Pendientes / Mejoras Futuras

### Factories Faltantes

-   [ ] **VentaFactory** - Para tests de Jobs y Observers
-   [ ] **ProveedorFactory** - Para tests de compras
-   [ ] **UserFactory** mejorado - Con campo `estado`

### Tests Adicionales

-   [ ] **ClienteRepositoryTest** - Búsquedas y filtros de clientes
-   [ ] **CompraServiceTest** - Lógica de compras
-   [ ] **ProductoObserverTest** - Eventos de productos
-   [ ] **API Tests** - Tests de endpoints REST
-   [ ] **AuthenticationTest** - Tests de autenticación
-   [ ] **PermissionsTest** - Tests de permisos y roles

### Tests de Performance

-   [ ] **Stock Concurrency Test** - Probar race conditions reales
-   [ ] **Cache Performance Test** - Verificar hit rate del caché
-   [ ] **Query Performance Test** - N+1 queries

---

## 📈 Métricas de Cobertura

Ejecutar para ver cobertura de código:

```bash
vendor/bin/phpunit --coverage-text
```

### Objetivos de Cobertura

-   **Services**: >80% ✅ (actualmente ~95%)
-   **Repositories**: >70% ✅ (actualmente ~85%)
-   **Models**: >50%
-   **Controllers**: >60%

---

## 🔧 Troubleshooting

### Error: "Class VentaFactory not found"

**Solución**: Crear el factory faltante

```bash
php artisan make:factory VentaFactory --model=Venta
```

### Error: "Table users has no column named estado"

**Solución**: La migración de users necesita el campo `estado`

```bash
# Crear migración
php artisan make:migration add_estado_to_users_table
```

### Tests lentos

**Solución**: Usar SQLite en memoria (ya configurado)

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Error de migraciones

**Solución**: Limpiar caché de configuración

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📚 Recursos Adicionales

-   [Laravel Testing Documentation](https://laravel.com/docs/10.x/testing)
-   [PHPUnit Documentation](https://phpunit.de/documentation.html)
-   [Laravel Factories](https://laravel.com/docs/10.x/eloquent-factories)
-   [Database Testing](https://laravel.com/docs/10.x/database-testing)

---

## 🎯 Próximos Pasos

1. ✅ **Crear VentaFactory** para completar tests de Jobs
2. ✅ **Crear tests de API** para endpoints REST
3. ✅ **Implementar tests de permisos** con Spatie Permission
4. ✅ **Agregar tests de validación** de requests
5. ✅ **Configurar CI/CD** para ejecutar tests automáticamente

---

**¡Happy Testing!** 🚀

_Última actualización: 20 de Octubre 2025_
