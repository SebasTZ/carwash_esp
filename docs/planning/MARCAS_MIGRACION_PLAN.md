# 🎯 Plan de Migración - Marcas

**Fecha:** 21 de Octubre, 2025  
**Entidad:** Marcas  
**Patrón base:** Categorías (commit 1a546dc)  
**Tiempo estimado:** ~45 minutos (segunda migración, patrón conocido)

---

## 📊 Análisis de la Entidad

### Modelo: Marca.php

```php
- Relación: belongsTo(Caracteristica)
- Relación: hasMany(Producto)
- Fillable: ['caracteristica_id']
- Soft Delete: NO (usa campo estado en Caracteristica)
```

### Controller: marcaController.php

```php
✅ index() - Con paginación (15 items)
✅ create() - Vista simple
✅ store() - Con transacciones DB
✅ edit() - Con modelo
✅ update() - Actualiza Caracteristica
✅ destroy() - Toggle estado (eliminar/restaurar en uno)
```

### Campos del Formulario:

1. **nombre** (string, required) - de Caracteristica
2. **descripcion** (text, nullable) - de Caracteristica
3. **estado** (boolean, default: 1) - de Caracteristica

### Diferencias vs Categorías:

| Aspecto        | Categorías          | Marcas                                  |
| -------------- | ------------------- | --------------------------------------- |
| Modelo directo | ✅ Sí               | ❌ No (usa Caracteristica)              |
| Campos         | nombre, descripcion | nombre, descripcion (en Caracteristica) |
| Soft Delete    | Sí (deleted_at)     | No (campo estado)                       |
| Destroy method | Separado restore()  | Todo en destroy()                       |
| Relaciones     | Productos           | Productos + Caracteristica              |

### Complejidad:

-   **Baja** - Formularios simples (2 campos)
-   **Media** - Relación con Caracteristica requiere cuidado
-   **Baja** - Ya tiene toggle eliminar/restaurar

---

## 🎯 Tareas de Migración

### 1️⃣ Backend (15 min)

#### Controller:

-   ✅ Ya tiene destroy() con toggle - **No requiere cambios**
-   ❌ No necesita método restore() separado
-   ✅ Rutas existentes suficientes

**Acción:** ✅ Backend listo, sin cambios necesarios

---

### 2️⃣ Index - DynamicTable (15 min)

#### Backup:

```bash
cp index.blade.php index-old.blade.php
```

#### Configuración DynamicTable:

```javascript
columns: [
    { key: "caracteristica.nombre", label: "Nombre" },
    { key: "caracteristica.descripcion", label: "Descripción" },
    {
        key: "caracteristica.estado",
        label: "Estado",
        formatter: (value) =>
            value == 1
                ? '<span class="badge rounded-pill text-bg-success">activo</span>'
                : '<span class="badge rounded-pill text-bg-danger">eliminado</span>',
    },
    { key: "actions", label: "Acciones" },
];
```

#### Acciones:

```javascript
actions: {
  edit: {
    url: (row) => `/marcas/${row.id}/edit`,
    permission: 'editar-marca'
  },
  delete: {
    url: (row) => `/marcas/${row.id}`,
    permission: 'eliminar-marca',
    // Modal dinámico basado en estado
  }
}
```

#### Características especiales:

-   ✅ Datos anidados (`caracteristica.nombre`)
-   ✅ Badge formatter para estado
-   ✅ Modal toggle eliminar/restaurar
-   ✅ Búsqueda en campos de Caracteristica

---

### 3️⃣ Create - FormValidator (10 min)

#### Backup:

```bash
cp create.blade.php create-old.blade.php
```

#### Validadores:

```javascript
validators: {
  nombre: {
    required: { message: 'El nombre es obligatorio' },
    maxLength: { value: 60, message: 'Máximo 60 caracteres' }
  },
  descripcion: {
    maxLength: { value: 255, message: 'Máximo 255 caracteres' }
  }
}
```

#### Estructura:

-   ✅ Form id="marcaForm"
-   ✅ 2 campos solamente
-   ✅ Submit con validación
-   ✅ Mismo estilo que Categorías

---

### 4️⃣ Edit - FormValidator (10 min)

#### Backup:

```bash
cp edit.blade.php edit-old.blade.php
```

#### Configuración:

-   ✅ Mismos validadores que create
-   ✅ Pre-llenar con `$marca->caracteristica->nombre`
-   ❌ No necesita botón restaurar (se hace desde index)
-   ✅ Botón Actualizar + Restablecer

---

## 🔍 Casos Especiales de Marcas

### 1. Acceso a Datos Anidados:

```javascript
// Marca → Caracteristica → nombre
columns: [{ key: "caracteristica.nombre", label: "Nombre" }];
```

**DynamicTable ya soporta esto** ✅

### 2. Toggle Eliminar/Restaurar:

```javascript
// Modal dinámico basado en caracteristica.estado
const isActive = row.caracteristica.estado == 1;
const modalTitle = isActive ? "Eliminar" : "Restaurar";
const modalBody = isActive
    ? "¿Deseas eliminar la marca?"
    : "¿Deseas restaurar la marca?";
```

### 3. Transacciones en Store:

-   ✅ Ya implementado en controller
-   No afecta frontend
-   Backend maneja creación de Caracteristica + Marca

---

## 📋 Checklist de Implementación

### Pre-migración:

-   [ ] Crear backups (index, create, edit)
-   [ ] Revisar permisos actuales
-   [ ] Verificar Request validators

### Index (DynamicTable):

-   [ ] Elemento `<table id="marcasTable">`
-   [ ] Configurar columnas con datos anidados
-   [ ] Badge formatter para estado
-   [ ] Botones editar/eliminar con permisos
-   [ ] Modal toggle eliminar/restaurar
-   [ ] Búsqueda en tiempo real
-   [ ] Botón "Agregar Nuevo Registro"

### Create (FormValidator):

-   [ ] Form id="marcaForm"
-   [ ] Validador required para nombre
-   [ ] Validador maxLength (60) para nombre
-   [ ] Validador maxLength (255) para descripcion
-   [ ] Textarea resize: none
-   [ ] Submit con validación

### Edit (FormValidator):

-   [ ] Misma config que create
-   [ ] Pre-llenar campos con old() o modelo
-   [ ] Sin botón restaurar
-   [ ] Botones Actualizar + Restablecer

---

## ⚠️ Puntos de Atención

### 1. Datos Anidados:

```javascript
// ✅ CORRECTO
{ key: 'caracteristica.nombre', label: 'Nombre' }

// ❌ INCORRECTO
{ key: 'nombre', label: 'Nombre' } // No existe directamente
```

### 2. Modal Toggle:

```blade
<!-- Texto dinámico basado en estado -->
{{ $item->caracteristica->estado == 1
  ? '¿Eliminar marca?'
  : '¿Restaurar marca?' }}
```

### 3. Validaciones Backend:

```php
// StoreCaracteristicaRequest
'nombre' => 'required|max:60|unique:caracteristicas,nombre'
'descripcion' => 'nullable|max:255'
```

Frontend debe coincidir ✅

---

## 🧪 Plan de Testing

### Index:

1. ✅ Tabla renderiza con datos
2. ✅ Búsqueda encuentra marcas
3. ✅ Badge muestra estado correcto
4. ✅ Botón eliminar (estado=1)
5. ✅ Botón restaurar (estado=0)
6. ✅ Modal correcto según estado
7. ✅ Eliminar cambia estado a 0
8. ✅ Restaurar cambia estado a 1

### Create:

1. ✅ Validación required en nombre
2. ✅ Validación maxLength en nombre
3. ✅ Descripción opcional
4. ✅ Submit crea marca correctamente
5. ✅ Redirección a index con mensaje

### Edit:

1. ✅ Campos pre-llenados
2. ✅ Validación funciona
3. ✅ Actualización exitosa
4. ✅ Botón restablecer limpia cambios

---

## 📊 Métricas Esperadas

### Código:

-   **Líneas eliminadas:** ~100 (HTML repetitivo)
-   **Líneas agregadas:** ~80 (config JavaScript)
-   **Reducción neta:** ~20 líneas
-   **Mejora legibilidad:** +50%

### Funcionalidad:

-   **Búsqueda en tiempo real:** ✅
-   **Validación client-side:** ✅
-   **Feedback visual:** ✅
-   **UX mejorada:** +100%

### Tiempo:

-   **Estimado:** 45 min
-   **vs Primera migración:** -50%
-   **Razón:** Patrón ya conocido

---

## 🚀 Siguiente Paso

Una vez completado:

1. ✅ Testing completo
2. ✅ Commit con mensaje descriptivo
3. ✅ Documentar diferencias encontradas
4. ✅ Actualizar checklist
5. ✅ Proceder a Presentaciones

---

## 📝 Notas Especiales

### Ventajas de Marcas:

-   ✅ Formulario más simple que Categorías (solo 2 campos)
-   ✅ Ya tiene toggle eliminar/restaurar
-   ✅ Backend no requiere cambios

### Complejidad adicional:

-   ⚠️ Datos anidados (caracteristica.\*)
-   ⚠️ Modal dinámico basado en estado
-   ⚠️ Transacción DB en create (backend)

### Aprendizajes aplicables:

-   ✅ DynamicTable maneja datos anidados
-   ✅ Formatters pueden ser condicionales
-   ✅ Modales pueden ser dinámicos

---

**LISTO PARA INICIAR** 🎯

_Plan creado el 21 de Octubre, 2025_
