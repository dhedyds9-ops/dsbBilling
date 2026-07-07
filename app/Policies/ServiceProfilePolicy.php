<?php

namespace App\Policies;

use App\Models\ISP\ServiceProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('service-profile.view');
    }

    public function view(User $user, ServiceProfile $serviceProfile): bool
    {
        return $user->hasPermission('service-profile.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('service-profile.create');
    }

    public function update(User $user, ServiceProfile $serviceProfile): bool
    {
        return $user->hasPermission('service-profile.update');
    }

    public function delete(User $user, ServiceProfile $serviceProfile): bool
    {
        return $user->hasPermission('service-profile.delete');
    }

    public function restore(User $user, ServiceProfile $serviceProfile): bool
    {
        return $user->hasPermission('service-profile.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('service-profile.export');
    }
}
