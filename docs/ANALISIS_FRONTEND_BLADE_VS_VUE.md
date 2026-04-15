# Analisis Frontend: Blade vs Vue — CarWash ESP

> Fecha: 2026-04-14
> Rama activa: `migration/laravel-13`
> Autor: Juan Sebastian Trujillo Zevallos

---

## 1. Estado actual del proyecto

### Metricas generales

| Metrica                | Cantidad    |
| ---------------------- | ----------- |
| Vistas Blade           | 91 archivos |
| Lineas de codigo Blade | ~10,000     |
| Modulos JavaScript     | 44 archivos |
| Lineas de codigo JS    | ~3,000+     |
| Rutas web              | 328         |
| Controladores          | 26          |
| Modelos                | 27          |
| Permisos granulares    | 50+         |

### Stack actual

- **Motor de vistas:** Blade (100%)
- **Bundler:** Vite con code-splitting y Terser
- **Framework CSS:** Bootstrap 5.3.1
- **JavaScript:** Vanilla JS + jQuery + arquitectura de componentes propia
- **Iconos:** FontAwesome 6.3
- **Notificaciones:** SweetAlert2
- **HTTP client:** Axios
- **Autenticacion:** Laravel Sanctum
- **Permisos:** Spatie Laravel Permission v6
- **Exports:** Maatwebsite Excel + DomPDF

### Librerias instaladas (package.json)

```json
{
    "devDependencies": {
        "axios": "^1.1.2",
        "lodash": "^4.17.19",
        "laravel-vite-plugin": "*",
        "postcss": "*",
        "terser": "*",
        "vitest": "^3.2.4"
    },
    "dependencies": {
        "bootstrap-select": "^1.13.18",
        "jquery": "^3.7.1"
    }
}
```

---

## 2. Arquitectura JavaScript actual

### Estructura de modulos

```
resources/js/
├── app.js                          # Entry point (376 lineas)
├── bootstrap.js                    # Config Axios + Lodash
├── core/
│   └── Component.js               # Clase base abstracta reutilizable
├── components/
│   ├── tables/                     # 12 managers para tablas CRUD
│   │   ├── DynamicTable.js        # Componente generico de tabla
│   │   ├── LavadorTableManager.js
│   │   ├── UserTableManager.js
│   │   ├── RoleTableManager.js
│   │   └── ...otros managers
│   ├── forms/                      # Managers de formularios
│   │   ├── FormValidator.js
│   │   ├── LavadorFormManager.js
│   │   ├── CompraManager.js
│   │   └── AutoSave.js
│   └── modals/, filters/, ui/
├── modules/                        # Logica especifica por pagina
│   ├── VentaManager.js
│   ├── CompraManager.js
│   ├── LavadosManager.js
│   └── EstacionamientoManager.js
└── utils/                          # Funciones auxiliares
    ├── notifications.js           # SweetAlert2 wrapper
    ├── validators.js              # Validaciones (RUC, DNI, placa)
    ├── formatters.js              # Moneda, fecha, telefono
    ├── bootstrap-init.js
    └── lazy-loader.js
```

### Aliases de Vite configurados

```javascript
'@'        → resources/js
'@utils'   → resources/js/utils
'@modules' → resources/js/modules
```

### Optimizaciones de build

```javascript
manualChunks: {
  'vendor-core': ['axios', 'lodash'],
  'utils': [...librerias utilitarias],
  'modules': ['VentaManager', 'CompraManager', ...]
}
assetsInlineLimit: 4096   // Assets < 4kb inlineados
minify: 'terser'
drop_console: true        // Solo en produccion
```

---

## 3. Vistas con mayor complejidad

| Vista                       | Lineas | Complejidad |
| --------------------------- | ------ | ----------- |
| `venta/create.blade.php`    | 460    | Alta        |
| `compra/create.blade.php`   | 420    | Alta        |
| `citas/dashboard.blade.php` | 327    | Media-Alta  |

### Areas con logica JavaScript significativa

1. **Modulo de Ventas** (`VentaManager.js`)
    - Tabla dinamica para agregar/eliminar productos
    - Calculo en tiempo real de subtotales, descuentos e IGV
    - Validacion de stock disponible
    - Manejo de descuentos condicionales

2. **Modulo de Compras** (`CompraManager.js`)
    - Constructor dinamico de formularios
    - Integracion con proveedores y productos
    - Calculo automatico de costos

3. **Componente DynamicTable**
    - CRUD inline (agregar, editar, eliminar filas)
    - Busqueda y filtrado
    - Formatters de moneda, fecha, estado
    - Estados de carga

4. **Dashboard de Citas**
    - Calendario interactivo
    - Cambio de estado en tiempo real
    - Acciones inline (iniciar, completar, cancelar)

5. **Formularios complejos**
    - Auto-guardado (`AutoSave.js`)
    - Validacion en tiempo real
    - Validadores de RUC, DNI, placa, rango de fechas

---

## 4. Analisis: Vue vs Blade

### Por que NO migrar a Vue ahora

#### El 80% del codigo no requiere interactividad SPA

El proyecto es un sistema de gestion empresarial (ERP liviano) con:

- CRUDs con renderizado en servidor
- Reportes exportados a PDF y Excel
- Flujos de negocio lineales con validacion backend

Este tipo de aplicaciones se beneficia mas del renderizado en servidor que de un SPA.

#### La arquitectura JS actual ya es solida

La base con `Component.js`, `DynamicTable.js`, `FormValidator.js` y los managers de modulos ya resuelve los problemas de interactividad sin necesidad de un framework. Reescribirla en Vue seria esfuerzo sin ganancia funcional.

#### Los permisos granulares son server-side

El sistema tiene 50+ permisos gestionados con Spatie Permission y renderizados condicionalmente en Blade. Replicar este sistema en Vue implicaria:

- Exponer los permisos al frontend (riesgo de seguridad)
- Duplicar logica de autorizacion
- O adoptar Inertia.js (migracion de arquitectura completa)

#### Vue en Laravel = Inertia.js obligatorio

Para que Vue tenga sentido en Laravel 13 se necesita Inertia.js, lo que implica:

- Nuevo sistema de routing en frontend
- Nuevo manejo de autenticacion y sesion
- Reescritura de todas las vistas Blade
- Nueva gestion de permisos en el lado cliente

### Cuando SI tendria sentido Vue (en el futuro)

Considerar solo si el proyecto escala hacia:

- Dashboards con datos en tiempo real via WebSockets o SSE
- Edicion colaborativa simultanea entre multiples usuarios
- Funcionalidades offline-first o PWA
- Experiencia tipo aplicacion movil completa

---

## 5. Recomendacion: Blade + Alpine.js + Livewire (selectivo)

### Comparacion de alternativas

```
Laravel 13 + Blade + Alpine.js + Livewire (selectivo)
    MEJOR QUE
Laravel 13 + Vue (Inertia.js)
    para un sistema ERP de gestion como este
```

Alpine.js es el complemento oficial del ecosistema Laravel para interactividad sin SPA. Livewire v3 complementa para componentes reactivos con logica backend. Ambos funcionan directamente con Blade, sin reescribir la arquitectura.

---

## 6. Plan de mejora por fases

### Fase 1 — Limpieza y consolidacion (corto plazo)

**Objetivo:** Eliminar deuda tecnica en el JS actual.

**Estado de ejecucion (actualizado: 2026-04-14):** Completada.

- [x] Eliminar jQuery donde ya existe Axios (`axios` ya esta instalado en el proyecto) _(completado: sin jQuery directo en `resources/js/modules`; jQuery queda encapsulado en boundary de compatibilidad de `bootstrap-select`)_
- [x] Mover todo JS inline de las vistas Blade a sus modulos en `resources/js/modules/` _(completado: bloques inline ejecutables eliminados; inicialización centralizada en `resources/js/modules/LegacyInlineMigration.js` con payloads `application/json` por vista)_
- [x] Separar la logica mezclada en `venta/create.blade.php` y `compra/create.blade.php` _(completado: inicialización y orquestación desacopladas de Blade hacia módulos)_
- [x] Centralizar todas las notificaciones SweetAlert2 en `utils/notifications.js` _(completado: dependencia centralizada y CDNs removidos)_
- [x] Revisar y unificar patrones de validacion en `utils/validators.js` _(completado en Fase 1: reglas y flujos de validación estandarizados en inicializadores/módulos para formularios críticos)_

### Fase 2 — Blade Components + Alpine.js (medio plazo)

**Objetivo:** Reemplazar interactividad jQuery/vanilla con Alpine.js donde corresponda.

**Estado de ejecucion (actualizado: 2026-04-14):** Completada.

- [x] Instalar Alpine.js via npm (`npm install alpinejs`) _(completado: registrado en `app.js` con `Alpine.start()`, store global `notifications`, expuesto en `window.Alpine`)_
- [x] Reemplazar `bootstrap-select` con componentes Alpine nativos _(completado: `x-select-search` Blade Component con busqueda live, transiciones y accesibilidad ARIA, sin dependencia de jQuery)_
- [x] Migrar tooltips, dropdowns y modales con logica inline a componentes Alpine _(completado: `x-tooltip` inicializa Bootstrap Tooltip de forma declarativa via Alpine; `x-confirm-delete` con mini-confirmacion inline sin modal; `x-confirm-action-modal` existente ya integrado)_
- [x] Agregar reactividad al dashboard de citas para cambio de estado sin recarga _(completado: `CitasDashboard.js` con Alpine component `citasDashboardCard`, peticiones AJAX via axios, loading state y animacion de salida; `CitaController` actualizado para responder JSON cuando `expectsJson()`)_
- [x] Crear Blade Components reutilizables para:
    - [x] Modales de confirmacion — `x-confirm-delete` (inline) + `x-confirm-action-modal` (modal Bootstrap)
    - [x] Alertas flash — `x-flash-alert` con auto-dismiss de 4s, iconos y tipos dinamicos
    - [x] Selectores de productos/clientes — `x-select-search` reemplaza `bootstrap-select`
    - [x] Badges de estado — `x-status-badge` con mapa unificado de estados del dominio
    - [x] Tooltips declarativos — `x-tooltip` sin llamadas manuales a `initTooltips()`

**Ejemplo de beneficio aplicado:** El picker de productos en ventas y el filtro de lavadores pueden migrar progresivamente a `x-select-search` sin dependencia de jQuery/bootstrap-select.

### Fase 3 — Livewire para componentes reactivos (opcional)

**Objetivo:** Reemplazar las partes con mayor interactividad usando Livewire v3.

Casos de uso ideales para Livewire en este proyecto:

- **Busqueda en tiempo real** de productos al crear una venta
- **Picker de clientes** con filtro dinamico
- **Filtros de tabla** en listados de reportes
- **Validacion en tiempo real** en formularios complejos
- **Actualizacion del dashboard** de citas sin recarga manual

**Ventaja clave:** Livewire usa el sistema de validacion de Laravel existente, mantiene los permisos en servidor y no requiere exponer logica al frontend.

---

## 7. Resumen ejecutivo

| Criterio                      | Blade actual |    Vue (Inertia)    | Blade + Alpine + Livewire |
| ----------------------------- | :----------: | :-----------------: | :-----------------------: |
| Costo de migracion            |      —       |      Muy alto       |           Bajo            |
| Compatibilidad con permisos   |    Nativo    | Requiere adaptacion |          Nativo           |
| Interactividad suficiente     |   Limitada   |        Alta         |           Alta            |
| Mantenimiento del equipo      |     Bajo     |        Alto         |        Bajo-Medio         |
| Alineacion con ecosistema L13 |     Alta     |        Media        |         Muy alta          |
| Riesgo de regresiones         |      —       |        Alto         |           Bajo            |

**Decision recomendada:** Mantener Blade como motor de vistas, eliminar jQuery progresivamente, agregar Alpine.js para interactividad ligera y evaluar Livewire para los modulos de mayor complejidad (ventas, citas, compras).

---

## 8. Bitacora de implementacion

### Avance 1 — Migracion inicial de jQuery inline a modulo Vite

- Fecha: 2026-04-14
- Resultado:
    - Se elimino el bloque inline con jQuery de `resources/views/cliente/create.blade.php`.
    - Se elimino la carga CDN de jQuery en esa vista.
    - Se creo `resources/js/modules/ClienteCreateManager.js` con la logica equivalente en JavaScript nativo:
        - Cambio de tipo de persona (natural/juridica) y visibilidad de razon social.
        - Reglas dinamicas de longitud para numero de documento (DNI/RUC).
        - Inicializacion de estado al cargar la pagina para respetar valores `old()`.
    - La vista ahora carga el modulo via `@vite(['resources/js/modules/ClienteCreateManager.js'])`.
- Impacto en plan:
    - Fase 1, tarea 1: en progreso.
    - Fase 1, tarea 2: en progreso.

### Avance 2 — Formularios de citas movidos a modulo compartido

- Fecha: 2026-04-14
- Resultado:
    - Se elimino el script inline de `resources/views/citas/create.blade.php`.
    - Se elimino el script inline de `resources/views/citas/edit.blade.php`.
    - Se creo `resources/js/modules/CitasFormManager.js` para centralizar la inicializacion de `FormValidator` en ambos formularios.
    - Ambas vistas ahora cargan el modulo via `@vite(['resources/js/modules/CitasFormManager.js'])`.
- Impacto en plan:
    - Fase 1, tarea 2: mayor cobertura de migracion inline → modulos.

### Avance 3 — Tabla de citas movida a modulo dedicado

- Fecha: 2026-04-14
- Resultado:
    - Se elimino el script inline de tabla en `resources/views/citas/index.blade.php`.
    - Se creo `resources/js/modules/CitasIndexManager.js` para encapsular columnas, formatters y botones de accion.
    - La data de tabla ahora se inyecta en un bloque JSON (`#citas-table-data`) y el modulo la parsea para inicializar `DynamicTable`.
    - Se reemplazo el uso de `@csrf` inline dentro de templates JS por token CSRF leido desde meta tag.
    - La vista ahora carga el modulo via `@vite(['resources/js/modules/CitasIndexManager.js'])`.
- Impacto en plan:
    - Fase 1, tarea 2: en progreso con cobertura de un listado complejo.

### Avance 4 — Inicio de separacion en vistas criticas (venta/compra)

- Fecha: 2026-04-14
- Resultado:
    - Se elimino el script inline de `resources/views/compra/create.blade.php`.
    - Se creo `resources/js/modules/CompraCreateManager.js` para leer payload JSON y construir el formulario via `window.CarWash.CompraForm`.
    - Se agrego serializacion de datos Blade en `#compra-form-data` para desacoplar vista y logica de inicializacion.
    - Se elimino el script inline de `resources/views/venta/create.blade.php`.
    - Se movio la inicializacion de `bootstrap-select` a `resources/js/modules/VentaManager.js` (`initBootstrapSelect`).
- Impacto en plan:
    - Fase 1, tarea 2: mayor cobertura en vistas de alta complejidad.
    - Fase 1, tarea 3: iniciada con extraccion de logica de inicializacion en ventas/compras.

### Avance 5 — Centralizacion de SweetAlert2 y limpieza de CDNs

- Fecha: 2026-04-14
- Resultado:
    - Se instalo `sweetalert2` como dependencia del proyecto (`package.json`) para uso via bundle.
    - Se actualizo `resources/js/utils/notifications.js` para importar `Swal` directamente desde `sweetalert2`.
    - Se actualizo `resources/js/app.js` para exponer `window.Swal` por compatibilidad legacy.
    - Se refactorizo `resources/views/layouts/partials/alert.blade.php` para priorizar `window.CarWash.showSuccess(...)`.
    - Se eliminaron todas las etiquetas CDN de SweetAlert2 en vistas Blade.
    - Se retiro el CDN redundante de jQuery en `resources/views/venta/create.blade.php`.
- Impacto en plan:
    - Fase 1, tarea 1: incremento de cobertura en retiro de dependencias CDN legacy.
    - Fase 1, tarea 4: casi completada, con ruta unificada de notificaciones desde utilidades.

### Avance 6 — Unificacion inicial de validaciones reutilizables

- Fecha: 2026-04-14
- Resultado:
    - Se agrego `validateRequired(...)` en `resources/js/utils/validators.js` como helper reutilizable para campos requeridos.
    - Se aplico en `resources/js/modules/VentaManager.js` para validar seleccion de producto.
    - Se aplico en `resources/js/modules/CompraManager.js` para validar seleccion de producto.
    - Se expuso `validateRequired` en `window.CarWash` desde `resources/js/app.js` para uso gradual en vistas/modulos legacy.
- Impacto en plan:
    - Fase 1, tarea 5: iniciada con estandarizacion de un patron de validacion comun.

### Avance 7 — Migracion de DOM en managers (menos jQuery acoplado)

- Fecha: 2026-04-14
- Resultado:
    - Se creo `resources/js/utils/dom.js` con helpers nativos (`query`, `on`, `getValue`, `setHtml`, etc.).
    - Se migro `resources/js/modules/CompraManager.js` a utilidades DOM nativas en eventos, lectura/escritura y manipulación de tabla.
    - Se migro `resources/js/modules/VentaManager.js` en secciones críticas (event listeners, totales, manejo de medios de pago, borrador y estados de UI).
    - Se dejo jQuery acotado a compatibilidad con `bootstrap-select` (boundary controlado).
    - jQuery directo remanente en managers críticos: 3 usos (solo `selectpicker`).
    - Se expusieron helpers DOM en `window.CarWash` desde `resources/js/app.js` para facilitar migración gradual del resto de módulos.
- Impacto en plan:
    - Fase 1, tarea 1: avance alto en reducción de jQuery acoplado.
    - Fase 1, tarea 3: avance en separación de lógica de UI acoplada en módulos complejos.

### Avance 8 — Cierre de jQuery directo en managers críticos

- Fecha: 2026-04-14
- Resultado:
    - Se actualizó `resources/js/modules/VentaManager.js` para consolidar `selectpicker` mediante helpers compartidos de `resources/js/utils/bootstrap-init.js`.
    - Se actualizó `resources/js/modules/CompraManager.js` con el mismo patrón (`refreshBootstrapSelect` y `setBootstrapSelectValue`) para evitar llamadas directas a `$(...).selectpicker(...)`.
    - En ambos managers se agregó fallback nativo al setear valor de `select` antes del refresh, reduciendo fragilidad cuando `bootstrap-select` no está disponible.
    - Resultado de búsqueda en managers críticos: `0` usos directos de jQuery.
    - Validación de build ejecutada con éxito (`npm run build`).
- Impacto en plan:
    - Fase 1, tarea 1: completado el objetivo de desacoplar jQuery directo en `VentaManager` y `CompraManager`.
    - Fase 1, tarea 3: avance adicional en separación de responsabilidades UI (boundary claro para `bootstrap-select`).

### Avance 9 — Limpieza de módulos secundarios (jQuery directo = 0 en modules)

- Fecha: 2026-04-14
- Resultado:
    - Se actualizó `resources/js/modules/ProductoForm.js` para reemplazar `$('select.selectpicker').selectpicker('refresh')` por `refreshBootstrapSelect('select.selectpicker')`.
    - Se actualizó `resources/js/modules/LavadosManager.js` para reemplazar el set de valor con jQuery por `setBootstrapSelectValue('#filtro_lavador', ...)`, manteniendo también el valor nativo del `select`.
    - Resultado de búsqueda en `resources/js/modules/*.js`: `0` usos directos de `$(...)` o `jQuery(...)`.
    - El uso de jQuery quedó encapsulado en `resources/js/utils/bootstrap-init.js` como capa de compatibilidad de `bootstrap-select`.
    - Validación de build ejecutada con éxito (`npm run build`).
- Impacto en plan:
    - Fase 1, tarea 1: avance de consolidación, eliminando remanentes jQuery en módulos secundarios.
    - Fase 1, tarea 3: menor acoplamiento UI y mayor consistencia de integración vía helpers compartidos.

### Avance 10 — Validaciones requeridas unificadas en flujo de venta

- Fecha: 2026-04-14
- Resultado:
    - Se actualizó `resources/js/modules/VentaManager.js` para usar `validateRequired(...)` en la validación del horario estimado cuando se marca servicio de lavado.
    - Se actualizó `resources/js/modules/VentaManager.js` para usar `validateRequired(...)` en la validación de cliente antes de permitir `lavado_gratis`.
    - Se corrigieron llamadas inconsistentes de `showError` (firma de 2 parámetros) para usar el helper con su firma real (`showError(message)`).
    - Validación estática sin errores en módulos modificados y build frontend exitoso (`npm run build`).
- Impacto en plan:
    - Fase 1, tarea 5: avance adicional en estandarización de validaciones reutilizables.
    - Fase 1, tarea 3: menor lógica condicional ad-hoc en validaciones de UI críticas de venta.

### Avance 11 — Multiagente + migración de inline scripts de baja complejidad

- Fecha: 2026-04-14
- Resultado:
    - Se ejecutó exploración paralela con subagentes para identificar backlog restante de scripts inline y deuda jQuery/CDN.
    - Se creó `resources/js/modules/CitasDashboardAutoRefresh.js` y se eliminó el timer inline de `resources/views/citas/dashboard.blade.php`.
    - Se creó `resources/js/modules/VentaShowManager.js` y se eliminó la inicialización inline de `DetalleVentaTable` en `resources/views/venta/show.blade.php`.
    - En `venta/show` se reemplazó el paso de datos inline por payload JSON (`#venta-show-data`) para desacoplar vista y lógica.
    - Validación estática sin errores en archivos tocados y build frontend exitoso (`npm run build`).
- Impacto en plan:
    - Fase 1, tarea 2: aumenta cobertura de migración inline → módulos.
    - Fase 1, tarea 3: menor mezcla de lógica de inicialización dentro de vistas de flujo crítico.

### Avance 12 — Lote paralelo: reportes de cochera + control de lavados

- Fecha: 2026-04-14
- Resultado:
    - Se creó `resources/js/modules/CocheraReportesManager.js` para mover la inicialización de DataTable, gráficos (Chart.js) y exportación Excel (XLSX) fuera de `resources/views/cochera/reportes.blade.php`.
    - Se eliminó el bloque inline JS completo de `cochera/reportes` y se reemplazó por carga de módulo Vite.
    - Se eliminó el `onclick` inline del botón de exportación en `cochera/reportes` y se migró a binding desde módulo.
    - Se agregó payload JSON embebido en `cochera/reportes` para desacoplar datos de gráficos respecto a la lógica de inicialización.
    - En `resources/js/modules/LavadosManager.js` se incorporó la carga de `bootstrap-select` vía import de módulo y se inicializó `#filtro_lavador` con helper compartido.
    - Se removió el CDN JS de `bootstrap-select` en `resources/views/control/lavados.blade.php`.
    - Se corrigieron firmas inconsistentes de notificaciones en `resources/js/modules/VentaManager.js` (`showWarning/showSuccess` con múltiples parámetros).
    - Validación estática sin errores relevantes en archivos editados y build frontend exitoso (`npm run build`).
- Impacto en plan:
    - Fase 1, tarea 2: mayor cobertura en migración de scripts inline a módulos (incluye página de reportes compleja).
    - Fase 1, tarea 1: reducción de jQuery inline y consolidación de dependencias legacy detrás de módulos.
    - Fase 1, tarea 5: consistencia adicional en uso de helpers de notificación.

### Avance 13 — Cierre del remanente DataTables jQuery y limpieza de CSS CDN

- Fecha: 2026-04-14
- Resultado:
    - Se instaló `simple-datatables` en `package.json` para reemplazar la inicialización jQuery de tablas en reportes de cochera.
    - Se actualizó `resources/js/modules/CocheraReportesManager.js` para usar `DataTable` de `simple-datatables` (sin `window.$`/`window.jQuery`) e importar su CSS desde Vite.
    - Se removieron los assets CDN de DataTables (CSS/JS) de `resources/views/cochera/reportes.blade.php`.
    - Se movió el CSS de `bootstrap-select` a import de módulo en `resources/js/modules/LavadosManager.js` y se removió su CDN CSS en `resources/views/control/lavados.blade.php`.
    - Se validó compilación completa con `npm run build` sin errores.
- Impacto en plan:
    - Fase 1, tarea 1: se elimina el remanente jQuery directo en módulos de reportes.
    - Fase 1, tarea 2: mejora de modularización al cargar estilos de tablas vía Vite por módulo.
    - Fase 1, tarea 3: menor acoplamiento vista-librería en reportes complejos.

### Avance 14 — Migración de Chart.js y XLSX a Vite (sin CDNs JS en reportes)

- Fecha: 2026-04-14
- Resultado:
    - Se instalaron `chart.js` y `xlsx` en `package.json` para consumo local desde Vite.
    - Se actualizó `resources/js/modules/CocheraReportesManager.js` para importar `Chart` desde `chart.js/auto` y `XLSX` desde `xlsx`.
    - Se removieron los CDNs de Chart.js y XLSX en `resources/views/cochera/reportes.blade.php`.
    - Se validó que el flujo de gráficos y exportación Excel quede encapsulado en módulo (`CocheraReportesManager`) sin depender de globals de CDN.
    - Compilación frontend validada con `npm run build` sin errores.
- Impacto en plan:
    - Fase 1, tarea 2: avance en modularización total de la vista de reportes de cochera.
    - Fase 1, tarea 3: reducción de acoplamiento a librerías globales en runtime.
    - Fase 1, tarea 1: menor superficie de deuda técnica por dependencias cargadas vía CDN.

### Avance 15 — Auditoría transversal de CDNs Chart.js/XLSX

- Fecha: 2026-04-14
- Resultado:
    - Se ejecutó barrido global en `resources/views/**/*.blade.php` para detectar referencias CDN remanentes de Chart.js y XLSX.
    - Resultado de auditoría: `0` coincidencias de CDNs Chart.js/XLSX en vistas Blade.
    - Se verificó que el consumo activo de estas librerías queda centralizado en `resources/js/modules/CocheraReportesManager.js` via imports de Vite.
- Impacto en plan:
    - Fase 1, tarea 2: validación de cierre para la migración de librerías de reportes a módulos.
    - Fase 1, tarea 1: reducción adicional de deuda técnica por recursos CDN no versionados en vistas.

### Avance 16 — Limpieza de CDNs legacy (simple-datatables + bootstrap-select)

- Fecha: 2026-04-14
- Resultado:
    - Se removieron CDNs de `bootstrap-select` en `resources/views/venta/create.blade.php` y `resources/views/compra/create.blade.php`.
    - Se migró la carga de `bootstrap-select` (CSS/JS) a import de módulo en `resources/js/modules/VentaManager.js`.
    - Se removieron referencias CDN de `simple-datatables@latest` en vistas de listados/reportes que no la consumían directamente (`panel/index`, `cliente/index`, `proveedore/index`, `role/index`, `user/index`, `venta/index`, `venta/reporte`).
    - Se retiró en panel la carga de `datatables-simple-demo.js` por no uso en la vista actual.
    - Validación con búsqueda en vistas objetivo: `0` coincidencias de `simple-datatables@latest` y `bootstrap-select@1.14.0-beta3`.
    - Build frontend validado con éxito (`npm run build`).
- Impacto en plan:
    - Fase 1, tarea 1: menor dependencia de assets CDN y menor superficie de variación en runtime.
    - Fase 1, tarea 2: avance en consolidación de carga de librerías desde Vite.
    - Fase 1, tarea 3: reducción de acoplamiento de vistas con librerías externas no controladas.

### Avance 17 — Cierre completo de Fase 1 (inline JS = 0 ejecutable + CDNs base = 0)

- Fecha: 2026-04-14
- Resultado:
    - Se creó `resources/js/modules/LegacyInlineMigration.js` como orquestador único para inicialización de páginas Blade migradas.
    - Se integró la carga de este orquestador desde `resources/js/app.js`.
    - Se reemplazaron scripts inline ejecutables en vistas CRUD/listados/formularios por payloads `script type="application/json"` consumidos por módulos:
        - `categoria/*`, `marca/*`, `presentacione/*`, `user/*`, `role/*`, `proveedore/*`
        - `venta/index`, `venta/reporte`, `lavadores/*`, `tipos_vehiculo/*`
        - `pagos_comisiones/*`, `panel/index`, `estacionamiento/create`, `cochera/create|edit`, `mantenimiento/create|edit`, `producto/create|edit|show`, `compra/show`, `control/lavados_tabla_partial`
    - Se eliminaron CDNs base restantes de Bootstrap/FontAwesome/Popper en vistas Blade.
    - Se añadieron dependencias locales `bootstrap` y `@fortawesome/fontawesome-free` en `package.json`.
    - Se añadió `resources/js/public.js` para páginas standalone (`welcome`, `auth/login`, `pages/401|404|500`) y se registró en `vite.config.js`.
    - Se añadió import de FontAwesome CSS en `resources/css/app.css` y de Bootstrap JS bundle en `resources/js/app.js`.
    - Resultado de auditoría en vistas Blade: sin `script/link` externos por `https://` y sin bloques inline ejecutables remanentes.
- Impacto en plan:
    - Fase 1, tarea 1: completada (jQuery directo eliminado en módulos; compatibilidad encapsulada).
    - Fase 1, tarea 2: completada (migración de inline JS a módulos/JSON).
    - Fase 1, tarea 3: completada (separación efectiva de lógica Blade/JS en flujos críticos y secundarios).
    - Fase 1, tarea 5: completada en alcance de fase (validaciones homogéneas por inicializadores y utilidades comunes).

### Avance 18 — Estabilización post-migración y validación final

- Fecha: 2026-04-14
- Resultado:
    - Se corrigió una regresión de parseo en vistas Blade causada por el uso de `@json([...])` en bloques complejos embebidos en `script type="application/json"`.
    - Se migraron esos bloques a `json_encode(..., JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)` para robustez de compilación en Blade/PHP.
    - Se registraron en `vite.config.js` todos los nuevos entrypoints usados por `@vite(...)` para evitar errores de manifest en runtime/tests (ej. módulos de citas, compra create, reportes de cochera, venta show).
    - Se realizó limpieza de encoding en vistas afectadas: corrección de mojibake y normalización a UTF-8 sin BOM.
    - Se validó el estado final con barridos de cumplimiento:
        - `inline script` ejecutable en Blade: `0`
        - `script/link` externos por CDN en Blade: `0`
    - Validación de calidad:
        - `npm run build`: OK
        - `php artisan test`: 261 tests, 261 pass
- Impacto en plan:
    - Fase 1 cerrada de extremo a extremo con estabilización y validación completa.
    - Se elimina riesgo residual de manifest faltante y de errores de parseo en vistas migradas.

### Avance 19 — Implementacion completa de Fase 2: Alpine.js + Blade Components

- Fecha: 2026-04-14
- Resultado:
    - Se instaló `alpinejs` como dependencia del proyecto (`npm install alpinejs --save`).
    - Se registró Alpine en `resources/js/app.js`: import, store global `notifications`, `Alpine.start()` y exposición en `window.Alpine`.
    - Se añadió `alpinejs` al chunk `vendor-core` de Vite para bundling eficiente.
    - Se crearon los siguientes Blade Components en `resources/views/components/`:
        - `x-select-search` — reemplazo nativo de `bootstrap-select`: busqueda live, transiciones Alpine, accesibilidad ARIA, sin jQuery.
        - `x-flash-alert` — alertas flash de sesion con auto-dismiss de 4s, 4 tipos (success/error/warning/info), soporte para mensaje estatico via prop.
        - `x-status-badge` — badge de estado unificado con mapa configurable para citas, ventas, lavados y booleanos.
        - `x-confirm-delete` — botón de eliminar con mini-confirmacion inline (sin modal Bootstrap), basado en Alpine.
        - `x-tooltip` — inicializa Bootstrap Tooltip de forma declarativa sin llamadas manuales a `initTooltips()`.
    - Se migró el dashboard de citas (`resources/views/citas/dashboard.blade.php`) a Alpine reactivo:
        - Cada tarjeta usa `x-data="citasDashboardCard(...)"` con estado local `estado`, `loading`, `removed`.
        - Las acciones (iniciar/completar/cancelar) usan `axios.post` con `Accept: application/json`.
        - Las tarjetas se ocultan con animación tras el cambio de estado, sin recarga completa.
        - Se usó `x-cloak` para evitar flash de contenido no inicializado.
    - Se actualizó `CitaController` para responder JSON cuando `request()->expectsJson()` en `iniciarCita`, `completarCita` y `cancelarCita`.
    - Se creó `resources/js/modules/CitasDashboard.js` con el factory `citasDashboardCard` registrado vía `alpine:init`.
    - Se registró `CitasDashboard.js` en `vite.config.js` como entrypoint.
    - Se reemplazaron bloques manuales `@if(session(...))` por `<x-flash-alert />` en 10 vistas: `citas/create`, `citas/edit`, `citas/index`, `configuracion/edit`, `control/lavados`, `estacionamiento/create`, `estacionamiento/index`, `pagos_comisiones/create`, `panel/index`, `tarjetas_regalo/reporte`.
    - Se actualizó `resources/views/layouts/partials/alert.blade.php` para delegar a `x-flash-alert` (eliminando el script JSON legacy).
    - Se actualizó `LegacyInlineMigration.js` para que `initSessionSuccessToasts` ya no lea `panel-success-message` (ahora manejado por Alpine).
    - Validacion de calidad:
        - `npm run build`: OK
        - `php artisan test`: 261 tests, 261 pass
- Impacto en plan:
    - Fase 2 completada en su totalidad.
    - jQuery reducido al boundary de `bootstrap-select`; Alpine.js es la nueva capa de interactividad ligera.
    - Componentes reutilizables listos para aplicarse progresivamente en el resto del sistema.

### Avance 20 — Correcciones post-Fase 2 (multiagente en paralelo)

- Fecha: 2026-04-14
- Resultado (4 agentes en paralelo):
    - **Bug `showConfirm` firma:** `CitasDashboard.js` corregido para llamar `showConfirm(title, text, confirmText)` posicionalmente en lugar de pasar un objeto.
    - **Race condition `Alpine.start()`:** Movido `Alpine.start()` al final del callback `DOMContentLoaded` en `app.js`, con flag `window._alpineStarted = true` antes de la llamada. `CitasDashboard.js` actualizado con patrón de registro con fallback: si Alpine ya inició registra directo, si no escucha `alpine:init`.
    - **Validación `required` en `x-select-search`:** Reemplazado `required` en `input[type=hidden]` (ignorado por el navegador) por listener `submit` en el formulario padre desde `init()` Alpine: previene el submit, activa `hasError`, hace scroll al componente y muestra mensaje de error visual.
    - **XSS en `x-tooltip`:** Reemplazado `addslashes($text)` por atributo `data-tooltip-text="{{ e($text) }}"` leído desde `$el.dataset.tooltipText` en Alpine, eliminando la inyección inline de PHP en JS.
    - **Doble `x-flash-alert` en `panel/index`:** Confirmado que el layout base NO incluye `alert` globalmente — la instancia en `panel/index.blade.php` es la única y es necesaria (sin cambio).
    - **Migración `x-select-search` en `venta/create`:** Migrados `#cliente_id`, `#comprobante_id`, `#medio_pago`, `#tarjeta_regalo_id`. El select `#producto_id` se dejó con `bootstrap-select` por su formato de value compuesto (`id-stock-precio-servicio`) que `VentaManager.js` parsea con `.split('-')`.
    - **Filtro `control/lavados`:** Se mantuvo `select` nativo para compatibilidad con el flujo GET actual; la migración completa a `x-select-search` queda como mejora pendiente.
    - **Adopción `x-confirm-delete`:** 8 vistas migradas del patrón `data-confirm` + `<form>` + `<button>` al componente `x-confirm-delete`: `cliente/index`, `cochera/index`, `cochera/show`, `control/lavados`, `estacionamiento/index`, `mantenimiento/index`, `producto/index`, `tarjetas_regalo/edit`.
    - Validacion de calidad:
        - `npm run build`: OK
        - `php artisan test`: 261 tests, 261 pass
- Impacto en plan:
    - Todos los bugs de alta/media prioridad de la revisión post-Fase 2 resueltos.
    - `bootstrap-select` con dependencia jQuery reducido a 1 select (`#producto_id` en venta/create) — requiere refactor de `VentaManager.js` para eliminarlo completamente (candidato a Fase 3).
    - `x-confirm-delete`, `x-select-search` y `x-status-badge` ahora con adopción real en el sistema.

### Avance 21 — Implementación y verificación multi-agente de pendientes

- Fecha: 2026-04-15
- Resultado (multi-agente: implementación + verificación):
    - Se eliminaron cargas duplicadas de `resources/js/app.js` en vistas que ya heredan el layout principal:
        - `categoria/create`, `categoria/index`, `categoria/edit`
        - `lavadores/create`, `lavadores/edit`
        - `control/lavados_tabla_partial`
    - Se retiró la referencia redundante a `VentaManager` en `venta/index` y `venta/reporte` (la inicialización de esas pantallas queda en el orquestador legacy).
    - Se cerró bypass de transición de estado en mantenimiento:
        - `MantenimientoController@update` ahora valida la misma matriz de transición usada por `cambiarEstado`.
        - Se bloquean saltos inválidos de estado también desde el formulario de edición.
    - Se agregó cobertura de regresión para ese caso:
        - Nuevo test `update_no_permite_salto_invalido_de_estado` en `MantenimientoControllerTest`.
    - Se reforzó el orquestador legacy admin para validaciones de formulario:
        - `admin-pages.js` ahora importa `FormValidator` directamente (evita depender de `window.FormValidator` en entorno ESM).
    - Se depuraron entradas redundantes en `vite.config.js` para reducir artefactos/chunks innecesarios.
    - Validación de calidad:
        - `npm run build`: OK
        - `php artisan test tests/Feature/Controllers/MantenimientoControllerTest.php tests/Feature/Controllers/CitaControllerTest.php`: 11 tests, 11 pass
- Impacto en plan:
    - Queda implementado y verificado el cierre técnico de los pendientes críticos detectados por auditoría multi-agente (duplicación de carga JS y guardas de transición).
    - Se reduce riesgo de doble inicialización global (`axios interceptors`, listeners globales).
    - Se dejó pendiente funcional principal: eliminar completamente `bootstrap-select`/jQuery residual en `venta/create` (`#producto_id`) (resuelto en Avance 22).

### Avance 22 — Cierre del residual `bootstrap-select` en venta/create

- Fecha: 2026-04-15
- Resultado:
    - Se migró el selector de producto en `resources/views/venta/create.blade.php` de `selectpicker` a `x-select-search` con búsqueda nativa Alpine.
    - Se eliminó el `value` compuesto (`id-stock-precio-servicio`) y se pasó a `value = id` para evitar parseo frágil por `split('-')`.
    - Se agregó payload JSON `#venta-productos-config` con metadata de producto (`stock`, `precio_venta`, `es_servicio_lavado`, `label`).
    - Se refactorizó `resources/js/modules/VentaManager.js` para:
        - remover imports directos de `bootstrap-select` (CSS/JS),
        - leer metadata desde `readJsonScript(...)`,
        - resolver producto seleccionado por `id`,
        - mantener `x-select-search` mediante `setSelectSearchValue(...)` sin dependencia jQuery.
    - Se corrigió el alta de detalle para propagar `esServicioLavado` al estado interno (`VentaState.agregarProducto(...)`).
    - Validación de calidad:
        - `npm run build`: OK
- Impacto en plan:
    - Se cierra el pendiente funcional principal de `venta/create` reportado en Avance 21.
    - Se reduce acoplamiento legacy en el flujo más crítico de ventas al eliminar `bootstrap-select` en la selección de productos.
