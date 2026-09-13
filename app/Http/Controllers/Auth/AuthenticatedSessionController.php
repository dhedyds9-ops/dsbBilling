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
        
        if ($user->hasRole('customer')) {
            return redirect()->intended(route('customer-portal.dashboard', absolute: false));
        }
        
        if ($user->hasRole('reseller')) {
            return redirect()->intended(route('reseller-portal.dashboard', absolute: false));
        }
        
        if ($user->job_function === \App\Enums\JobFunction::TECHNICIAN->value) {
            return redirect()->intended(route('technician.dashboard', absolute: false));
        }
        
        if ($user->job_function === \App\Enums\JobFunction::NOC->value || 
            $user->hasRole('noc') || $user->hasRole('noc_operator') || $user->hasRole('operator') || $user->hasRole('owner')) {
            return redirect()->intended(route('noc.overview', absolute: false));
        }
        
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
