# Análisis de Migraciones - Plan de Consolidación

## 📊 Estado Actual

Se identificaron **13 migraciones modificadoras** que agregan o modifican columnas en tablas existentes:

### 1. **Tabla: `documentos`**

-   ❌ **Migración modificadora:** `2023_05_02_214216_update_colums_to_documentos_table.php`
    -   Elimina: `numero_documento`
-   ✅ **Acción:** Consolidar en migración original

### 2. **Tabla: `personas`**

-   ❌ **Migración modificadora:** `2023_05_02_214713_update_colums_to_personas_table.php`
    -   Agrega: `numero_documento` (string 20)
    -   Modifica: `documento_id` (cambia constraint)
-   ✅ **Acción:** Consolidar en migración original

### 3. **Tabla: `productos`**

-   ❌ **Migraciones modificadoras:**
    1. `2025_04_29_021016_add_es_servicio_lavado_to_productos_table.php`
        - Agrega: `es_servicio_lavado` (boolean, default false)
    2. `2025_04_29_035844_add_precio_venta_to_productos_table.php`
        - Agrega: `precio_venta` (decimal 10,2, nullable)
    3. `2025_10_20_000003_add_stock_minimo_to_productos_table.php`
        - Agrega: `stock_minimo` (integer, default 10)
-   ✅ **Acción:** Consolidar 3 campos en migración original

### 4. **Tabla: `clientes`**

-   ❌ **Migración modificadora:** `2025_06_26_000001_add_lavados_acumulados_to_clientes_table.php`
    -   Agrega: `lavados_acumulados` (unsigned integer, default 0)
-   ✅ **Acción:** Consolidar en migración original

### 5. **Tabla: `ventas`**

-   ❌ **Migración modificadora:** `2025_06_26_000002_add_tarjeta_regalo_and_lavado_gratis_to_ventas_table.php`
    -   Agrega: `tarjeta_regalo_id` (foreign key nullable)
    -   Agrega: `lavado_gratis` (boolean, default false)
-   ✅ **Acción:** Consolidar en migración original

### 6. **Tabla: `control_lavados`**

-   ❌ **Migraciones modificadoras:**
    1. `2025_06_25_000004_add_relations_to_lavados_table.php`
        - Agrega: `lavador_id` (foreign key nullable)
        - Agrega: `tipo_vehiculo_id` (foreign key nullable)
    2. `2025_10_20_201159_add_deleted_at_to_control_lavados_table.php`
        - Agrega: `deleted_at` (soft deletes)
-   ✅ **Acción:** Consolidar 3 campos en migración original

### 7. **Tabla: `users`**

-   ❌ **Migración modificadora:** `2025_10_20_174544_add_estado_to_users_table.php`
    -   Agrega: `estado` (tinyInteger, default 1)
-   ✅ **Acción:** Consolidar en migración original

---

## 🎯 Plan de Consolidación

### Fase 1: Backup y Preparación

1. ✅ Crear backup de la base de datos actual
2. ✅ Ejecutar todos los tests antes de modificar
3. ✅ Documentar estado actual (130/130 tests ✅)

### Fase 2: Consolidación de Migraciones

#### A. **Migración: `create_documentos_table.php`**

```php
Schema::create('documentos', function (Blueprint $table) {
    $table->id();
    $table->string('tipo_documento', 30);
    // Campo numero_documento ELIMINADO según migración modificadora
    $table->timestamps();
});
```

#### B. **Migración: `create_personas_table.php`**

```php
Schema::create('personas', function (Blueprint $table) {
    $table->id();
    $table->string('razon_social', 80);
    $table->string('direccion', 80);
    $table->string('tipo_persona', 20);
    $table->string('telefono', 20);
    $table->tinyInteger('estado')->default(1);
    $table->foreignId('documento_id')->constrained('documentos')->onDelete('cascade');
    $table->string('numero_documento', 20)->nullable(); // ✅ CONSOLIDADO
    $table->timestamps();
});
```

#### C. **Migración: `create_productos_table.php`**

```php
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('codigo', 50);
    $table->string('nombre', 80);
    $table->integer('stock')->unsigned()->default(0);
    $table->integer('stock_minimo')->default(10); // ✅ CONSOLIDADO
    $table->decimal('precio_venta', 10, 2)->nullable(); // ✅ CONSOLIDADO
    $table->string('descripcion', 255)->nullable();
    $table->date('fecha_vencimiento')->nullable();
    $table->string('img_path', 255)->nullable();
    $table->tinyInteger('estado')->default(1);
    $table->boolean('es_servicio_lavado')->default(false); // ✅ CONSOLIDADO
    $table->foreignId('marca_id')->constrained('marcas')->onDelete('cascade');
    $table->foreignId('presentacione_id')->constrained('presentaciones')->onDelete('cascade');
    $table->timestamps();
});
```

#### D. **Migración: `create_clientes_table.php`**

```php
Schema::create('clientes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('persona_id')->unique()->constrained('personas')->onDelete('cascade');
    $table->unsignedInteger('lavados_acumulados')->default(0); // ✅ CONSOLIDADO
    $table->timestamps();
});
```

#### E. **Migración: `create_ventas_table.php`**

```php
Schema::create('ventas', function (Blueprint $table) {
    $table->id();
    $table->dateTime('fecha_hora');
    $table->decimal('impuesto', 8, 2, true);
    $table->string('numero_comprobante', 255);
    $table->decimal('total', 8, 2, true);
    $table->tinyInteger('estado')->default(1);
    $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('comprobante_id')->nullable()->constrained('comprobantes')->onDelete('set null');
    $table->text('comentarios')->nullable();
    $table->string('medio_pago')->default('efectivo');
    $table->decimal('efectivo', 8, 2)->nullable();
    $table->decimal('tarjeta_credito', 8, 2)->nullable();
    $table->boolean('servicio_lavado')->default(false);
    $table->dateTime('horario_lavado')->nullable();
    $table->unsignedBigInteger('tarjeta_regalo_id')->nullable(); // ✅ CONSOLIDADO
    $table->boolean('lavado_gratis')->default(false); // ✅ CONSOLIDADO
    $table->foreign('tarjeta_regalo_id')->references('id')->on('tarjetas_regalo')->onDelete('set null');
    $table->timestamps();
});
```

#### F. **Migración: `create_control_lavados_table.php`**

```php
Schema::create('control_lavados', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('venta_id');
    $table->unsignedBigInteger('cliente_id');
    $table->foreignId('lavador_id')->nullable()->constrained('lavadores'); // ✅ CONSOLIDADO
    $table->foreignId('tipo_vehiculo_id')->nullable()->constrained('tipos_vehiculo'); // ✅ CONSOLIDADO
    $table->string('lavador_nombre', 100)->nullable();
    $table->timestamp('hora_llegada');
    $table->dateTime('horario_estimado');
    $table->dateTime('inicio_lavado')->nullable();
    $table->dateTime('fin_lavado')->nullable();
    $table->dateTime('inicio_interior')->nullable();
    $table->dateTime('fin_interior')->nullable();
    $table->dateTime('hora_final')->nullable();
    $table->integer('tiempo_total')->nullable();
    $table->string('estado', 20)->default('En espera');
    $table->timestamps();
    $table->softDeletes(); // ✅ CONSOLIDADO

    $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
    $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
});
```

#### G. **Migración: `create_users_table.php`**

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->tinyInteger('estado')->default(1); // ✅ CONSOLIDADO
    $table->timestamps();
});
```

### Fase 3: Eliminar Migraciones Obsoletas

Eliminar las siguientes 10 migraciones modificadoras:

1. ❌ `2023_05_02_214216_update_colums_to_documentos_table.php`
2. ❌ `2023_05_02_214713_update_colums_to_personas_table.php`
3. ❌ `2025_04_29_021016_add_es_servicio_lavado_to_productos_table.php`
4. ❌ `2025_04_29_035844_add_precio_venta_to_productos_table.php`
5. ❌ `2025_10_20_000003_add_stock_minimo_to_productos_table.php`
6. ❌ `2025_06_26_000001_add_lavados_acumulados_to_clientes_table.php`
7. ❌ `2025_06_26_000002_add_tarjeta_regalo_and_lavado_gratis_to_ventas_table.php`
8. ❌ `2025_06_25_000004_add_relations_to_lavados_table.php`
9. ❌ `2025_10_20_174544_add_estado_to_users_table.php`
10. ❌ `2025_10_20_201159_add_deleted_at_to_control_lavados_table.php`

### Fase 4: Validación Final

1. ✅ Ejecutar `php artisan migrate:fresh --seed`
2. ✅ Ejecutar `php artisan test` (debe mantener 130/130 ✅)
3. ✅ Verificar estructura de base de datos

---

## 📋 Resumen de Cambios

| Tabla             | Campos Consolidados | Migraciones Eliminadas |
| ----------------- | ------------------- | ---------------------- |
| `documentos`      | 0 (eliminación)     | 1                      |
| `personas`        | 1                   | 1                      |
| `productos`       | 3                   | 3                      |
| `clientes`        | 1                   | 1                      |
| `ventas`          | 2                   | 1                      |
| `control_lavados` | 3                   | 2                      |
| `users`           | 1                   | 1                      |
| **TOTAL**         | **11 campos**       | **10 migraciones**     |

---

## ⚠️ Consideraciones

1. **Backup obligatorio** antes de ejecutar
2. **Entorno de desarrollo** primero
3. **Tests completos** después de cada cambio
4. **Seeders actualizados** si es necesario
5. **Factories actualizados** ya están correctos ✅

---

## ✅ Beneficios

1. 📁 **Menos archivos:** De 45 migraciones a 35 (-22%)
2. 🚀 **Más rápido:** Menos migraciones = deploy más rápido
3. 🧹 **Más limpio:** Estructura clara y organizada
4. 🐛 **Menos errores:** Sin dependencias entre migraciones
5. 📖 **Mejor mantenimiento:** Fácil entender estructura DB

---

**Fecha de análisis:** 20 de octubre de 2025  
**Tests actuales:** 130/130 pasando ✅  
**Estado:** Listo para consolidación 🚀
