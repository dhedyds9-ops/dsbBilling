<?php

namespace App\Policies;

use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('payment.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('payment.create');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.update');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.delete');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('payment.export');
    }
}
