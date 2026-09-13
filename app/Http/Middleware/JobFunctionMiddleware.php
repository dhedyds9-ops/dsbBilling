<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JobFunctionMiddleware
{
    public function handle(Request $request, Closure $next, ...$functions): Response
    {
        if (!Auth::check()) {
            return abort(401);
        }

        $user = Auth::user();

        // 0. Administrator bypasses job function restrictions
        if ($user->hasRole(\App\Enums\UserRole::Administrator->value)) {
            return $next($request);
        }

        // 1. Job Function hanya berlaku untuk Manager atau Reseller
        $allowedRoles = [\App\Enums\UserRole::Manager->value, \App\Enums\UserRole::Reseller->value];
        $userRole = $user->roles->first()->name ?? null;

        if (!in_array($userRole, $allowedRoles)) {
            return abort(403, 'Unauthorized portal access.');
        }

        // 2. Cek apakah Job Function user ada di dalam daftar yang diizinkan
        if (in_array($user->job_function, $functions)) {
            return $next($request);
        }

        // Fail-closed
        return abort(403, 'Unauthorized job function access.');
    }
}
