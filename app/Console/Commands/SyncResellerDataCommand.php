<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SyncResellerDataCommand extends Command
{
    protected $signature = 'reseller:sync-data {tenant? : Nama tenant spesifik (opsional)}';
    protected $description = 'Sinkronisasi reseller_id pada customer_services, pppoe_users, hotspot_users, invoices, dan payments';

    public function handle()
    {
        $specificTenant = $this->argument('tenant');
        $dbConnection = config('database.default');

        if ($dbConnection === 'sqlite') {
            $tenantsDir = database_path('tenants');
            $targets = [];

            if ($specificTenant) {
                $targets = [$specificTenant];
            } elseif (File::exists($tenantsDir)) {
                foreach (File::files($tenantsDir) as $file) {
                    if ($file->getExtension() === 'sqlite') {
                        $targets[] = $file->getFilenameWithoutExtension();
                    }
                }
            }

            // Also include default database if no targets or run for all
            if (empty($targets)) {
                $this->info("Menjalankan sinkronisasi pada database default...");
                $this->syncDatabase();
                return 0;
            }

            foreach ($targets as $tenantName) {
                $dbPath = database_path("tenants/{$tenantName}.sqlite");
                if (!File::exists($dbPath)) {
                    $this->error("Database tenant [{$tenantName}] tidak ditemukan di {$dbPath}");
                    continue;
                }

                $this->info("--------------------------------------------------");
                $this->info("Menjalankan sinkronisasi data reseller untuk tenant [{$tenantName}]...");
                
                config(['database.connections.tenant_sync.driver' => 'sqlite']);
                config(['database.connections.tenant_sync.database' => $dbPath]);
                DB::purge('tenant_sync');
                
                $this->syncDatabase('tenant_sync');
            }

            return 0;
        }

        $this->syncDatabase();
        return 0;
    }

    private function syncDatabase(?string $connection = null)
    {
        $conn = DB::connection($connection);

        $customerTable = Schema::connection($connection)->hasTable('customers') ? 'customers' : (Schema::connection($connection)->hasTable('members') ? 'members' : null);

        if (!$customerTable) {
            $this->warn("Tabel pelanggan tidak ditemukan.");
            return;
        }

        // 1. Sync customer_services.reseller_id from customers.reseller_id
        if (Schema::connection($connection)->hasTable('customer_services') && Schema::connection($connection)->hasColumn('customer_services', 'reseller_id')) {
            $affected = $conn->table('customer_services')
                ->whereNull('reseller_id')
                ->whereExists(function ($q) use ($customerTable) {
                    $q->select(DB::raw(1))->from($customerTable)
                      ->whereColumn("{$customerTable}.id", 'customer_services.customer_id')
                      ->whereNotNull("{$customerTable}.reseller_id");
                })
                ->update([
                    'reseller_id' => DB::raw("(SELECT reseller_id FROM {$customerTable} WHERE {$customerTable}.id = customer_services.customer_id)")
                ]);
            $this->info("CustomerServices updated with reseller_id: {$affected}");
        }

        // 2. Sync pppoe_users.reseller_id from customer_services or customers
        if (Schema::connection($connection)->hasTable('pppoe_users') && Schema::connection($connection)->hasColumn('pppoe_users', 'reseller_id')) {
            $affected = $conn->table('pppoe_users')
                ->whereNull('reseller_id')
                ->whereExists(function ($q) use ($customerTable) {
                    $q->select(DB::raw(1))->from('customer_services')
                      ->join($customerTable, 'customer_services.customer_id', '=', "{$customerTable}.id")
                      ->whereColumn('customer_services.id', 'pppoe_users.customer_service_id')
                      ->whereNotNull("{$customerTable}.reseller_id");
                })
                ->update([
                    'reseller_id' => DB::raw("(SELECT {$customerTable}.reseller_id FROM customer_services JOIN {$customerTable} ON customer_services.customer_id = {$customerTable}.id WHERE customer_services.id = pppoe_users.customer_service_id LIMIT 1)")
                ]);
            $this->info("PPPoEUsers updated with reseller_id: {$affected}");
        }

        // 3. Sync hotspot_users.reseller_id from customer_services or customers
        if (Schema::connection($connection)->hasTable('hotspot_users') && Schema::connection($connection)->hasColumn('hotspot_users', 'reseller_id')) {
            $affected = $conn->table('hotspot_users')
                ->whereNull('reseller_id')
                ->whereExists(function ($q) use ($customerTable) {
                    $q->select(DB::raw(1))->from('customer_services')
                      ->join($customerTable, 'customer_services.customer_id', '=', "{$customerTable}.id")
                      ->whereColumn('customer_services.id', 'hotspot_users.customer_service_id')
                      ->whereNotNull("{$customerTable}.reseller_id");
                })
                ->update([
                    'reseller_id' => DB::raw("(SELECT {$customerTable}.reseller_id FROM customer_services JOIN {$customerTable} ON customer_services.customer_id = {$customerTable}.id WHERE customer_services.id = hotspot_users.customer_service_id LIMIT 1)")
                ]);
            $this->info("HotspotUsers updated with reseller_id: {$affected}");
        }

        // 4. Sync invoices.reseller_id from customers
        if (Schema::connection($connection)->hasTable('invoices') && Schema::connection($connection)->hasColumn('invoices', 'reseller_id')) {
            $affected = $conn->table('invoices')
                ->whereNull('reseller_id')
                ->whereExists(function ($q) use ($customerTable) {
                    $q->select(DB::raw(1))->from($customerTable)
                      ->whereColumn("{$customerTable}.id", 'invoices.customer_id')
                      ->whereNotNull("{$customerTable}.reseller_id");
                })
                ->update([
                    'reseller_id' => DB::raw("(SELECT reseller_id FROM {$customerTable} WHERE {$customerTable}.id = invoices.customer_id)")
                ]);
            $this->info("Invoices updated with reseller_id: {$affected}");
        }

        // 5. Sync payments.reseller_id from customers
        if (Schema::connection($connection)->hasTable('payments') && Schema::connection($connection)->hasColumn('payments', 'reseller_id')) {
            $affected = $conn->table('payments')
                ->whereNull('reseller_id')
                ->whereExists(function ($q) use ($customerTable) {
                    $q->select(DB::raw(1))->from($customerTable)
                      ->whereColumn("{$customerTable}.id", 'payments.customer_id')
                      ->whereNotNull("{$customerTable}.reseller_id");
                })
                ->update([
                    'reseller_id' => DB::raw("(SELECT reseller_id FROM {$customerTable} WHERE {$customerTable}.id = payments.customer_id)")
                ]);
            $this->info("Payments updated with reseller_id: {$affected}");
        }

        $this->info("Sinkronisasi selesai!");
    }
}
