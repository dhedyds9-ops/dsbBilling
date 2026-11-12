<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileAppApiController extends Controller
{
    /**
     * Check for app updates (Staff or Customer).
     *
     * GET /api/mobile/app/check-update?app=staff|customer&version_code=10101
     * GET /api/customer/app/check-update?version_code=10101
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        $app = strtolower((string) ($request->query('app') ?? ($request->is('*customer*') ? 'customer' : 'staff')));
        $currentVersionCode = (int) $request->query('version_code', 1);
        $platform = strtolower($request->query('platform', 'android'));

        $configKey = ($app === 'customer') ? 'mobile.customer' : 'mobile.staff';
        $appConfig = config($configKey, []);

        $defaultApk = ($app === 'customer') ? 'dsbilling-customer-release.apk' : 'dsbilling-mobile-release.apk';
        $defaultName = ($app === 'customer') ? 'dsBilling Customer' : 'dsBilling Staff';

        $latestVersionCode = (int) ($appConfig['latest_version_code'] ?? 10101);
        $latestVersionName = (string) ($appConfig['latest_version_name'] ?? '1.1.1');
        $minimumSupportedCode = (int) ($appConfig['minimum_supported_code'] ?? 1);
        $downloadUrl = (string) ($appConfig['download_url'] ?? url("/downloads/{$defaultApk}"));
        $releaseNotes = (string) ($appConfig['release_notes'] ?? "Versi {$latestVersionName} telah tersedia dengan berbagai peningkatan performa dan stabilitas.");

        $updateAvailable = $latestVersionCode > $currentVersionCode;
        $updateRequired = $currentVersionCode < $minimumSupportedCode;

        return response()->json([
            'success' => true,
            'message' => $updateAvailable ? "Versi baru {$defaultName} ({$latestVersionName}) tersedia." : 'Aplikasi sudah menggunakan versi terbaru.',
            'data' => [
                'app' => $app,
                'app_name' => $defaultName,
                'platform' => $platform,
                'current_version_code' => $currentVersionCode,
                'latest_version_code' => $latestVersionCode,
                'latest_version_name' => $latestVersionName,
                'minimum_supported_code' => $minimumSupportedCode,
                'update_available' => $updateAvailable,
                'update_required' => $updateRequired,
                'download_url' => $downloadUrl,
                'release_notes' => $releaseNotes,
                'checked_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
