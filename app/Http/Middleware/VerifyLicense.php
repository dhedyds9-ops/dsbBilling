<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pengecualian route agar tidak terjadi redirect loop
        $exceptPaths = [
            'pengaturan/lisensi',
            'login',
            'logout',
            'assets/*',
            'api/*'
        ];

        if ($request->is($exceptPaths) || app()->environment('testing')) {
            return $next($request);
        }

        $licenseManager = app(\App\Services\License\LicenseManager::class);

        if (!$licenseManager->isValid()) {
            // Redirect ke halaman pengaturan lisensi jika belum valid
            return redirect()->route('pengaturan.license.index')
                ->with('error', 'Lisensi tidak valid atau telah kadaluarsa. Silakan masukkan License Key Anda.');
        }

        return $next($request);
    }
}
