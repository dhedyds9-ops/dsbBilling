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
        return env('DSBILLING_LICENSE_SERVER', 'https://dsbilling.mstore.id/api/v1/verify');
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
        $licenseKey = env('DSBILLING_LICENSE_KEY') ?: config('app.license_key');

        if (empty($licenseKey)) {
            return [
                'valid'   => false,
                'status'  => 'missing_key',
                'message' => 'License Key belum dikonfigurasi di file .env server ini.',
                'client'  => config('app.name'),
            ];
        }

        // Cache status pengecekan selama 30 detik agar responsive terhadap tombol Suspend/Aktif
        return Cache::remember("dsb_license_data_{$licenseKey}", 30, function () use ($licenseKey) {
            return $this->verifyWithServer($licenseKey);
        });
    }

    /**
     * Mengecek lisensi langsung ke server utama.
     */
    private function verifyWithServer(string $licenseKey): array
    {
        try {
            $response = Http::timeout(4)->withoutVerifying()->post($this->getLicenseServerUrl(), [
                'license_key' => $licenseKey,
                'hardware_id' => $this->getHardwareId(),
                'domain'      => request()->getHost(),
            ]);

            $json = $response->json();

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

                // Simpan fallback 12 jam jika sewaktu-waktu server lisensi maintenance
                Cache::put("dsb_license_fallback_{$licenseKey}", $data, 43200);

                return $data;
            }

            // Jika status suspended, expired, atau invalid
            return [
                'valid'   => false,
                'status'  => $json['status'] ?? ($response->status() === 403 ? 'suspended' : 'invalid'),
                'message' => $json['message'] ?? 'Lisensi software dsBilling telah dinonaktifkan oleh administrator.',
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
        $licenseKey = env('DSBILLING_LICENSE_KEY') ?: config('app.license_key');
        if ($licenseKey) {
            Cache::forget("dsb_license_data_{$licenseKey}");
            Cache::forget("dsb_license_fallback_{$licenseKey}");
        }
    }
}
