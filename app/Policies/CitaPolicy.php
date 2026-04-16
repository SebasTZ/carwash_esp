<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny([
            'ver-cita',
            'crear-cita',
            'editar-cita',
            'eliminar-cita',
            'calendario-cita',
            'confirmar-cita',
        ]);
    }

    public function view(User $user, Cita $cita): bool
    {
        if (!$user->canAny([
            'ver-cita',
            'crear-cita',
            'editar-cita',
            'eliminar-cita',
            'confirmar-cita',
        ])) {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        return $this->ownsResource($user, $cita);
    }

    public function create(User $user): bool
    {
        return $user->can('crear-cita');
    }

    public function update(User $user, Cita $cita): bool
    {
        if (!$user->can('editar-cita')) {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        return $this->ownsResource($user, $cita);
    }

    public function delete(User $user, Cita $cita): bool
    {
        if (!$user->can('eliminar-cita')) {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        return $this->ownsResource($user, $cita);
    }

    public function confirm(User $user, Cita $cita): bool
    {
        if (!$user->can('confirmar-cita')) {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        return $this->ownsResource($user, $cita);
    }

    public function viewCalendar(User $user): bool
    {
        return $user->can('calendario-cita');
    }

    public function export(User $user): bool
    {
        return $user->can('exportar-reporte-cita');
    }

    private function ownsResource(User $user, Cita $cita): bool
    {
        // Compatibilidad con datos legacy sin usuario creador.
        if ($cita->user_id === null) {
            return true;
        }

        return $cita->user_id === $user->id;
    }

    private function isPrivileged(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin', 'administrador']);
    }
}
