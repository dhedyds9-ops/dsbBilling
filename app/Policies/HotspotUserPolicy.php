<?php

namespace App\Policies;

use App\Models\ISP\HotspotUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HotspotUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hotspot-user.view');
    }

    public function view(User $user, HotspotUser $hotspotUser): bool
    {
        return $user->hasPermission('hotspot-user.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('hotspot-user.create');
    }

    public function update(User $user, HotspotUser $hotspotUser): bool
    {
        return $user->hasPermission('hotspot-user.update');
    }

    public function delete(User $user, HotspotUser $hotspotUser): bool
    {
        return $user->hasPermission('hotspot-user.delete');
    }

    public function restore(User $user, HotspotUser $hotspotUser): bool
    {
        return $user->hasPermission('hotspot-user.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('hotspot-user.export');
    }
}
