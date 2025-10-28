# Componentes JS Modernos para Carwash ESP

Este directorio contiene los componentes JavaScript utilizados para la modernización de los módulos CRUD y vistas dinámicas del sistema. Cada componente está diseñado para integrarse con las vistas Blade y facilitar la gestión de formularios, tablas y dashboards.

## Estructura

-   **forms/**: Componentes para formularios dinámicos y validación.
    -   `FormValidator.js`: Valida formularios en tiempo real.
    -   `RoleFormManager.js`: Renderiza y gestiona el formulario de roles.
    -   `ProveedorFormManager.js`: Renderiza y gestiona el formulario de proveedores.
-   **tables/**: Componentes para tablas dinámicas.
    -   `DynamicTable.js`: Renderiza tablas con datos dinámicos.
    -   `RoleTableManager.js`: Renderiza la tabla de roles con acciones.
    -   `ProveedorTableManager.js`: Renderiza la tabla de proveedores con acciones.
-   **PanelDashboard.js**: Renderiza el dashboard principal del panel de control.
-   **EstacionamientoDetalle.js**: Muestra el detalle de estacionamiento.
-   **VentaManager.js**: Renderiza y gestiona el formulario de ventas.
-   **CompraManager.js**: Renderiza y gestiona el formulario de compras.
-   **FidelidadManager.js**: Renderiza y gestiona el formulario de fidelidad.
-   **MantenimientoManager.js**: Renderiza y gestiona el formulario de mantenimiento.
-   **ControlLavadoManager.js**: Renderiza y gestiona el formulario de control de lavado.

## Uso

Cada componente expone un método `init` que recibe un objeto de configuración con los datos necesarios y el selector del elemento donde se renderiza. Ejemplo:

```js
PanelDashboard.init({
  el: '#panel-dashboard-root',
  data: {...},
  userPermissions: [...]
});
```

## Integración

-   Importa el componente en la vista Blade usando Vite:
    ```blade
    @vite(['resources/js/components/PanelDashboard.js'])
    ```
-   Llama a `init` en el evento `DOMContentLoaded`.
-   Los datos deben ser pasados desde el backend como JSON.

## Personalización

Puedes extender los componentes para agregar validaciones, eventos o lógica adicional según las necesidades del sistema.

---

**Última actualización:** Octubre 2025
