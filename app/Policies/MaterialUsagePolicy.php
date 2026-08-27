<?php

namespace App\Policies;

use App\Models\CRM\MaterialUsage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MaterialUsagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('material-usage.view');
    }

    public function view(User $user, MaterialUsage $materialUsage): bool
    {
        return $user->hasPermission('material-usage.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('material-usage.create');
    }

    public function update(User $user, MaterialUsage $materialUsage): bool
    {
        return $user->hasPermission('material-usage.update');
    }

    public function delete(User $user, MaterialUsage $materialUsage): bool
    {
        return $user->hasPermission('material-usage.delete');
    }

    public function restore(User $user, MaterialUsage $materialUsage): bool
    {
        return $user->hasPermission('material-usage.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('material-usage.export');
    }
}
