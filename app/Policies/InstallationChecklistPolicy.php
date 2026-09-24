<?php

namespace App\Policies;

use App\Models\CRM\InstallationChecklist;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InstallationChecklistPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('installation-checklist.view');
    }

    public function view(User $user, InstallationChecklist $installationChecklist): bool
    {
        return $user->hasPermission('installation-checklist.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('installation-checklist.create');
    }

    public function update(User $user, InstallationChecklist $installationChecklist): bool
    {
        return $user->hasPermission('installation-checklist.update');
    }

    public function delete(User $user, InstallationChecklist $installationChecklist): bool
    {
        return $user->hasPermission('installation-checklist.delete');
    }

    public function restore(User $user, InstallationChecklist $installationChecklist): bool
    {
        return $user->hasPermission('installation-checklist.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('installation-checklist.export');
    }
}
