<?php

namespace App\Policies;

use App\Models\CRM\Activation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ActivationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('activation.view');
    }

    public function view(User $user, Activation $activation): bool
    {
        return $user->hasPermission('activation.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('activation.create');
    }

    public function update(User $user, Activation $activation): bool
    {
        return $user->hasPermission('activation.update');
    }

    public function delete(User $user, Activation $activation): bool
    {
        return $user->hasPermission('activation.delete');
    }

    public function restore(User $user, Activation $activation): bool
    {
        return $user->hasPermission('activation.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('activation.export');
    }
}
