<?php

namespace App\Policies;

use App\Models\CRM\Survey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('survey.view');
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->hasPermission('survey.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('survey.create');
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->hasPermission('survey.update');
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->hasPermission('survey.delete');
    }

    public function restore(User $user, Survey $survey): bool
    {
        return $user->hasPermission('survey.restore');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('survey.export');
    }
}
