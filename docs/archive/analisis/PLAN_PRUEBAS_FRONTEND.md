# 🧪 PLAN DE PRUEBAS EXHAUSTIVO - FRONTEND CARWASH ESP

**Fecha:** 21 de Octubre, 2025  
**Versión:** 1.0  
**Responsable:** Equipo de Desarrollo  
**Basado en:** ANALISIS_FRONTEND_COMPLETO.md

---

## 📋 ÍNDICE

1. [Estrategia de Pruebas](#estrategia-de-pruebas)
2. [Pruebas de Flujos Críticos](#pruebas-de-flujos-críticos)
3. [Pruebas de Performance](#pruebas-de-performance)
4. [Pruebas de Usabilidad](#pruebas-de-usabilidad)
5. [Pruebas de Accesibilidad](#pruebas-de-accesibilidad)
6. [Pruebas de Compatibilidad](#pruebas-de-compatibilidad)
7. [Pruebas de Seguridad Frontend](#pruebas-de-seguridad-frontend)
8. [Métricas y KPIs](#métricas-y-kpis)

---

## 🎯 ESTRATEGIA DE PRUEBAS

### Pirámide de Testing Frontend

```
                 ┌─────────────┐
                 │   E2E (10%) │  ← Playwright/Cypress
                 ├─────────────┤
                 │ Integration │  ← Testing Library
                 │    (30%)    │
                 ├─────────────┤
                 │    Unit     │  ← Jest/Vitest
                 │    (60%)    │
                 └─────────────┘
```

### Herramientas Propuestas

| Categoría         | Herramienta           | Justificación                        |
| ----------------- | --------------------- | ------------------------------------ |
| Unit Testing      | **Vitest**            | Rápido, compatible con Vite, moderno |
| Integration       | **Testing Library**   | Best practices, enfoque en usuario   |
| E2E               | **Playwright**        | Multi-browser, rápido, screenshots   |
| Performance       | **Lighthouse CI**     | Métricas estándar, automatizable     |
| Accesibilidad     | **axe-core**          | Estándar de la industria             |
| Visual Regression | **Percy / Chromatic** | Detecta cambios visuales             |

---

## 🔥 PRUEBAS DE FLUJOS CRÍTICOS

### 1. FLUJO DE VENTA COMPLETA

#### 1.1 Pruebas End-to-End

**Test Suite: Venta Completa - Caso Feliz**

```javascript
// tests/e2e/venta-completa.spec.js
import { test, expect } from "@playwright/test";

test.describe("Flujo de Venta Completa", () => {
    test.beforeEach(async ({ page }) => {
        // Login y navegación
        await page.goto("/login");
        await page.fill("#email", "admin@carwash.com");
        await page.fill("#password", "password");
        await page.click('button[type="submit"]');
        await page.waitForURL("/panel");

        // Ir a crear venta
        await page.goto("/ventas/create");
    });

    test("CVE-001: Crear venta con producto normal", async ({ page }) => {
        // Paso 1: Seleccionar producto
        await page.click("#producto_id");
        await page.fill(".bs-searchbox input", "Shampoo");
        await page.click("text=Shampoo Premium");

        // Verificar que se cargó el stock y precio
        const stock = await page.inputValue("#stock");
        expect(parseInt(stock)).toBeGreaterThan(0);

        const precioVenta = await page.inputValue("#precio_venta");
        expect(parseFloat(precioVenta)).toBeGreaterThan(0);

        // Paso 2: Ingresar cantidad
        await page.fill("#cantidad", "2");

        // Paso 3: Agregar producto
        await page.click("#btn_agregar");

        // Verificar que se agregó a la tabla
        const filas = await page.locator("#tabla_detalle tbody tr").count();
        expect(filas).toBe(1);

        // Paso 4: Seleccionar cliente
        await page.click("#cliente_id");
        await page.click("text=Cliente General");

        // Paso 5: Seleccionar comprobante
        await page.selectOption("#comprobante_id", { label: "Boleta" });

        // Paso 6: Verificar totales
        const total = await page.textContent("#total");
        expect(parseFloat(total)).toBeGreaterThan(0);

        // Paso 7: Guardar venta
        await page.click("#guardar");

        // Verificar redirección y mensaje de éxito
        await expect(page).toHaveURL(/\/ventas\/\d+/);
        await expect(page.locator(".alert-success")).toBeVisible();
    });

    test("CVE-002: Crear venta con servicio de lavado", async ({ page }) => {
        // Seleccionar servicio de lavado
        await page.click("#producto_id");
        await page.click("text=Lavado Premium");

        // Ingresar cantidad (debe permitir aunque no tenga stock)
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Marcar como servicio de lavado
        await page.check("#servicio_lavado");

        // Verificar que aparece el campo de horario
        await expect(page.locator("#horario_lavado_div")).toBeVisible();

        // Ingresar horario
        await page.fill("#horario_lavado", "14:30");

        // Completar venta
        await page.click("#cliente_id");
        await page.click("text=Cliente VIP");
        await page.selectOption("#comprobante_id", { label: "Boleta" });
        await page.click("#guardar");

        await expect(page.locator(".alert-success")).toBeVisible();
    });

    test("CVE-003: Validación de stock insuficiente", async ({ page }) => {
        // Seleccionar producto con stock bajo
        await page.click("#producto_id");
        await page.click("text=Producto Stock Bajo");

        const stock = await page.inputValue("#stock");
        const stockNum = parseInt(stock);

        // Intentar agregar más de lo disponible
        await page.fill("#cantidad", (stockNum + 10).toString());
        await page.click("#btn_agregar");

        // Verificar que aparece el mensaje de error
        await expect(page.locator(".swal2-popup")).toBeVisible();
        await expect(page.locator(".swal2-popup")).toContainText(
            "stock disponible"
        );
    });

    test("CVE-004: Eliminar producto de la lista", async ({ page }) => {
        // Agregar producto
        await page.click("#producto_id");
        await page.click("text=Shampoo");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Verificar que se agregó
        let filas = await page.locator("#tabla_detalle tbody tr").count();
        expect(filas).toBe(1);

        // Eliminar producto
        await page.click("#tabla_detalle tbody tr button.btn-danger");

        // Verificar que se eliminó
        const totalText = await page.textContent("#total");
        expect(parseFloat(totalText)).toBe(0);

        // Verificar que los botones se ocultan
        await expect(page.locator("#guardar")).not.toBeVisible();
    });

    test("CVE-005: Cancelar venta con confirmación", async ({ page }) => {
        // Agregar productos
        await page.click("#producto_id");
        await page.click("text=Shampoo");
        await page.fill("#cantidad", "2");
        await page.click("#btn_agregar");

        // Intentar cancelar
        await page.click("#cancelar");

        // Verificar que aparece el modal de confirmación
        await expect(page.locator("#exampleModal")).toBeVisible();

        // Confirmar cancelación
        await page.click("#btnCancelarVenta");

        // Verificar que se limpia todo
        const total = await page.textContent("#total");
        expect(parseFloat(total)).toBe(0);
    });

    test("CVE-006: Cálculo de IGV con factura", async ({ page }) => {
        // Agregar producto
        await page.click("#producto_id");
        await page.click("text=Shampoo");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Obtener suma antes de IGV
        const sumas = await page.textContent("#sumas");

        // Cambiar a Factura
        await page.selectOption("#comprobante_id", { label: "Factura" });

        // Marcar checkbox de IGV
        await page.check("#con_igv");

        // Verificar que el IGV se calcula
        const igv = await page.textContent("#igv");
        const igvNum = parseFloat(igv);
        const sumasNum = parseFloat(sumas);

        expect(igvNum).toBe(sumasNum * 0.18);

        // Verificar que el total incluye IGV
        const total = await page.textContent("#total");
        expect(parseFloat(total)).toBe(sumasNum + igvNum);
    });

    test("CVE-007: Método de pago - Tarjeta de Regalo", async ({ page }) => {
        // Agregar producto
        await page.click("#producto_id");
        await page.click("text=Lavado Express");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Seleccionar método de pago
        await page.selectOption("#medio_pago", "tarjeta_regalo");

        // Verificar que aparece el campo de código
        await expect(page.locator("#tarjeta_regalo_div")).toBeVisible();
        await expect(page.locator("#tarjeta_regalo_codigo")).toBeVisible();

        // Verificar que es requerido
        const isRequired = await page
            .locator("#tarjeta_regalo_codigo")
            .getAttribute("required");
        expect(isRequired).toBe("");
    });

    test("CVE-008: Validar múltiples productos", async ({ page }) => {
        // Agregar primer producto
        await page.click("#producto_id");
        await page.click("text=Shampoo");
        await page.fill("#cantidad", "2");
        await page.click("#btn_agregar");

        // Agregar segundo producto
        await page.click("#producto_id");
        await page.click("text=Cera");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Agregar tercer producto
        await page.click("#producto_id");
        await page.click("text=Lavado Premium");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Verificar que hay 3 filas
        const filas = await page.locator("#tabla_detalle tbody tr").count();
        expect(filas).toBe(3);

        // Verificar que los totales son correctos
        const total = await page.textContent("#total");
        expect(parseFloat(total)).toBeGreaterThan(0);
    });

    test("CVE-009: Performance - Tiempo de carga", async ({ page }) => {
        const startTime = Date.now();

        await page.goto("/ventas/create");
        await page.waitForLoadState("networkidle");

        const loadTime = Date.now() - startTime;

        // La página debe cargar en menos de 3 segundos
        expect(loadTime).toBeLessThan(3000);
    });

    test("CVE-010: Persistencia después de error de red", async ({ page }) => {
        // Agregar productos
        await page.click("#producto_id");
        await page.click("text=Shampoo");
        await page.fill("#cantidad", "2");
        await page.click("#btn_agregar");

        // Simular error de red
        await page.route("**/ventas", (route) => {
            route.abort("failed");
        });

        // Intentar guardar
        await page.click("#guardar");

        // Verificar que los datos siguen en la tabla
        const filas = await page.locator("#tabla_detalle tbody tr").count();
        expect(filas).toBe(1);

        // TODO: Implementar localStorage para persistir
    });
});
```

---

### 2. FLUJO DE CONTROL DE LAVADOS

#### 2.1 Pruebas End-to-End

```javascript
// tests/e2e/control-lavados.spec.js
import { test, expect } from "@playwright/test";

test.describe("Control de Lavados", () => {
    test.beforeEach(async ({ page }) => {
        await page.goto("/login");
        await page.fill("#email", "admin@carwash.com");
        await page.fill("#password", "password");
        await page.click('button[type="submit"]');
        await page.goto("/control/lavados");
    });

    test("CCL-001: Visualizar lista de lavados", async ({ page }) => {
        // Verificar que la página cargó
        await expect(page.locator("h1")).toContainText("Control de Lavados");

        // Verificar que hay tarjetas de lavado
        const cards = await page.locator(".control-card").count();
        expect(cards).toBeGreaterThan(0);
    });

    test("CCL-002: Filtrar por lavador", async ({ page }) => {
        // Abrir selector de lavador
        await page.click("#filtro_lavador");

        // Seleccionar un lavador específico
        await page.click("text=Juan Pérez");

        // Aplicar filtro
        await page.click('button[type="submit"]:has-text("Filtrar")');

        // Verificar que se aplicó el filtro
        await expect(page).toHaveURL(/lavador_id=\d+/);
    });

    test("CCL-003: Filtrar por estado", async ({ page }) => {
        // Seleccionar estado "En proceso"
        await page.selectOption("#filtro_estado", "En proceso");

        // Aplicar filtro
        await page.click('button[type="submit"]:has-text("Filtrar")');

        // Verificar URL
        await expect(page).toHaveURL(/estado=En\+proceso/);
    });

    test("CCL-004: Filtrar por fecha", async ({ page }) => {
        const today = new Date().toISOString().split("T")[0];

        // Ingresar fecha
        await page.fill("#fecha", today);

        // Aplicar filtro
        await page.click('button[type="submit"]:has-text("Filtrar")');

        // Verificar que se aplicó
        await expect(page).toHaveURL(new RegExp(`fecha=${today}`));
    });

    test("CCL-005: Exportar reporte diario", async ({ page }) => {
        // Click en exportar diario
        const [download] = await Promise.all([
            page.waitForEvent("download"),
            page.click('a:has-text("Exportar Diario")'),
        ]);

        // Verificar que se descargó
        expect(download.suggestedFilename()).toContain(".xlsx");
    });

    test("CCL-006: Exportar reporte personalizado", async ({ page }) => {
        // Ingresar fechas
        await page.fill('input[name="fecha_inicio"]', "2025-10-01");
        await page.fill('input[name="fecha_fin"]', "2025-10-21");

        // Exportar
        const [download] = await Promise.all([
            page.waitForEvent("download"),
            page.click('form[action*="personalizado"] button[type="submit"]'),
        ]);

        expect(download.suggestedFilename()).toContain(".xlsx");
    });

    test("CCL-007: Iniciar lavado", async ({ page }) => {
        // Buscar lavado en estado "Pendiente"
        await page.selectOption("#filtro_estado", "En espera");
        await page.click('button[type="submit"]:has-text("Filtrar")');

        // Click en iniciar
        await page.click('button:has-text("Iniciar"):first');

        // Verificar mensaje de éxito
        await expect(page.locator(".alert-success")).toBeVisible();
    });

    test("CCL-008: Completar lavado", async ({ page }) => {
        // Filtrar lavados en proceso
        await page.selectOption("#filtro_estado", "En proceso");
        await page.click('button[type="submit"]:has-text("Filtrar")');

        // Click en completar
        await page.click('button:has-text("Completar"):first');

        // Verificar mensaje
        await expect(page.locator(".alert-success")).toBeVisible();
    });

    test("CCL-009: Cambiar lavador asignado", async ({ page }) => {
        // Buscar un lavado
        const primerLavado = page.locator(".control-card").first();

        // Click en cambiar lavador
        await primerLavado.locator('button:has-text("Cambiar")').click();

        // Seleccionar nuevo lavador en modal
        await page.click("#nuevo_lavador_id");
        await page.click("text=María González");

        // Confirmar
        await page.click('button:has-text("Guardar")');

        // Verificar éxito
        await expect(page.locator(".alert-success")).toBeVisible();
    });

    test("CCL-010: Validar campos de fecha en export", async ({ page }) => {
        // Intentar exportar sin fecha_fin
        await page.fill('input[name="fecha_inicio"]', "2025-10-01");
        await page.click('form[action*="personalizado"] button[type="submit"]');

        // Verificar que no se permite (HTML5 validation)
        const isInvalid = await page
            .locator('input[name="fecha_fin"]')
            .evaluate((el) => el.validity.valueMissing);
        expect(isInvalid).toBe(true);
    });
});
```

---

### 3. FLUJO DE ESTACIONAMIENTO

```javascript
// tests/e2e/estacionamiento.spec.js
import { test, expect } from "@playwright/test";

test.describe("Gestión de Estacionamiento", () => {
    test.beforeEach(async ({ page }) => {
        await page.goto("/login");
        await page.fill("#email", "admin@carwash.com");
        await page.fill("#password", "password");
        await page.click('button[type="submit"]');
    });

    test("CES-001: Registrar entrada de vehículo", async ({ page }) => {
        await page.goto("/estacionamiento/create");

        // Seleccionar tipo de vehículo
        await page.selectOption("#tipo_vehiculo_id", { label: "Automóvil" });

        // Ingresar placa
        await page.fill("#placa", "ABC-123");

        // Guardar
        await page.click('button[type="submit"]');

        // Verificar éxito
        await expect(page.locator(".alert-success")).toBeVisible();
    });

    test("CES-002: Validar placa duplicada", async ({ page }) => {
        await page.goto("/estacionamiento/create");

        // Ingresar placa que ya existe
        await page.fill("#placa", "PLACA-EXISTE");
        await page.click('button[type="submit"]');

        // Verificar error
        await expect(page.locator(".alert-danger")).toBeVisible();
        await expect(page.locator(".alert-danger")).toContainText("placa");
    });

    test("CES-003: Validar capacidad máxima", async ({ page }) => {
        // TODO: Llenar hasta capacidad máxima
        // Intentar registrar uno más
        // Verificar mensaje de error
    });

    test("CES-004: Registrar salida de vehículo", async ({ page }) => {
        await page.goto("/estacionamiento");

        // Buscar vehículo activo
        await page.click('button:has-text("Salida"):first');

        // Verificar que se liberó la cochera
        await expect(page.locator(".alert-success")).toBeVisible();
    });

    test("CES-005: Visualizar historial", async ({ page }) => {
        await page.goto("/estacionamiento/historial");

        // Verificar que hay registros
        const filas = await page.locator("table tbody tr").count();
        expect(filas).toBeGreaterThan(0);
    });
});
```

---

## ⚡ PRUEBAS DE PERFORMANCE

### 4.1 Lighthouse CI

```javascript
// lighthouserc.js
module.exports = {
    ci: {
        collect: {
            url: [
                "http://localhost:8000/ventas/create",
                "http://localhost:8000/control/lavados",
                "http://localhost:8000/estacionamiento",
            ],
            numberOfRuns: 3,
        },
        assert: {
            preset: "lighthouse:recommended",
            assertions: {
                "first-contentful-paint": ["error", { maxNumericValue: 1800 }],
                "largest-contentful-paint": [
                    "error",
                    { maxNumericValue: 2500 },
                ],
                interactive: ["error", { maxNumericValue: 3800 }],
                "total-blocking-time": ["error", { maxNumericValue: 300 }],
                "cumulative-layout-shift": ["error", { maxNumericValue: 0.1 }],
                "speed-index": ["error", { maxNumericValue: 3400 }],

                // Performance
                "uses-text-compression": "error",
                "uses-optimized-images": "warn",
                "unused-css-rules": "warn",
                "unused-javascript": "warn",

                // Accesibilidad
                "color-contrast": "error",
                "image-alt": "error",
                label: "error",
                "valid-lang": "error",

                // Best Practices
                "uses-https": "error",
                "no-vulnerable-libraries": "error",
            },
        },
        upload: {
            target: "temporary-public-storage",
        },
    },
};
```

### 4.2 Web Vitals Manual Testing

**Checklist de Performance:**

```markdown
## Core Web Vitals

### LCP (Largest Contentful Paint)

-   [ ] Página de ventas: < 2.5s
-   [ ] Control de lavados: < 2.5s
-   [ ] Dashboard: < 2.5s

### FID (First Input Delay)

-   [ ] Click en botón "Agregar": < 100ms
-   [ ] Selección de producto: < 100ms
-   [ ] Filtros: < 100ms

### CLS (Cumulative Layout Shift)

-   [ ] No hay saltos al cargar imágenes
-   [ ] No hay saltos al cargar fonts
-   [ ] Modales no causan reflow

### TTI (Time to Interactive)

-   [ ] Formulario de venta: < 3.8s
-   [ ] Controles interactivos: < 3.8s

### TBT (Total Blocking Time)

-   [ ] Script principal: < 300ms
-   [ ] Bootstrap Select init: < 200ms
-   [ ] SweetAlert2 init: < 100ms
```

### 4.3 Pruebas de Carga de Assets

```javascript
// tests/performance/assets-loading.spec.js
import { test, expect } from "@playwright/test";

test.describe("Performance de Assets", () => {
    test("PERF-001: Número de requests", async ({ page }) => {
        const requests = [];

        page.on("request", (request) => {
            requests.push(request.url());
        });

        await page.goto("/ventas/create");
        await page.waitForLoadState("networkidle");

        console.log(`Total requests: ${requests.length}`);

        // Debe ser menos de 15 requests
        expect(requests.length).toBeLessThan(15);
    });

    test("PERF-002: Tamaño total de assets", async ({ page }) => {
        let totalSize = 0;

        page.on("response", async (response) => {
            const headers = response.headers();
            const contentLength = headers["content-length"];
            if (contentLength) {
                totalSize += parseInt(contentLength);
            }
        });

        await page.goto("/ventas/create");
        await page.waitForLoadState("networkidle");

        const totalMB = totalSize / 1024 / 1024;
        console.log(`Total size: ${totalMB.toFixed(2)} MB`);

        // Debe ser menos de 2MB
        expect(totalMB).toBeLessThan(2);
    });

    test("PERF-003: Assets cacheables", async ({ page, context }) => {
        // Primera carga
        await page.goto("/ventas/create");
        await page.waitForLoadState("networkidle");

        // Segunda carga (debe usar cache)
        const requests = [];
        page.on("request", (request) => {
            if (
                request.resourceType() === "script" ||
                request.resourceType() === "stylesheet"
            ) {
                requests.push(request);
            }
        });

        await page.goto("/ventas/create");
        await page.waitForLoadState("networkidle");

        // Verificar que algunos recursos vienen del cache
        const cachedRequests = requests.filter(
            (req) =>
                req.headers()["if-none-match"] ||
                req.headers()["if-modified-since"]
        );

        expect(cachedRequests.length).toBeGreaterThan(0);
    });

    test("PERF-004: Tiempo de renderizado de tabla", async ({ page }) => {
        await page.goto("/ventas/create");

        // Agregar 10 productos y medir tiempo
        const startTime = Date.now();

        for (let i = 0; i < 10; i++) {
            await page.click("#producto_id");
            await page.click("text=Shampoo");
            await page.fill("#cantidad", "1");
            await page.click("#btn_agregar");
        }

        const endTime = Date.now();
        const totalTime = endTime - startTime;

        console.log(`Tiempo para 10 productos: ${totalTime}ms`);

        // Debe ser menos de 2 segundos
        expect(totalTime).toBeLessThan(2000);
    });
});
```

---

## 👥 PRUEBAS DE USABILIDAD

### 5.1 Tareas de Usuario

**Test de Usabilidad #1: Completar una venta**

```markdown
## Escenario

Eres un empleado nuevo que debe realizar su primera venta.

## Tarea

Vende 2 Shampoos y 1 Lavado Premium a un cliente llamado "Juan Pérez".

## Observaciones

-   [ ] ¿Encontró fácilmente la opción de crear venta?
-   [ ] ¿Entendió cómo buscar productos?
-   [ ] ¿Le quedó claro el proceso de agregar productos?
-   [ ] ¿Entendió el cálculo de IGV?
-   [ ] ¿Tuvo dudas sobre qué botón presionar?
-   [ ] Tiempo total: \_\_\_ minutos (target: < 2 min)
-   [ ] Número de errores: \_\_\_ (target: 0)
-   [ ] Nivel de frustración (1-10): \_\_\_ (target: < 3)
```

**Test de Usabilidad #2: Gestionar lavados**

```markdown
## Escenario

Debes iniciar un lavado que está en espera y asignarlo a un lavador.

## Tarea

1. Encuentra el lavado de la placa "ABC-123"
2. Inicialo
3. Asigna al lavador "María González"

## Observaciones

-   [ ] ¿Encontró fácilmente los filtros?
-   [ ] ¿Entendió los estados de los lavados?
-   [ ] ¿Le fue intuitivo cambiar el lavador?
-   [ ] Tiempo total: \_\_\_ minutos (target: < 1 min)
-   [ ] Errores: \_\_\_ (target: 0)
```

### 5.2 Pruebas de Accesibilidad con Teclado

```javascript
// tests/accessibility/keyboard-navigation.spec.js
import { test, expect } from "@playwright/test";

test.describe("Navegación con Teclado", () => {
    test("ACC-001: Tab navigation en formulario de venta", async ({ page }) => {
        await page.goto("/ventas/create");

        // Presionar Tab repetidamente
        await page.keyboard.press("Tab"); // Producto
        let focused = await page.evaluate(() => document.activeElement.id);
        expect(focused).toBe("producto_id");

        await page.keyboard.press("Tab"); // Cantidad
        focused = await page.evaluate(() => document.activeElement.id);
        expect(focused).toBe("cantidad");

        await page.keyboard.press("Tab"); // Descuento
        focused = await page.evaluate(() => document.activeElement.id);
        expect(focused).toBe("descuento");

        await page.keyboard.press("Tab"); // Botón agregar
        focused = await page.evaluate(() => document.activeElement.id);
        expect(focused).toBe("btn_agregar");
    });

    test("ACC-002: Enter para agregar producto", async ({ page }) => {
        await page.goto("/ventas/create");

        // Navegar con teclado
        await page.keyboard.press("Tab");
        await page.keyboard.type("Shampoo");
        await page.keyboard.press("Enter");

        await page.keyboard.press("Tab");
        await page.keyboard.type("2");

        await page.keyboard.press("Tab"); // Skip descuento
        await page.keyboard.press("Tab"); // Focus en agregar
        await page.keyboard.press("Enter");

        // Verificar que se agregó
        const filas = await page.locator("#tabla_detalle tbody tr").count();
        expect(filas).toBe(1);
    });

    test("ACC-003: Escape para cerrar modal", async ({ page }) => {
        await page.goto("/ventas/create");

        // Agregar un producto
        await page.click("#producto_id");
        await page.click("text=Shampoo");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Abrir modal de cancelar
        await page.click("#cancelar");
        await expect(page.locator("#exampleModal")).toBeVisible();

        // Presionar Escape
        await page.keyboard.press("Escape");

        // Verificar que se cerró
        await expect(page.locator("#exampleModal")).not.toBeVisible();
    });
});
```

### 5.3 Pruebas con Lectores de Pantalla

```javascript
// tests/accessibility/screen-reader.spec.js
import { test, expect } from "@playwright/test";
import { injectAxe, checkA11y } from "axe-playwright";

test.describe("Accesibilidad para Lectores de Pantalla", () => {
    test("ACC-004: Verificar estructura semántica", async ({ page }) => {
        await page.goto("/ventas/create");
        await injectAxe(page);

        await checkA11y(page, null, {
            detailedReport: true,
            detailedReportOptions: {
                html: true,
            },
        });
    });

    test("ACC-005: Labels asociados a inputs", async ({ page }) => {
        await page.goto("/ventas/create");

        // Verificar que todos los inputs tienen label
        const inputs = await page.locator("input, select, textarea").all();

        for (const input of inputs) {
            const id = await input.getAttribute("id");
            if (id) {
                const label = await page.locator(`label[for="${id}"]`).count();
                expect(label).toBeGreaterThan(0);
            }
        }
    });

    test("ACC-006: Botones con texto descriptivo", async ({ page }) => {
        await page.goto("/ventas/create");

        // Verificar botones con solo iconos
        const buttons = await page.locator("button:has(i)").all();

        for (const button of buttons) {
            const ariaLabel = await button.getAttribute("aria-label");
            const title = await button.getAttribute("title");
            const text = await button.textContent();

            // Debe tener al menos uno
            expect(ariaLabel || title || text.trim()).toBeTruthy();
        }
    });
});
```

---

## 🌐 PRUEBAS DE COMPATIBILIDAD

### 6.1 Cross-Browser Testing

```javascript
// playwright.config.js
import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
    projects: [
        {
            name: "chromium",
            use: { ...devices["Desktop Chrome"] },
        },
        {
            name: "firefox",
            use: { ...devices["Desktop Firefox"] },
        },
        {
            name: "webkit",
            use: { ...devices["Desktop Safari"] },
        },
        {
            name: "Mobile Chrome",
            use: { ...devices["Pixel 5"] },
        },
        {
            name: "Mobile Safari",
            use: { ...devices["iPhone 12"] },
        },
    ],
});
```

### 6.2 Responsive Testing

```javascript
// tests/responsive/mobile.spec.js
import { test, expect } from "@playwright/test";

test.describe("Diseño Responsive", () => {
    test("RESP-001: Venta en móvil (320px)", async ({ page }) => {
        await page.setViewportSize({ width: 320, height: 568 });
        await page.goto("/ventas/create");

        // Verificar que no hay overflow horizontal
        const hasScroll = await page.evaluate(
            () =>
                document.documentElement.scrollWidth >
                document.documentElement.clientWidth
        );
        expect(hasScroll).toBe(false);
    });

    test("RESP-002: Tabla responsive en móvil", async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto("/ventas/create");

        // Agregar producto
        await page.click("#producto_id");
        await page.click("text=Shampoo");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Verificar que la tabla es scrollable
        const table = page.locator("#tabla_detalle");
        const wrapper = table.locator("xpath=..");

        const isScrollable = await wrapper.evaluate(
            (el) => el.scrollWidth > el.clientWidth
        );
        expect(isScrollable).toBe(true);
    });

    test("RESP-003: Filtros en tablet", async ({ page }) => {
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.goto("/control/lavados");

        // Verificar que los filtros se ven bien
        const filtros = page.locator(".filter-section");
        await expect(filtros).toBeVisible();

        // Verificar que no se superponen
        const bounds = await filtros.boundingBox();
        expect(bounds.width).toBeLessThanOrEqual(768);
    });
});
```

---

## 🔒 PRUEBAS DE SEGURIDAD FRONTEND

### 7.1 XSS Testing

```javascript
// tests/security/xss.spec.js
import { test, expect } from "@playwright/test";

test.describe("Pruebas de XSS", () => {
    test("SEC-001: XSS en nombre de producto", async ({ page }) => {
        await page.goto("/ventas/create");

        // Intentar inyectar script
        await page.evaluate(() => {
            window.cont = 0;
            window.subtotal = [];
            // Simular producto malicioso
            $("#producto_id").append(
                `<option value="999-10-100-0"><script>alert('XSS')</script>Producto</option>`
            );
        });

        await page.selectOption("#producto_id", "999");
        await page.fill("#cantidad", "1");
        await page.click("#btn_agregar");

        // Verificar que no se ejecutó el script
        const alertShown = await page.evaluate(
            () => typeof window.alertShown !== "undefined"
        );
        expect(alertShown).toBe(false);
    });

    test("SEC-002: SQL Injection en filtros", async ({ page }) => {
        await page.goto("/control/lavados");

        // Intentar SQL injection
        await page.fill("#fecha", "'; DROP TABLE lavados; --");
        await page.click('button[type="submit"]');

        // Verificar que la página sigue funcionando
        await expect(page.locator(".control-card")).toBeVisible();
    });
});
```

---

## 📊 MÉTRICAS Y KPIS

### Dashboard de Métricas

```markdown
## KPIs de Frontend

### Performance

-   [ ] FCP < 1.8s ✅
-   [ ] LCP < 2.5s ✅
-   [ ] TTI < 3.8s ✅
-   [ ] TBT < 300ms ✅
-   [ ] CLS < 0.1 ✅

### Usabilidad

-   [ ] Tiempo promedio venta: < 60s
-   [ ] Tasa de error: < 2%
-   [ ] Satisfacción usuario: > 8/10

### Accesibilidad

-   [ ] Score Lighthouse: > 90
-   [ ] Contraste colores: AAA
-   [ ] Navegación teclado: 100%

### Compatibilidad

-   [ ] Chrome: ✅
-   [ ] Firefox: ✅
-   [ ] Safari: ✅
-   [ ] Edge: ✅
-   [ ] Mobile: ✅

### Coverage

-   [ ] Unit Tests: > 80%
-   [ ] Integration: > 60%
-   [ ] E2E: > 40%
```

---

## 🚀 PLAN DE EJECUCIÓN

### Fase 1: Setup (1 día)

```bash
# Instalar dependencias
npm install -D @playwright/test axe-core axe-playwright
npm install -D vitest @testing-library/dom
npm install -D lighthouse

# Configurar Playwright
npx playwright install
```

### Fase 2: Tests Críticos (3 días)

-   Día 1: Flujo de ventas (CVE-001 a CVE-010)
-   Día 2: Control de lavados (CCL-001 a CCL-010)
-   Día 3: Estacionamiento (CES-001 a CES-005)

### Fase 3: Performance (2 días)

-   Performance testing con Lighthouse CI
-   Optimización de assets
-   Testing de carga

### Fase 4: Accesibilidad (2 días)

-   Auditoría con axe-core
-   Testing con teclado
-   Testing con lectores de pantalla

### Fase 5: Integración CI/CD (1 día)

-   GitHub Actions setup
-   Reportes automáticos
-   Badges de coverage

---

**Total estimado:** 9 días de trabajo  
**Próximo documento:** `PLAN_OPTIMIZACION_FRONTEND.md`
