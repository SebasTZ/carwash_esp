<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador solo si no existe
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('12345678')
            ]
        );

        // Crear rol administrador solo si no existe
        $rol = Role::firstOrCreate(['name' => 'administrador']);
        
        // Asignar todos los permisos al rol
        $permisos = Permission::pluck('id', 'id')->all();
        $rol->syncPermissions($permisos);
        
        // Asignar rol al usuario si aún no lo tiene
        if (!$user->hasRole('administrador')) {
            $user->assignRole('administrador');
        }
        
        // Ejemplo de rol para cajero con permisos limitados
        $cajero = Role::firstOrCreate(['name' => 'cajero']);
        $cajeroPermisos = [
            // Ventas (función principal del cajero)
            'ver-venta',
            'crear-venta',
            'mostrar-venta',

            // Clientes
            'ver-cliente',
            'crear-cliente',
            'ver-fidelizacion',

            // Tarjetas de Regalo
            'ver-tarjeta-regalo',
            'crear-tarjeta-regalo',
            'editar-tarjeta-regalo',
            'reporte-tarjeta-regalo',
            'exportar-tarjeta-regalo',
            'ver-historial-uso-tarjeta-regalo',

            // Fidelidad
            'ver-fidelidad',
            'gestionar-fidelidad',
            'reporte-fidelidad',
            'exportar-fidelidad',

            // Perfil propio
            'ver-perfil',
            'editar-perfil',

            // Control de lavado (registro básico)
            'ver-control-lavado',
        ];
        $cajero->syncPermissions($cajeroPermisos);

        // Rol lavador: solo puede ver y actualizar estado de lavados asignados
        $lavador = Role::firstOrCreate(['name' => 'lavador']);
        $lavador->syncPermissions([
            'ver-control-lavado',
            'ver-cita',
            'ver-perfil',
            'editar-perfil',
        ]);

        // Rol supervisor: gestiona lavadores y controla el proceso
        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisor->syncPermissions([
            'ver-control-lavado',
            'crear-control-lavado',
            'editar-control-lavado',
            'ver-lavador',
            'ver-cita',
            'confirmar-cita',
            'ver-pago-comision',
            'ver-historial-pago-comision',
            'ver-perfil',
            'editar-perfil',
        ]);

        // Rol contador: acceso a todos los reportes y exports, sin CRUD
        $contador = Role::firstOrCreate(['name' => 'contador']);
        $contador->syncPermissions([
            'ver-venta',
            'mostrar-venta',
            'reporte-diario-venta',
            'reporte-semanal-venta',
            'reporte-mensual-venta',
            'reporte-personalizado-venta',
            'exportar-reporte-venta',
            'ver-compra',
            'mostrar-compra',
            'reporte-diario-compra',
            'reporte-semanal-compra',
            'reporte-mensual-compra',
            'reporte-personalizado-compra',
            'exportar-reporte-compra',
            'ver-pago-comision',
            'ver-historial-pago-comision',
            'reporte-fidelidad',
            'exportar-fidelidad',
            'reporte-tarjeta-regalo',
            'exportar-tarjeta-regalo',
            'ver-perfil',
            'editar-perfil',
        ]);
    }
}
