<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;

class VentaPolicy
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
        return $user->canAny([
            'ver-venta',
            'crear-venta',
            'mostrar-venta',
            'eliminar-venta',
        ]);
    }

    public function view(User $user, Venta $venta): bool
    {
        if (!$user->canAny(['ver-venta', 'mostrar-venta'])) {
            return false;
        }

        return $this->ownsResource($user, $venta);
    }

    public function create(User $user): bool
    {
        return $user->can('crear-venta');
    }

    public function delete(User $user, Venta $venta): bool
    {
        if (!$user->can('eliminar-venta')) {
            return false;
        }

        return $this->ownsResource($user, $venta);
    }

    public function viewReports(User $user): bool
    {
        return $user->canAny([
            'reporte-diario-venta',
            'reporte-semanal-venta',
            'reporte-mensual-venta',
            'reporte-personalizado-venta',
        ]);
    }

    public function export(User $user): bool
    {
        return $user->can('exportar-reporte-venta');
    }

    public function searchProducts(User $user): bool
    {
        return $user->can('crear-venta');
    }

    public function validateFidelizacion(User $user): bool
    {
        return $user->can('crear-venta');
    }

    private function ownsResource(User $user, Venta $venta): bool
    {
        return $venta->user_id !== null && $venta->user_id === $user->id;
    }

    private function isPrivileged(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin', 'administrador']);
    }
}
