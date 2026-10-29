<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Jangan jalankan tenancy saat mode CLI command standard (seperti artisan migrate pusat), 
        // KECUALI kita secara eksplisit menyuruhnya untuk tenant.
        if (app()->runningInConsole() && !isset($_SERVER['TENANT_MODE'])) {
            return;
        }

        $host = request()->header('X-Forwarded-Host') ?: request()->getHost();
        
        // Daftar domain utama (Super Admin Pusat)
        $centralDomains = ['localhost', '127.0.0.1', env('CENTRAL_DOMAIN', 'dsbilling.local')];

        // Deteksi jika akses menggunakan IP address (seperti panggilan API internal 192.168.x.x)
        $isIpAddress = filter_var($host, FILTER_VALIDATE_IP) 
            || str_starts_with($host, '192.168.') 
            || str_starts_with($host, '10.') 
            || str_starts_with($host, '172.')
            || str_starts_with($host, '127.');

        if ($isIpAddress) {
            // Ambil tenant dari header eksplisit jika ada, atau default ke 'mstore'
            $tenantName = request()->header('X-Tenant') 
                ?: request()->input('tenant') 
                ?: env('DEFAULT_TENANT', 'mstore');
        } else {
            // Ambil subdomain pertama (contoh: ispbudi.dsbilling.com -> ispbudi)
            $tenantName = explode('.', $host)[0];
        }

        // Jangan izinkan angka murni sebagai tenant name (mencegah dsbilling_192 dsb)
        if (is_numeric($tenantName)) {
            $tenantName = env('DEFAULT_TENANT', 'mstore');
        }

        // Jika bukan domain pusat, atau ada instruksi paksa dari CLI
        if ((! in_array($host, $centralDomains, true) && ! $isIpAddress) || isset($_SERVER['TENANT_NAME']) || request()->header('X-Tenant')) {
            
            // Allow override via ENV / CLI
            if (isset($_SERVER['TENANT_NAME'])) {
                $tenantName = $_SERVER['TENANT_NAME'];
            }

            // === 1. ISOLASI DATABASE ===
            $dbConnection = config('database.default');
            
            if ($dbConnection === 'sqlite') {
                $dbPath = database_path("tenants/{$tenantName}.sqlite");
                if (file_exists($dbPath)) {
                    config(['database.connections.sqlite.database' => $dbPath]);
                }
            } else if ($dbConnection === 'mysql') {
                // Format DB name tenant: dsbilling_mstore dsb
                $dbName = env('DB_TENANT_PREFIX', 'dsbilling_') . $tenantName;
                
                try {
                    $dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1", [$dbName]);
                    if (! empty($dbExists)) {
                        config(['database.connections.mysql.database' => $dbName]);
                    } else {
                        // Cek alternatif dsbilling_mstore jika tenant lain tidak ditemukan
                        $altDb = env('DB_TENANT_PREFIX', 'dsbilling_') . 'mstore';
                        $altExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1", [$altDb]);
                        if (! empty($altExists)) {
                            config(['database.connections.mysql.database' => $altDb]);
                        }
                    }
                } catch (\Throwable) {
                    if (! is_numeric($tenantName)) {
                        config(['database.connections.mysql.database' => $dbName]);
                    }
                }
            }

            // Putuskan koneksi lama, paksa framework pakai koneksi tenant baru
            DB::purge($dbConnection);
            DB::reconnect($dbConnection);

            // === 2. ISOLASI FILE UPLOAD (FOTO/LOGO) ===
            // Agar logo ISP A tidak tertimpa Logo ISP B
            $tenantStorage = storage_path("app/public/tenants/{$tenantName}");
            if (!is_dir($tenantStorage)) {
                @mkdir($tenantStorage, 0775, true);
            }
            config([
                'filesystems.disks.public.root' => $tenantStorage,
                'filesystems.disks.public.url' => "/storage/tenants/{$tenantName}",
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
