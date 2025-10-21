# 📦 Componentes Frontend - API Reference

**Última actualización:** 21 de Octubre, 2025  
**Versión:** 2.0.0  
**Tests:** 91/91 pasando (100%)

---

## 📋 Índice

- [Componentes Disponibles](#componentes-disponibles)
- [DynamicTable](#dynamictable)
- [AutoSave](#autosave)
- [FormValidator](#formvalidator)
- [Ejemplos de Uso](#ejemplos-de-uso)

---

## 🎯 Componentes Disponibles

| Componente | Líneas | Tests | Estado | Propósito |
|------------|--------|-------|--------|-----------|
| **DynamicTable** | 520 | 13 | ✅ Estable | Tablas dinámicas con CRUD |
| **AutoSave** | 525 | 35 | ✅ Estable | Guardado automático con debouncing |
| **FormValidator** | 570 | 43 | ✅ Estable | Validación de formularios completa |
| **Total** | **1,615** | **91** | - | - |

---

## 🗂️ DynamicTable

Componente para crear tablas dinámicas con operaciones CRUD completas.

### Constructor

```javascript
import DynamicTable from '@/components/tables/DynamicTable.js';

const table = new DynamicTable('#mi-tabla', {
    // Opciones de configuración
    columns: [
        { 
            data: 'nombre', 
            title: 'Nombre',
            formatter: null  // Función de formateo opcional
        },
        { 
            data: 'precio', 
            title: 'Precio',
            formatter: 'currency'  // Formatter predefinido
        }
    ],
    
    // Datos iniciales (opcional)
    data: [],
    
    // Clases CSS personalizadas
    tableClass: 'table table-striped',
    theadClass: 'table-dark',
    tbodyClass: '',
    
    // Callbacks de eventos
    onRowClick: (row, data) => {},
    onRowAdd: (data) => {},
    onRowUpdate: (data) => {},
    onRowRemove: (data) => {},
    onDataChange: (allData) => {},
    
    // Configuración de acciones
    showActions: true,
    actionsConfig: {
        edit: { 
            show: true, 
            class: 'btn-sm btn-primary',
            icon: 'bi-pencil',
            callback: (row, data) => {}
        },
        delete: { 
            show: true, 
            class: 'btn-sm btn-danger',
            icon: 'bi-trash',
            callback: (row, data) => {}
        }
    },
    
    // Paginación
    pagination: true,
    pageSize: 10,
    
    // Búsqueda
    searchable: true,
    searchPlaceholder: 'Buscar...'
});
```

### Métodos Principales

#### `addRow(data)`
Agrega una nueva fila a la tabla.

```javascript
table.addRow({
    id: 1,
    nombre: 'Producto A',
    precio: 100.50,
    fecha: '2025-10-21'
});
```

#### `updateRow(index, newData)`
Actualiza una fila existente por su índice.

```javascript
table.updateRow(0, {
    id: 1,
    nombre: 'Producto A Modificado',
    precio: 120.00
});
```

#### `removeRow(index)`
Elimina una fila por su índice.

```javascript
table.removeRow(0);
```

#### `clearTable()`
Limpia todas las filas de la tabla.

```javascript
table.clearTable();
```

#### `getData()`
Obtiene todos los datos actuales de la tabla.

```javascript
const allData = table.getData();
console.log(allData); // Array de objetos
```

#### `setData(data)`
Reemplaza todos los datos de la tabla.

```javascript
table.setData([
    { id: 1, nombre: 'Item 1' },
    { id: 2, nombre: 'Item 2' }
]);
```

#### `refresh()`
Re-renderiza la tabla completa.

```javascript
table.refresh();
```

### Formatters Predefinidos

Los formatters transforman los datos antes de mostrarlos.

#### `currency`
Formatea números como moneda (S/. para Perú).

```javascript
{ data: 'precio', formatter: 'currency' }
// 100.50 → S/. 100.50
```

#### `date`
Formatea fechas en formato DD/MM/YYYY.

```javascript
{ data: 'fecha', formatter: 'date' }
// 2025-10-21 → 21/10/2025
```

#### `datetime`
Formatea fecha y hora.

```javascript
{ data: 'created_at', formatter: 'datetime' }
// 2025-10-21 14:30:00 → 21/10/2025 14:30:00
```

#### `status`
Muestra badges de estado con colores.

```javascript
{ data: 'estado', formatter: 'status' }
// 'activo' → <span class="badge bg-success">activo</span>
// 'inactivo' → <span class="badge bg-secondary">inactivo</span>
```

#### `boolean`
Muestra checkmarks o cruces para booleanos.

```javascript
{ data: 'disponible', formatter: 'boolean' }
// true → ✓
// false → ✗
```

#### `badge`
Muestra el valor como badge.

```javascript
{ data: 'categoria', formatter: 'badge' }
// 'Premium' → <span class="badge bg-primary">Premium</span>
```

#### Formatter Personalizado

```javascript
{
    data: 'precio',
    formatter: (value, row) => {
        return value > 100 ? `<strong>${value}</strong>` : value;
    }
}
```

### Eventos

```javascript
// Evento cuando se hace clic en una fila
table.on('rowClick', (row, data) => {
    console.log('Fila clickeada:', data);
});

// Evento cuando se agrega una fila
table.on('rowAdd', (data) => {
    console.log('Fila agregada:', data);
});

// Evento cuando se actualiza una fila
table.on('rowUpdate', (data) => {
    console.log('Fila actualizada:', data);
});

// Evento cuando se elimina una fila
table.on('rowRemove', (data) => {
    console.log('Fila eliminada:', data);
});

// Evento cuando cambian los datos
table.on('dataChange', (allData) => {
    console.log('Datos totales:', allData);
});
```

---

## 💾 AutoSave

Componente para implementar guardado automático con debouncing, reintentos y validación.

### Constructor

```javascript
import AutoSave from '@/components/forms/AutoSave.js';

const autoSave = new AutoSave('#mi-formulario', {
    // Endpoint para guardar
    endpoint: '/api/guardar',
    method: 'POST',  // GET, POST, PUT, DELETE
    
    // Debouncing (ms)
    debounceTime: 1000,  // Espera 1s después del último cambio
    
    // Reintentos en caso de error
    retryAttempts: 3,
    retryDelay: 2000,  // 2s entre reintentos
    
    // Validación antes de guardar
    validateBeforeSave: true,
    validationRules: {
        nombre: { required: true, minLength: 3 },
        email: { required: true, email: true }
    },
    
    // Storage local
    useLocalStorage: true,
    storageKey: 'autosave-formulario',
    
    // Callbacks
    onSaveStart: (data) => {
        console.log('Iniciando guardado...', data);
    },
    onSaveSuccess: (response) => {
        console.log('Guardado exitoso:', response);
    },
    onSaveError: (error) => {
        console.error('Error al guardar:', error);
    },
    onValidationError: (errors) => {
        console.log('Errores de validación:', errors);
    },
    onDataChange: (data) => {
        console.log('Datos cambiaron:', data);
    },
    
    // Campos a incluir/excluir
    includeFields: null,  // null = todos, o ['campo1', 'campo2']
    excludeFields: ['_token'],  // Campos a excluir
    
    // Indicador visual
    showSavingIndicator: true,
    savingIndicatorText: 'Guardando...',
    savedIndicatorText: 'Guardado ✓',
    errorIndicatorText: 'Error al guardar'
});
```

### Métodos Principales

#### `save()`
Fuerza un guardado inmediato (sin esperar debounce).

```javascript
autoSave.save();
```

#### `pause()`
Pausa el auto-guardado temporalmente.

```javascript
autoSave.pause();
```

#### `resume()`
Reanuda el auto-guardado.

```javascript
autoSave.resume();
```

#### `clearStorage()`
Limpia los datos guardados en localStorage.

```javascript
autoSave.clearStorage();
```

#### `getFormData()`
Obtiene los datos actuales del formulario.

```javascript
const data = autoSave.getFormData();
console.log(data);
```

#### `setFormData(data)`
Establece valores en el formulario.

```javascript
autoSave.setFormData({
    nombre: 'Juan',
    email: 'juan@example.com'
});
```

#### `restoreFromStorage()`
Restaura datos desde localStorage.

```javascript
autoSave.restoreFromStorage();
```

### Validación Integrada

AutoSave puede validar antes de guardar:

```javascript
const autoSave = new AutoSave('#form', {
    validateBeforeSave: true,
    validationRules: {
        nombre: { 
            required: true, 
            minLength: 3,
            maxLength: 50 
        },
        email: { 
            required: true, 
            email: true 
        },
        edad: { 
            required: false,
            min: 18,
            max: 100
        }
    },
    onValidationError: (errors) => {
        // errors = { campo: 'mensaje de error' }
        Object.keys(errors).forEach(field => {
            console.error(`${field}: ${errors[field]}`);
        });
    }
});
```

### Storage Local

Guarda automáticamente en localStorage para recuperar datos si el usuario cierra la página:

```javascript
const autoSave = new AutoSave('#form', {
    useLocalStorage: true,
    storageKey: 'mi-formulario-autosave'
});

// Al cargar la página, restaurar datos guardados
document.addEventListener('DOMContentLoaded', () => {
    autoSave.restoreFromStorage();
});
```

### Reintentos Automáticos

Si falla el guardado, reintenta automáticamente:

```javascript
const autoSave = new AutoSave('#form', {
    endpoint: '/api/guardar',
    retryAttempts: 3,     // Intentará 3 veces
    retryDelay: 2000,     // 2s entre intentos
    onSaveError: (error, attempt) => {
        if (attempt < 3) {
            console.log(`Reintentando... (${attempt}/3)`);
        } else {
            console.error('Fallo después de 3 intentos');
        }
    }
});
```

---

## ✅ FormValidator

Componente completo de validación de formularios con 16+ validadores predefinidos.

### Constructor

```javascript
import FormValidator from '@/components/forms/FormValidator.js';

const validator = new FormValidator('#mi-formulario', {
    // Reglas de validación por campo
    rules: {
        nombre: {
            required: true,
            minLength: 3,
            maxLength: 50,
            alpha: true  // Solo letras
        },
        email: {
            required: true,
            email: true
        },
        edad: {
            required: true,
            number: true,
            min: 18,
            max: 100
        },
        telefono: {
            required: false,
            phone: true
        },
        password: {
            required: true,
            minLength: 8,
            pattern: /^(?=.*[A-Z])(?=.*[0-9])/  // Al menos 1 mayúscula y 1 número
        },
        password_confirmation: {
            required: true,
            equal: 'password'  // Debe ser igual al campo password
        }
    },
    
    // Mensajes personalizados
    messages: {
        nombre: {
            required: 'El nombre es obligatorio',
            minLength: 'Mínimo {min} caracteres',
            alpha: 'Solo se permiten letras'
        },
        email: {
            email: 'Email inválido'
        }
    },
    
    // Modos de validación
    validateOnBlur: true,    // Validar al salir del campo
    validateOnInput: false,  // Validar mientras escribe
    validateOnSubmit: true,  // Validar al enviar
    
    // UX
    scrollToError: true,           // Scroll al primer error
    focusOnError: true,            // Focus en primer campo con error
    disableSubmitOnInvalid: true,  // Deshabilitar botón submit si inválido
    
    // Callbacks
    onValid: (form) => {
        console.log('Formulario válido');
    },
    onInvalid: (errors) => {
        console.log('Errores:', errors);
    },
    onFieldValid: (field, value) => {
        console.log(`Campo ${field} válido`);
    },
    onFieldInvalid: (field, error) => {
        console.log(`Campo ${field} inválido: ${error}`);
    },
    
    // Validadores personalizados
    customValidators: {
        mayorDeEdad: (value) => {
            const edad = parseInt(value);
            return edad >= 18 ? true : 'Debes ser mayor de 18 años';
        }
    }
});
```

### Validadores Predefinidos

#### `required`
Campo obligatorio.

```javascript
{ required: true }
```

#### `email`
Formato de email válido.

```javascript
{ email: true }
```

#### `url`
URL válida.

```javascript
{ url: true }
```

#### `number`
Número válido (entero o decimal).

```javascript
{ number: true }
```

#### `integer`
Solo números enteros.

```javascript
{ integer: true }
```

#### `digits`
Solo dígitos (sin decimales ni signos).

```javascript
{ digits: true }
```

#### `minLength` / `maxLength`
Longitud mínima/máxima del texto.

```javascript
{ minLength: 3, maxLength: 50 }
```

#### `min` / `max`
Valor mínimo/máximo para números.

```javascript
{ min: 18, max: 100 }
```

#### `pattern`
Expresión regular personalizada.

```javascript
{ pattern: /^[A-Z0-9]+$/ }  // Solo mayúsculas y números
// O como string
{ pattern: '^[A-Z0-9]+$' }
```

#### `equal`
Debe ser igual a otro campo.

```javascript
{ equal: 'password' }  // Debe ser igual al campo 'password'
```

#### `date`
Fecha válida.

```javascript
{ date: true }
```

#### `time`
Hora válida.

```javascript
{ time: true }
```

#### `phone`
Teléfono válido (formato internacional flexible).

```javascript
{ phone: true }
```

#### `alphanumeric`
Solo letras y números.

```javascript
{ alphanumeric: true }
```

#### `alpha`
Solo letras.

```javascript
{ alpha: true }
```

### Métodos Principales

#### `validate()`
Valida el formulario completo. Retorna `true` si es válido.

```javascript
if (validator.validate()) {
    console.log('Formulario válido');
    // Enviar formulario
} else {
    console.log('Formulario inválido');
}
```

#### `validateField(fieldName)`
Valida un campo específico.

```javascript
const isValid = validator.validateField('email');
```

#### `clearErrors()`
Limpia todos los errores del formulario.

```javascript
validator.clearErrors();
```

#### `reset()`
Resetea el formulario (limpia valores y errores).

```javascript
validator.reset();
```

#### `addRule(fieldName, rules)`
Agrega reglas de validación dinámicamente.

```javascript
validator.addRule('nuevocampo', {
    required: true,
    minLength: 5
});
```

#### `removeRule(fieldName)`
Elimina reglas de un campo.

```javascript
validator.removeRule('campoOpcional');
```

#### `addValidator(name, validatorFn)`
Agrega un validador personalizado.

```javascript
validator.addValidator('esPar', (value) => {
    const num = parseInt(value);
    return num % 2 === 0 ? true : 'El número debe ser par';
});

// Usar en reglas
validator.addRule('numero', {
    esPar: true
});
```

### Mensajes Personalizados

Los mensajes pueden incluir placeholders:

```javascript
messages: {
    nombre: {
        required: 'El campo {field} es obligatorio',
        minLength: 'Mínimo {min} caracteres para {field}',
        maxLength: 'Máximo {max} caracteres'
    },
    edad: {
        min: 'La edad mínima es {min} años',
        max: 'La edad máxima es {max} años'
    }
}
```

**Placeholders disponibles:**
- `{field}` - Nombre del campo
- `{min}` - Valor mínimo
- `{max}` - Valor máximo
- `{value}` - Valor actual del campo

### Validadores Personalizados

```javascript
const validator = new FormValidator('#form', {
    rules: {
        username: {
            required: true,
            sinEspacios: true,
            disponible: true
        }
    },
    customValidators: {
        // Validador síncrono
        sinEspacios: (value) => {
            return !/\s/.test(value) ? true : 'No se permiten espacios';
        },
        
        // Validador con parámetros
        disponible: async (value) => {
            const response = await fetch(`/api/check-username?name=${value}`);
            const data = await response.json();
            return data.available ? true : 'Nombre de usuario no disponible';
        }
    }
});
```

### Integración con Bootstrap 5

FormValidator añade automáticamente las clases de Bootstrap:

```html
<!-- Campo válido -->
<input class="form-control is-valid" />
<div class="valid-feedback">Campo válido</div>

<!-- Campo inválido -->
<input class="form-control is-invalid" />
<div class="invalid-feedback">Este campo es obligatorio</div>
```

### Manejo de Submit

```javascript
const validator = new FormValidator('#form', {
    validateOnSubmit: true,
    onValid: (form) => {
        // Enviar datos por AJAX
        const formData = new FormData(form);
        
        fetch('/api/guardar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Guardado exitoso');
        });
    },
    onInvalid: (errors) => {
        console.error('Formulario inválido:', errors);
        // Mostrar toast de error
    }
});
```

---

## 📚 Ejemplos de Uso

### Ejemplo 1: CRUD Completo de Productos

```javascript
import DynamicTable from '@/components/tables/DynamicTable.js';
import FormValidator from '@/components/forms/FormValidator.js';
import AutoSave from '@/components/forms/AutoSave.js';

// 1. Tabla de productos
const tabla = new DynamicTable('#tabla-productos', {
    columns: [
        { data: 'id', title: 'ID' },
        { data: 'nombre', title: 'Nombre' },
        { data: 'precio', title: 'Precio', formatter: 'currency' },
        { data: 'stock', title: 'Stock' },
        { data: 'estado', title: 'Estado', formatter: 'status' }
    ],
    actionsConfig: {
        edit: {
            show: true,
            callback: (row, data) => {
                // Abrir modal de edición
                $('#modalEditar').modal('show');
                autoSave.setFormData(data);
            }
        },
        delete: {
            show: true,
            callback: async (row, data) => {
                if (confirm('¿Eliminar producto?')) {
                    await fetch(`/api/productos/${data.id}`, { method: 'DELETE' });
                    tabla.removeRow(row);
                }
            }
        }
    }
});

// 2. Validador para formulario de producto
const validator = new FormValidator('#form-producto', {
    rules: {
        nombre: { required: true, minLength: 3, maxLength: 100 },
        precio: { required: true, number: true, min: 0 },
        stock: { required: true, integer: true, min: 0 }
    },
    onValid: async (form) => {
        const data = new FormData(form);
        const response = await fetch('/api/productos', {
            method: 'POST',
            body: data
        });
        const producto = await response.json();
        
        // Agregar a la tabla
        tabla.addRow(producto);
        
        // Cerrar modal
        $('#modalNuevo').modal('hide');
        validator.reset();
    }
});

// 3. Auto-guardado para borrador
const autoSave = new AutoSave('#form-producto', {
    endpoint: '/api/productos/draft',
    debounceTime: 2000,
    useLocalStorage: true,
    storageKey: 'producto-draft',
    validateBeforeSave: true,
    validationRules: {
        nombre: { required: true }
    }
});

// 4. Cargar datos iniciales
fetch('/api/productos')
    .then(r => r.json())
    .then(productos => tabla.setData(productos));
```

### Ejemplo 2: Formulario de Registro con Validación

```javascript
import FormValidator from '@/components/forms/FormValidator.js';

const validator = new FormValidator('#form-registro', {
    rules: {
        nombre: {
            required: true,
            minLength: 3,
            alpha: true
        },
        email: {
            required: true,
            email: true
        },
        telefono: {
            required: true,
            phone: true
        },
        password: {
            required: true,
            minLength: 8,
            pattern: /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])/
        },
        password_confirmation: {
            required: true,
            equal: 'password'
        },
        terminos: {
            required: true
        }
    },
    messages: {
        nombre: {
            alpha: 'Solo se permiten letras en el nombre'
        },
        password: {
            pattern: 'La contraseña debe tener mayúsculas, números y símbolos'
        },
        password_confirmation: {
            equal: 'Las contraseñas no coinciden'
        },
        terminos: {
            required: 'Debes aceptar los términos y condiciones'
        }
    },
    validateOnBlur: true,
    scrollToError: true,
    onValid: async (form) => {
        const data = new FormData(form);
        
        try {
            const response = await fetch('/api/registro', {
                method: 'POST',
                body: data
            });
            
            if (response.ok) {
                window.location.href = '/bienvenido';
            }
        } catch (error) {
            alert('Error al registrar');
        }
    }
});
```

### Ejemplo 3: Tabla con Búsqueda y Paginación

```javascript
import DynamicTable from '@/components/tables/DynamicTable.js';

const tabla = new DynamicTable('#tabla-clientes', {
    columns: [
        { data: 'id', title: 'ID' },
        { data: 'nombre', title: 'Cliente' },
        { data: 'email', title: 'Email' },
        { data: 'telefono', title: 'Teléfono' },
        { 
            data: 'created_at', 
            title: 'Registro',
            formatter: 'datetime'
        },
        {
            data: 'activo',
            title: 'Estado',
            formatter: 'boolean'
        }
    ],
    pagination: true,
    pageSize: 25,
    searchable: true,
    searchPlaceholder: 'Buscar cliente...',
    onRowClick: (row, data) => {
        // Navegar a detalle
        window.location.href = `/clientes/${data.id}`;
    },
    onDataChange: (allData) => {
        // Actualizar contador
        document.getElementById('total-clientes').textContent = allData.length;
    }
});
```

---

## 🧪 Testing

Todos los componentes tienen cobertura completa de tests:

```bash
# Ejecutar todos los tests
npm test

# Ejecutar tests en modo watch
npm run test:watch

# Ver cobertura
npm run test:coverage

# UI interactiva
npm run test:ui
```

**Estadísticas actuales:**
- ✅ 91/91 tests pasando (100%)
- ⚡ Ejecución: ~5 segundos
- 📊 Cobertura: Alta (>80% líneas críticas)

---

## 🚀 Próximos Componentes

En desarrollo:

- [ ] **DateTimePicker** - Selector de fecha/hora con Flatpickr
- [ ] **ImageUploader** - Upload con preview, crop y validación
- [ ] **AlertManager** - Sistema de toasts y notificaciones
- [ ] **Modal** - Modales reutilizables
- [ ] **Tabs** - Sistema de pestañas dinámicas

---

## 📖 Recursos Adicionales

- [Guía de Migración](../planning/FASE_3_ACELERADA.md)
- [Ejemplos de Uso](../examples/)
- [Testing Guide](../TESTING.md)
- [Contribuir](../CONTRIBUTING.md)

---

**Documentación mantenida por:** Equipo de Desarrollo CarWash ESP  
**Última revisión:** 21 de Octubre, 2025
