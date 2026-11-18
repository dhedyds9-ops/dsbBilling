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

    /**
     * Authenticate user into web session using a single-use mobile SSO token.
     * GET /sso/login?token={ssoToken}
     */
    public function ssoLogin(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token');
        if (empty($token)) {
            return redirect()->route('login')->withErrors(['error' => 'Token SSO tidak ditemukan.']);
        }

        $cacheKey = 'mobile_sso_token:' . $token;
        $data = \Illuminate\Support\Facades\Cache::pull($cacheKey);

        if (!$data || empty($data['user_id'])) {
            return redirect()->route('login')->withErrors(['error' => 'Sesi login satu-klik telah kedaluwarsa atau sudah digunakan. Silakan coba kembali dari aplikasi.']);
        }

        $user = \App\Models\User::withoutGlobalScopes()->find($data['user_id']);
        if (!$user || (isset($user->is_active) && !$user->is_active)) {
            return redirect()->route('login')->withErrors(['error' => 'Akun pengguna tidak ditemukan atau dinonaktifkan.']);
        }

        // Establish standard web authentication
        Auth::guard('web')->login($user, remember: true);
        $request->session()->regenerate();

        \Illuminate\Support\Facades\Log::info("SSO 1-Click Auto-Login: User #{$user->id} ({$user->name}) authenticated successfully.");

        // Clean any customer-portal intended url if user is staff/reseller
        session()->forget('url.intended');

        $targetUrl = $data['target_path'] ?? null;
        if (!empty($targetUrl)) {
            return redirect($targetUrl);
        }

        // Fallback to role-based portal
        if ($user->hasRole('reseller') || (method_exists($user, 'isReseller') && $user->isReseller())) {
            return redirect()->route('reseller-portal.dashboard');
        } elseif (strtoupper($user->job_function ?? '') === 'TECHNICIAN' || $user->hasRole(['technician', 'teknisi']) || (method_exists($user, 'isTechnician') && $user->isTechnician())) {
            return redirect()->route('technician.dashboard');
        } elseif ($user->hasRole('customer')) {
            return redirect()->route('customer-portal.dashboard');
        } elseif ($user->hasRole('noc') || strtoupper($user->job_function ?? '') === 'NOC') {
            return redirect()->route('noc.overview');
        }

        return redirect()->route('dashboard');
    }
}

