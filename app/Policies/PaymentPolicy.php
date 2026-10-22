<?php

namespace App\Policies;

use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('reseller') || !empty($user->getEffectiveResellerId()) || $user->hasPermission('payment.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasRole('reseller') || !empty($user->getEffectiveResellerId())) {
            $effectiveId = $user->getEffectiveResellerId() ?? $user->id;
            return (int)$payment->reseller_id === (int)$effectiveId 
                || (int)$payment->created_by === (int)$effectiveId
                || ($payment->customer && ((int)$payment->customer->reseller_id === (int)$effectiveId || (int)$payment->customer->created_by === (int)$effectiveId));
        }
        return $user->hasPermission('payment.view');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('reseller') || !empty($user->getEffectiveResellerId()) || $user->hasPermission('payment.create');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.update');
    }

    public function delete(User $user, Payment $payment): bool
    {
        if ($user->hasRole('reseller') || !empty($user->getEffectiveResellerId())) {
            return false;
        }
        return $user->hasPermission('payment.delete');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasRole('reseller') || !empty($user->getEffectiveResellerId()) || $user->hasPermission('payment.export');
    }
}
