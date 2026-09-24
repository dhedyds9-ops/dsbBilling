<?php

namespace App\Policies;

use App\Models\ISP\Voucher;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class VoucherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('voucher.view');
    }

    public function view(User $user, Voucher $voucher): bool
    {
        return $user->hasPermission('voucher.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('voucher.create');
    }

    public function update(User $user, Voucher $voucher): bool
    {
        return $user->hasPermission('voucher.update');
    }

    public function delete(User $user, Voucher $voucher): bool
    {
        return $user->hasPermission('voucher.delete');
    }

    public function restore(User $user, Voucher $voucher): bool
    {
        return $user->hasPermission('voucher.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('voucher.export');
    }
}
