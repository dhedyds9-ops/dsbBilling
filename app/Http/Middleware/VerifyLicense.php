<?php

namespace App\Http\Middleware;

use App\Services\License\LicenseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLicense
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pengecualian route agar aset CSS/JS dan API tidak diblokir
        $exceptPaths = [
            'assets/*',
            'build/*',
            'api/*',
            'up',
            'livewire/*',
        ];

        if ($request->is($exceptPaths) || app()->environment('testing')) {
            return $next($request);
        }

        /** @var LicenseManager $licenseManager */
        $licenseManager = app(LicenseManager::class);

        if (!$licenseManager->isValid()) {
            $info = $licenseManager->getLicenseInfo();
            return response()->view('errors.license_locked', [
                'status'  => $info['status'] ?? 'suspended',
                'message' => $info['message'] ?? 'Lisensi software dsBilling Anda belum aktif atau telah dinonaktifkan oleh administrator.',
                'client'  => $info['client'] ?? config('app.name'),
            ], 403);
        }

        return $next($request);
    }
}
