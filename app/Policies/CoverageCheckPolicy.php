<?php

namespace App\Policies;

use App\Models\CRM\CoverageCheck;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoverageCheckPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('coverage-check.view');
    }

    public function view(User $user, CoverageCheck $coverageCheck): bool
    {
        return $user->hasPermission('coverage-check.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('coverage-check.create');
    }

    public function update(User $user, CoverageCheck $coverageCheck): bool
    {
        return $user->hasPermission('coverage-check.update');
    }

    public function delete(User $user, CoverageCheck $coverageCheck): bool
    {
        return $user->hasPermission('coverage-check.delete');
    }

    public function restore(User $user, CoverageCheck $coverageCheck): bool
    {
        return $user->hasPermission('coverage-check.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('coverage-check.export');
    }
}
