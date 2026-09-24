<?php

namespace App\Policies;

use App\Models\ISP\PPPoEUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PPPoEUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pppoe-user.view');
    }

    public function view(User $user, PPPoEUser $pppoeUser): bool
    {
        return $user->hasPermission('pppoe-user.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pppoe-user.create');
    }

    public function update(User $user, PPPoEUser $pppoeUser): bool
    {
        return $user->hasPermission('pppoe-user.update');
    }

    public function delete(User $user, PPPoEUser $pppoeUser): bool
    {
        return $user->hasPermission('pppoe-user.delete');
    }

    public function restore(User $user, PPPoEUser $pppoeUser): bool
    {
        return $user->hasPermission('pppoe-user.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('pppoe-user.export');
    }
}
