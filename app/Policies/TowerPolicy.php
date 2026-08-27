<?php

namespace App\Policies;

use App\Models\ISP\Tower;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TowerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tower.view');
    }

    public function view(User $user, Tower $tower): bool
    {
        return $user->hasPermission('tower.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('tower.create');
    }

    public function update(User $user, Tower $tower): bool
    {
        return $user->hasPermission('tower.update');
    }

    public function delete(User $user, Tower $tower): bool
    {
        return $user->hasPermission('tower.delete');
    }

    public function restore(User $user, Tower $tower): bool
    {
        return $user->hasPermission('tower.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('tower.export');
    }
}
