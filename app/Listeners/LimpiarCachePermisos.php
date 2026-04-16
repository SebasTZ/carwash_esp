<?php

namespace App\Listeners;

use Spatie\Permission\PermissionRegistrar;

class LimpiarCachePermisos
{
    public function __construct(private PermissionRegistrar $permissionRegistrar) {}

    public function handle(object $event): void
    {
        $this->permissionRegistrar->forgetCachedPermissions();
    }
}
