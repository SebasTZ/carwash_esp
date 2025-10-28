# Documentación de Componentes JS: Tarjetas de Regalo

Este documento describe los componentes JavaScript utilizados para la gestión de tarjetas de regalo en el sistema.

## Componentes

### 1. TarjetaRegaloFormManager

-   **Ubicación:** `resources/js/components/forms/TarjetaRegaloFormManager.js`
-   **Función:** Gestiona la validación y lógica de los formularios de creación y edición de tarjetas de regalo.
-   **Integración:**
    -   Se importa como módulo en las vistas Blade `create.blade.php` y `edit.blade.php`.
    -   Inicializa validaciones y valores por defecto.
-   **Uso:**
    ```js
    import TarjetaRegaloFormManager from "/resources/js/components/forms/TarjetaRegaloFormManager.js";
    new TarjetaRegaloFormManager("#tarjetaRegaloForm");
    ```

### 2. TarjetaRegaloTableManager

-   **Ubicación:** `resources/js/components/tables/TarjetaRegaloTableManager.js`
-   **Función:** Permite ordenar y gestionar la tabla de reporte de tarjetas de regalo.
-   **Integración:**
    -   Se importa como módulo en la vista Blade `reporte.blade.php`.
-   **Uso:**
    ```js
    import TarjetaRegaloTableManager from "/resources/js/components/tables/TarjetaRegaloTableManager.js";
    new TarjetaRegaloTableManager("#tarjetaRegaloTable");
    ```

### 3. TarjetaRegaloUsosTableManager

-   **Ubicación:** `resources/js/components/tables/TarjetaRegaloUsosTableManager.js`
-   **Función:** Permite ordenar y gestionar la tabla de historial de usos de tarjetas de regalo.
-   **Integración:**
    -   Se importa como módulo en la vista Blade `usos.blade.php`.
-   **Uso:**
    ```js
    import TarjetaRegaloUsosTableManager from "/resources/js/components/tables/TarjetaRegaloUsosTableManager.js";
    new TarjetaRegaloUsosTableManager("#tarjetaRegaloUsosTable");
    ```

## Vistas Migradas

-   `create.blade.php`: Formulario de creación, ahora gestionado por `TarjetaRegaloFormManager`.
-   `edit.blade.php`: Formulario de edición, gestionado por `TarjetaRegaloFormManager`.
-   `reporte.blade.php`: Tabla de reporte, gestionada por `TarjetaRegaloTableManager`.
-   `usos.blade.php`: Tabla de historial de usos, gestionada por `TarjetaRegaloUsosTableManager`.

## Notas

-   Todos los componentes usan ES Modules y deben ser importados con rutas absolutas desde la raíz del proyecto.
-   Los formularios y tablas mantienen la funcionalidad original y añaden validación y ordenamiento dinámico.
-   Se crearon backups `.old` de todas las vistas antes de la migración.
