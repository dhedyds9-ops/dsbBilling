<?php

namespace App\Policies;

use App\Models\ISP\Odp;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OdpPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('odp.view');
    }

    public function view(User $user, Odp $odp): bool
    {
        return $user->hasPermission('odp.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('odp.create');
    }

    public function update(User $user, Odp $odp): bool
    {
        return $user->hasPermission('odp.update');
    }

    public function delete(User $user, Odp $odp): bool
    {
        return $user->hasPermission('odp.delete');
    }

    public function restore(User $user, Odp $odp): bool
    {
        return $user->hasPermission('odp.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('odp.export');
    }
}
