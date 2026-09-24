<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $permissions = [
            'view-dashboard',
            'view-crm',
            'view-billing',
            'view-network',
            'view-gis',
        ];

        $user = Auth::user();
        $userData = $user ? [
            'name' => $user->name ?? 'User',
            'email' => $user->email ?? 'user@example.com',
            'avatar' => null,
            'role' => $user->job_title ?: ($user->roles->first()->display_name ?? 'Staff'),
        ] : null;

        $view->with([
            'navigation' => [], // Unused, we use MenuRegistry natively in the components
            'permissions' => $permissions,
            'favorites' => [],
            'breadcrumbs' => [],
            'user' => $userData,
        ]);
    }
}
