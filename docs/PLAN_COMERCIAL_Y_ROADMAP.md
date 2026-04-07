# Plan Comercial y Roadmap de Valor Agregado
### Sistema de Gestión para Lavado de Autos — CarWash ESP

> Versión 1.0 | Abril 2026

---

## 1. Modelo de Negocio

| Elemento | Detalle |
|---|---|
| **Tipo de venta** | Licencia de software con pago único |
| **Hosting** | El cliente contrata y paga su propia cuenta en Laravel Cloud |
| **Soporte incluido** | 1 año de mantenimiento activo + capacitación |
| **Extras** | Funcionalidades especiales a costo adicional |

---

## 2. Estructura de Costos

### 2.1 Licencia del Sistema (pago único)

| Plan | Precio USD | Precio PEN | Perfil del cliente |
|---|---|---|---|
| **Básico** | $800 | S/ 3,000 | 1 local, bajo volumen de operaciones |
| **Estándar** | $1,500 | S/ 5,600 | 1–2 locales, volumen medio *(más elegido)* |
| **Profesional** | $2,500 | S/ 9,300 | Múltiples locales o alto volumen operativo |

> Tipo de cambio referencial: 1 USD = S/ 3.72 PEN

### 2.2 ¿Qué incluye la licencia?

| Beneficio | Básico | Estándar | Profesional |
|---|:---:|:---:|:---:|
| Los 15 módulos del sistema | ✅ | ✅ | ✅ |
| Instalación y configuración inicial | ✅ | ✅ | ✅ |
| 1 año de mantenimiento y corrección de bugs | ✅ | ✅ | ✅ |
| Capacitación en vivo (sesiones) | 2 | 4 | 6 |
| Soporte por WhatsApp / correo | ✅ | ✅ | ✅ |
| Actualizaciones menores del año | ✅ | ✅ | ✅ |
| Prioridad de atención | Normal | Alta | Prioritaria |

### 2.3 Costo de Hosting — Laravel Cloud (lo paga el cliente)

El cliente crea y paga su propia cuenta en [cloud.laravel.com](https://cloud.laravel.com).
Este costo **no está incluido** en el precio de la licencia.

| Perfil del negocio | Plan sugerido | Costo estimado / mes |
|---|---|---|
| Carwash pequeño (1 local, uso diurno) | Starter | $10–20 USD |
| Carwash mediano (1–2 locales) | Starter / Growth | $20–40 USD |
| Carwash grande o cadena | Growth | $40–80 USD |

**Ventajas de Laravel Cloud para el cliente:**
- Sin administración de servidores
- Hibernación automática = $0 fuera del horario operativo
- Base de datos MySQL gestionada incluida
- SSL, dominio personalizado y backups automáticos
- Despliegues desde Git sin configuración manual

### 2.4 Servicios Adicionales

| Servicio | Precio |
|---|---|
| Funcionalidad especial (por hora) | $50–80 USD/hora |
| Funcionalidad especial (precio fijo por módulo) | $200–800 USD |
| Mantenimiento a partir del año 2 | $300–500 USD/año |
| Sesión de capacitación adicional (1 hora) | $60 USD |
| Migración de datos desde sistema anterior | Cotización |
| Configuración de impresora térmica | $80 USD |

### 2.5 Costo Total para el Cliente — Primer Año (ejemplo)

| Concepto | Monto |
|---|---|
| Licencia plan Estándar | $1,500 USD *(pago único)* |
| Hosting Laravel Cloud 12 meses (~$20/mes) | ~$240 USD *(cliente paga directo)* |
| **Total estimado primer año** | **~$1,740 USD** |
| Años siguientes (hosting + mantenimiento opcional) | ~$540–740 USD/año |

---

## 3. Módulos Actuales del Sistema (15 módulos)

| # | Módulo | Descripción |
|---|---|---|
| 1 | POS / Ventas | Venta con efectivo, tarjeta, gift card y canje de puntos |
| 2 | Inventario | Control de stock con alertas y auditoría |
| 3 | Fidelización | Puntos (10% del total) + lavado gratis cada 10 servicios |
| 4 | Tarjetas de regalo | Generación, carga y canje de gift cards |
| 5 | Estacionamiento | Cobro por hora con pagos adelantados |
| 6 | Cochera | Tarifas por hora y por día con cálculo mixto automático |
| 7 | Control de lavado | Seguimiento del proceso por vehículo |
| 8 | Mantenimiento | Servicios de mantenimiento con costo estimado y final |
| 9 | Comisiones | Cálculo automático por tipo de vehículo para cada lavador |
| 10 | Citas / Agenda | Programación de citas para clientes |
| 11 | Comprobantes | Tickets PDF e impresión térmica |
| 12 | Reportes | Diarios, semanales y mensuales en Excel y PDF |
| 13 | Compras | Registro de compras a proveedores |
| 14 | Usuarios y roles | Permisos granulares por rol |
| 15 | Configuración | Datos del negocio, tarifas y parámetros |

---

## 4. Roadmap de Valor Agregado

Funcionalidades que incrementan el valor del sistema y justifican precios más altos o ventas recurrentes.

---

### 4.1 Facturación Electrónica (SUNAT / SAT)
**Prioridad: CRÍTICA | Impacto: MUY ALTO**

En Perú la emisión de boletas y facturas electrónicas es obligatoria por ley (SUNAT).
Actualmente los negocios usan un sistema paralelo solo para facturar. Integrar esto
elimina esa necesidad y convierte el sistema en la única herramienta que el negocio requiere.

**Alcance técnico:**
- Integración con OSE (Operador de Servicios Electrónicos) o directa con SUNAT
- Generación de XML en formato UBL 2.1
- Emisión de Boleta Electrónica, Factura Electrónica y Nota de Crédito
- Envío automático al correo del cliente
- Consulta de estado del comprobante

**Precio sugerido como módulo adicional:** $400–600 USD

---

### 4.2 Notificaciones por WhatsApp
**Prioridad: ALTA | Impacto: ALTO**

WhatsApp tiene >90% de tasa de apertura en Latinoamérica.
Usando WhatsApp Business API (via Twilio o Meta directa):

**Casos de uso:**
- Recordatorio de cita 1 hora antes
- Aviso cuando el vehículo está listo para recoger
- Resumen de puntos de fidelización al finalizar la venta
- Envío del comprobante como mensaje
- Promociones a clientes del programa de fidelización

**Precio sugerido como módulo adicional:** $300–500 USD
*(El cliente paga su propia cuenta de WhatsApp Business API ~$0.01–0.05 por mensaje)*

---

### 4.3 Dashboard Visual con KPIs en Tiempo Real
**Prioridad: ALTA | Impacto: ALTO**

Los reportes actuales son exportables (Excel/PDF), pero no existe un panel visual.
El dueño del negocio necesita ver el estado del negocio al instante.

**Métricas propuestas:**
- Ingresos del día vs. día anterior / semana anterior
- Heatmap de horas pico por día de la semana
- Top 5 servicios más vendidos
- Estado del stock (productos en alerta de mínimo)
- Rendimiento por lavador (comisiones acumuladas)
- Ocupación del estacionamiento en tiempo real
- Lavados acumulados vs. meta del día

**Precio sugerido como módulo adicional:** $300–500 USD

---

### 4.4 Reservas Online (Página Pública)
**Prioridad: ALTA | Impacto: ALTO**

Una página pública y sencilla donde el cliente final puede:
- Ver disponibilidad de citas
- Seleccionar servicio y horario
- Registrar su vehículo
- Recibir confirmación automática (email o WhatsApp)

Reduce la carga operativa del personal y es un diferenciador visible para los clientes del negocio.

**Tecnología:** Blade view pública, sin login, integrada al módulo de Citas existente.

**Precio sugerido como módulo adicional:** $350–500 USD

---

### 4.5 Soporte Multi-Sucursal
**Prioridad: MEDIA-ALTA | Impacto: ALTO**

Si el cliente crece a 2 o más locales, hoy tendría que instalar el sistema por separado
en cada uno. Un modo multi-sucursal permite:

- Una cuenta para todas las sucursales
- Reportes consolidados y por sucursal
- Inventario independiente por local
- Un solo panel de administración

Esto convierte el sistema en una plataforma escalable y justifica directamente
el plan **Profesional** a $2,500 USD.

**Precio sugerido:** Incluido en plan Profesional o $500–800 USD adicionales.

---

### 4.6 App PWA para Clientes Finales
**Prioridad: MEDIA | Impacto: MEDIO**

Una Progressive Web App (PWA) que el cliente final del lavado instala desde el navegador
(sin App Store). Funcionalidades:

- Consulta de puntos de fidelización acumulados
- Historial de servicios
- Agendar cita
- Recibir notificaciones push

**Ventaja:** No requiere desarrollo nativo. Se construye sobre el sistema existente.

**Precio sugerido como módulo adicional:** $400–700 USD

---

## 5. Resumen del Roadmap por Prioridad

| # | Feature | Impacto | Precio adicional sugerido |
|---|---|---|---|
| 1 | **Facturación electrónica (SUNAT)** | Crítico / legal | $400–600 USD |
| 2 | **Dashboard visual KPIs** | Alto | $300–500 USD |
| 3 | **Notificaciones WhatsApp** | Alto | $300–500 USD |
| 4 | **Reservas online** | Alto | $350–500 USD |
| 5 | **Multi-sucursal** | Medio-Alto | $500–800 USD |
| 6 | **App PWA clientes** | Medio | $400–700 USD |

---

## 6. Propuesta de Valor / ROI

- **Prevención de pérdidas documentada:** El sistema resolvió errores que representaban **S/ 360,000/año** en pérdidas para un negocio tipo.
- **Automatización:** Comisiones, stock, fidelización y reportes calculados en tiempo real.
- **Todo en uno:** Reemplaza POS, control de citas, gestión de personal y reportes contables.
- **Escalable:** Funciona para 1 local hoy; crece a múltiples locales mañana.
- **Sin mensualidad por el software:** El cliente solo paga el hosting (~$10–40/mes), que es suyo.

---

## 7. Proceso de Entrega

1. **Acuerdo comercial** — Se define el plan y se recibe el 50% del pago.
2. **Configuración** — Instalación en la cuenta Laravel Cloud del cliente (1–3 días hábiles).
3. **Personalización** — Carga de productos, tipos de vehículo, tarifas y datos del negocio.
4. **Capacitación** — Sesiones en vivo (Zoom / Meet) con el personal.
5. **Entrega oficial** — Pago del 50% restante. Inicio del año de mantenimiento.
6. **Soporte continuo** — Atención de dudas y corrección de bugs por 12 meses.

---

*Documento interno — Abril 2026*
