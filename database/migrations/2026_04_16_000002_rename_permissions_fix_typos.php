<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            'ver-presentacione'    => 'ver-presentacion',
            'crear-presentacione'  => 'crear-presentacion',
            'editar-presentacione' => 'editar-presentacion',
            'eliminar-presentacione' => 'eliminar-presentacion',
            'ver-proveedore'       => 'ver-proveedor',
            'crear-proveedore'     => 'crear-proveedor',
            'editar-proveedore'    => 'editar-proveedor',
            'eliminar-proveedore'  => 'eliminar-proveedor',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')
                ->where('name', $old)
                ->update(['name' => $new]);
        }
    }

    public function down(): void
    {
        $renames = [
            'ver-presentacion'    => 'ver-presentacione',
            'crear-presentacion'  => 'crear-presentacione',
            'editar-presentacion' => 'editar-presentacione',
            'eliminar-presentacion' => 'eliminar-presentacione',
            'ver-proveedor'       => 'ver-proveedore',
            'crear-proveedor'     => 'crear-proveedore',
            'editar-proveedor'    => 'editar-proveedore',
            'eliminar-proveedor'  => 'eliminar-proveedore',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')
                ->where('name', $old)
                ->update(['name' => $new]);
        }
    }
};
