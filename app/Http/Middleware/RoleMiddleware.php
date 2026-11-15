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
            if (($role === 'technician' || $role === 'teknisi') && ($user->hasRole(['technician', 'teknisi']) || (method_exists($user, 'isTechnician') && $user->isTechnician()))) {
                return $next($request);
            }
            if (($role === 'admin_gudang' || $role === 'gudang') && ($user->hasRole(['admin_gudang', 'gudang']) || (method_exists($user, 'isWarehouseAdmin') && $user->isWarehouseAdmin()))) {
                return $next($request);
            }
            if ($role === 'administrator' && $user->hasRole('superadmin')) {
                return $next($request);
            }
        }

        // Redirect berdasarkan role + job_function user yang sebenarnya
        if ($user->hasRole(['technician', 'teknisi']) || (method_exists($user, 'isTechnician') && $user->isTechnician())) {
            return new \Illuminate\Http\RedirectResponse(route('technician.dashboard'));
        }

        if ($user->hasRole('noc') || strtoupper($user->job_function ?? '') === 'NOC') {
            return new \Illuminate\Http\RedirectResponse(route('noc.overview'));
        }

        if ($user->hasRole(UserRole::Administrator->value) || $user->hasRole('superadmin')) {
            return new \Illuminate\Http\RedirectResponse(route('dashboard'));
        }

        if ($user->hasRole(UserRole::Reseller->value) || (method_exists($user, 'isReseller') && $user->isReseller())) {
            return new \Illuminate\Http\RedirectResponse(route('reseller-portal.dashboard'));
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            if (str_contains(strtolower($user->job_title ?? ''), 'gudang') 
                || str_contains(strtolower($user->job_title ?? ''), 'logistik')
                || ($user->hasPermission('inventory.view') && !$user->hasPermission('router.view') && !$user->hasPermission('customer.view'))) {
                return new \Illuminate\Http\RedirectResponse(route('inventory.dashboard'));
            }
            return new \Illuminate\Http\RedirectResponse(route('dashboard'));
        }

        if ($user->hasRole(UserRole::Customer->value)) {
            // Customer diarahkan ke customer portal
            return new \Illuminate\Http\RedirectResponse(route('customer-portal.dashboard'));
        }

        // User tanpa role yang valid: Logout dan kembali ke login
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki Role Akses (Minimal Manager/Administrator). Hubungi Super Admin.');
    }
}

