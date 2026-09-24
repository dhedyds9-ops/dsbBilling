<?php

namespace App\Policies;

use App\Models\ISP\Router;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RouterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('router.view');
    }

    public function view(User $user, Router $router): bool
    {
        return $user->hasPermission('router.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('router.create');
    }

    public function update(User $user, Router $router): bool
    {
        return $user->hasPermission('router.update');
    }

    public function delete(User $user, Router $router): bool
    {
        return $user->hasPermission('router.delete');
    }

    public function restore(User $user, Router $router): bool
    {
        return $user->hasPermission('router.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('router.export');
    }
}
