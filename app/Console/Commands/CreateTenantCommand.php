<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name : Nama subdomain ISP (contoh: ispbudi)} {--admin-email=admin@ispbudi.com} {--admin-pass=rahasia123}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membangun instance/database baru untuk ISP penyewa (SaaS Tenant)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantName = strtolower($this->argument('name'));
        
        $this->info("Memulai pembuatan instance SaaS untuk ISP: {$tenantName}");

        $dbConnection = config('database.default');

        if ($dbConnection === 'sqlite') {
            $dbPath = database_path("tenants/{$tenantName}.sqlite");
            if (File::exists($dbPath)) {
                $this->error("Database tenant {$tenantName}.sqlite sudah ada!");
                return 1;
            }
            // Buat file sqlite kosong
            File::put($dbPath, '');
            $this->info("✔ File database SQLite dibuat: {$dbPath}");
            
            // Set konfigurasi dinamis untuk koneksi saat ini
            config(['database.connections.sqlite.database' => $dbPath]);
            
        } else if ($dbConnection === 'mysql') {
            $dbName = env('DB_TENANT_PREFIX', 'dsbilling_') . $tenantName;
            
            // Connect to default mysql first to create DB
            $query = "CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
            DB::statement($query);
            $this->info("✔ Database MySQL dibuat: {$dbName}");
            
            config(['database.connections.mysql.database' => $dbName]);
        }

        // Purge and reconnect ke database tenant yang baru
        DB::purge($dbConnection);
        DB::reconnect($dbConnection);

        // Bypass proteksi console di TenancyServiceProvider dengan Set variable
        $_SERVER['TENANT_MODE'] = true;
        $_SERVER['TENANT_NAME'] = $tenantName;

        $this->info("Menjalankan migrasi struktur tabel (Artisan Migrate) ke database {$tenantName}...");
        
        // Jalankan migrasi khusus di database ini
        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        $this->info("Tabel berhasil dibangun. Membuat akun Administrator...");
        
        // Buat folder storage tenant
        $storagePath = storage_path("app/public/tenants/{$tenantName}");
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        // Jika menggunakan seeder admin default (kita inject env dinamis sementara)
        putenv('ADMIN_DEFAULT_PASSWORD=' . $this->option('admin-pass'));
        
        // Jalankan seeder master data
        Artisan::call('db:seed', ['--force' => true]);
        
        // Ganti email admin sesuai input
        $admin = \App\Models\User::where('username', 'admin')->first();
        if ($admin) {
            $admin->update(['email' => $this->option('admin-email')]);
        }

        $this->info("============================================");
        $this->info("✨ Instance ISP '{}' BERHASIL dibuat! ✨");
        $this->info("============================================");
        $this->info("Akses URL : http://{$tenantName}.domainanda.com");
        $this->info("Email     : " . $this->option('admin-email'));
        $this->info("Password  : " . $this->option('admin-pass'));
        $this->info("Database  : " . ($dbConnection === 'sqlite' ? "database/tenants/{$tenantName}.sqlite" : "MySQL: dsbilling_{$tenantName}"));
        $this->info("============================================");
    }
}
