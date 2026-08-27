<?php

namespace App\Policies;

use App\Models\Billing\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoice.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoice.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoice.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoice.update');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoice.delete');
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoice.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('invoice.export');
    }
}
