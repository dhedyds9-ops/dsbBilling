<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrateTenantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {tenant? : Nama tenant spesifik (opsional, jika kosong akan memigrasi seluruh tenant)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan database migration untuk satu atau seluruh tenant SaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specificTenant = $this->argument('tenant');
        $dbConnection = config('database.default');

        if ($dbConnection === 'sqlite') {
            $tenantsDir = database_path('tenants');
            if (!File::exists($tenantsDir)) {
                $this->warn("Direktori tenants tidak ditemukan di {$tenantsDir}");
                return 0;
            }

            $files = File::files($tenantsDir);
            $sqliteTenants = [];
            foreach ($files as $file) {
                if ($file->getExtension() === 'sqlite') {
                    $sqliteTenants[] = $file->getFilenameWithoutExtension();
                }
            }

            $targets = $specificTenant ? [$specificTenant] : $sqliteTenants;

            if (empty($targets)) {
                $this->warn('Tidak ada database tenant SQLite yang ditemukan.');
                return 0;
            }

            foreach ($targets as $tenantName) {
                $dbPath = database_path("tenants/{$tenantName}.sqlite");
                if (!File::exists($dbPath)) {
                    $this->error("Database untuk tenant [{$tenantName}] tidak ditemukan di {$dbPath}");
                    continue;
                }

                $this->info("=== Memigrasi Tenant SQLite: {$tenantName} ===");
                $_SERVER['TENANT_MODE'] = true;
                $_SERVER['TENANT_NAME'] = $tenantName;

                config(['database.connections.sqlite.database' => $dbPath]);
                DB::purge('sqlite');
                DB::reconnect('sqlite');

                Artisan::call('migrate', ['--force' => true]);
                $this->line(Artisan::output());
            }
        } elseif ($dbConnection === 'mysql') {
            $prefix = env('DB_TENANT_PREFIX', 'dsbilling_');
            if ($specificTenant) {
                $targets = [$specificTenant];
            } else {
                $databases = DB::select("SHOW DATABASES LIKE '{$prefix}%'");
                $key = "Database ({$prefix}%)";
                $targets = [];
                foreach ($databases as $dbObj) {
                    $dbArr = (array) $dbObj;
                    $fullDbName = reset($dbArr);
                    $targets[] = str_replace($prefix, '', $fullDbName);
                }
            }

            foreach ($targets as $tenantName) {
                $dbName = $prefix . $tenantName;
                $this->info("=== Memigrasi Tenant MySQL: {$tenantName} ({$dbName}) ===");

                $_SERVER['TENANT_MODE'] = true;
                $_SERVER['TENANT_NAME'] = $tenantName;

                config(['database.connections.mysql.database' => $dbName]);
                DB::purge('mysql');
                DB::reconnect('mysql');

                Artisan::call('migrate', ['--force' => true]);
                $this->line(Artisan::output());
            }
        }

        $this->info('✔ Selesai menjalankan migrasi tenant.');
        return 0;
    }
}
