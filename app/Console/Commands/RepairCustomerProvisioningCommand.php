<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\CRM\Customer;
use App\Models\CRM\Contract;
use App\Models\Customer\CustomerService;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Onu;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Router;
use App\Models\Master\Branch;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Billing\Invoice;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\SubscriptionService;

class RepairCustomerProvisioningCommand extends Command
{
    protected $signature = 'dsbilling:repair-customer-provisioning
                            {target? : ID, Code (e.g. 2026112001), Phone, or Name of the customer}
                            {--dry-run : Simulate repair and output planned changes without modifying database}
                            {--orphaned : Automatically scan and repair all customers lacking CustomerService}
                            {--package= : Service Profile ID or Code (e.g. HG-20M)}
                            {--sn= : ONU Serial Number (e.g. ZTEGC1234567)}
                            {--reseller= : Reseller User ID}
                            {--branch= : Branch ID}
                            {--router= : Router ID}
                            {--type=pppoe : Service type: pppoe or hotspot}';

    protected $description = 'Production-safe audit and repair for incomplete/orphaned Customer Provisioning (Wizard PSB recovery)';

    public function handle(
        InvoiceService $invoiceService,
        SubscriptionService $subscriptionService
    ): int {
        $target = $this->argument('target');
        $isDryRun = (bool) $this->option('dry-run');
        $scanOrphaned = (bool) $this->option('orphaned');

        $this->info("===============================================================================");
        $this->info(" dsBilling Enterprise — Customer Provisioning Recovery & Integrity Auditor    ");
        $this->info(" Mode: " . ($isDryRun ? "DRY-RUN (Simulasi Aman - Read Only)" : "LIVE EXECUTION (Atomic Database Transaction)"));
        $this->info("===============================================================================");

        $customers = collect();

        if ($target) {
            $customer = Customer::withoutGlobalScopes()
                ->where('id', $target)
                ->orWhere('code', $target)
                ->orWhere('phone', $target)
                ->orWhere('name', 'like', "%{$target}%")
                ->first();

            if (!$customer) {
                $this->error("Pelanggan dengan target '{$target}' tidak ditemukan di database.");
                return Command::FAILURE;
            }
            $customers->push($customer);
        } elseif ($scanOrphaned) {
            $orphans = Customer::withoutGlobalScopes()->doesntHave('customerServices')->get();
            if ($orphans->isEmpty()) {
                $this->info("Tidak ada data pelanggan yang berstatus yatim / tanpa layanan (orphaned). Seluruh data valid.");
                return Command::SUCCESS;
            }
            $this->info("Ditemukan {$orphans->count()} pelanggan tanpa CustomerService.");
            $customers = $orphans;
        } else {
            $this->warn("Harap tentukan target pelanggan (ID / Code / Nama / No HP) atau gunakan flag --orphaned.");
            $this->line("Contoh: php artisan dsbilling:repair-customer-provisioning Sarminah --dry-run");
            $this->line("Contoh: php artisan dsbilling:repair-customer-provisioning 2026112001");
            return Command::INVALID;
        }

        foreach ($customers as $customer) {
            $this->processCustomer($customer, $isDryRun, $invoiceService, $subscriptionService);
        }

        $this->info("\nProses audit & recovery selesai.");
        return Command::SUCCESS;
    }

    protected function processCustomer(
        Customer $customer,
        bool $isDryRun,
        InvoiceService $invoiceService,
        SubscriptionService $subscriptionService
    ): void {
        $this->line("\n-------------------------------------------------------------------------------");
        $this->info("AUDITING CUSTOMER: [{$customer->id}] {$customer->name} (Code: {$customer->code})");
        $this->line("-------------------------------------------------------------------------------");

        // 1. Audit Kondisi Sebelum (BEFORE)
        $existingServices = CustomerService::withoutGlobalScopes()->where('customer_id', $customer->id)->get();
        $existingContract = Contract::withoutGlobalScopes()->where('customer_id', $customer->id)->first();
        $existingInvoices = Invoice::withoutGlobalScopes()->where('customer_id', $customer->id)->get();
        $existingOnu = Onu::withoutGlobalScopes()->where('name', 'like', "%{$customer->name}%")->first();

        $beforeData = [
            ['Customer ID', $customer->id],
            ['Customer Code', $customer->code ?: '(KOSONG)'],
            ['Nama Pelanggan', $customer->name],
            ['No. Telepon / WA', $customer->phone ?: '-'],
            ['Status Pelanggan', $customer->status],
            ['Branch ID', $customer->branch_id ? "ID {$customer->branch_id} (" . ($customer->branch?->name ?? 'Unknown') . ")" : 'NULL (Belum Terikat Cabang)'],
            ['Reseller ID', $customer->reseller_id ? "ID {$customer->reseller_id} (" . ($customer->reseller?->name ?? 'Unknown') . ")" : 'NULL (Pusat / Direct HQ)'],
            ['Customer Services', $existingServices->count() . " record"],
            ['Contract Terdaftar', $existingContract ? $existingContract->contract_number : 'NULL (Belum Ada Kontrak)'],
            ['Invoices Terbit', $existingInvoices->count() . " invoice"],
            ['ONU Tertaut', $existingOnu ? "SN: {$existingOnu->serial_number}" : 'NULL (Belum Ada)'],
        ];

        $this->table(['Parameter (BEFORE)', 'Nilai Aktual'], $beforeData);

        if ($existingServices->isNotEmpty()) {
            $this->info("Status: Pelanggan ini SUDAH memiliki {$existingServices->count()} CustomerService.");
            
            // Check if branch / reseller sync is needed
            $needsSync = false;
            $syncDetails = [];
            foreach ($existingServices as $svc) {
                if (empty($svc->branch_id) && !empty($customer->branch_id)) {
                    $needsSync = true;
                    $syncDetails[] = "Sinkronisasi Branch ID {$customer->branch_id} ke CustomerService #{$svc->id}";
                }
                if (empty($svc->reseller_id) && !empty($customer->reseller_id)) {
                    $needsSync = true;
                    $syncDetails[] = "Sinkronisasi Reseller ID {$customer->reseller_id} ke CustomerService #{$svc->id}";
                }
            }

            if ($needsSync) {
                $this->warn("Terdapat ketidaksinkronan tenant/branch pada relasi layanan.");
                foreach ($syncDetails as $det) {
                    $this->line("  -> {$det}");
                }

                if (!$isDryRun) {
                    DB::transaction(function () use ($customer, $existingServices) {
                        foreach ($existingServices as $svc) {
                            $svc->update([
                                'branch_id' => $svc->branch_id ?: $customer->branch_id,
                                'reseller_id' => $svc->reseller_id ?: $customer->reseller_id,
                            ]);
                        }
                    });
                    $this->info("Berhasil menyinkronkan data relasi tenant/branch.");
                } else {
                    $this->comment("[DRY-RUN] Sinkronisasi dilewati.");
                }
            } else {
                $this->info("Integritas data pelanggan ini dalam kondisi prima. Tidak ada perubahan yang diperlukan.");
            }
            return;
        }

        // 2. Evaluasi Target Parameter untuk Recovery
        $branchId = (int) ($this->option('branch') ?: ($customer->branch_id ?: Branch::withoutGlobalScopes()->where('is_active', true)->value('id') ?: 1));
        $resellerId = $this->option('reseller') ? (int) $this->option('reseller') : ($customer->reseller_id ?: null);
        $serviceType = strtolower($this->option('type') ?: 'pppoe');

        // Service Profile
        $packageOpt = $this->option('package');
        if ($packageOpt) {
            $serviceProfile = ServiceProfile::withoutGlobalScopes()
                ->where('id', $packageOpt)
                ->orWhere('code', $packageOpt)
                ->first();
        } else {
            $serviceProfile = ServiceProfile::withoutGlobalScopes()
                ->where('is_active', true)
                ->where('service_type', $serviceType)
                ->first()
                ?? ServiceProfile::withoutGlobalScopes()->where('is_active', true)->first();
        }

        if (!$serviceProfile) {
            $this->error("Gagal recovery: Tidak ditemukan ServiceProfile aktif pada sistem.");
            return;
        }

        // Router
        $routerOpt = $this->option('router');
        if ($routerOpt) {
            $router = Router::withoutGlobalScopes()->find($routerOpt);
        } else {
            $router = Router::withoutGlobalScopes()->where('branch_id', $branchId)->first()
                ?? Router::withoutGlobalScopes()->first();
        }

        // ONU Serial Number
        $snOpt = $this->option('sn');
        if ($snOpt) {
            $sn = strtoupper(trim($snOpt));
        } elseif ($existingOnu) {
            $sn = $existingOnu->serial_number;
        } else {
            // Standard generated SN from phone/timestamp
            $suffix = substr(preg_replace('/[^0-9]/', '', $customer->phone ?: (string) time()), -8);
            $sn = 'ZTEGC' . str_pad($suffix, 8, '0', STR_PAD_LEFT);
        }

        // Username & Password
        $companyName = \App\Models\Setting::getValue('company.name', 'winets');
        $companySuffix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $companyName));
        $customerCode = $customer->code ?: \App\Services\CRM\CustomerCodeGenerator::generate();
        $username = strtolower($customerCode) . ($serviceType === 'pppoe' ? '@' . $companySuffix : '');
        $password = '123456';

        // 3. Tampilkan Rencana Pemulihan (PLANNED ACTIONS)
        $plannedActions = [
            ['Update Customer', "Set Branch ID: {$branchId}, Reseller ID: " . var_export($resellerId, true)],
            ['Contract', $existingContract ? "Gunakan kontrak ID #{$existingContract->id}" : "Buat Kontrak Baru (Status: active)"],
            ['Service Profile', "[{$serviceProfile->id}] {$serviceProfile->name} ({$serviceProfile->code}) - Base Price: Rp " . number_format($serviceProfile->base_price, 0, ',', '.')],
            ['ONU Perangkat', "Bind SN: {$sn} ke Branch {$branchId}"],
            ['Akun Jaringan', "Tipe: " . strtoupper($serviceType) . " | Username: {$username} | Router: " . ($router ? "ID {$router->id} ({$router->name})" : "Default")],
            ['Invoice Perdana', $existingInvoices->isNotEmpty() ? "Pertahankan {$existingInvoices->count()} invoice yang sudah ada" : "Terbitkan Invoice Perdana Rp " . number_format($serviceProfile->base_price, 0, ',', '.')],
        ];

        $this->table(['Rencana Pemulihan (PLANNED ACTIONS)', 'Detail Konfigurasi'], $plannedActions);

        if ($isDryRun) {
            $this->comment("\n[DRY-RUN SELESAI] Tidak ada data yang diubah pada database.");
            $this->info("Untuk mengeksekusi perbaikan secara permanen, jalankan perintah tanpa opsi --dry-run:");
            $this->line("php artisan dsbilling:repair-customer-provisioning {$customer->id}");
            return;
        }

        // 4. Eksekusi Perbaikan Nyata dalam Database Transaction
        try {
            DB::transaction(function () use (
                $customer, $branchId, $resellerId, $serviceProfile, $router, $sn,
                $username, $password, $serviceType, $existingContract, $existingInvoices,
                $existingOnu, $invoiceService, $subscriptionService
            ) {
                $now = now();
                $systemUserId = 1;

                // A. Update Customer Core
                $customer->update([
                    'branch_id' => $branchId,
                    'reseller_id' => $resellerId,
                    'status' => 'active',
                ]);
                $customer->refresh();

                // Pastikan user portal login tersedia
                if (empty($customer->user_id)) {
                    $existingLogin = User::where('customer_code', $customer->code)
                        ->orWhere('whatsapp', $customer->phone)
                        ->first();

                    if ($existingLogin) {
                        $customer->update(['user_id' => $existingLogin->id]);
                    } else {
                        $loginUser = User::create([
                            'name' => $customer->name,
                            'email' => $customer->email,
                            'whatsapp' => $customer->phone,
                            'customer_code' => $customer->code,
                            'username' => $customer->code,
                            'password' => Hash::make('123456'),
                            'branch_id' => $branchId,
                            'is_active' => true,
                        ]);
                        if ($role = \App\Models\Role::where('name', 'customer')->first()) {
                            $loginUser->roles()->attach($role->id);
                        }
                        $customer->update(['user_id' => $loginUser->id]);
                    }
                }

                // B. Contract
                if (!$existingContract) {
                    $existingContract = Contract::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_id' => $customer->id,
                        'contract_number' => 'CONTRACT-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                        'start_date' => $now,
                        'status' => 'active',
                        'created_by' => $systemUserId,
                        'updated_by' => $systemUserId,
                    ]);
                }

                // C. ONU Record
                $onu = Onu::withoutGlobalScopes()->where('serial_number', $sn)->first();
                if (!$onu) {
                    $onu = Onu::create([
                        'code' => 'ONU-' . strtoupper(substr(md5($sn . time()), 0, 6)),
                        'name' => 'ONU ' . $customer->name,
                        'serial_number' => $sn,
                        'status' => 'active',
                        'branch_id' => $branchId,
                        'created_by' => $systemUserId,
                    ]);
                } else {
                    $onu->update([
                        'branch_id' => $branchId,
                        'status' => 'active',
                    ]);
                }

                // D. CustomerService
                $customerService = CustomerService::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $customer->id,
                    'contract_id' => $existingContract->id,
                    'service_id' => null,
                    'service_profile_id' => $serviceProfile->id,
                    'onu_id' => $onu->id,
                    'username' => $username,
                    'password' => $password,
                    'status' => 'active',
                    'reseller_id' => $resellerId,
                    'branch_id' => $branchId,
                    'activated_at' => $now,
                    'created_by' => $systemUserId,
                    'updated_by' => $systemUserId,
                ]);

                // E. PPPoE / Hotspot User
                if ($serviceType === 'pppoe') {
                    PPPoEUser::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_service_id' => $customerService->id,
                        'service_profile_id' => $serviceProfile->id,
                        'router_id' => $router?->id,
                        'username' => $username,
                        'password' => $password,
                        'status' => 'active',
                        'reseller_id' => $resellerId,
                        'branch_id' => $branchId,
                        'mac_address' => $sn,
                        'activated_at' => $now,
                        'created_by' => $systemUserId,
                        'updated_by' => $systemUserId,
                    ]);
                } else {
                    HotspotUser::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_service_id' => $customerService->id,
                        'service_profile_id' => $serviceProfile->id,
                        'router_id' => $router?->id,
                        'username' => $username,
                        'password' => $password,
                        'status' => 'active',
                        'reseller_id' => $resellerId,
                        'branch_id' => $branchId,
                        'mac_address' => $sn,
                        'activated_at' => $now,
                        'created_by' => $systemUserId,
                        'updated_by' => $systemUserId,
                    ]);
                }

                // F. Subscription & First Invoice
                $subscriptionService->createSubscription(
                    $customerService,
                    $existingContract,
                    (float) $serviceProfile->base_price,
                    $systemUserId
                );

                if ($existingInvoices->isEmpty() && (float) $serviceProfile->base_price > 0) {
                    $invoiceService->createInvoice(
                        $customer->id,
                        $systemUserId,
                        [
                            [
                                'description' => "Langganan {$serviceProfile->name}",
                                'quantity' => 1,
                                'unit_price' => (float) $serviceProfile->base_price,
                            ]
                        ],
                        null,
                        null,
                        null,
                        $existingContract->id,
                        'IDR',
                        'unpaid'
                    );
                }

                // G. Enterprise Audit Trail
                AuditLog::create([
                    'auditable_type' => Customer::class,
                    'auditable_id' => $customer->id,
                    'event' => 'Customer Provisioning Recovered',
                    'new_values' => [
                        'customer_service_id' => $customerService->id,
                        'service_profile_id' => $serviceProfile->id,
                        'onu_sn' => $sn,
                        'username' => $username,
                        'branch_id' => $branchId,
                        'reseller_id' => $resellerId,
                    ],
                    'user_id' => $systemUserId,
                    'notes' => 'Recovered via Artisan command dsbilling:repair-customer-provisioning',
                ]);
            });

            $this->info("\n[BERHASIL] Pemulihan data provisioning untuk pelanggan {$customer->name} telah dieksekusi secara atomik!");

            // 5. Tampilkan Hasil Sesudah (AFTER)
            $customer->refresh();
            $afterSvc = $customer->customerServices()->with(['serviceProfile', 'pppoeUser', 'onu', 'reseller', 'branch'])->first();

            $afterData = [
                ['Customer ID', $customer->id],
                ['Nama Pelanggan', $customer->name],
                ['Status Layanan', $afterSvc?->status ?? '-'],
                ['Paket Internet', $afterSvc?->serviceProfile?->name ?? '-'],
                ['Username Koneksi', $afterSvc?->username ?? '-'],
                ['Serial Number ONU', $afterSvc?->onu?->serial_number ?? '-'],
                ['Owner / Reseller', $customer->reseller?->name ?? 'Kantor Pusat (HQ)'],
                ['Cabang (Branch)', $customer->branch?->name ?? 'Pusat'],
            ];

            $this->table(['Parameter (AFTER RECOVERY)', 'Status Terkini'], $afterData);

        } catch (\Throwable $e) {
            $this->error("Gagal melakukan pemulihan: " . $e->getMessage());
            throw $e;
        }
    }
}
