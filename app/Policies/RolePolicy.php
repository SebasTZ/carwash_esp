<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($this->isPrivileged($user)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canAny(['ver-role', 'crear-role', 'editar-role', 'eliminar-role']);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('ver-role');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-role');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('editar-role');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('eliminar-role');
    }

    private function isPrivileged(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin', 'administrador']);
    }
}
