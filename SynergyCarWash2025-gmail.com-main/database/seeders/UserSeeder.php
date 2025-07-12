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
            'ver-tarjeta-regalo',
            'crear-tarjeta-regalo',
            'reporte-tarjeta-regalo',
            'exportar-tarjeta-regalo',
            'ver-fidelidad',
            'gestionar-fidelidad',
            'reporte-fidelidad',
            'exportar-fidelidad',
        ];
        $cajero->syncPermissions($cajeroPermisos);
    }
}
