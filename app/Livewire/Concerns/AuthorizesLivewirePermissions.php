<?php

namespace App\Livewire\Concerns;

trait AuthorizesLivewirePermissions
{
    protected function ensureAuthenticated(): void
    {
        abort_unless(auth()->check(), 401);
    }

    protected function ensurePermission(string $permission): void
    {
        $this->ensureAuthenticated();

        abort_unless($this->hasPermission($permission), 403);
    }

    protected function ensureAnyPermission(array $permissions): void
    {
        $this->ensureAuthenticated();

        abort_unless($this->userHasAnyPermission($permissions), 403);
    }

    protected function ensurePermissionOrRole(string|array $permissions, array $roles = []): void
    {
        $this->ensureAuthenticated();

        $permissions = is_array($permissions) ? $permissions : [$permissions];
        $hasPermission = $this->userHasAnyPermission($permissions);
        $hasRole = $this->userHasAnyRole($roles);

        abort_unless($hasPermission || $hasRole, 403);
    }

    protected function hasPermission(string $permission): bool
    {
        return (bool) (auth()->user()?->can($permission) ?? false);
    }

    protected function userHasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    protected function userHasAnyRole(array $roles): bool
    {
        if ($roles === []) {
            return false;
        }

        return (bool) (auth()->user()?->hasAnyRole($roles) ?? false);
    }

    protected function isPrivilegedUser(array $additionalRoles = []): bool
    {
        $roles = array_values(array_unique(array_merge(
            ['admin', 'superadmin', 'administrador'],
            $additionalRoles
        )));

        return $this->userHasAnyRole($roles);
    }
}
