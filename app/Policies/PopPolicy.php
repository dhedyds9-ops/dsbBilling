<?php

namespace App\Policies;

use App\Models\ISP\Pop;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PopPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pop.view');
    }

    public function view(User $user, Pop $pop): bool
    {
        return $user->hasPermission('pop.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pop.create');
    }

    public function update(User $user, Pop $pop): bool
    {
        return $user->hasPermission('pop.update');
    }

    public function delete(User $user, Pop $pop): bool
    {
        return $user->hasPermission('pop.delete');
    }

    public function restore(User $user, Pop $pop): bool
    {
        return $user->hasPermission('pop.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('pop.export');
    }
}
