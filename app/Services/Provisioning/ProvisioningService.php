<?php

namespace App\Services\Provisioning;

use App\Jobs\Provisioning\ReserveResourcesJob;
use App\Jobs\Provisioning\RollbackProvisioningJob;
use App\Jobs\Notifications\SendNotificationJob;
use App\Models\AuditLog;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Models\CRM\Customer;
use App\Models\CRM\Contract;
use App\Models\Customer\CustomerService;
use App\Models\Notification\Notification;
use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Provisioning\ProvisionPipelineStep;
use App\Models\Provisioning\ServiceInstance;
use App\Models\ServiceCatalog\Service;
use App\Repositories\Provisioning\ProvisionPipelineRepository;
use App\Repositories\Provisioning\ServiceInstanceRepository;
use App\Services\ISP\HotspotService;
use App\Services\ISP\PPPoEService;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Domain\Customer\Events\ServiceActivatedEvent;
use Src\Domain\Provisioning\Events\ProvisioningCompletedEvent;
use Src\Domain\Provisioning\Events\ProvisioningFailedEvent;
use Src\Domain\Provisioning\Events\RollbackCompletedEvent;
use Src\Domain\Provisioning\Events\RollbackStartedEvent;
use Src\Domain\Provisioning\Events\ServiceInstanceCreatedEvent;

class ProvisioningService
{
    public function __construct(
        protected ServiceInstanceRepository $serviceInstanceRepository,
        protected ProvisionPipelineRepository $pipelineRepository,
        protected SubscriptionService $subscriptionService,
        protected InvoiceService $invoiceService,
        protected PPPoEService $pppoeService,
        protected HotspotService $hotspotService,
    ) {}

    public function activatePPPoEService(array $data, int $userId): array
    {
        // Validasi Keunikan Data (Nomor HP, Username lintas sistem)
        if (empty($data['customer_id'])) {
            \App\Services\CRM\CustomerValidationService::validateBeforeCreate($data);
        }

        // STEP 1-8: Core Database Transaction (Pelanggan, Layanan, Paket, Instance, Akun Jaringan)
        $coreData = DB::transaction(function () use ($data, $userId) {
            // Step 1: Find or Create Customer
            $customer = $this->findOrCreateCustomer($data, $userId);

            // Step 2: Find or Create Contract for Customer
            $contract = $this->findOrCreateContract($customer, $userId);

            $effectiveBranchId = !empty($data['branch_id']) ? $data['branch_id'] : ($customer->branch_id ?? null);
            $effectiveResellerId = !empty($data['reseller_id']) ? $data['reseller_id'] : ($customer->reseller_id ?? null);

            // Step 3: Get Service Profile (SSOT - withoutGlobalScopes to prevent branch/reseller nullification)
            $serviceProfile = null;
            if (!empty($data['service_profile_id'])) {
                $serviceProfile = \App\Models\ISP\ServiceProfile::withoutGlobalScopes()->find($data['service_profile_id']);
                if (!$serviceProfile) {
                    throw new \DomainException("Aktivasi gagal: Paket Layanan ID '{$data['service_profile_id']}' tidak ditemukan.");
                }
                if (!$serviceProfile->is_active) {
                    throw new \DomainException("Aktivasi gagal: Paket Layanan '{$serviceProfile->name}' berstatus tidak aktif.");
                }
            } else {
                $spQuery = \App\Models\ISP\ServiceProfile::withoutGlobalScopes()
                    ->active()
                    ->where(function ($q) {
                        $q->whereIn('service_type', ['pppoe', 'PPPoE', 'ftth', 'FTTH', 'kombinasi', 'Kombinasi'])
                          ->orWhereNull('service_type');
                    });
                if ($effectiveBranchId) {
                    $spQuery->where(function ($q) use ($effectiveBranchId) {
                        $q->whereNull('branch_id')->orWhere('branch_id', $effectiveBranchId);
                    });
                }
                $serviceProfile = $spQuery->first()
                    ?? \App\Models\ISP\ServiceProfile::withoutGlobalScopes()->active()->first()
                    ?? \App\Models\ISP\ServiceProfile::withoutGlobalScopes()->first();
            }

            if (!$serviceProfile) {
                throw new \DomainException("Aktivasi gagal: Tidak ada Paket Layanan (ServiceProfile) yang terdaftar di sistem.");
            }

            $service = Service::first();
            if (!$service) {
                try {
                    $catalog = \App\Models\ServiceCatalog\ServiceCatalog::withoutGlobalScopes()->firstOrCreate(
                        ['name' => 'Layanan Internet'],
                        [
                            'uuid' => (string) Str::uuid(),
                            'description' => 'Katalog Layanan Default',
                            'is_active' => true,
                            'created_by' => $userId,
                            'updated_by' => $userId
                        ]
                    );
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] == 1062 || $e->errorInfo[1] == 19) {
                        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::withoutGlobalScopes()->where('name', 'Layanan Internet')->firstOrFail();
                    } else {
                        throw $e;
                    }
                }

                $service = Service::create([
                    'uuid' => (string) Str::uuid(),
                    'service_catalog_id' => $catalog->id,
                    'name' => 'Default Internet Service',
                    'description' => 'Auto-generated service',
                    'type' => 'pppoe',
                    'price' => 0,
                    'is_active' => true,
                    'created_by' => $userId,
                    'updated_by' => $userId
                ]);
            }

            $actualOnuId = $this->resolveOnuId($data['onu_id'] ?? null, $data, $userId);

            // Cek konflik kepemilikan ONU (cegah pembajakan perangkat yang aktif pada pelanggan lain)
            if ($actualOnuId) {
                $conflictCs = CustomerService::withoutGlobalScopes()
                    ->where('onu_id', $actualOnuId)
                    ->where('status', 'active')
                    ->where('customer_id', '!=', $customer->id)
                    ->first();
                if ($conflictCs) {
                    $otherCust = Customer::withoutGlobalScopes()->find($conflictCs->customer_id);
                    $otherName = $otherCust ? $otherCust->name : "ID #{$conflictCs->customer_id}";
                    throw new \DomainException("Perangkat ONU sedang aktif digunakan oleh {$otherName}. Aktivasi ditolak untuk mencegah konflik layanan.");
                }
            }

            // Step 4: Find or Create CustomerService (Idempotent)
            $customerService = CustomerService::withoutGlobalScopes()
                ->where('customer_id', $customer->id)
                ->where(function ($q) use ($data) {
                    $q->where('username', $data['username']);
                    if (!empty($data['service_profile_id'])) {
                        $q->orWhere('service_profile_id', $data['service_profile_id']);
                    }
                })
                ->first();

            if ($customerService) {
                $customerService->update([
                    'service_id' => $service?->id ?? $customerService->service_id,
                    'service_profile_id' => $serviceProfile?->id ?? $customerService->service_profile_id,
                    'network_profile_id' => $data['network_profile_id'] ?? $customerService->network_profile_id,
                    'onu_id' => $actualOnuId ?: $customerService->onu_id,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'reseller_id' => $effectiveResellerId,
                    'branch_id' => $effectiveBranchId,
                    'status' => 'pending_provisioning',
                    'updated_by' => $userId,
                ]);
            } else {
                $customerService = CustomerService::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $customer->id,
                    'contract_id' => $contract->id,
                    'service_id' => $service?->id,
                    'service_profile_id' => $serviceProfile?->id,
                    'network_profile_id' => $data['network_profile_id'] ?? null,
                    'onu_id' => $actualOnuId,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'status' => 'pending_provisioning',
                    'reseller_id' => $effectiveResellerId,
                    'branch_id' => $effectiveBranchId,
                    'activated_at' => isset($data['activation_date']) ? \Illuminate\Support\Carbon::parse($data['activation_date']) : now(),
                    'attributes' => [
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'] ?? null,
                        'address' => $data['address'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // Step 5: Create or Retrieve Subscription
            $isFreePackage = $serviceProfile && ($serviceProfile->is_free || (float) $serviceProfile->base_price === 0.0);
            $recurringPrice = $isFreePackage ? 0.0 : (float) ($serviceProfile?->base_price ?? 0);

            $subscription = \App\Models\Billing\Subscription::withoutGlobalScopes()
                ->where('customer_service_id', $customerService->id)
                ->first();

            if (!$subscription) {
                $subscription = $this->subscriptionService->createSubscription(
                    $customerService,
                    $contract,
                    $recurringPrice,
                    $userId
                );
            }

            // Step 6: Create First Invoice if not yet exists
            $invoice = Invoice::withoutGlobalScopes()
                ->where('contract_id', $contract->id)
                ->where('status', 'unpaid')
                ->first();

            if (!$invoice && ($recurringPrice > 0 || $isFreePackage)) {
                $invoiceStatus = $isFreePackage ? 'paid' : 'unpaid';
                $packageName = $serviceProfile?->name ?? 'Bulanan';
                $itemDescription = $isFreePackage ? "Langganan {$packageName} (Gratis)" : "Langganan {$packageName}";

                $invoice = $this->invoiceService->createInvoice(
                    $contract->customer_id,
                    $userId,
                    [
                        [
                            'description' => $itemDescription,
                            'quantity' => 1,
                            'unit_price' => $recurringPrice,
                        ]
                    ],
                    null,
                    null,
                    null,
                    $contract->id,
                    'IDR',
                    $invoiceStatus
                );

                if ($isFreePackage) {
                    $invoice->update([
                        'paid_amount' => 0,
                        'paid_at' => now(),
                        'payment_method' => 'free',
                        'status' => 'paid',
                    ]);
                }
            }

            // Step 7: Create or Update PPPoE User
            $networkData = collect($data)->only([
                'router_id', 'mac_address', 'static_ip', 'odp_id', 'port_number', 'onu_id', 
                'reseller_id', 'branch_id', 'billing_cycle', 'setup_fee'
            ])->filter(fn($val) => $val !== null && $val !== '')->toArray();

            $networkData['reseller_id'] = $effectiveResellerId;
            $networkData['branch_id'] = $effectiveBranchId;
            $networkData['status'] = 'pending_provisioning';

            $pppoeUser = \App\Models\ISP\PPPoEUser::withoutGlobalScopes()
                ->where('customer_service_id', $customerService->id)
                ->orWhere('username', $data['username'])
                ->first();

            if ($pppoeUser) {
                $pppoeUser->update(array_merge([
                    'customer_service_id' => $customerService->id,
                    'service_profile_id' => $serviceProfile?->id ?? $pppoeUser->service_profile_id,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'updated_by' => $userId,
                ], $networkData));
            } else {
                $pppoeUser = $this->pppoeService->createPPPoEUser(
                    $customerService,
                    $serviceProfile?->id ?? 1,
                    null,
                    $userId,
                    $data['username'],
                    $data['password'],
                    $networkData
                );
            }

            // Step 8: Ensure ServiceInstance & ProvisionPipeline exist (SSOT)
            $serviceInstance = ServiceInstance::withoutGlobalScopes()
                ->where('customer_service_id', $customerService->id)
                ->first();

            if (!$serviceInstance) {
                $serviceInstance = $this->serviceInstanceRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'customer_service_id' => $customerService->id,
                    'service_id' => $customerService->service_id,
                    'status' => 'pending_provisioning',
                    'provisioned_at' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            $pipeline = ProvisionPipeline::withoutGlobalScopes()
                ->where('service_instance_id', $serviceInstance->id)
                ->first();

            if (!$pipeline) {
                $pipeline = $this->pipelineRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'service_instance_id' => $serviceInstance->id,
                    'status' => 'pending',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $this->createPipelineSteps($pipeline, $customerService);
            }

            // Audit Log
            $this->logAudit($customerService, $userId, 'Service PPPoE Didaftarkan');

            return [
                'customer' => $customer,
                'contract' => $contract,
                'customer_service' => $customerService,
                'subscription' => $subscription,
                'invoice' => $invoice,
                'pppoe_user' => $pppoeUser,
                'service_instance' => $serviceInstance,
                'pipeline' => $pipeline,
                'service_profile' => $serviceProfile,
            ];
        });

        $customer = $coreData['customer'];
        $contract = $coreData['contract'] ?? null;
        $customerService = $coreData['customer_service'];
        $subscription = $coreData['subscription'];
        $invoice = $coreData['invoice'];
        $pppoeUser = $coreData['pppoe_user'];
        $serviceInstance = $coreData['service_instance'];
        $pipeline = $coreData['pipeline'];
        $serviceProfile = $coreData['service_profile'];

        // STEP 9: Eksekusi Hardware Provisioning (Terpisah dari Transaksi Inti DB)
        $hardwareSuccess = false;
        $hardwareError = null;

        try {
            $ignoreHardware = !empty($data['ignore_hardware_errors']) || !empty($data['is_import']);
            $this->pppoeService->activatePPPoEUser($pppoeUser->id, $userId, $ignoreHardware);
            $hardwareSuccess = true;
        } catch (\Throwable $e) {
            $hardwareError = $e->getMessage();
            \Illuminate\Support\Facades\Log::warning("[ProvisioningService] PPPoE MikroTik hardware provisioning failed: {$hardwareError}");
        }

        if ($hardwareSuccess) {
            $customerService->update([
                'status' => 'active',
                'service_status' => 'online',
                'activated_at' => $customerService->activated_at ?? now(),
            ]);
            $pppoeUser->update(['status' => 'active']);
            $serviceInstance->update([
                'status' => 'active',
                'provisioned_at' => now(),
                'activated_at' => now(),
            ]);
            if ($pipeline) {
                $pipeline->update(['status' => 'completed', 'completed_at' => now()]);
            }

            // Create Notification
            try {
                $notification = Notification::create([
                    'uuid' => (string) Str::uuid(),
                    'recipient_id' => $userId,
                    'title' => 'Layanan PPPoE Aktif',
                    'message' => "Layanan PPPoE untuk {$data['name']} ({$data['username']}) telah aktif.",
                    'type' => 'system',
                    'status' => 'pending',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                SendNotificationJob::dispatch($notification);
            } catch (\Throwable $ne) {}

            // Dispatch Service Activated Event
            try {
                Event::dispatch(new ServiceActivatedEvent(
                    $customerService->uuid,
                    $customer->id,
                    $userId
                ));
            } catch (\Throwable $ee) {}

            // Otomatis kirim notifikasi WhatsApp ke Pelanggan Baru via Gateway
            try {
                $user = \App\Models\User::find($customer->user_id);
                $dueDate = isset($invoice) && $invoice?->due_date 
                    ? (is_object($invoice->due_date) ? $invoice->due_date->format('d M Y') : (string)$invoice->due_date)
                    : now()->addMonth()->format('d M Y');

                app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)->notifyCustomerCreated(
                    $customer,
                    [
                        'service_type' => 'pppoe',
                        'package_name' => $serviceProfile?->name ?? 'Internet PPPoE',
                        'due_date' => $dueDate,
                        'portal_username' => $user?->username ?? $customer->code ?? $customer->phone,
                        'portal_password' => '123456',
                    ],
                    sync: true
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[ProvisioningService] Gagal kirim WA notif pelanggan PPPoE baru: ' . $e->getMessage());
            }
        } else {
            $customerService->update([
                'status' => 'provisioning_failed',
                'service_status' => 'hardware_unreachable',
            ]);
            $pppoeUser->update(['status' => 'failed_provisioning']);
            $serviceInstance->update(['status' => 'failed']);
            if ($pipeline) {
                $pipeline->update([
                    'status' => 'failed',
                    'error_message' => $hardwareError,
                    'failed_at' => now(),
                ]);
            }
        }

        return [
            'success' => $hardwareSuccess,
            'hardware_error' => $hardwareError,
            'customer' => $customer,
            'contract' => $contract,
            'customer_service' => $customerService,
            'service_instance' => $serviceInstance,
            'subscription' => $subscription,
            'invoice' => $invoice,
            'pppoe_user' => $pppoeUser,
        ];
    }


    public function activateHotspotService(array $data, int $userId): array
    {
        // Validasi Keunikan Data (Nomor HP, Username lintas sistem)
        if (empty($data['customer_id'])) {
            \App\Services\CRM\CustomerValidationService::validateBeforeCreate($data);
        }

        // STEP 1-8: Core Database Transaction (Pelanggan, Layanan, Paket, Instance, Akun Jaringan)
        $coreData = DB::transaction(function () use ($data, $userId) {
            // Step 1: Find or Create Customer
            $customer = $this->findOrCreateCustomer($data, $userId);

            // Step 2: Find or Create Contract for Customer
            $contract = $this->findOrCreateContract($customer, $userId);

            $effectiveBranchId = !empty($data['branch_id']) ? $data['branch_id'] : ($customer->branch_id ?? null);
            $effectiveResellerId = !empty($data['reseller_id']) ? $data['reseller_id'] : ($customer->reseller_id ?? null);

            // Step 3: Get Service Profile (SSOT - withoutGlobalScopes)
            $serviceProfile = null;
            if (!empty($data['service_profile_id'])) {
                $serviceProfile = \App\Models\ISP\ServiceProfile::withoutGlobalScopes()->find($data['service_profile_id']);
                if (!$serviceProfile) {
                    throw new \DomainException("Aktivasi gagal: Paket Layanan ID '{$data['service_profile_id']}' tidak ditemukan.");
                }
                if (!$serviceProfile->is_active) {
                    throw new \DomainException("Aktivasi gagal: Paket Layanan '{$serviceProfile->name}' berstatus tidak aktif.");
                }
            } else {
                $spQuery = \App\Models\ISP\ServiceProfile::withoutGlobalScopes()
                    ->active()
                    ->where(function ($q) {
                        $q->whereIn('service_type', ['hotspot', 'Hotspot', 'voucher', 'Voucher', 'member_hotspot', 'kombinasi', 'Kombinasi'])
                          ->orWhereNull('service_type');
                    });
                if ($effectiveBranchId) {
                    $spQuery->where(function ($q) use ($effectiveBranchId) {
                        $q->whereNull('branch_id')->orWhere('branch_id', $effectiveBranchId);
                    });
                }
                $serviceProfile = $spQuery->first()
                    ?? \App\Models\ISP\ServiceProfile::withoutGlobalScopes()->active()->first()
                    ?? \App\Models\ISP\ServiceProfile::withoutGlobalScopes()->first();
            }

            if (!$serviceProfile) {
                throw new \DomainException("Aktivasi gagal: Tidak ada Paket Layanan (ServiceProfile) yang terdaftar di sistem.");
            }

            $service = Service::first();
            if (!$service) {
                try {
                    $catalog = \App\Models\ServiceCatalog\ServiceCatalog::withoutGlobalScopes()->firstOrCreate(
                        ['name' => 'Layanan Internet'],
                        [
                            'uuid' => (string) Str::uuid(),
                            'description' => 'Katalog Layanan Default',
                            'is_active' => true,
                            'created_by' => $userId,
                            'updated_by' => $userId
                        ]
                    );
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] == 1062 || $e->errorInfo[1] == 19) {
                        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::withoutGlobalScopes()->where('name', 'Layanan Internet')->firstOrFail();
                    } else {
                        throw $e;
                    }
                }

                $service = Service::create([
                    'uuid' => (string) Str::uuid(),
                    'service_catalog_id' => $catalog->id,
                    'name' => 'Default Internet Service',
                    'description' => 'Auto-generated service',
                    'type' => 'hotspot',
                    'price' => 0,
                    'is_active' => true,
                    'created_by' => $userId,
                    'updated_by' => $userId
                ]);
            }

            $actualOnuId = $this->resolveOnuId($data['onu_id'] ?? null, $data, $userId);

            // Cek konflik kepemilikan ONU (cegah pembajakan perangkat yang aktif pada pelanggan lain)
            if ($actualOnuId) {
                $conflictCs = CustomerService::withoutGlobalScopes()
                    ->where('onu_id', $actualOnuId)
                    ->where('status', 'active')
                    ->where('customer_id', '!=', $customer->id)
                    ->first();
                if ($conflictCs) {
                    $otherCust = Customer::withoutGlobalScopes()->find($conflictCs->customer_id);
                    $otherName = $otherCust ? $otherCust->name : "ID #{$conflictCs->customer_id}";
                    throw new \DomainException("Perangkat ONU sedang aktif digunakan oleh {$otherName}. Aktivasi ditolak untuk mencegah konflik layanan.");
                }
            }

            // Step 4: Find or Create CustomerService (Idempotent)
            $customerService = CustomerService::withoutGlobalScopes()
                ->where('customer_id', $customer->id)
                ->where(function ($q) use ($data) {
                    $q->where('username', $data['username']);
                    if (!empty($data['service_profile_id'])) {
                        $q->orWhere('service_profile_id', $data['service_profile_id']);
                    }
                })
                ->first();

            if ($customerService) {
                $customerService->update([
                    'service_id' => $service?->id ?? $customerService->service_id,
                    'service_profile_id' => $serviceProfile?->id ?? $customerService->service_profile_id,
                    'network_profile_id' => $data['network_profile_id'] ?? $customerService->network_profile_id,
                    'onu_id' => $actualOnuId ?: $customerService->onu_id,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'reseller_id' => $effectiveResellerId,
                    'branch_id' => $effectiveBranchId,
                    'status' => 'pending_provisioning',
                    'updated_by' => $userId,
                ]);
            } else {
                $customerService = CustomerService::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $customer->id,
                    'contract_id' => $contract->id,
                    'service_id' => $service?->id,
                    'service_profile_id' => $serviceProfile?->id,
                    'network_profile_id' => $data['network_profile_id'] ?? null,
                    'onu_id' => $actualOnuId,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'status' => 'pending_provisioning',
                    'reseller_id' => $effectiveResellerId,
                    'branch_id' => $effectiveBranchId,
                    'activated_at' => isset($data['activation_date']) ? \Illuminate\Support\Carbon::parse($data['activation_date']) : now(),
                    'attributes' => [
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'] ?? null,
                        'address' => $data['address'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // Step 5: Create or Retrieve Subscription if base_price exists or profile is free
            $isFreePackage = $serviceProfile && ($serviceProfile->is_free || (float) $serviceProfile->base_price === 0.0);
            $recurringPrice = $isFreePackage ? 0.0 : (float) ($serviceProfile?->base_price ?? 0);

            $subscription = \App\Models\Billing\Subscription::withoutGlobalScopes()
                ->where('customer_service_id', $customerService->id)
                ->first();

            if (!$subscription && ($recurringPrice > 0 || $isFreePackage)) {
                $subscription = $this->subscriptionService->createSubscription(
                    $customerService,
                    $contract,
                    $recurringPrice,
                    $userId
                );
            }

            // Step 6: Create First Invoice if not exists
            $invoice = Invoice::withoutGlobalScopes()
                ->where('contract_id', $contract->id)
                ->where('status', 'unpaid')
                ->first();

            if (!$invoice && ($recurringPrice > 0 || $isFreePackage)) {
                $invoiceStatus = $isFreePackage ? 'paid' : 'unpaid';
                $packageName = $serviceProfile?->name ?? 'Bulanan';
                $itemDescription = $isFreePackage ? "Langganan {$packageName} (Gratis)" : "Langganan {$packageName}";

                $invoice = $this->invoiceService->createInvoice(
                    $contract->customer_id,
                    $userId,
                    [
                        [
                            'description' => $itemDescription,
                            'quantity' => 1,
                            'unit_price' => $recurringPrice,
                        ]
                    ],
                    null,
                    null,
                    null,
                    $contract->id,
                    'IDR',
                    $invoiceStatus
                );

                if ($isFreePackage) {
                    $invoice->update([
                        'paid_amount' => 0,
                        'paid_at' => now(),
                        'payment_method' => 'free',
                        'status' => 'paid',
                    ]);
                }
            }

            // Step 7: Create or Update Hotspot User
            $networkData = collect($data)->only([
                'router_id', 'mac_address', 'static_ip', 'odp_id', 'port_number', 'onu_id', 
                'reseller_id', 'branch_id', 'billing_cycle', 'setup_fee'
            ])->filter(fn($val) => $val !== null && $val !== '')->toArray();

            $networkData['reseller_id'] = $effectiveResellerId;
            $networkData['branch_id'] = $effectiveBranchId;
            $networkData['status'] = 'pending_provisioning';

            $hotspotUser = \App\Models\ISP\HotspotUser::withoutGlobalScopes()
                ->where('customer_service_id', $customerService->id)
                ->orWhere('username', $data['username'])
                ->first();

            if ($hotspotUser) {
                $hotspotUser->update(array_merge([
                    'customer_service_id' => $customerService->id,
                    'service_profile_id' => $serviceProfile?->id ?? $hotspotUser->service_profile_id,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'updated_by' => $userId,
                ], $networkData));
            } else {
                $hotspotUser = $this->hotspotService->createHotspotUser(
                    $customerService,
                    $serviceProfile?->id ?? 1,
                    null,
                    $userId,
                    $data['username'],
                    $data['password'],
                    false, // provision deferred to step 9
                    $networkData
                );
            }

            // Step 8: Ensure ServiceInstance & ProvisionPipeline exist (SSOT)
            $serviceInstance = ServiceInstance::withoutGlobalScopes()
                ->where('customer_service_id', $customerService->id)
                ->first();

            if (!$serviceInstance) {
                $serviceInstance = $this->serviceInstanceRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'customer_service_id' => $customerService->id,
                    'service_id' => $customerService->service_id,
                    'status' => 'pending_provisioning',
                    'provisioned_at' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            $pipeline = ProvisionPipeline::withoutGlobalScopes()
                ->where('service_instance_id', $serviceInstance->id)
                ->first();

            if (!$pipeline) {
                $pipeline = $this->pipelineRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'service_instance_id' => $serviceInstance->id,
                    'status' => 'pending',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $this->createPipelineSteps($pipeline, $customerService);
            }

            // Audit Log
            $this->logAudit($customerService, $userId, 'Service Hotspot Didaftarkan');

            return [
                'customer' => $customer,
                'contract' => $contract,
                'customer_service' => $customerService,
                'subscription' => $subscription,
                'invoice' => $invoice,
                'hotspot_user' => $hotspotUser,
                'service_instance' => $serviceInstance,
                'pipeline' => $pipeline,
                'service_profile' => $serviceProfile,
            ];
        });

        $customer = $coreData['customer'];
        $contract = $coreData['contract'] ?? null;
        $customerService = $coreData['customer_service'];
        $subscription = $coreData['subscription'];
        $invoice = $coreData['invoice'];
        $hotspotUser = $coreData['hotspot_user'];
        $serviceInstance = $coreData['service_instance'];
        $pipeline = $coreData['pipeline'];
        $serviceProfile = $coreData['service_profile'];

        // STEP 9: Eksekusi Hardware Provisioning (Terpisah dari Transaksi Inti DB)
        $hardwareSuccess = false;
        $hardwareError = null;

        try {
            $this->hotspotService->activateHotspotUser($hotspotUser->id, $userId);
            $hardwareSuccess = true;
        } catch (\Throwable $e) {
            $hardwareError = $e->getMessage();
            \Illuminate\Support\Facades\Log::warning("[ProvisioningService] Hotspot hardware provisioning failed: {$hardwareError}");
        }

        if ($hardwareSuccess) {
            $customerService->update([
                'status' => 'active',
                'service_status' => 'online',
                'activated_at' => $customerService->activated_at ?? now(),
            ]);
            $hotspotUser->update(['status' => 'active']);
            $serviceInstance->update([
                'status' => 'active',
                'provisioned_at' => now(),
                'activated_at' => now(),
            ]);
            if ($pipeline) {
                $pipeline->update(['status' => 'completed', 'completed_at' => now()]);
            }

            // Notification
            try {
                $notification = Notification::create([
                    'uuid' => (string) Str::uuid(),
                    'recipient_id' => $userId,
                    'title' => 'Layanan Hotspot Aktif',
                    'message' => "Layanan Hotspot untuk {$data['name']} ({$data['username']}) telah aktif.",
                    'type' => 'system',
                    'status' => 'pending',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                SendNotificationJob::dispatch($notification);
            } catch (\Throwable $ne) {}

            // Dispatch Event
            try {
                Event::dispatch(new ServiceActivatedEvent(
                    $customerService->uuid,
                    $customer->id,
                    $userId
                ));
            } catch (\Throwable $ee) {}

            // WA Notification
            try {
                $user = \App\Models\User::find($customer->user_id);
                $dueDate = isset($invoice) && $invoice?->due_date 
                    ? (is_object($invoice->due_date) ? $invoice->due_date->format('d M Y') : (string)$invoice->due_date)
                    : now()->addMonth()->format('d M Y');

                app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)->notifyCustomerCreated(
                    $customer,
                    [
                        'service_type' => 'hotspot',
                        'package_name' => $serviceProfile?->name ?? 'Internet Hotspot',
                        'due_date' => $dueDate,
                        'hotspot_username' => $data['username'],
                        'hotspot_password' => $data['password'],
                        'portal_username' => $user?->username ?? $customer->code ?? $customer->phone,
                        'portal_password' => '123456',
                    ],
                    sync: true
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[ProvisioningService] Gagal kirim WA notif pelanggan Hotspot baru: ' . $e->getMessage());
            }
        } else {
            $customerService->update([
                'status' => 'provisioning_failed',
                'service_status' => 'hardware_unreachable',
            ]);
            $hotspotUser->update(['status' => 'failed_provisioning']);
            $serviceInstance->update(['status' => 'failed']);
            if ($pipeline) {
                $pipeline->update([
                    'status' => 'failed',
                    'error_message' => $hardwareError,
                    'failed_at' => now(),
                ]);
            }
        }

        return [
            'success' => $hardwareSuccess,
            'hardware_error' => $hardwareError,
            'customer' => $customer,
            'contract' => $contract,
            'customer_service' => $customerService,
            'service_instance' => $serviceInstance,
            'subscription' => $subscription,
            'invoice' => $invoice ?? null,
            'hotspot_user' => $hotspotUser,
        ];
    }


    protected function findOrCreateCustomer(array $data, int $userId): Customer
    {
        // 1. Jika customer_id sudah diberikan secara eksplisit, gunakan langsung
        if (!empty($data['customer_id'])) {
            $customer = Customer::withoutGlobalScopes()->find($data['customer_id']);
            if ($customer) {
                // Pastikan user portal login tersedia jika belum ada
                if (empty($customer->user_id)) {
                    $maxAttempts = 5;
                    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                        DB::beginTransaction();
                        try {
                            $code = ($attempt === 1 && !empty($customer->code))
                                ? $customer->code
                                : \App\Services\CRM\CustomerCodeGenerator::generate();

                            if ($customer->code !== $code) {
                                $customer->update(['code' => $code]);
                            }

                            $custEmail = !empty($customer->email) ? trim($customer->email) : null;
                            if ($custEmail && \App\Models\User::where('email', $custEmail)->exists()) {
                                $custEmail = null;
                            }

                            $user = \App\Models\User::create([
                                'name' => $customer->name,
                                'email' => $custEmail,
                                'whatsapp' => $customer->phone,
                                'customer_code' => $code,
                                'username' => $code,
                                'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                                'is_active' => true,
                            ]);

                            if ($role = \App\Models\Role::where('name', 'customer')->first()) {
                                $user->roles()->attach($role->id);
                            }

                            $customer->update(['user_id' => $user->id]);
                            DB::commit();
                            break;
                        } catch (\Illuminate\Database\QueryException $qe) {
                            DB::rollBack();
                            if ($this->isDuplicateCodeException($qe) && $attempt < $maxAttempts) {
                                usleep(random_int(10000, 50000));
                                continue;
                            }
                            throw $qe;
                        }
                    }
                }
                return $customer;
            }
        }

        // === VALIDASI KEAMANAN: Cek jika nomor HP sudah dipakai oleh Admin/Reseller ===
        $phone = $data['phone'];
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $phone0 = $cleanPhone;
        $phone62 = $cleanPhone;
        if (str_starts_with($cleanPhone, '62')) {
            $phone0 = '0' . substr($cleanPhone, 2);
        } elseif (str_starts_with($cleanPhone, '0')) {
            $phone62 = '62' . substr($cleanPhone, 1);
        }

        $existingUser = \App\Models\User::whereRaw('LOWER(whatsapp) IN (?, ?)', [$phone0, $phone62])->first();
        if ($existingUser) {
            // Jika user yang ditemukan BUKAN customer, tolak dengan tegas!
            if (!$existingUser->hasRole('customer')) {
                throw new \InvalidArgumentException(
                    "Nomor HP '{$phone}' sudah terdaftar sebagai akun pengurus/admin ({$existingUser->name}). " .
                    "Tidak dapat mendaftarkan pelanggan baru dengan nomor HP yang sama."
                );
            }
        }

        // Try to find by phone (normalisasi 0 / 62)
        $customer = Customer::withoutGlobalScopes()
            ->where(function($q) use ($phone, $phone0, $phone62) {
                $q->where('phone', $phone)
                  ->orWhere('phone', $phone0)
                  ->orWhere('phone', $phone62);
            })
            ->first();

        // Cek apakah customer yang ditemukan adalah akun guest voucher
        $isGuest = $customer && (empty($customer->code) || str_starts_with($customer->code, 'GST-') || str_starts_with($customer->name, 'Guest '));

        // Jika customer yang ditemukan BUKAN guest dan bukan explicit customer_id, tolak dengan tegas!
        if ($customer && !$isGuest && empty($data['customer_id'])) {
            $code = $customer->display_id ?? $customer->code ?? 'ID-' . $customer->id;
            throw new \InvalidArgumentException(
                "Nomor HP '{$phone}' sudah terdaftar di database milik pelanggan '{$customer->name}' (ID: {$code}). " .
                "Satu nomor HP hanya boleh untuk satu pelanggan."
            );
        }

        if (!$customer) {
            $requestedCode = $data['customer_code'] ?? null;
            $maxAttempts = 5;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                DB::beginTransaction();

                try {
                    $generatedCode = ($attempt === 1 && !empty($requestedCode))
                        ? \App\Services\CRM\CustomerCodeGenerator::ensureUnique($requestedCode)
                        : \App\Services\CRM\CustomerCodeGenerator::generate();

                    // Create user portal login
                    $portalEmail = !empty($data['email']) ? trim($data['email']) : null;
                    if ($portalEmail && \App\Models\User::where('email', $portalEmail)->exists()) {
                        $portalEmail = null;
                    }

                    $user = \App\Models\User::create([
                        'name' => $data['name'],
                        'email' => $portalEmail,
                        'whatsapp' => $data['phone'],
                        'customer_code' => $generatedCode,
                        'username' => $generatedCode,
                        'pppoe_username' => $data['username'] ?? null,
                        'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                        'is_active' => true,
                    ]);

                    if ($role = \App\Models\Role::where('name', 'customer')->first()) {
                        $user->roles()->attach($role->id);
                    }

                    $customer = Customer::create([
                        'code' => $generatedCode,
                        'user_id' => $user->id,
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'] ?? null,
                        'address' => $data['address'] ?? null,
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                        'reseller_id' => !empty($data['reseller_id']) ? $data['reseller_id'] : null,
                        'branch_id' => !empty($data['branch_id']) ? $data['branch_id'] : null,
                        'status' => 'active',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);

                    DB::commit();
                    break;
                } catch (\Illuminate\Database\QueryException $qe) {
                    DB::rollBack();

                    if ($this->isDuplicateCodeException($qe) && $attempt < $maxAttempts) {
                        \Illuminate\Support\Facades\Log::warning("[Provisioning] Customer code collision ('{$generatedCode}') on attempt {$attempt}: {$qe->getMessage()}. Retrying with fresh sequence...");
                        usleep(random_int(10000, 50000));
                        continue;
                    }

                    throw $qe;
                }
            }
        } else {
            // Jika customer yang ditemukan adalah Guest Voucher, upgrade menjadi regular customer
            if ($isGuest) {
                $maxAttempts = 5;
                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    DB::beginTransaction();
                    try {
                        $newCode = ($attempt === 1 && !empty($data['customer_code']))
                            ? \App\Services\CRM\CustomerCodeGenerator::ensureUnique($data['customer_code'])
                            : \App\Services\CRM\CustomerCodeGenerator::generate();

                        $customer->update([
                            'code' => $newCode,
                            'name' => $data['name'],
                            'email' => $data['email'] ?? $customer->email,
                            'address' => $data['address'] ?? $customer->address,
                            'latitude' => $data['latitude'] ?? $customer->latitude,
                            'longitude' => $data['longitude'] ?? $customer->longitude,
                            'reseller_id' => !empty($data['reseller_id']) ? $data['reseller_id'] : $customer->reseller_id,
                            'status' => 'active',
                            'updated_by' => $userId,
                        ]);

                        if (!empty($customer->user_id)) {
                            $user = \App\Models\User::find($customer->user_id);
                            if ($user) {
                                $user->update([
                                    'name' => $data['name'],
                                    'customer_code' => $newCode,
                                    'username' => $newCode,
                                    'email' => $data['email'] ?? $user->email,
                                    'is_active' => true,
                                ]);
                            }
                        }
                        DB::commit();
                        break;
                    } catch (\Illuminate\Database\QueryException $qe) {
                        DB::rollBack();
                        if ($this->isDuplicateCodeException($qe) && $attempt < $maxAttempts) {
                            usleep(random_int(10000, 50000));
                            continue;
                        }
                        throw $qe;
                    }
                }
            } else {
                $updateFields = [];
                if (!empty($data['address']) && empty($customer->address)) {
                    $updateFields['address'] = $data['address'];
                }
                if (!empty($data['latitude'])) {
                    $updateFields['latitude'] = $data['latitude'];
                }
                if (!empty($data['longitude'])) {
                    $updateFields['longitude'] = $data['longitude'];
                }
                if (!empty($data['reseller_id']) && empty($customer->reseller_id)) {
                    $updateFields['reseller_id'] = $data['reseller_id'];
                }
                if (!empty($data['branch_id']) && empty($customer->branch_id)) {
                    $updateFields['branch_id'] = $data['branch_id'];
                }
                if (!empty($updateFields)) {
                    $customer->update($updateFields);
                    $customer->refresh();
                }
            }

            // Jika customer sudah ada tapi belum punya akun login portal, buatkan sekarang
            if (empty($customer->user_id)) {
                $maxAttempts = 5;
                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    DB::beginTransaction();
                    try {
                        $code = ($attempt === 1 && !empty($customer->code))
                            ? $customer->code
                            : \App\Services\CRM\CustomerCodeGenerator::generate();

                        if ($customer->code !== $code) {
                            $customer->update(['code' => $code]);
                        }

                        $user = \App\Models\User::create([
                            'name' => $customer->name,
                            'email' => $customer->email,
                            'whatsapp' => $customer->phone,
                            'customer_code' => $code,
                            'username' => $code,
                            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                            'is_active' => true,
                        ]);

                        if ($role = \App\Models\Role::where('name', 'customer')->first()) {
                            $user->roles()->attach($role->id);
                        }

                        $customer->update(['user_id' => $user->id]);
                        DB::commit();
                        break;
                    } catch (\Illuminate\Database\QueryException $qe) {
                        DB::rollBack();
                        if ($this->isDuplicateCodeException($qe) && $attempt < $maxAttempts) {
                            usleep(random_int(10000, 50000));
                            continue;
                        }
                        throw $qe;
                    }
                }
            }
        }

        return $customer;
    }

    protected function findOrCreateContract(Customer $customer, int $userId): Contract
    {
        // Find active contract or create new
        $contract = $customer->contracts()->where('status', 'active')->first();

        if (!$contract) {
            $contract = Contract::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'contract_number' => 'CONTRACT-' . strtoupper(Str::random(10)),
                'start_date' => now(),
                'status' => 'active',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }

        return $contract;
    }

    protected function logAudit(CustomerService $customerService, int $userId, string $action): void
    {
        \App\Models\AuditLog::create([
            'auditable_type' => CustomerService::class,
            'auditable_id' => $customerService->id,
            'event' => $action,
            'new_values' => $customerService->getChanges(),
            'user_id' => $userId,
            'notes' => 'Generated by ProvisioningService',
        ]);
    }

    public function startProvisioning(CustomerService $customerService, int $userId): ServiceInstance
    {
        return DB::transaction(function () use ($customerService, $userId) {
            $serviceInstance = $this->serviceInstanceRepository->create([
                'uuid' => (string) Str::uuid(),
                'customer_service_id' => $customerService->id,
                'service_id' => $customerService->service_id,
                'status' => 'provisioning',
                'provisioned_at' => null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $pipeline = $this->pipelineRepository->create([
                'uuid' => (string) Str::uuid(),
                'service_instance_id' => $serviceInstance->id,
                'status' => 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->createPipelineSteps($pipeline, $customerService);

            try {
                Event::dispatch(new ServiceInstanceCreatedEvent($serviceInstance->uuid, $customerService->id));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[ProvisioningService] Legacy ServiceInstanceCreatedEvent dispatch ignored: ' . $e->getMessage());
            }

            app(\App\Services\Provisioning\Pipeline\PipelineOrchestrator::class)->processNext($pipeline);

            return $serviceInstance;
        });
    }

    protected function createPipelineSteps(ProvisionPipeline $pipeline, ?CustomerService $customerService = null): void
    {
        $steps = [
            ['name' => 'Validasi Layanan', 'type' => 'customer_validation', 'order' => 1],
            ['name' => 'Reservasi Port & Resource', 'type' => 'resource_reservation', 'order' => 2],
            ['name' => 'Registrasi Perangkat ONU', 'type' => 'olt_onu_registration', 'order' => 3],
            ['name' => 'Konfigurasi Profil OLT & VLAN', 'type' => 'olt_service_provisioning', 'order' => 4],
            ['name' => 'Aktivasi PPPoE / RADIUS', 'type' => 'radius_provisioning', 'order' => 5],
            ['name' => 'Konfigurasi TR-069 (GenieACS)', 'type' => 'onu_tr069_provisioning', 'order' => 6],
            ['name' => 'Verifikasi Layanan', 'type' => 'service_verification', 'order' => 7],
        ];

        foreach ($steps as $stepData) {
            ProvisionPipelineStep::create([
                'provision_pipeline_id' => $pipeline->id,
                'step_name' => $stepData['name'],
                'step_type' => $stepData['type'],
                'order' => $stepData['order'],
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
            ]);
        }

        $pipeline->update([
            'total_steps' => count($steps),
            'status' => 'pending',
            'current_step' => 0,
            'completed_at' => null,
        ]);
    }

    public function executeStep(int $pipelineId, string $stepName, int $serviceInstanceId, callable $callback): bool
    {
        $pipeline = $this->pipelineRepository->find($pipelineId);
        $step = $pipeline->steps()->where('step_name', $stepName)->first();

        try {
            $step->update(['status' => 'in_progress', 'started_at' => now()]);
            $callback();
            $step->update(['status' => 'completed', 'completed_at' => now()]);
            $pipeline->increment('current_step');

            $this->checkPipelineCompletion($pipeline, $serviceInstanceId);

            return true;
        } catch (\Exception $e) {
            $step->update(['status' => 'failed', 'failed_at' => now(), 'error_message' => $e->getMessage()]);
            $pipeline->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'failed_at' => now()]);

            Event::dispatch(new ProvisioningFailedEvent(
                ServiceInstance::find($serviceInstanceId)->uuid,
                $stepName,
                $e->getMessage()
            ));

            $this->triggerRollback($pipeline, $serviceInstanceId);

            return false;
        }
    }

    protected function checkPipelineCompletion(ProvisionPipeline $pipeline, int $serviceInstanceId): void
    {
        if ($pipeline->current_step == $pipeline->total_steps) {
            $pipeline->update(['status' => 'completed', 'completed_at' => now()]);

            $serviceInstance = $this->serviceInstanceRepository->find($serviceInstanceId);
            $serviceInstance->update(['status' => 'provisioned', 'provisioned_at' => now()]);

            Event::dispatch(new ProvisioningCompletedEvent($serviceInstance->uuid));
        }
    }

    protected function triggerRollback(ProvisionPipeline $pipeline, int $serviceInstanceId): void
    {
        $stepsToRollback = $pipeline->steps()
            ->where('status', 'completed')
            ->orderBy('order', 'desc')
            ->pluck('step_name')
            ->toArray();

        $pipeline->update(['rollback_steps' => $stepsToRollback]);

        $serviceInstance = $this->serviceInstanceRepository->find($serviceInstanceId);
        Event::dispatch(new RollbackStartedEvent($serviceInstance->uuid, $stepsToRollback));

        RollbackProvisioningJob::dispatch($serviceInstanceId, $stepsToRollback);
    }

    public function completeRollback(int $serviceInstanceId): void
    {
        $serviceInstance = $this->serviceInstanceRepository->find($serviceInstanceId);
        $pipeline = $serviceInstance->provisionPipeline;

        $pipeline->steps()->update(['rollback_completed_at' => now()]);
        $pipeline->update(['status' => 'rolled_back']);
        $serviceInstance->update(['status' => 'cancelled']);

        Event::dispatch(new RollbackCompletedEvent($serviceInstance->uuid));
    }

    public function cancelProvisioning(ServiceInstance $serviceInstance): void
    {
        $pipeline = $serviceInstance->provisionPipeline;
        if ($pipeline && $pipeline->status != 'completed') {
            $this->triggerRollback($pipeline, $serviceInstance->id);
        }
    }

    /**
     * Resolusi nilai onu_id dari input (bisa berupa integer ID atau string Serial Number ONU/modem).
     * Jika berupa string SN, cari record ONU atau buat otomatis agar tidak memicu error integer mismatch.
     */
    private function resolveOnuId(mixed $onuInput, array $data, int $userId): ?int
    {
        if (empty($onuInput)) {
            return null;
        }

        if (is_numeric($onuInput)) {
            return (int) $onuInput;
        }

        $sn = trim((string) $onuInput);
        if ($sn === '') {
            return null;
        }

        // Cari berdasarkan serial_number, mac_address, atau code di tabel onus
        $onu = \App\Models\ISP\Onu::where('serial_number', $sn)
            ->orWhere('mac_address', $sn)
            ->orWhere('code', $sn)
            ->first();

        if ($onu) {
            return (int) $onu->id;
        }

        // Jika belum terdaftar, otomatis daftarkan ONU baru dengan serial_number tersebut
        try {
            $createdOnu = \App\Models\ISP\Onu::create([
                'code' => 'ONU-' . strtoupper(substr(md5($sn . time()), 0, 6)),
                'name' => 'ONU ' . ($data['name'] ?? $sn),
                'serial_number' => $sn,
                'status' => 'active',
                'branch_id' => $data['branch_id'] ?? null,
                'olt_id' => $data['olt_id'] ?? null,
                'odp_id' => $data['odp_id'] ?? null,
                'created_by' => $userId,
            ]);
            return (int) $createdOnu->id;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[ProvisioningService] Gagal auto-create ONU: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Memeriksa apakah exception database disebabkan oleh benturan unique constraint pada kode customer / username.
     */
    protected function isDuplicateCodeException(\Illuminate\Database\QueryException $qe): bool
    {
        $sqlState = (string) $qe->getCode();
        $errorCode = $qe->errorInfo[1] ?? 0;
        $message = $qe->getMessage();

        // MySQL error 1062, SQLite error 19, SQLSTATE 23000
        $isDuplicateCodeOrState = 
            $sqlState === '23000' || 
            $sqlState === '1062' || 
            $sqlState === '19' || 
            $errorCode == 1062 || 
            $errorCode == 19 ||
            str_contains($message, '1062') ||
            str_contains($message, '23000');

        if ($isDuplicateCodeOrState) {
            if (
                str_contains($message, 'users_customer_code_unique') ||
                str_contains($message, 'users_username_unique') ||
                str_contains($message, 'customers_code_unique') ||
                str_contains($message, 'members_code_unique') ||
                str_contains($message, 'customer_code') ||
                str_contains($message, 'Duplicate entry') ||
                str_contains($message, 'UNIQUE constraint failed')
            ) {
                return true;
            }
        }

        return false;
    }
}
