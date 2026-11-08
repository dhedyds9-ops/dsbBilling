<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileAppApiController extends Controller
{
    /**
     * Check for app updates.
     *
     * GET /api/mobile/app/check-update?version_code=1&platform=android
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        $currentVersionCode = (int) $request->query('version_code', 1);
        $platform = strtolower($request->query('platform', 'android'));

        // Server-configured production version metadata
        $latestVersionCode = (int) config('mobile.latest_version_code', 2);
        $latestVersionName = config('mobile.latest_version_name', '1.1.0');
        $minimumSupportedCode = (int) config('mobile.minimum_supported_code', 1);
        $downloadUrl = config('mobile.download_url', config('app.url') . '/downloads/dsbilling-staff-release.apk');
        $releaseNotes = config('mobile.release_notes', "Pembaruan Resmi dsBilling Staff:\n- Integrasi Modul SPK Lapangan & Checklist\n- Pengukuran Redaman OPM & Tanda Tangan Digital\n- Alokasi ODP, TR-069 ONT Provisioning via Proxy\n- Mode Offline Queue & Sinkronisasi Otomatis\n- Kartu KPI Performa Teknisi & Feed Aktivitas");

        $updateAvailable = $latestVersionCode > $currentVersionCode;
        $updateRequired = $currentVersionCode < $minimumSupportedCode;

        return response()->json([
            'success' => true,
            'message' => $updateAvailable ? 'Versi baru dsBilling Staff tersedia' : 'Aplikasi sudah menggunakan versi terbaru',
            'data' => [
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
