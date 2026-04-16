<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        return $user->canAny(['ver-user', 'crear-user', 'editar-user', 'eliminar-user']);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('ver-user');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-user');
    }

    public function update(User $user, User $model): bool
    {
        if ($model->id === $user->id) {
            return true; // puede editar su propio perfil
        }

        return $user->can('editar-user');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->id === $user->id) {
            return false; // no puede eliminarse a sí mismo
        }

        return $user->can('eliminar-user');
    }

    private function isPrivileged(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin', 'administrador']);
    }
}
