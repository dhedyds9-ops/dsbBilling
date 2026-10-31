<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseManager
{
    /**
     * URL API Pusat (Server Pembuat Billing).
     */
    public function getLicenseServerUrl(): string
    {
        return config('app.license_server') ?: env('DSBILLING_LICENSE_SERVER', 'http://127.0.0.1:8001/api/v1/verify');
    }

    /**
     * Kunci Lisensi Terdaftar.
     */
    public function getLicenseKey(): ?string
    {
        return config('app.license_key') ?: env('DSBILLING_LICENSE_KEY');
    }

    /**
     * Memeriksa apakah instalasi dsBilling ini memiliki lisensi yang valid.
     */
    public function isValid(): bool
    {
        $info = $this->getLicenseInfo();
        return ($info['valid'] ?? false) === true;
    }

    /**
     * Mendapatkan informasi detail status lisensi.
     */
    public function getLicenseInfo(): array
    {
        $licenseKey = $this->getLicenseKey();

        if (empty($licenseKey)) {
            return [
                'valid'   => false,
                'status'  => 'missing_key',
                'message' => 'License Key belum dikonfigurasi di file .env server ini.',
                'client'  => config('app.name'),
            ];
        }

        $cacheKey = "dsb_license_data_{$licenseKey}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data = $this->verifyWithServer($licenseKey);

        // Jika aktif simpan 30 detik. Jika suspended/gagal simpan hanya 3 detik agar responsif saat dibuka kembali!
        $ttl = ($data['valid'] ?? false) ? 30 : 3;
        Cache::put($cacheKey, $data, $ttl);

        return $data;
    }

    /**
     * Mengecek lisensi langsung ke server utama.
     */
    private function verifyWithServer(string $licenseKey): array
    {
        try {
            $url = $this->getLicenseServerUrl();
            $domain = request()->getHost();
            if (empty($domain) || $domain === 'localhost' || $domain === '127.0.0.1') {
                $domain = 'mstore.mstore.id';
            }

            $payload = [
                'license_key' => $licenseKey,
                'hardware_id' => $this->getHardwareId(),
                'domain'      => $domain,
            ];

            // 1. Coba hubungi server lisensi utama
            $response = Http::timeout(4)->withoutVerifying()->asForm()->post($url, $payload);

            // 2. Jika gagal atau non-200 dan URL bukan 127.0.0.1, coba fallback ke port lokal 8001
            if (!$response->successful() && !str_contains($url, '127.0.0.1:8001')) {
                try {
                    $fallbackResponse = Http::timeout(3)->withoutVerifying()->asForm()->post('http://127.0.0.1:8001/api/v1/verify', $payload);
                    if ($fallbackResponse->successful()) {
                        $response = $fallbackResponse;
                    }
                } catch (\Throwable $fbEx) {
                    // Abaikan fallback jika port 8001 juga tidak dapat dijangkau
                }
            }

            $rawBody = $response->body();
            // Bersihkan UTF-8 BOM atau karakter tersembunyi yang mungkin menempel di awal JSON
            $cleanBody = preg_replace('/^[\xEF\xBB\xBF\s\x00-\x1F\x7F]+/', '', $rawBody);
            $cleanBody = trim($cleanBody);
            $json = json_decode($cleanBody, true) ?: $response->json();

            if ($response->successful() && ($json['status'] ?? '') === 'active') {
                $data = [
                    'valid'       => true,
                    'status'      => 'active',
                    'message'     => $json['message'] ?? 'Lisensi valid dan aktif.',
                    'client'      => $json['client'] ?? config('app.name'),
                    'package'     => $json['package'] ?? 'Standard',
                    'max_users'   => $json['max_users'] ?? 500,
                    'max_routers' => $json['max_routers'] ?? 3,
                    'features'    => $json['features'] ?? [],
                    'expires_at'  => $json['expires_at'] ?? null,
                ];

                Cache::put("dsb_license_fallback_{$licenseKey}", $data, 43200);

                return $data;
            }

            // Jika status suspended, expired, atau invalid dari server
            return [
                'valid'   => false,
                'status'  => $json['status'] ?? ($response->status() === 403 ? 'suspended' : 'invalid'),
                'message' => $json['message'] ?? ($response->status() === 403 ? 'Lisensi software telah dinonaktifkan oleh administrator.' : 'Lisensi software tidak valid.'),
                'client'  => $json['client'] ?? config('app.name'),
            ];
        } catch (\Throwable $e) {
            Log::error('License verification connection error: ' . $e->getMessage());

            if (Cache::has("dsb_license_fallback_{$licenseKey}")) {
                return Cache::get("dsb_license_fallback_{$licenseKey}");
            }

            return [
                'valid'   => false,
                'status'  => 'server_unreachable',
                'message' => 'Gagal menghubungi server lisensi dsBilling. Periksa koneksi jaringan Anda.',
                'client'  => config('app.name'),
            ];
        }
    }

    /**
     * Mengambil ID unik hardware (Disk / MAC Address) untuk mencegah copy-paste instalasi.
     */
    public function getHardwareId(): string
    {
        return md5(php_uname('n') . '_' . php_uname('m'));
    }

    /**
     * Bersihkan cache lisensi agar perubahan langsung dicek ulang seketika.
     */
    public function clearCache(): void
    {
        $licenseKey = $this->getLicenseKey();
        if ($licenseKey) {
            Cache::forget("dsb_license_data_{$licenseKey}");
            Cache::forget("dsb_license_fallback_{$licenseKey}");
        }
    }
}
