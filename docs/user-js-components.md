# Documentación de Componentes JS - Módulo User

## Descripción

Este documento explica el uso e integración de los componentes JavaScript modernos para la gestión de usuarios en el sistema Carwash ESP. Los componentes permiten formularios y tablas dinámicas, mejorando la experiencia y mantenibilidad del frontend.

## Componentes

### UserFormManager.js

-   **Ubicación:** `resources/js/components/forms/UserFormManager.js`
-   **Función:** Renderiza y gestiona el formulario de creación/edición de usuarios.
-   **Método principal:** `init({ el, user, old, roles })`
    -   `el`: Selector del contenedor donde se renderiza el formulario.
    -   `user`: Datos del usuario (para edición).
    -   `old`: Valores antiguos (para repoblar tras error).
    -   `roles`: Array de roles disponibles.
-   **Ejemplo de uso:**
    ```js
    UserFormManager.init({
        el: "#user-create-form-fields",
        roles: ["admin", "user"],
        old: { name: "", email: "", role: "", status: "" },
    });
    ```

### UserTableManager.js

-   **Ubicación:** `resources/js/components/tables/UserTableManager.js`
-   **Función:** Renderiza la tabla dinámica de usuarios con acciones de editar y eliminar.
-   **Método principal:** `init({ el, users, canEdit, canDelete })`
    -   `el`: Selector del contenedor de la tabla.
    -   `users`: Array de usuarios (con campos: id, name, email, role, status).
    -   `canEdit`: Permiso para mostrar botón de edición.
    -   `canDelete`: Permiso para mostrar botón de eliminación.
-   **Ejemplo de uso:**
    ```js
    UserTableManager.init({
      el: '#users-dynamic-table',
      users: [...],
      canEdit: true,
      canDelete: true
    });
    ```

## Integración en Blade

-   Importa el componente con Vite:
    ```blade
    @vite(['resources/js/components/forms/UserFormManager.js'])
    @vite(['resources/js/components/tables/UserTableManager.js'])
    ```
-   Llama a `init` en el evento `DOMContentLoaded`.
-   Los datos deben ser pasados desde el backend como JSON.

## Ejemplo de integración

```blade
<form id="user-create-form" ...>
  <div id="user-create-form-fields"></div>
</form>
@vite(['resources/js/components/forms/UserFormManager.js'])
<script>
  document.addEventListener('DOMContentLoaded', function() {
    window.UserFormManager.init({
      el: '#user-create-form-fields',
      roles: @json($roles),
      old: {...}
    });
  });
</script>
```

## Notas

-   Los componentes están diseñados para ser reutilizables y fáciles de extender.
-   Se recomienda validar los datos en backend y frontend.
-   Los botones de acción dependen de los permisos del usuario autenticado.

---

**Última actualización:** Octubre 2025
