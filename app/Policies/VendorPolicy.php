<?php

namespace App\Policies;

use App\Models\ISP\Vendor;
use App\Models\User;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vendor.view');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->can('vendor.view');
    }

    public function create(User $user): bool
    {
        return $user->can('vendor.create');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->can('vendor.update');
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->can('vendor.delete');
    }

    public function restore(User $user, Vendor $vendor): bool
    {
        return $user->can('vendor.restore');
    }

    public function export(User $user): bool
    {
        return $user->can('vendor.export');
    }
}
