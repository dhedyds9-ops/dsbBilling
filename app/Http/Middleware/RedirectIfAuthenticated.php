<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                if ($user->hasRole('customer')) {
                    return new \Illuminate\Http\RedirectResponse(route('customer-portal.dashboard', absolute: false));
                }
                
                if ($user->hasRole('reseller')) {
                    return new \Illuminate\Http\RedirectResponse(route('reseller-portal.dashboard', absolute: false));
                }
                
                if ($user->job_function === \App\Enums\JobFunction::TECHNICIAN->value) {
                    return new \Illuminate\Http\RedirectResponse(route('technician.dashboard', absolute: false));
                }
                
                if ($user->job_function === \App\Enums\JobFunction::NOC->value || 
                    $user->hasRole('noc') || $user->hasRole('noc_operator') || $user->hasRole('operator') || $user->hasRole('owner')) {
                    return new \Illuminate\Http\RedirectResponse(route('noc.overview', absolute: false));
                }
                
                return new \Illuminate\Http\RedirectResponse(route('dashboard', absolute: false));
            }
        }

        return $next($request);
    }
}
