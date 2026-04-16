<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AuthorizationAuditService
{
    /**
     * Registra cambios de roles asignados a un usuario.
     *
     * @param array<int, string> $rolesAntes
     * @param array<int, string> $rolesDespues
     */
    public function logUserRolesSynced(?User $actor, User $targetUser, array $rolesAntes, array $rolesDespues, string $action): void
    {
        $this->write('user_roles_synced', [
            'action' => $action,
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email,
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
            'roles_before' => array_values($rolesAntes),
            'roles_after' => array_values($rolesDespues),
            'changed_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Registra cambios de permisos asignados a un rol.
     *
     * @param array<int, string> $permisosAntes
     * @param array<int, string> $permisosDespues
     */
    public function logRolePermissionsSynced(?User $actor, Role $role, array $permisosAntes, array $permisosDespues, string $action): void
    {
        $this->write('role_permissions_synced', [
            'action' => $action,
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email,
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions_before' => array_values($permisosAntes),
            'permissions_after' => array_values($permisosDespues),
            'changed_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Registra eventos de eliminación de roles de un usuario.
     *
     * @param array<int, string> $rolesEliminados
     */
    public function logUserDeletedWithRoles(?User $actor, User $targetUser, array $rolesEliminados): void
    {
        $this->write('user_deleted_with_roles', [
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email,
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
            'removed_roles' => array_values($rolesEliminados),
            'changed_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Registra eventos de eliminación de un rol.
     *
     * @param array<int, string> $permisosAsociados
     */
    public function logRoleDeleted(?User $actor, Role $role, array $permisosAsociados): void
    {
        $this->write('role_deleted', [
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email,
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions_before_delete' => array_values($permisosAsociados),
            'changed_at' => now()->toDateTimeString(),
        ]);
    }

    private function write(string $event, array $context): void
    {
        Log::info("authorization.audit.{$event}", $context);
    }
}
