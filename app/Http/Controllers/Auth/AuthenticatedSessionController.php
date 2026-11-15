<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $routeName = $request->route()->getName();
        
        if ($routeName === 'customer.login') {
            return view('auth.login-customer');
        }
        
        return view('auth.login-admin');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $user = Auth::user();
        
        // Bersihkan session intended jika sebelumnya tersangkut di portal customer
        $intended = session()->get('url.intended', '');
        $isCustomerPortalIntended = str_contains($intended, 'customer-portal');

        // 1. Cek Teknisi Lapangan (Prioritas Operasional)
        if (strtoupper($user->job_function ?? '') === 'TECHNICIAN' 
            || strtoupper($user->job_function ?? '') === 'TEKNISI'
            || $user->hasRole(['technician', 'teknisi']) 
            || (method_exists($user, 'isTechnician') && $user->isTechnician())) {
            if ($isCustomerPortalIntended) {
                session()->forget('url.intended');
                return redirect()->route('technician.dashboard');
            }
            return redirect()->intended(route('technician.dashboard', absolute: false));
        }

        // 2. Cek NOC & Monitoring
        if (strtoupper($user->job_function ?? '') === 'NOC' || $user->hasRole('noc')) {
            if ($isCustomerPortalIntended) {
                session()->forget('url.intended');
                return redirect()->route('noc.overview');
            }
            return redirect()->intended(route('noc.overview', absolute: false));
        }

        // 3. Staff Gudang / Logistik
        if (!$user->isSuperAdmin()) {
            $isWarehouseStaff = str_contains(strtolower($user->job_title ?? ''), 'gudang')
                || str_contains(strtolower($user->job_title ?? ''), 'logistik')
                || ($user->hasPermission('inventory.view') && !$user->hasPermission('router.view') && !$user->hasPermission('customer.view'));

            if ($isWarehouseStaff) {
                if ($isCustomerPortalIntended) {
                    session()->forget('url.intended');
                    return redirect()->route('inventory.dashboard');
                }
                return redirect()->intended(route('inventory.dashboard', absolute: false));
            }
        }

        // 4. Reseller Partner
        if ($user->hasRole('reseller') || (method_exists($user, 'isReseller') && $user->isReseller())) {
            if ($isCustomerPortalIntended) {
                session()->forget('url.intended');
                return redirect()->route('reseller-portal.dashboard');
            }
            return redirect()->intended(route('reseller-portal.dashboard', absolute: false));
        }

        // 5. Administrator dan Manager (Backoffice Umum)
        if ($user->hasRole('administrator') || $user->hasRole('manager') || $user->isSuperAdmin()) {
            if ($isCustomerPortalIntended) {
                session()->forget('url.intended');
                return redirect()->route('dashboard');
            }
            if (str_contains($intended, 'reseller-portal')) {
                return redirect()->intended(route('reseller-portal.dashboard', absolute: false));
            }
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // 6. Pelanggan (Customer Portal) - HANYA jika bukan staff internal
        if ($user->hasRole('customer')) {
            return redirect()->intended(route('customer-portal.dashboard', absolute: false));
        }
        
        // Default fallback
        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}

