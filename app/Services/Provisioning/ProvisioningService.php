<?php

namespace App\Services\Provisioning;

use App\Jobs\Provisioning\ReserveResourcesJob;
use App\Jobs\Provisioning\RollbackProvisioningJob;
use App\Jobs\Notifications\SendNotificationJob;
use App\Models\AuditLog;
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
        return DB::transaction(function () use ($data, $userId) {
            // Step 1: Find or Create Customer
            $customer = $this->findOrCreateCustomer($data, $userId);

            // Step 2: Find or Create Contract for Customer
            $contract = $this->findOrCreateContract($customer, $userId);

            // Step 3: Get Service Profile (Single Source of Truth)
            $serviceProfile = \App\Models\ISP\ServiceProfile::find($data['service_profile_id'] ?? null) ?? \App\Models\ISP\ServiceProfile::first();
            $service = Service::first();
            
            // Auto-create default Service and Catalog if empty to prevent NOT NULL constraint
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

            // Step 4: Create CustomerService
            $customerService = CustomerService::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'contract_id' => $contract->id,
                'service_id' => $service?->id,
                'service_profile_id' => $serviceProfile?->id,
                'network_profile_id' => $data['network_profile_id'] ?? null,
                'onu_id' => $data['onu_id'] ?? null,
                'username' => $data['username'],
                'password' => $data['password'],
                'status' => $data['status'] ?? 'active',
                'reseller_id' => !empty($data['reseller_id']) ? $data['reseller_id'] : null,
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

            // Step 5: Create Subscription - Use base_price from ServiceProfile
            $recurringPrice = $serviceProfile?->base_price ?? 100000;
            $subscription = $this->subscriptionService->createSubscription(
                $customerService,
                $contract,
                $recurringPrice,
                $userId
            );

            // Step 6: Create First Invoice
            $invoice = $this->invoiceService->createInvoice(
                $contract->customer_id,
                $userId,
                [
                    [
                        'description' => 'Langganan Bulanan',
                        'quantity' => 1,
                        'unit_price' => $recurringPrice,
                    ]
                ],
                null,
                null,
                null,
                $contract->id
            );

            // Step 7: Create PPPoE User
            $networkData = collect($data)->only([
                'router_id', 'mac_address', 'static_ip', 'odp_id', 'port_number', 'onu_id', 
                'reseller_id', 'billing_cycle', 'setup_fee'
            ])->filter(fn($val) => $val !== null && $val !== '')->toArray();

            $pppoeUser = $this->pppoeService->createPPPoEUser(
                $customerService,
                $serviceProfile?->id ?? 1,
                null,
                $userId,
                $data['username'],
                $data['password'],
                $networkData
            );

            $this->pppoeService->activatePPPoEUser($pppoeUser->id, $userId);

            // Step 8: Audit Log
            $this->logAudit($customerService, $userId, 'Service PPPoE Diaktifkan');

            // Step 9: Create Notification
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

            // Step 10: Dispatch Service Activated Event
            Event::dispatch(new ServiceActivatedEvent(
                $customerService->uuid,
                $customer->id,
                $userId
            ));

            return [
                'success' => true,
                'customer' => $customer,
                'customer_service' => $customerService,
                'subscription' => $subscription,
                'invoice' => $invoice,
                'pppoe_user' => $pppoeUser,
            ];
        });
    }

    public function activateHotspotService(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            // Step 1: Find or Create Customer
            $customer = $this->findOrCreateCustomer($data, $userId);

            // Step 2: Find or Create Contract for Customer
            $contract = $this->findOrCreateContract($customer, $userId);

            // Step 3: Get Service Profile (Single Source of Truth)
            $serviceProfile = \App\Models\ISP\ServiceProfile::find($data['service_profile_id'] ?? null) ?? \App\Models\ISP\ServiceProfile::first();
            $service = Service::first();
            
            // Auto-create default Service and Catalog if empty to prevent NOT NULL constraint
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

            // Step 4: Create CustomerService
            $customerService = CustomerService::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'contract_id' => $contract->id,
                'service_id' => $service?->id,
                'service_profile_id' => $serviceProfile?->id,
                'network_profile_id' => $data['network_profile_id'] ?? null,
                'onu_id' => $data['onu_id'] ?? null,
                'username' => $data['username'],
                'password' => $data['password'],
                'status' => $data['status'] ?? 'active',
                'reseller_id' => !empty($data['reseller_id']) ? $data['reseller_id'] : null,
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

            // Step 5: Create Subscription if base_price exists
            $recurringPrice = $serviceProfile?->base_price;
            $subscription = null;
            if ($recurringPrice) {
                $subscription = $this->subscriptionService->createSubscription(
                    $customerService,
                    $contract,
                    $recurringPrice,
                    $userId
                );

                // Step 6: Create First Invoice
                $invoice = $this->invoiceService->createInvoice(
                    $contract->customer_id,
                    $userId,
                    [
                        [
                            'description' => 'Langganan Bulanan',
                            'quantity' => 1,
                            'unit_price' => $recurringPrice,
                        ]
                    ],
                    null,
                    null,
                    null,
                    $contract->id
                );
            }

            // Step 7: Create Hotspot User
            $networkData = collect($data)->only([
                'router_id', 'mac_address', 'static_ip', 'odp_id', 'port_number', 'onu_id', 
                'reseller_id', 'billing_cycle', 'setup_fee'
            ])->filter(fn($val) => $val !== null && $val !== '')->toArray();

            $hotspotUser = $this->hotspotService->createHotspotUser(
                $customerService,
                $serviceProfile?->id ?? 1,
                null,
                $userId,
                $data['username'],
                $data['password'],
                true, // provisionOnCreate
                $networkData
            );

            $this->hotspotService->activateHotspotUser($hotspotUser->id, $userId);

            // Step 8: Audit Log
            $this->logAudit($customerService, $userId, 'Service Hotspot Diaktifkan');

            // Step 9: Create Notification
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

            // Step 10: Dispatch Service Activated Event
            Event::dispatch(new ServiceActivatedEvent(
                $customerService->uuid,
                $customer->id,
                $userId
            ));

            return [
                'success' => true,
                'customer' => $customer,
                'customer_service' => $customerService,
                'subscription' => $subscription,
                'invoice' => $invoice ?? null,
                'hotspot_user' => $hotspotUser,
            ];
        });
    }

    protected function findOrCreateCustomer(array $data, int $userId): Customer
    {
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

        // Jika customer yang ditemukan BUKAN guest tapi namanya berbeda secara signifikan:
        // Jangan timpa/gabungkan akun orang lain, melainkan buat customer baru
        if ($customer && !$isGuest && !empty($data['name']) && strcasecmp(trim($customer->name), trim($data['name'])) !== 0) {
            $customer = null;
        }

        if (!$customer) {
            $generatedCode = $data['customer_code'] ?? \App\Services\CRM\CustomerCodeGenerator::generate();

            // Create user portal login
            $user = \App\Models\User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
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
                'status' => 'active',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        } else {
            // Jika customer yang ditemukan adalah Guest Voucher, upgrade menjadi regular customer
            if ($isGuest) {
                $newCode = $data['customer_code'] ?? \App\Services\CRM\CustomerCodeGenerator::generate();
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
            } else {
                $updateFields = [];
                if (!empty($data['address']) && empty($customer->address)) {
                    $updateFields['address'] = $data['address'];
                }
                if (!empty($data['reseller_id']) && empty($customer->reseller_id)) {
                    $updateFields['reseller_id'] = $data['reseller_id'];
                }
                if (!empty($updateFields)) {
                    $customer->update($updateFields);
                }
            }

            // Jika customer sudah ada tapi belum punya akun login portal, buatkan sekarang
            if (empty($customer->user_id)) {
                $code = $customer->code ?: ($data['customer_code'] ?? \App\Services\CRM\CustomerCodeGenerator::generate());
                if (empty($customer->code)) {
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
            $isAlreadyActive = $customerService->status === 'active';

            $serviceInstance = $this->serviceInstanceRepository->create([
                'uuid' => (string) Str::uuid(),
                'customer_service_id' => $customerService->id,
                'service_id' => $customerService->service_id,
                'status' => $isAlreadyActive ? 'provisioned' : 'provisioning',
                'provisioned_at' => $isAlreadyActive ? now() : null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $pipeline = $this->pipelineRepository->create([
                'uuid' => (string) Str::uuid(),
                'service_instance_id' => $serviceInstance->id,
                'status' => $isAlreadyActive ? 'completed' : 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->createPipelineSteps($pipeline, $customerService);

            Event::dispatch(new ServiceInstanceCreatedEvent($serviceInstance->uuid, $customerService->id));

            if (!$isAlreadyActive) {
                app(\App\Services\Provisioning\Pipeline\PipelineOrchestrator::class)->processNext($pipeline);
            }

            return $serviceInstance;
        });
    }

    protected function createPipelineSteps(ProvisionPipeline $pipeline, ?CustomerService $customerService = null): void
    {
        $isAlreadyActive = $customerService && $customerService->status === 'active';

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
                'status' => $isAlreadyActive ? 'completed' : 'pending',
                'started_at' => $isAlreadyActive ? now() : null,
                'completed_at' => $isAlreadyActive ? now() : null,
            ]);
        }

        $pipeline->update([
            'total_steps' => count($steps),
            'status' => $isAlreadyActive ? 'completed' : 'pending',
            'current_step' => $isAlreadyActive ? count($steps) : 0,
            'completed_at' => $isAlreadyActive ? now() : null,
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
}
