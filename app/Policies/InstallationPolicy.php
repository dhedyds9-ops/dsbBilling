<?php

namespace App\Policies;

use App\Models\CRM\Installation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InstallationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('installation.view');
    }

    public function view(User $user, Installation $installation): bool
    {
        return $user->hasPermission('installation.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('installation.create');
    }

    public function update(User $user, Installation $installation): bool
    {
        return $user->hasPermission('installation.update');
    }

    public function delete(User $user, Installation $installation): bool
    {
        return $user->hasPermission('installation.delete');
    }

    public function restore(User $user, Installation $installation): bool
    {
        return $user->hasPermission('installation.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('installation.export');
    }
}
