<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            //categorías
            'ver-categoria',
            'crear-categoria',
            'editar-categoria',
            'eliminar-categoria',

            //Cliente
            'ver-cliente',
            'ver-fidelizacion',
            'crear-cliente',
            'editar-cliente',
            'eliminar-cliente',

            //Compra
            'ver-compra',
            'crear-compra',
            'mostrar-compra',
            'eliminar-compra',
            'reporte-diario-compra',
            'reporte-semanal-compra',
            'reporte-mensual-compra',
            'reporte-personalizado-compra',
            'exportar-reporte-compra',

            //Marca
            'ver-marca',
            'crear-marca',
            'editar-marca',
            'eliminar-marca',

            //Presentacione
            'ver-presentacione',
            'crear-presentacione',
            'editar-presentacione',
            'eliminar-presentacione',

            //Producto
            'ver-producto',
            'crear-producto',
            'editar-producto',
            'eliminar-producto',

            //Proveedore
            'ver-proveedore',
            'crear-proveedore',
            'editar-proveedore',
            'eliminar-proveedore',

            //Venta
            'ver-venta',
            'crear-venta',
            'mostrar-venta',
            'eliminar-venta',
            'reporte-diario-venta',
            'reporte-semanal-venta',
            'reporte-mensual-venta',
            'reporte-personalizado-venta',
            'exportar-reporte-venta',

            //Roles
            'ver-role',
            'crear-role',
            'editar-role',
            'eliminar-role',

            //User
            'ver-user',
            'crear-user',
            'editar-user',
            'eliminar-user',

            //Perfil 
            'ver-perfil',
            'editar-perfil',

            //Control de Lavado
            'ver-control-lavado',
            'crear-control-lavado',
            'editar-control-lavado',
            'eliminar-control-lavado',
            'reporte-diario-lavado',
            'reporte-semanal-lavado',
            'reporte-mensual-lavado',
            'reporte-personalizado-lavado',
            'exportar-reporte-lavado',

            //Citas
            'ver-cita',
            'crear-cita',
            'editar-cita',
            'eliminar-cita',
            'calendario-cita',
            'confirmar-cita',
            'reporte-diario-cita',
            'reporte-semanal-cita',
            'reporte-mensual-cita',
            'reporte-personalizado-cita',
            'exportar-reporte-cita',

            //Cocheras
            'ver-cochera',
            'crear-cochera',
            'editar-cochera',
            'eliminar-cochera',
            'reporte-cochera',

            //Mantenimiento
            'ver-mantenimiento',
            'crear-mantenimiento',
            'editar-mantenimiento',
            'eliminar-mantenimiento',
            'reporte-mantenimiento',

            //Configuración
            'ver-configuracion',
            'editar-configuracion',

            //Estacionamiento
            'ver-estacionamiento',
            'crear-estacionamiento',
            'editar-estacionamiento',
            'eliminar-estacionamiento',
            'historial-estacionamiento',
            'reporte-diario-estacionamiento',
            'reporte-semanal-estacionamiento',
            'reporte-mensual-estacionamiento',
            'reporte-personalizado-estacionamiento',
            'exportar-reporte-estacionamiento',

            // Lavadores
            'ver-lavador',
            'crear-lavador',
            'editar-lavador',
            'eliminar-lavador',

            // Tipos de Vehículo
            'ver-tipo-vehiculo',
            'crear-tipo-vehiculo',
            'editar-tipo-vehiculo',
            'eliminar-tipo-vehiculo',

            // Pagos de Comisiones
            'ver-pago-comision',
            'crear-pago-comision',
            'ver-historial-pago-comision',

            // Tarjetas de Regalo
            'ver-tarjeta-regalo',
            'crear-tarjeta-regalo',
            'editar-tarjeta-regalo',
            'eliminar-tarjeta-regalo',
            'reporte-tarjeta-regalo',
            'historial-tarjeta-regalo',
            'exportar-tarjeta-regalo',

            // Fidelidad de Clientes
            'ver-fidelidad',
            'gestionar-fidelidad',
            'reporte-fidelidad',
            'exportar-fidelidad',
        ];

        foreach($permisos as $permiso){
            if (!Permission::where('name', $permiso)->exists()) {
                Permission::create(['name' => $permiso]);
            }
        }
    }
}
