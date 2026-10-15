<?php

namespace App\Console\Commands\ISP;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SyncResellerOwnershipCommand extends Command
{
    protected $signature = 'isp:sync-reseller-ownership {tenant? : Nama tenant spesifik (misal winets)} {--all-tenants : Jalankan di seluruh database tenant}';
    protected $description = 'Sinkronisasi reseller_id dari customers ke customer_services, pppoe_users, dan hotspot_users';

    public function handle(): int
    {
        $specificTenant = $this->argument('tenant');
        $allTenants = (bool)$this->option('all-tenants');

        $tenantsDir = database_path('tenants');
        if (File::exists($tenantsDir)) {
            $files = File::files($tenantsDir);
            $sqliteTenants = [];
            foreach ($files as $file) {
                if ($file->getExtension() === 'sqlite') {
                    $sqliteTenants[] = $file->getFilenameWithoutExtension();
                }
            }

            if ($specificTenant) {
                $targets = [$specificTenant];
            } elseif ($allTenants) {
                $targets = $sqliteTenants;
            } else {
                $targets = $sqliteTenants;
            }

            foreach ($targets as $tenantName) {
                $dbPath = database_path("tenants/{$tenantName}.sqlite");
                if (File::exists($dbPath)) {
                    $this->info("Menyinkronkan reseller_id untuk tenant [{$tenantName}]...");
                    config(['database.connections.tenant_sync' => [
                        'driver' => 'sqlite',
                        'database' => $dbPath,
                        'prefix' => '',
                        'foreign_key_constraints' => false,
                    ]]);
                    $this->syncConnection('tenant_sync');
                }
            }
        }

        $this->info("Menyinkronkan database default...");
        $this->syncConnection(config('database.default'));

        $this->info("Sinkronisasi reseller_id selesai!");
        return self::SUCCESS;
    }

    private function syncConnection(string $connName): void
    {
        try {
            // 1. Sync customer_services from customers
            DB::connection($connName)->statement("
                UPDATE customer_services 
                SET reseller_id = (
                    SELECT customers.reseller_id 
                    FROM customers 
                    WHERE customers.id = customer_services.customer_id
                )
                WHERE customer_services.reseller_id IS NULL
                AND EXISTS (
                    SELECT 1 FROM customers 
                    WHERE customers.id = customer_services.customer_id 
                    AND customers.reseller_id IS NOT NULL
                )
            ");

            // 2. Sync pppoe_users from customers
            DB::connection($connName)->statement("
                UPDATE pppoe_users 
                SET reseller_id = (
                    SELECT customers.reseller_id 
                    FROM customer_services 
                    JOIN customers ON customer_services.customer_id = customers.id 
                    WHERE customer_services.id = pppoe_users.customer_service_id
                )
                WHERE pppoe_users.reseller_id IS NULL
                AND EXISTS (
                    SELECT 1 
                    FROM customer_services 
                    JOIN customers ON customer_services.customer_id = customers.id 
                    WHERE customer_services.id = pppoe_users.customer_service_id
                    AND customers.reseller_id IS NOT NULL
                )
            ");

            // 3. Sync hotspot_users from customers
            DB::connection($connName)->statement("
                UPDATE hotspot_users 
                SET reseller_id = (
                    SELECT customers.reseller_id 
                    FROM customer_services 
                    JOIN customers ON customer_services.customer_id = customers.id 
                    WHERE customer_services.id = hotspot_users.customer_service_id
                )
                WHERE hotspot_users.reseller_id IS NULL
                AND EXISTS (
                    SELECT 1 
                    FROM customer_services 
                    JOIN customers ON customer_services.customer_id = customers.id 
                    WHERE customer_services.id = hotspot_users.customer_service_id
                    AND customers.reseller_id IS NOT NULL
                )
            ");

            $this->line(" -> customer_services, pppoe_users, hotspot_users berhasil disinkronkan.");
        } catch (\Throwable $e) {
            $this->error(" -> Gagal sinkronisasi pada connection {$connName}: " . $e->getMessage());
        }
    }
}
