<?php

namespace App\Policies;

use App\Models\CRM\QualityControl;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QualityControlPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('quality-control.view');
    }

    public function view(User $user, QualityControl $qualityControl): bool
    {
        return $user->hasPermission('quality-control.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('quality-control.create');
    }

    public function update(User $user, QualityControl $qualityControl): bool
    {
        return $user->hasPermission('quality-control.update');
    }

    public function delete(User $user, QualityControl $qualityControl): bool
    {
        return $user->hasPermission('quality-control.delete');
    }

    public function restore(User $user, QualityControl $qualityControl): bool
    {
        return $user->hasPermission('quality-control.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('quality-control.export');
    }
}
