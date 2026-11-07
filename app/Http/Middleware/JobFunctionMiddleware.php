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
        if ($user->hasRole(\App\Enums\UserRole::Administrator->value) || $user->hasRole('superadmin') || $user->hasRole('administrator')) {
            return $next($request);
        }

        // 1. Direct role matches (teknisi/technician, noc, dll)
        $upperFunctions = array_map('strtoupper', $functions);
        if (in_array('TECHNICIAN', $upperFunctions) && ($user->hasRole(['teknisi', 'technician']) || (method_exists($user, 'isTechnician') && $user->isTechnician()))) {
            return $next($request);
        }
        if (in_array('NOC', $upperFunctions) && $user->hasRole('noc')) {
            return $next($request);
        }

        // 2. Cek apakah Job Function user ada di dalam daftar yang diizinkan
        if (in_array(strtoupper($user->job_function ?? ''), $upperFunctions)) {
            return $next($request);
        }

        // Fail-closed
        return abort(403, 'Unauthorized job function access.');
    }
}

