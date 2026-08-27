<?php

namespace App\Policies;

use App\Models\CRM\Lead;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('lead.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('lead.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.update');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.delete');
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('lead.export');
    }
}
