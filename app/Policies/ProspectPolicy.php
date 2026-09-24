<?php

namespace App\Policies;

use App\Models\CRM\Prospect;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProspectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('prospect.view');
    }

    public function view(User $user, Prospect $prospect): bool
    {
        return $user->hasPermission('prospect.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('prospect.create');
    }

    public function update(User $user, Prospect $prospect): bool
    {
        return $user->hasPermission('prospect.update');
    }

    public function delete(User $user, Prospect $prospect): bool
    {
        return $user->hasPermission('prospect.delete');
    }

    public function restore(User $user, Prospect $prospect): bool
    {
        return $user->hasPermission('prospect.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('prospect.export');
    }
}
