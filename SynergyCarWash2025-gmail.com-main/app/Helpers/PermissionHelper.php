<?php

namespace App\Helpers;

class PermissionHelper
{
    // Mapeo de permisos: clave => ['en' => '...', 'es' => '...']
    public static function labels()
    {
        return [
            'ver-categoria' => ['en' => 'View Category', 'es' => 'Ver Categoría'],
            'crear-categoria' => ['en' => 'Create Category', 'es' => 'Crear Categoría'],
            'editar-categoria' => ['en' => 'Edit Category', 'es' => 'Editar Categoría'],
            'eliminar-categoria' => ['en' => 'Delete Category', 'es' => 'Eliminar Categoría'],

            'ver-cliente' => ['en' => 'View Client', 'es' => 'Ver Cliente'],
            'ver-fidelizacion' => ['en' => 'View Loyalty', 'es' => 'Ver Fidelización'],
            'crear-cliente' => ['en' => 'Create Client', 'es' => 'Crear Cliente'],
            'editar-cliente' => ['en' => 'Edit Client', 'es' => 'Editar Cliente'],
            'eliminar-cliente' => ['en' => 'Delete Client', 'es' => 'Eliminar Cliente'],

            'ver-compra' => ['en' => 'View Purchase', 'es' => 'Ver Compra'],
            'crear-compra' => ['en' => 'Create Purchase', 'es' => 'Crear Compra'],
            'mostrar-compra' => ['en' => 'Show Purchase', 'es' => 'Mostrar Compra'],
            'eliminar-compra' => ['en' => 'Delete Purchase', 'es' => 'Eliminar Compra'],
            'reporte-diario-compra' => ['en' => 'Daily Purchase Report', 'es' => 'Reporte Diario Compra'],
            'reporte-semanal-compra' => ['en' => 'Weekly Purchase Report', 'es' => 'Reporte Semanal Compra'],
            'reporte-mensual-compra' => ['en' => 'Monthly Purchase Report', 'es' => 'Reporte Mensual Compra'],
            'reporte-personalizado-compra' => ['en' => 'Custom Purchase Report', 'es' => 'Reporte Personalizado Compra'],
            'exportar-reporte-compra' => ['en' => 'Export Purchase Report', 'es' => 'Exportar Reporte Compra'],

            'ver-marca' => ['en' => 'View Brand', 'es' => 'Ver Marca'],
            'crear-marca' => ['en' => 'Create Brand', 'es' => 'Crear Marca'],
            'editar-marca' => ['en' => 'Edit Brand', 'es' => 'Editar Marca'],
            'eliminar-marca' => ['en' => 'Delete Brand', 'es' => 'Eliminar Marca'],

            'ver-presentacione' => ['en' => 'View Presentation', 'es' => 'Ver Presentación'],
            'crear-presentacione' => ['en' => 'Create Presentation', 'es' => 'Crear Presentación'],
            'editar-presentacione' => ['en' => 'Edit Presentation', 'es' => 'Editar Presentación'],
            'eliminar-presentacione' => ['en' => 'Delete Presentation', 'es' => 'Eliminar Presentación'],

            'ver-producto' => ['en' => 'View Product', 'es' => 'Ver Producto'],
            'crear-producto' => ['en' => 'Create Product', 'es' => 'Crear Producto'],
            'editar-producto' => ['en' => 'Edit Product', 'es' => 'Editar Producto'],
            'eliminar-producto' => ['en' => 'Delete Product', 'es' => 'Eliminar Producto'],

            'ver-proveedore' => ['en' => 'View Supplier', 'es' => 'Ver Proveedor'],
            'crear-proveedore' => ['en' => 'Create Supplier', 'es' => 'Crear Proveedor'],
            'editar-proveedore' => ['en' => 'Edit Supplier', 'es' => 'Editar Proveedor'],
            'eliminar-proveedore' => ['en' => 'Delete Supplier', 'es' => 'Eliminar Proveedor'],

            'ver-venta' => ['en' => 'View Sale', 'es' => 'Ver Venta'],
            'crear-venta' => ['en' => 'Create Sale', 'es' => 'Crear Venta'],
            'mostrar-venta' => ['en' => 'Show Sale', 'es' => 'Mostrar Venta'],
            'eliminar-venta' => ['en' => 'Delete Sale', 'es' => 'Eliminar Venta'],
            'reporte-diario-venta' => ['en' => 'Daily Sale Report', 'es' => 'Reporte Diario Venta'],
            'reporte-semanal-venta' => ['en' => 'Weekly Sale Report', 'es' => 'Reporte Semanal Venta'],
            'reporte-mensual-venta' => ['en' => 'Monthly Sale Report', 'es' => 'Reporte Mensual Venta'],
            'reporte-personalizado-venta' => ['en' => 'Custom Sale Report', 'es' => 'Reporte Personalizado Venta'],
            'exportar-reporte-venta' => ['en' => 'Export Sale Report', 'es' => 'Exportar Reporte Venta'],

            'ver-role' => ['en' => 'View Role', 'es' => 'Ver Rol'],
            'crear-role' => ['en' => 'Create Role', 'es' => 'Crear Rol'],
            'editar-role' => ['en' => 'Edit Role', 'es' => 'Editar Rol'],
            'eliminar-role' => ['en' => 'Delete Role', 'es' => 'Eliminar Rol'],

            'ver-user' => ['en' => 'View User', 'es' => 'Ver Usuario'],
            'crear-user' => ['en' => 'Create User', 'es' => 'Crear Usuario'],
            'editar-user' => ['en' => 'Edit User', 'es' => 'Editar Usuario'],
            'eliminar-user' => ['en' => 'Delete User', 'es' => 'Eliminar Usuario'],

            'ver-perfil' => ['en' => 'View Profile', 'es' => 'Ver Perfil'],
            'editar-perfil' => ['en' => 'Edit Profile', 'es' => 'Editar Perfil'],

            'ver-control-lavado' => ['en' => 'View Wash Control', 'es' => 'Ver Control de Lavado'],
            'crear-control-lavado' => ['en' => 'Create Wash Control', 'es' => 'Crear Control de Lavado'],
            'editar-control-lavado' => ['en' => 'Edit Wash Control', 'es' => 'Editar Control de Lavado'],
            'eliminar-control-lavado' => ['en' => 'Delete Wash Control', 'es' => 'Eliminar Control de Lavado'],
            'reporte-diario-lavado' => ['en' => 'Daily Wash Report', 'es' => 'Reporte Diario Lavado'],
            'reporte-semanal-lavado' => ['en' => 'Weekly Wash Report', 'es' => 'Reporte Semanal Lavado'],
            'reporte-mensual-lavado' => ['en' => 'Monthly Wash Report', 'es' => 'Reporte Mensual Lavado'],
            'reporte-personalizado-lavado' => ['en' => 'Custom Wash Report', 'es' => 'Reporte Personalizado Lavado'],
            'exportar-reporte-lavado' => ['en' => 'Export Wash Report', 'es' => 'Exportar Reporte Lavado'],

            'ver-cita' => ['en' => 'View Appointment', 'es' => 'Ver Cita'],
            'crear-cita' => ['en' => 'Create Appointment', 'es' => 'Crear Cita'],
            'editar-cita' => ['en' => 'Edit Appointment', 'es' => 'Editar Cita'],
            'eliminar-cita' => ['en' => 'Delete Appointment', 'es' => 'Eliminar Cita'],
            'calendario-cita' => ['en' => 'Appointment Calendar', 'es' => 'Calendario Cita'],
            'confirmar-cita' => ['en' => 'Confirm Appointment', 'es' => 'Confirmar Cita'],
            'reporte-diario-cita' => ['en' => 'Daily Appointment Report', 'es' => 'Reporte Diario Cita'],
            'reporte-semanal-cita' => ['en' => 'Weekly Appointment Report', 'es' => 'Reporte Semanal Cita'],
            'reporte-mensual-cita' => ['en' => 'Monthly Appointment Report', 'es' => 'Reporte Mensual Cita'],
            'reporte-personalizado-cita' => ['en' => 'Custom Appointment Report', 'es' => 'Reporte Personalizado Cita'],
            'exportar-reporte-cita' => ['en' => 'Export Appointment Report', 'es' => 'Exportar Reporte Cita'],

            'ver-cochera' => ['en' => 'View Garage', 'es' => 'Ver Cochera'],
            'crear-cochera' => ['en' => 'Create Garage', 'es' => 'Crear Cochera'],
            'editar-cochera' => ['en' => 'Edit Garage', 'es' => 'Editar Cochera'],
            'eliminar-cochera' => ['en' => 'Delete Garage', 'es' => 'Eliminar Cochera'],
            'reporte-cochera' => ['en' => 'Garage Report', 'es' => 'Reporte Cochera'],

            'ver-mantenimiento' => ['en' => 'View Maintenance', 'es' => 'Ver Mantenimiento'],
            'crear-mantenimiento' => ['en' => 'Create Maintenance', 'es' => 'Crear Mantenimiento'],
            'editar-mantenimiento' => ['en' => 'Edit Maintenance', 'es' => 'Editar Mantenimiento'],
            'eliminar-mantenimiento' => ['en' => 'Delete Maintenance', 'es' => 'Eliminar Mantenimiento'],
            'reporte-mantenimiento' => ['en' => 'Maintenance Report', 'es' => 'Reporte Mantenimiento'],

            'ver-configuracion' => ['en' => 'View Configuration', 'es' => 'Ver Configuración'],
            'editar-configuracion' => ['en' => 'Edit Configuration', 'es' => 'Editar Configuración'],

            'ver-estacionamiento' => ['en' => 'View Parking', 'es' => 'Ver Estacionamiento'],
            'crear-estacionamiento' => ['en' => 'Create Parking', 'es' => 'Crear Estacionamiento'],
            'editar-estacionamiento' => ['en' => 'Edit Parking', 'es' => 'Editar Estacionamiento'],
            'eliminar-estacionamiento' => ['en' => 'Delete Parking', 'es' => 'Eliminar Estacionamiento'],
            'historial-estacionamiento' => ['en' => 'Parking History', 'es' => 'Historial Estacionamiento'],
            'reporte-diario-estacionamiento' => ['en' => 'Daily Parking Report', 'es' => 'Reporte Diario Estacionamiento'],
            'reporte-semanal-estacionamiento' => ['en' => 'Weekly Parking Report', 'es' => 'Reporte Semanal Estacionamiento'],
            'reporte-mensual-estacionamiento' => ['en' => 'Monthly Parking Report', 'es' => 'Reporte Mensual Estacionamiento'],
            'reporte-personalizado-estacionamiento' => ['en' => 'Custom Parking Report', 'es' => 'Reporte Personalizado Estacionamiento'],
            'exportar-reporte-estacionamiento' => ['en' => 'Export Parking Report', 'es' => 'Exportar Reporte Estacionamiento'],

            'ver-lavador' => ['en' => 'View Washer', 'es' => 'Ver Lavador'],
            'crear-lavador' => ['en' => 'Create Washer', 'es' => 'Crear Lavador'],
            'editar-lavador' => ['en' => 'Edit Washer', 'es' => 'Editar Lavador'],
            'eliminar-lavador' => ['en' => 'Delete Washer', 'es' => 'Eliminar Lavador'],

            'ver-tipo-vehiculo' => ['en' => 'View Vehicle Type', 'es' => 'Ver Tipo Vehículo'],
            'crear-tipo-vehiculo' => ['en' => 'Create Vehicle Type', 'es' => 'Crear Tipo Vehículo'],
            'editar-tipo-vehiculo' => ['en' => 'Edit Vehicle Type', 'es' => 'Editar Tipo Vehículo'],
            'eliminar-tipo-vehiculo' => ['en' => 'Delete Vehicle Type', 'es' => 'Eliminar Tipo Vehículo'],

            'ver-pago-comision' => ['en' => 'View Commission Payment', 'es' => 'Ver Pago Comisión'],
            'crear-pago-comision' => ['en' => 'Create Commission Payment', 'es' => 'Crear Pago Comisión'],
            'ver-historial-pago-comision' => ['en' => 'View Commission Payment History', 'es' => 'Ver Historial Pago Comisión'],

            'ver-tarjeta-regalo' => ['en' => 'View Gift Card', 'es' => 'Ver Tarjeta Regalo'],
            'crear-tarjeta-regalo' => ['en' => 'Create Gift Card', 'es' => 'Crear Tarjeta Regalo'],
            'editar-tarjeta-regalo' => ['en' => 'Edit Gift Card', 'es' => 'Editar Tarjeta Regalo'],
            'eliminar-tarjeta-regalo' => ['en' => 'Delete Gift Card', 'es' => 'Eliminar Tarjeta Regalo'],
            'reporte-tarjeta-regalo' => ['en' => 'Gift Card Report', 'es' => 'Reporte Tarjeta Regalo'],
            'historial-tarjeta-regalo' => ['en' => 'Gift Card History', 'es' => 'Historial Tarjeta Regalo'],
            'exportar-tarjeta-regalo' => ['en' => 'Export Gift Card', 'es' => 'Exportar Tarjeta Regalo'],

            'ver-fidelidad' => ['en' => 'View Loyalty', 'es' => 'Ver Fidelidad'],
            'gestionar-fidelidad' => ['en' => 'Manage Loyalty', 'es' => 'Gestionar Fidelidad'],
            'reporte-fidelidad' => ['en' => 'Loyalty Report', 'es' => 'Reporte Fidelidad'],
            'exportar-fidelidad' => ['en' => 'Export Loyalty', 'es' => 'Exportar Fidelidad'],
        ];
    }

    // Obtiene el label traducido
    public static function getLabel($permission, $locale = 'en')
    {
        $labels = self::labels();
        return $labels[$permission][$locale] ?? $permission;
    }
}
