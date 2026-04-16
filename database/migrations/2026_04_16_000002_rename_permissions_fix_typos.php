<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

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

        $this->applyRenames($renames);
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

        $this->applyRenames($renames);
    }

    /**
     * Aplica renombres de permisos de forma segura para evitar conflictos
     * con el índice único (name, guard_name).
     */
    private function applyRenames(array $renames): void
    {
        $tableNames = config('permission.table_names');
        $permissionPivotKey = app(PermissionRegistrar::class)->pivotPermission;

        DB::transaction(function () use ($renames, $tableNames, $permissionPivotKey) {
            foreach ($renames as $old => $new) {
                $oldPermissions = DB::table($tableNames['permissions'])
                    ->where('name', $old)
                    ->get();

                foreach ($oldPermissions as $oldPermission) {
                    $targetPermission = DB::table($tableNames['permissions'])
                        ->where('name', $new)
                        ->where('guard_name', $oldPermission->guard_name)
                        ->first();

                    if ($targetPermission !== null) {
                        DB::table($tableNames['role_has_permissions'])
                            ->where($permissionPivotKey, $oldPermission->id)
                            ->update([$permissionPivotKey => $targetPermission->id]);

                        DB::table($tableNames['model_has_permissions'])
                            ->where($permissionPivotKey, $oldPermission->id)
                            ->update([$permissionPivotKey => $targetPermission->id]);

                        DB::table($tableNames['permissions'])
                            ->where('id', $oldPermission->id)
                            ->delete();

                        continue;
                    }

                    DB::table($tableNames['permissions'])
                        ->where('id', $oldPermission->id)
                        ->update(['name' => $new]);
                }
            }
        });

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
