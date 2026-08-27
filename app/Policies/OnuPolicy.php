<?php

namespace App\Policies;

use App\Models\ISP\Onu;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OnuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('onu.view');
    }

    public function view(User $user, Onu $onu): bool
    {
        return $user->hasPermission('onu.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('onu.create');
    }

    public function update(User $user, Onu $onu): bool
    {
        return $user->hasPermission('onu.update');
    }

    public function delete(User $user, Onu $onu): bool
    {
        return $user->hasPermission('onu.delete');
    }

    public function restore(User $user, Onu $onu): bool
    {
        return $user->hasPermission('onu.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('onu.export');
    }
}
