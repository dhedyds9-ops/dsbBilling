<?php

namespace App\Listeners\ISP;

use App\Models\Customer\CustomerService;
use App\Services\Provisioning\ProvisioningOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Src\Domain\Customer\Events\ServiceActivatedEvent;
use Src\Domain\Customer\Events\ServiceReactivatedEvent;
use Throwable;

/**
 * Memicu provisioning perangkat saat layanan diaktifkan/diaktifkan ulang.
 *
 * Logika OLT-QoS, ACS/TR-069, dan ODP kini terpusat di ProvisioningOrchestrator
 * sehingga listener ini hanya bertugas sebagai jembatan event -> orkestrator.
 */
class FiberServiceProvisioningListener implements ShouldQueue
{
    public $queue = 'provisioning';

    public function __construct(
        protected ProvisioningOrchestrator $orchestrator,
    ) {
    }

    public function handle(ServiceActivatedEvent|ServiceReactivatedEvent $event): void
    {
        try {
            $uuid = $event->serviceUuid ?? $event->customerServiceUuid ?? null;
            if (!$uuid) {
                return;
            }

            $customerService = CustomerService::where('uuid', $uuid)->first();
            if (!$customerService) {
                return;
            }

            $result = $this->orchestrator->provisionOnu($customerService);

            Log::info('Fiber provisioning done for service activation', [
                'uuid' => $uuid,
                'onu_id' => $customerService->onu_id,
                'result' => $result['status'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('FiberServiceProvisioningListener failed', [
                'event' => $event::class,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
