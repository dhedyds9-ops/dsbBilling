<?php

namespace App\Listeners\ISP;

use App\Models\Customer\CustomerService;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\ISP\GenieAcsProvisioningService;
use App\Services\ISP\OdpOccupancyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Customer\Events\ServiceSuspendedEvent;
use Src\Domain\Customer\Events\ServiceTerminatedEvent;
use Throwable;

class FiberServiceSuspensionListener implements ShouldQueue
{
    public $queue = 'provisioning';

    public function __construct(
        protected OltRegistry $oltRegistry,
        protected GenieAcsProvisioningService $genieAcs,
        protected OdpOccupancyService $odpOccupancy,
    ) {
    }

    public function handle(ServiceSuspendedEvent|ServiceTerminatedEvent $event): void
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
            $onu = $customerService->onu;
            if (!$onu) {
                return;
            }
            $reason = $event->reason ?? 'suspended';

            DB::transaction(function () use ($onu, $reason) {
                if ($onu->olt) {
                    try {
                        $driver = $this->oltRegistry->forOlt($onu->olt);
                        if ($reason === 'terminated') {
                            $driver->setOnuAdminStatus($onu, 'disable');
                        } else {
                            $driver->setOnuBandwidthLimit($onu, 1, 1);
                            $driver->setOnuAdminStatus($onu, 'disable');
                        }
                        $onu->provision_status = 'suspended';
                        $onu->saveQuietly();
                    } catch (Throwable $e) {
                        Log::warning('OLT suspension failed', [
                            'onu_id' => $onu->id,
                            'msg' => $e->getMessage(),
                        ]);
                    }
                }

                try {
                    $this->genieAcs->updateSsidAndPassword(
                        $onu,
                        $onu->wifi_ssid ?: 'ISP-SUSPENDED',
                        'pay-your-bill-' . substr(md5((string)$onu->id), 0, 6)
                    );
                } catch (Throwable) {
                }

                if ($onu->odp_id) {
                    try {
                        $this->odpOccupancy->recalculateOne($onu->odp);
                    } catch (Throwable) {
                    }
                }
            });
            Log::info('Fiber suspension done', ['uuid' => $uuid, 'onu_id' => $onu->id]);
        } catch (Throwable $e) {
            Log::error('FiberServiceSuspensionListener failed', ['msg' => $e->getMessage()]);
        }
    }
}
