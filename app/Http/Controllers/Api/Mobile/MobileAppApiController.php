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

        $defaultApk = ($app === 'customer') ? 'dsbilling-customer-release.apk' : 'dsbilling-technician.apk';
        $defaultName = ($app === 'customer') ? 'dsBilling Customer' : 'dsBilling Staff';

        $latestVersionCode = (int) (config('mobile.latest_version_code') ?? $appConfig['latest_version_code'] ?? 10101);
        $latestVersionName = (string) (config('mobile.latest_version_name') ?? $appConfig['latest_version_name'] ?? '1.1.1');
        $minimumSupportedCode = (int) (config('mobile.minimum_supported_code') ?? $appConfig['minimum_supported_code'] ?? 1);

        $configuredUrl = (string) ($appConfig['download_url'] ?? config('mobile.download_url') ?? '');
        $urlPath = parse_url($configuredUrl, PHP_URL_PATH);
        $filename = $urlPath ? basename($urlPath) : $defaultApk;

        // Use incoming request scheme & host so mobile client always downloads from reachable host
        $downloadUrl = $request->getSchemeAndHttpHost() . "/downloads/{$filename}";

        // Anti-cache query parameter to ensure download is always fresh
        $separator = str_contains($downloadUrl, '?') ? '&' : '?';
        $downloadUrl = "{$downloadUrl}{$separator}v={$latestVersionCode}";

        $releaseNotes = (string) ($appConfig['release_notes'] ?? config('mobile.release_notes') ?? "Versi {$latestVersionName} telah tersedia dengan berbagai peningkatan performa dan stabilitas.");

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

    /**
     * Get official ISP upstream partner & company identity for landing & mobile login branding.
     * GET /api/mobile/partner
     * GET /api/mobile/public/partner-info
     */
    public function partnerInfo(Request $request): JsonResponse
    {
        $host = $request->getHost();
        $isWinets = str_contains($host, 'winets');

        $partnerName = \App\Models\Setting::getValue('company.partner_name');
        if (empty($partnerName)) {
            $partnerName = $isWinets ? 'PT Winet Nusantara' : 'PT MSTORE DIGITAL NUSANTARA';
        }

        $partnerLegal = \App\Models\Setting::getValue('company.partner_legal_name') ?: $partnerName;
        $partnerLogo = \App\Models\Setting::getValue('company.partner_logo_url');
        $partnerWebsite = \App\Models\Setting::getValue('company.partner_website');
        $partnerPhone = \App\Models\Setting::getValue('company.partner_mobile') ?: \App\Models\Setting::getValue('company.partner_phone');
        $partnerEmail = \App\Models\Setting::getValue('company.partner_email');
        $partnerServiceType = \App\Models\Setting::getValue('company.partner_service_type') ?: 'Dedicated Internet & FTTH Backbone';
        $partnerLicenseNo = \App\Models\Setting::getValue('company.partner_license_no') ?: 'Izin Penyelenggara Jasa Internet (ISP) Kominfo RI';
        $partnerDescription = \App\Models\Setting::getValue('company.partner_description')
            ?: "Layanan internet diselenggarakan secara legal melalui kemitraan strategis dengan {$partnerName} sebagai penyedia jaringan backbone dan upstream berlisensi resmi Kominfo RI.";
        $partnerBackup = \App\Models\Setting::getValue('company.partner_upstream_backup');

        $companyName = \App\Models\Setting::getValue('company.name') ?: config('app.name', 'dsBilling');
        $companyLogo = \App\Models\Setting::getValue('company.logo_url') ?: \App\Models\Setting::getValue('company.logo');

        if (empty($partnerLogo) && !empty($companyLogo)) {
            $partnerLogo = $companyLogo;
        }

        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $logoUrl = null;
        if (!empty($partnerLogo)) {
            $logoUrl = str_starts_with($partnerLogo, 'http') ? $partnerLogo : ($baseUrl . '/' . ltrim($partnerLogo, '/'));
        } else {
            $logoUrl = $baseUrl . '/images/dsbilling_logo_tech_light.png';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'partner_name' => $partnerName,
                'partner_legal_name' => $partnerLegal,
                'partner_logo_url' => $logoUrl,
                'partner_service_type' => $partnerServiceType,
                'partner_license_no' => $partnerLicenseNo,
                'partner_description' => $partnerDescription,
                'partner_website' => $partnerWebsite,
                'partner_phone' => $partnerPhone,
                'partner_email' => $partnerEmail,
                'partner_backup' => $partnerBackup,
                'company_name' => $companyName,
                'server_cluster' => $isWinets ? 'Winets' : 'MStore',
            ],
        ]);
    }
}
