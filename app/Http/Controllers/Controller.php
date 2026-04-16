<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;

    protected function authorizePermission(string $permission): void
    {
        abort_unless((bool) request()->user()?->can($permission), 403);
    }

    protected function authorizeAnyPermission(array $permissions): void
    {
        abort_unless((bool) request()->user()?->canAny($permissions), 403);
    }
}
