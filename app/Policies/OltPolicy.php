<?php

namespace App\Policies;

use App\Models\ISP\Olt;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OltPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('olt.view');
    }

    public function view(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('olt.create');
    }

    public function update(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.update');
    }

    public function delete(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.delete');
    }

    public function restore(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('olt.export');
    }
}
