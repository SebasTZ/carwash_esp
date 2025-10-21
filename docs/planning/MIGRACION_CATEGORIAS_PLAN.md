# 🔄 Plan de Migración: Vista Categorías

**Fecha:** 21 de Octubre, 2025  
**Objetivo:** Migrar vistas de Categorías a componentes modernos  
**Componentes a usar:** DynamicTable, FormValidator

---

## 📋 Análisis del Estado Actual

### Vista Index (index.blade.php)

**Actual:**

-   ❌ Tabla HTML estática con foreach manual
-   ❌ Modales repetidos por cada registro
-   ❌ No usa DataTables (comentado)
-   ✅ Paginación Laravel (x-pagination-info)
-   ✅ Permisos con @can
-   ✅ Badges de estado (activo/eliminado)

**Campos mostrados:**

1. Nombre (caracteristica->nombre)
2. Descripción (caracteristica->descripcion)
3. Estado (badge: activo/eliminado)
4. Acciones (editar, eliminar/restaurar)

### Vista Create (create.blade.php)

**Actual:**

-   ❌ Validación solo en backend
-   ❌ Errores mostrados manualmente con @error
-   ✅ Formulario simple: nombre + descripción
-   ✅ CSRF protection

### Vista Edit (edit.blade.php)

**Actual:**

-   ❌ Validación solo en backend
-   ❌ Errores mostrados manualmente con @error
-   ✅ Pre-llenado con old() helpers
-   ✅ Botón reset

---

## 🎯 Plan de Migración

### Fase 1: Refactor Vista Index ✅

**Objetivo:** Usar DynamicTable para tabla dinámica

**Cambios:**

1. ✅ Eliminar tabla HTML estática
2. ✅ Reemplazar con DynamicTable component
3. ✅ Configurar formatters para estado (badge)
4. ✅ Implementar acciones (editar, eliminar)
5. ✅ Modal único reutilizable (no uno por registro)
6. ✅ Mantener paginación Laravel

**Beneficios:**

-   Código más limpio (~50% menos líneas)
-   Modal único vs N modales
-   Búsqueda integrada (bonus)
-   Formatters reutilizables

### Fase 2: Refactor Formularios (Create + Edit) ✅

**Objetivo:** Usar FormValidator para validación frontend

**Cambios:**

1. ✅ Agregar FormValidator a ambos formularios
2. ✅ Validación en tiempo real (onBlur)
3. ✅ Mensajes de error consistentes
4. ✅ Validación de nombre (required, minLength: 3)
5. ✅ Validación de descripción (opcional, maxLength: 500)
6. ✅ Mantener validación backend (seguridad)

**Beneficios:**

-   UX mejorado (validación instant

ánea)

-   Menos requests fallidos
-   Mensajes claros antes de submit

### Fase 3: Auto-guardado (Opcional) 🎯

**Objetivo:** Agregar AutoSave a formularios

**Decisión:** ⏸️ POSPONER

-   Categorías son registros simples
-   No justifica auto-guardado
-   Priorizar migración de más vistas

---

## 📝 Implementación Detallada

### 1. Refactor Index.blade.php

#### Antes (139 líneas):

```blade
<!-- Tabla HTML estática -->
<table class="table table-striped fs-6">
    <thead>...</thead>
    <tbody>
        @foreach ($categorias as $categoria)
        <tr>...</tr>
        <!-- Modal por cada registro -->
        <div class="modal fade" id="confirmModal-{{$categoria->id}}">
        ...
        </div>
        @endforeach
    </tbody>
</table>
```

#### Después (~80 líneas estimadas):

```blade
<!-- Contenedor para DynamicTable -->
<div id="categorias-table"></div>

<!-- Modal único reutilizable -->
<div class="modal fade" id="confirmModal">...</div>

@push('js')
<script type="module">
import DynamicTable from '@/components/tables/DynamicTable.js';

// Datos desde Laravel
const categorias = @json($categorias->items());

// Configurar tabla
const tabla = new DynamicTable('#categorias-table', {
    columns: [
        { data: 'caracteristica.nombre', title: 'Nombre' },
        { data: 'caracteristica.descripcion', title: 'Descripción' },
        {
            data: 'caracteristica.estado',
            title: 'Estado',
            formatter: (value) => {
                return value === 1
                    ? '<span class="badge rounded-pill text-bg-success">activo</span>'
                    : '<span class="badge rounded-pill text-bg-danger">eliminado</span>';
            }
        }
    ],
    data: categorias,
    pagination: false, // Laravel ya pagina
    searchable: true,
    actionsConfig: {
        edit: {
            show: {{ auth()->user()->can('editar-categoria') ? 'true' : 'false' }},
            callback: (row, data) => {
                window.location.href = `/categorias/${data.id}/edit`;
            }
        },
        delete: {
            show: {{ auth()->user()->can('eliminar-categoria') ? 'true' : 'false' }},
            class: 'btn-sm btn-danger',
            icon: 'bi-trash',
            callback: (row, data) => {
                // Mostrar modal de confirmación
                showDeleteModal(data);
            }
        }
    }
});

function showDeleteModal(categoria) {
    // Configurar y mostrar modal único
}
</script>
@endpush
```

**Reducción:** ~42% menos código (139 → 80 líneas)

---

### 2. Refactor Create.blade.php

#### Antes (54 líneas):

```blade
<form action="{{ route('categorias.store') }}" method="post">
    @csrf
    <div class="col-md-6">
        <input type="text" name="nombre" class="form-control">
        @error('nombre')
        <small class="text-danger">{{'*'.$message}}</small>
        @enderror
    </div>
    ...
</form>
```

#### Después (~65 líneas con validación):

```blade
<form action="{{ route('categorias.store') }}" method="post" id="form-categoria">
    @csrf
    <div class="col-md-6">
        <input type="text" name="nombre" class="form-control" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-12">
        <textarea name="descripcion" class="form-control"></textarea>
        <div class="invalid-feedback"></div>
    </div>
</form>

@push('js')
<script type="module">
import FormValidator from '@/components/forms/FormValidator.js';

const validator = new FormValidator('#form-categoria', {
    rules: {
        nombre: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/  // Solo letras y espacios
        },
        descripcion: {
            required: false,
            maxLength: 500
        }
    },
    messages: {
        nombre: {
            required: 'El nombre es obligatorio',
            minLength: 'El nombre debe tener al menos 3 caracteres',
            pattern: 'El nombre solo puede contener letras'
        },
        descripcion: {
            maxLength: 'La descripción no puede superar 500 caracteres'
        }
    },
    validateOnBlur: true,
    scrollToError: true,
    onValid: (form) => {
        // Enviar formulario si es válido
        form.submit();
    },
    onInvalid: (errors) => {
        console.log('Errores de validación:', errors);
    }
});
</script>
@endpush
```

**Incremento:** +11 líneas PERO con validación frontend completa

---

### 3. Refactor Edit.blade.php

Similar a Create, con pre-llenado de datos.

---

## 🎯 Componentes Usados

### DynamicTable

**Configuración:**

-   ✅ Columns: nombre, descripción, estado
-   ✅ Formatter custom para badges de estado
-   ✅ Actions: edit, delete con callbacks
-   ✅ Searchable: búsqueda integrada
-   ✅ Pagination: false (usa Laravel)

### FormValidator

**Reglas:**

-   ✅ nombre: required, minLength:3, maxLength:100, pattern (solo letras)
-   ✅ descripcion: maxLength:500
-   ✅ validateOnBlur: true
-   ✅ Bootstrap 5 classes

---

## 📊 Métricas Esperadas

| Métrica                      | Antes                | Después | Mejora |
| ---------------------------- | -------------------- | ------- | ------ |
| **Líneas index.blade.php**   | 139                  | ~80     | -42%   |
| **Líneas create.blade.php**  | 54                   | ~65     | +20%   |
| **Líneas edit.blade.php**    | 61                   | ~70     | +15%   |
| **Modales en index**         | N (uno por registro) | 1       | -99%   |
| **Validación frontend**      | ❌ No                | ✅ Sí   | Nuevo  |
| **Búsqueda en tabla**        | ❌ No                | ✅ Sí   | Nuevo  |
| **Formatters reutilizables** | ❌ No                | ✅ Sí   | Nuevo  |

**Balance:** Ligero incremento en líneas PERO con funcionalidad superior

---

## ✅ Checklist de Implementación

### Paso 1: Setup

-   [ ] Compilar componentes con Vite
-   [ ] Verificar que DynamicTable y FormValidator están en build
-   [ ] Probar imports en consola del navegador

### Paso 2: Migrar Index

-   [ ] Crear tabla vacía con DynamicTable
-   [ ] Configurar columns y data
-   [ ] Implementar formatter de estado
-   [ ] Configurar actions (edit, delete)
-   [ ] Crear modal único de confirmación
-   [ ] Probar eliminación/restauración
-   [ ] Probar búsqueda integrada

### Paso 3: Migrar Create

-   [ ] Agregar FormValidator al formulario
-   [ ] Configurar reglas de validación
-   [ ] Personalizar mensajes
-   [ ] Probar validación on blur
-   [ ] Probar submit con datos válidos
-   [ ] Probar submit con datos inválidos

### Paso 4: Migrar Edit

-   [ ] Agregar FormValidator (similar a create)
-   [ ] Verificar pre-llenado de datos
-   [ ] Probar validación y actualización

### Paso 5: Testing Manual

-   [ ] Crear categoría nueva
-   [ ] Editar categoría existente
-   [ ] Eliminar categoría
-   [ ] Restaurar categoría eliminada
-   [ ] Buscar en tabla
-   [ ] Validar permisos (@can)

### Paso 6: Git Commit

-   [ ] Commit con mensaje descriptivo
-   [ ] Documentar cambios y aprendizajes

---

## 🎓 Aprendizajes Esperados

### ¿Qué descubriremos?

1. **¿DynamicTable cubre el caso de uso?**

    - ¿Necesita mejoras?
    - ¿Falta alguna feature?

2. **¿FormValidator es suficiente?**

    - ¿Los validadores cubren los casos reales?
    - ¿Necesitamos más validadores predefinidos?

3. **¿Qué componentes faltan?**

    - ¿AlertManager para toasts de éxito/error?
    - ¿Modal component para confirmaciones?
    - ¿Otros?

4. **¿Integración con Laravel?**
    - ¿Cómo pasar datos desde Blade?
    - ¿Cómo manejar CSRF tokens?
    - ¿Cómo integrar con paginación Laravel?

---

## 🚀 Próximos Pasos Después

### Si todo va bien:

1. ✅ Migrar vista "Marcas" (similar a Categorías)
2. ✅ Documentar patrones descubiertos
3. ✅ Crear componentes adicionales SI son necesarios

### Si encontramos gaps:

1. 🔧 Mejorar DynamicTable con features faltantes
2. 🔧 Agregar validadores a FormValidator
3. 🎨 Crear componentes nuevos (AlertManager, Modal, etc.)

---

**Inicio de migración:** Ahora  
**Tiempo estimado:** 2-3 horas  
**Riesgo:** Bajo (código backend no cambia)

---

_Generado el 21 de Octubre, 2025_
