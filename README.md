---

![Img](https://github.com/SakNoelCode/Imagenes_Proyectos/blob/master/sistemaAbarrotePanel.png)

# 🚗 Sistema de Gestión CarWash ESP

Sistema integral de punto de venta y gestión para lavaderos de autos, desarrollado con Laravel 10 y arquitectura moderna orientada a servicios.

## 🏗️ Arquitectura

Este proyecto implementa una arquitectura robusta con:

### **Capa de Servicios**

-   `VentaService`: Gestión completa del flujo de ventas
-   `StockService`: Control de inventario con auditoría
-   `FidelizacionService`: Programa de puntos y recompensas
-   `TarjetaRegaloService`: Gestión de tarjetas de regalo
-   `ComprobanteService`: Generación de comprobantes únicos

### **Capa de Repositorios**

-   `VentaRepository`: Consultas optimizadas con caché
-   `ProductoRepository`: Gestión de productos con eager loading

### **Observadores y Eventos**

-   `VentaObserver`: Auditoría de ventas
-   `ProductoObserver`: Auditoría de productos
-   `StockBajoEvent`: Alertas de stock bajo

### **Jobs Asíncronos**

-   `GenerarReporteVentasJob`: Reportes en segundo plano
-   `GenerarReporteComprasJob`: Reportes de compras

### **Testing Completo**

-   ✅ **44 tests** con 100% de aprobación
-   91 aserciones
-   Cobertura: Servicios, Repositorios, Observers, Events, Jobs, Integración

## 📊 Características Principales

-   ✅ **Ventas con múltiples medios de pago**: Efectivo, tarjeta, tarjetas de regalo, lavados gratis
-   ✅ **Control de stock inteligente**: Actualizaciones atómicas con locks pesimistas
-   ✅ **Programa de fidelización**: Acumulación y canje de puntos
-   ✅ **Gestión de tarjetas de regalo**: Creación, validación y uso
-   ✅ **Sistema de caché**: Consultas optimizadas para mejor rendimiento
-   ✅ **Auditoría completa**: Logs específicos para ventas y stock
-   ✅ **Reportes diarios/semanales/mensuales**: Generación asíncrona
-   ✅ **Impresión térmica**: Tickets de venta
-   ✅ **Gestión de estacionamiento**: Control de cocheras

## 🛠️ Dependencias

-   Se debe tener instalado [XAMPP](https://www.apachefriends.org/es/download.html "XAMPP") (versión **PHP** **8.1** o superior)
-   Se debe tener instalado [Composer](https://getcomposer.org/download/ "Composer")

## Como instalar en Local

1. Clone o descargue el repositorio a una carpeta en Local

1. Abra el repositorio en su editor de código favorito (**Visual Studio Code**)

1. Ejecute la aplicación **XAMPP** e inice los módulos de **Apache** y **MySQL**

1. Abra una nueva terminal en su editor

1. Compruebe de que tiene instalado todas dependencias correctamente, ejecute los siguientes comandos: **(Ambos comandos deberán ejecutarse correctamente - ejecutar en la terminal)**

```bash
php -v
```

```bash
composer -v
```

1. Ahora ejecute los comandos para la configuración del proyecto (**ejecutar en la terminal**):

-   Este comando nos va a instalar todas la dependencias de composer

```bash
composer install
```

-   En el directorio raíz encontrará el arhivo **.env.example**, dupliquelo, al archivo duplicado cambiar de nombre como **.env**, este archivo se debe modificar según las configuraciones de nuestro proyecto. Ahí se muestran como debería quedar

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dbsistemaventas
DB_USERNAME=root
DB_PASSWORD=
```

-   Ejecutar el comando para crear la Key de seguridad

```bash
php artisan key:generate
```

-   Ingrese al administrador de [PHP MyAdmin](http://localhost/phpmyadmin/) y cree una nueva base de datos, el nombre es opcional, pero por defecto nombrarla **dbsistemaventas**

-   Correr la migraciones del proyecto

```bash
php artisan migrate
```

-   Ejecute los seeders, esto creará un usuario administrador, puede revisar las credenciales en el archivo (**database/seeders/UserSeeder**)

```bash
php artisan db:seed
```

-   Ejecute el proyecto

```bash
php artisan serve
```

## 🧪 Testing

El proyecto cuenta con una suite completa de tests:

```bash
# Ejecutar todos los tests
vendor/bin/phpunit

# Ejecutar tests específicos
vendor/bin/phpunit tests/Unit/Services/VentaServiceTest.php
vendor/bin/phpunit tests/Feature/VentaFlowIntegrationTest.php

# Ver cobertura detallada
vendor/bin/phpunit --coverage-html coverage
```

**Estadísticas de Testing:**

-   ✅ **72 tests pasando (100%)**
-   **181 assertions**
-   **Cobertura completa:** Services, Repositories, Observers, Events, Jobs, Integration, Pagination

**Suite de Tests:**
- Refactoring: 44 tests (Services, Repositories, Observers, Events, Jobs, Integration)
- Pagination: 17 tests (Endpoints, componentes, navegación)
- Components: 10 tests (Renderizado, estados, parámetros)
- Examples: 2 tests

Para más información, consulta [`TESTING_README.md`](TESTING_README.md) y [`REFACTORING_COMPLETADO.md`](REFACTORING_COMPLETADO.md)

## 📁 Estructura del Proyecto

```
app/
├── Services/           # Lógica de negocio
│   ├── VentaService.php
│   ├── StockService.php
│   ├── FidelizacionService.php
│   ├── TarjetaRegaloService.php
│   └── ComprobanteService.php
├── Repositories/       # Capa de acceso a datos
│   ├── VentaRepository.php
│   └── ProductoRepository.php
├── Observers/          # Observadores de modelos
│   ├── VentaObserver.php
│   └── ProductoObserver.php
├── Events/             # Eventos del sistema
│   └── StockBajoEvent.php
├── Jobs/               # Tareas asíncronas
│   ├── GenerarReporteVentasJob.php
│   └── GenerarReporteComprasJob.php
├── Exceptions/         # Excepciones personalizadas
│   ├── VentaException.php
│   ├── StockInsuficienteException.php
│   └── TarjetaRegaloException.php
└── Models/             # Modelos Eloquent optimizados
```

## 📈 Optimizaciones Implementadas

### **Consultas Optimizadas**

-   Eager loading con `with()` para evitar N+1
-   Scopes reutilizables: `delDia()`, `delaSemana()`, `conRelaciones()`
-   Caché en consultas frecuentes (productos para venta)

### **Logs Específicos**

```php
// Logs de ventas: storage/logs/ventas.log
Log::channel('ventas')->info('Venta procesada', [...]);

// Logs de stock: storage/logs/stock.log
Log::channel('stock')->warning('Stock bajo detectado', [...]);
```

### **Comandos Útiles**

```bash
# Limpiar caché de productos
php artisan cache:productos:clear

# Ver logs en tiempo real
tail -f storage/logs/ventas.log
tail -f storage/logs/stock.log

# Optimizar para producción
php artisan optimize
php artisan config:cache
php artisan route:cache
```

## 📚 Documentación Adicional

-   [`GUIA_IMPLEMENTACION.md`](GUIA_IMPLEMENTACION.md) - Guía paso a paso de implementación
-   [`TESTING_README.md`](TESTING_README.md) - Documentación completa de tests
-   [`documentacion_tecnica.md`](documentacion_tecnica.md) - Análisis técnico detallado
-   [`PROJECT_DOCUMENTATION_EN.md`](PROJECT_DOCUMENTATION_EN.md) - Documentación en inglés

## 🚀 Mejoras Implementadas (2025)

-   ✅ Refactorización completa a arquitectura de servicios
-   ✅ Implementación de repositorios con caché
-   ✅ Sistema de observers y events
-   ✅ Jobs asíncronos para reportes
-   ✅ Suite completa de 44 tests
-   ✅ Logs específicos por canal
-   ✅ Optimización de consultas SQL
-   ✅ Manejo robusto de excepciones

## Notas

-   Obtenga más información sobre este proyecto [aquí](https://universityproyectx.blogspot.com/2022/10/sistema-de-ventas-web-minersa-srl.html).
-   [FAQ sobre el proyecto](https://universityproyectx.blogspot.com/2023/06/faq-sobre-el-sistema-de-ventas-de.html)

## Licencia

-   Este proyecto está licenciado bajo la Licencia MIT. Para más información, consulta el archivo [LICENSE](LICENSE).
-   Obtenga más información sobre esta licencia [MIT license](https://opensource.org/licenses/MIT).

---

![Img](https://github.com/SakNoelCode/Imagenes_Proyectos/blob/master/sistemaAbarrotecategory.png)
