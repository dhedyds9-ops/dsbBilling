<?php

namespace App\Policies;

use App\Models\CRM\Quotation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('quotation.view');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission('quotation.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('quotation.create');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission('quotation.update');
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission('quotation.delete');
    }

    public function restore(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission('quotation.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('quotation.export');
    }
}
