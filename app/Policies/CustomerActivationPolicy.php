<?php

namespace App\Policies;

use App\Models\CRM\CustomerActivation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerActivationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customer-activation.view');
    }

    public function view(User $user, CustomerActivation $customerActivation): bool
    {
        return $user->hasPermission('customer-activation.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('customer-activation.create');
    }

    public function update(User $user, CustomerActivation $customerActivation): bool
    {
        return $user->hasPermission('customer-activation.update');
    }

    public function delete(User $user, CustomerActivation $customerActivation): bool
    {
        return $user->hasPermission('customer-activation.delete');
    }

    public function restore(User $user, CustomerActivation $customerActivation): bool
    {
        return $user->hasPermission('customer-activation.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('customer-activation.export');
    }
}
