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

        $host = request()->getHost();
        
        // Daftar domain utama (Super Admin Pusat)
        $centralDomains = ['localhost', '127.0.0.1', env('CENTRAL_DOMAIN', 'dsbilling.local')];

        // Jika bukan domain pusat, atau ada instruksi paksa dari CLI
        if (!in_array($host, $centralDomains) || isset($_SERVER['TENANT_NAME'])) {
            // Ambil subdomain pertama (contoh: ispbudi.dsbilling.com -> ispbudi)
            $tenantName = explode('.', $host)[0];
            
            // Allow override via ENV / CLI
            if (isset($_SERVER['TENANT_NAME'])) {
                $tenantName = $_SERVER['TENANT_NAME'];
            }

            // === 1. ISOLASI DATABASE ===
            $dbConnection = config('database.default');
            
            if ($dbConnection === 'sqlite') {
                $dbPath = database_path("tenants/{$tenantName}.sqlite");
                // Switch database file
                config(['database.connections.sqlite.database' => $dbPath]);
            } else if ($dbConnection === 'mysql') {
                // Jika pakai MySQL, format DB name: dsbilling_ispbudi
                $dbName = env('DB_TENANT_PREFIX', 'dsbilling_') . $tenantName;
                config(['database.connections.mysql.database' => $dbName]);
            }

            // Putuskan koneksi lama, paksa framework pakai koneksi tenant baru
            DB::purge($dbConnection);
            DB::reconnect($dbConnection);

            // === 2. ISOLASI FILE UPLOAD (FOTO/LOGO) ===
            // Agar logo ISP A tidak tertimpa Logo ISP B
            config([
                'filesystems.disks.public.root' => storage_path("app/public/tenants/{$tenantName}"),
                'filesystems.disks.public.url' => env('APP_URL') . "/storage/tenants/{$tenantName}",
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
