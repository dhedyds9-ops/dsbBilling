<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware — Middleware untuk enforce role-based access control.
 *
 * PRINSIP:
 *  - Hanya 3 backoffice roles: administrator, manager, reseller
 *  - 1 portal role: customer (redirect ke customer portal)
 *
 * Penggunaan di route:
 *   ->middleware('role:administrator')
 *   ->middleware('role:administrator,manager')
 *   ->middleware('role:administrator,manager,reseller')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return new \Illuminate\Http\RedirectResponse(route('login'));
        }

        $user = Auth::user();

        // Cek apakah user memiliki salah satu dari roles yang diperlukan
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Redirect berdasarkan role + job_function user yang sebenarnya
        if ($user->hasRole(UserRole::Customer->value)) {
            // Customer diarahkan ke customer portal
            return new \Illuminate\Http\RedirectResponse(route('customer-portal.dashboard'));
        }

        if ($user->hasRole(UserRole::Reseller->value)) {
            // Reseller (dan sub-staff) diarahkan ke reseller portal
            return new \Illuminate\Http\RedirectResponse(route('reseller-portal.dashboard'));
        }

        if ($user->hasRole(UserRole::Administrator->value)) {
            // Admin diarahkan ke admin dashboard
            return new \Illuminate\Http\RedirectResponse(route('dashboard'));
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            // Manager diarahkan berdasarkan job_function
            if ($user->job_function === \App\Enums\JobFunction::NOC->value) {
                return new \Illuminate\Http\RedirectResponse(route('noc.overview'));
            }
            if ($user->job_function === \App\Enums\JobFunction::TECHNICIAN->value) {
                return new \Illuminate\Http\RedirectResponse(route('technician.dashboard'));
            }
            // Fallback manager portal
            return new \Illuminate\Http\RedirectResponse(route('dashboard'));
        }

        // User tanpa role yang valid: kembali ke login
        return new \Illuminate\Http\RedirectResponse(route('login'));
    }
}
