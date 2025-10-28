# Documentación General de Componentes JS Modernos

## Descripción

Este documento describe los componentes JavaScript utilizados para la modernización de los módulos CRUD y vistas dinámicas en Carwash ESP. Cada componente facilita la gestión de formularios, tablas y dashboards, integrándose con las vistas Blade.

## Componentes Principales

### FormValidator.js

-   **Ubicación:** `resources/js/components/forms/FormValidator.js`
-   **Función:** Valida formularios en tiempo real, mostrando errores y previniendo envíos incorrectos.
-   **Uso:**
    ```js
    new FormValidator("#form-id");
    ```

### DynamicTable.js

-   **Ubicación:** `resources/js/components/tables/DynamicTable.js`
-   **Función:** Renderiza tablas con datos dinámicos y permite paginación, orden y acciones.
-   **Uso:**
    ```js
    DynamicTable.init({ el: '#table-root', data: [...] });
    ```

### PanelDashboard.js

-   **Ubicación:** `resources/js/components/PanelDashboard.js`
-   **Función:** Renderiza el dashboard principal del panel de control con tarjetas dinámicas.
-   **Uso:**
    ```js
    PanelDashboard.init({ el: '#panel-dashboard-root', data: {...}, userPermissions: [...] });
    ```

### EstacionamientoDetalle.js

-   **Ubicación:** `resources/js/components/EstacionamientoDetalle.js`
-   **Función:** Muestra el detalle de estacionamiento en una tarjeta informativa.
-   **Uso:**
    ```js
    EstacionamientoDetalle.init({ el: '#detalle-root', estacionamiento: {...} });
    ```

### ProveedorFormManager.js / ProveedorTableManager.js

-   **Ubicación:**
    -   Formulario: `resources/js/components/forms/ProveedorFormManager.js`
    -   Tabla: `resources/js/components/tables/ProveedorTableManager.js`
-   **Función:** Renderizan y gestionan el formulario y la tabla de proveedores.
-   **Uso:**
    ```js
    ProveedorFormManager.init({ el: '#proveedor-form-fields', proveedor: {...}, old: {...} });
    ProveedorTableManager.init({ el: '#proveedores-table', proveedores: [...], canEdit: true, canDelete: true });
    ```

### RoleFormManager.js / RoleTableManager.js

-   **Ubicación:**
    -   Formulario: `resources/js/components/forms/RoleFormManager.js`
    -   Tabla: `resources/js/components/tables/RoleTableManager.js`
-   **Función:** Renderizan y gestionan el formulario y la tabla de roles.
-   **Uso:**
    ```js
    RoleFormManager.init({ el: '#role-form-fields', permisos: [...], role: {...}, old: {...} });
    RoleTableManager.init({ el: '#roles-table', roles: [...], canEdit: true, canDelete: true });
    ```

### VentaManager.js

-   **Ubicación:** `resources/js/components/VentaManager.js`
-   **Función:** Renderiza y gestiona el formulario de ventas.
-   **Uso:**
    ```js
    VentaManager.init({ el: '#venta-form-fields', venta: {...}, productos: [...], clientes: [...], old: {...} });
    ```

### CompraManager.js

-   **Ubicación:** `resources/js/components/CompraManager.js`
-   **Función:** Renderiza y gestiona el formulario de compras.
-   **Uso:**
    ```js
    CompraManager.init({ el: '#compra-form-fields', compra: {...}, productos: [...], proveedores: [...], old: {...} });
    ```

### FidelidadManager.js

-   **Ubicación:** `resources/js/components/FidelidadManager.js`
-   **Función:** Renderiza y gestiona el formulario de fidelidad.
-   **Uso:**
    ```js
    FidelidadManager.init({ el: '#fidelidad-form-fields', fidelidad: {...}, clientes: [...], old: {...} });
    ```

### MantenimientoManager.js

-   **Ubicación:** `resources/js/components/MantenimientoManager.js`
-   **Función:** Renderiza y gestiona el formulario de mantenimiento.
-   **Uso:**
    ```js
    MantenimientoManager.init({ el: '#mantenimiento-form-fields', mantenimiento: {...}, old: {...} });
    ```

### ControlLavadoManager.js

-   **Ubicación:** `resources/js/components/ControlLavadoManager.js`
-   **Función:** Renderiza y gestiona el formulario de control de lavado.
-   **Uso:**
    ```js
    ControlLavadoManager.init({ el: '#lavado-form-fields', lavado: {...}, old: {...} });
    ```

## Integración

-   Importa el componente con Vite en la vista Blade:
    ```blade
    @vite(['resources/js/components/PanelDashboard.js'])
    @vite(['resources/js/components/forms/FormValidator.js'])
    ```
-   Llama a `init` en el evento `DOMContentLoaded`.
-   Los datos deben ser pasados desde el backend como JSON.

## Notas

-   Todos los componentes son reutilizables y pueden extenderse según las necesidades del sistema.
-   Se recomienda validar los datos tanto en frontend como en backend.

---

**Última actualización:** Octubre 2025
