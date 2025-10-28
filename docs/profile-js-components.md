# Documentación de Componente JS: Perfil de Usuario

Este documento describe el componente JavaScript utilizado para la gestión del formulario de perfil de usuario.

## Componente

### ProfileFormManager

-   **Ubicación:** `resources/js/components/forms/ProfileFormManager.js`
-   **Función:** Gestiona la validación y habilitación dinámica de los campos del formulario de perfil.
-   **Integración:**
    -   Se importa como módulo en la vista Blade `profile/index.blade.php`.
    -   Permite habilitar los campos para edición al hacer clic en el botón "Editar".
-   **Uso:**
    ```js
    import ProfileFormManager from "/resources/js/components/forms/ProfileFormManager.js";
    const manager = new ProfileFormManager("#profileForm");
    document.getElementById("editProfileBtn").addEventListener("click", () => {
        manager.enableFields();
    });
    ```

## Vista Migrada

-   `index.blade.php`: Formulario de perfil, ahora gestionado por `ProfileFormManager` y con edición dinámica.

## Notas

-   El formulario mantiene la funcionalidad original y añade validación y edición dinámica.
-   Se creó backup `.old` antes de la migración.
