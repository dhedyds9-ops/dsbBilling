<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseManager
{
    /**
     * URL API Pusat (Server Pembuat Billing).
     * Saat di-deploy komersial, ganti URL ini ke domain utama Anda.
     */
    private string $licenseServerUrl = 'https://license.dsbiling.id/api/v1/verify';

    /**
     * Memeriksa apakah instalasi dsBilling ini memiliki lisensi yang valid.
     * Menggunakan cache 12 jam agar aplikasi tetap cepat.
     */
    public function isValid(): bool
    {
        // Bypass license check for local development
        if (app()->environment('local')) {
            return true;
        }

        $licenseKey = config('app.license_key');
        
        if (empty($licenseKey)) {
            return false;
        }

        return Cache::remember("dsbilling_license_validity_{$licenseKey}", 43200, function () use ($licenseKey) {
            return $this->verifyWithServer($licenseKey);
        });
    }

    /**
     * Mengecek lisensi langsung ke server utama.
     */
    private function verifyWithServer(string $licenseKey): bool
    {
        try {
            // Uncomment baris di bawah saat server lisensi Anda sudah siap.
            /*
            $response = Http::timeout(5)->post($this->licenseServerUrl, [
                'license_key' => $licenseKey,
                'hardware_id' => $this->getHardwareId(),
                'domain' => request()->getHost(),
            ]);

            return $response->successful() && $response->json('status') === 'active';
            */
            
            // Dummy logic: Anggap lisensi valid jika formatnya mirip UUID atau panjangnya > 10.
            return strlen($licenseKey) >= 10;
        } catch (\Throwable $e) {
            Log::error('License verification failed: ' . $e->getMessage());
            // Fail-close (blokir) jika gagal verifikasi
            return false; 
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
     * Menyimpan lisensi ke file .env.
     */
    public function saveLicenseKey(string $key): void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            if (str_contains($content, 'DSBILLING_LICENSE_KEY=')) {
                $content = preg_replace('/DSBILLING_LICENSE_KEY=.*/', "DSBILLING_LICENSE_KEY={$key}", $content);
            } else {
                $content .= "\nDSBILLING_LICENSE_KEY={$key}\n";
            }

            file_put_contents($path, $content);
            
            // Hapus cache lisensi lama
            Cache::forget("dsbilling_license_validity_".config('app.license_key'));
        }
    }
}
