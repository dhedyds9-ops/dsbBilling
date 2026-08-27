<?php

namespace App\Policies;

use App\Models\ISP\Odc;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OdcPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('odc.view');
    }

    public function view(User $user, Odc $odc): bool
    {
        return $user->hasPermission('odc.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('odc.create');
    }

    public function update(User $user, Odc $odc): bool
    {
        return $user->hasPermission('odc.update');
    }

    public function delete(User $user, Odc $odc): bool
    {
        return $user->hasPermission('odc.delete');
    }

    public function restore(User $user, Odc $odc): bool
    {
        return $user->hasPermission('odc.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('odc.export');
    }
}
