# Documentación Técnica - Sistema de Punto de Venta

## 1. Descripción General

Sistema de gestión para comercios que incluye:

-   Control de ventas e inventario
-   Gestión de clientes y proveedores
-   Módulo especializado de control de lavado
-   Generación de reportes y tickets
-   Sistema de roles y permisos

## 2. Tecnologías Utilizadas

### Backend

-   PHP 8+
-   Laravel 9/10
-   MySQL
-   Laravel Sanctum (Autenticación)

### Frontend

-   Bootstrap 5
-   JavaScript
-   Vite (Bundler)
-   DataTables (para reportes)

## 3. Requisitos del Sistema

### Servidor

-   PHP 8.0+
-   Composer
-   MySQL 5.7+
-   Node.js 16+
-   Servidor web (Apache/Nginx)

### Cliente

-   Navegador moderno (Chrome, Firefox, Edge)
-   Resolución mínima recomendada: 1024x768

## 4. Instalación

1. Clonar repositorio:

```bash
git clone [url_repositorio]
cd punto-de-venta
```

2. Instalar dependencias:

```bash
composer install
npm install
```

3. Configurar entorno:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar base de datos en .env:

```ini
DB_DATABASE=nombre_bd
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```

5. Migraciones y datos iniciales:

```bash
php artisan migrate --seed
```

6. Compilar assets:

```bash
npm run build
```

## 5. Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/ # Controladores principales
│   └── Models/      # Modelos Eloquent
database/
├── migrations/      # Esquema de base de datos
└── seeders/         # Datos iniciales
resources/
├── views/           # Vistas Blade
└── js/              # JavaScript frontend
routes/
└── web.php          # Rutas principales
```

## 6. Funcionalidades Principales

### Módulo de Ventas

-   Registro de ventas (migrado a FormValidator y lógica JS con VentaManager.js)
-   Listado y reporte de ventas (migrados a DynamicTable.js para tablas dinámicas, filtros y paginación)
-   Generación de tickets
-   Reportes diarios/semanales/mensuales
-   Exportación a Excel

**Notas técnicas:**

-   Las vistas principales (`create`, `index`, `reporte`) ahora utilizan componentes JS modernos para una mejor experiencia de usuario y validación en tiempo real.
-   Se eliminaron dependencias de jQuery en favor de módulos JS nativos y componentes reutilizables.
-   El módulo `VentaManager.js` centraliza la lógica de gestión de ventas, productos, clientes y detalle de venta.
-   La tabla de ventas y reportes usa `DynamicTable.js` para paginación, búsqueda y acciones (ver, eliminar) sin recarga de página.
-   El formulario de registro de venta usa `FormValidator.js` para validación dinámica y feedback inmediato.

### Control de Inventario

-   Gestión de productos
-   Categorías y marcas
-   Control de existencias

### Sistema de Lavado

-   Asignación de lavadores
-   Seguimiento de estados (inicio/fin)
-   Control de tiempos

### Clientes

-   Programa de fidelización
-   Historial de compras
-   Datos de contacto

## 7. Licencia

```text
Copyright <2023> <SISTEMA DE VENTAS>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```

## 8. Capturas de Pantalla

_(Incluir imágenes de las principales interfaces del sistema)_

## 9. Soporte Técnico

Para soporte contactar a:

-   Email: soporte@sakcode.com
-   Teléfono: +51 XXX XXX XXX
