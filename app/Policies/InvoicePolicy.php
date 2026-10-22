<?php

namespace App\Policies;

use App\Models\Billing\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('reseller') || !empty($user->getEffectiveResellerId()) || $user->hasPermission('invoice.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('reseller') || !empty($user->getEffectiveResellerId())) {
            $effectiveId = $user->getEffectiveResellerId() ?? $user->id;
            return (int)$invoice->reseller_id === (int)$effectiveId 
                || ($invoice->customer && ((int)$invoice->customer->reseller_id === (int)$effectiveId || (int)$invoice->customer->created_by === (int)$effectiveId));
        }
        return $user->hasPermission('invoice.view');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('reseller') || !empty($user->getEffectiveResellerId()) || $user->hasPermission('invoice.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('reseller') || !empty($user->getEffectiveResellerId())) {
            $effectiveId = $user->getEffectiveResellerId() ?? $user->id;
            return (int)$invoice->reseller_id === (int)$effectiveId 
                || ($invoice->customer && ((int)$invoice->customer->reseller_id === (int)$effectiveId || (int)$invoice->customer->created_by === (int)$effectiveId));
        }
        return $user->hasPermission('invoice.update');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('reseller') || !empty($user->getEffectiveResellerId())) {
            return false;
        }
        return $user->hasPermission('invoice.delete');
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoice.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasRole('reseller') || !empty($user->getEffectiveResellerId()) || $user->hasPermission('invoice.export');
    }
}
