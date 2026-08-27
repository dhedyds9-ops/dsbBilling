<?php

namespace App\Listeners\ISP;

use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\ISP\GenieAcsProvisioningService;
use App\Services\ISP\OdpOccupancyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Customer\Events\ServiceActivatedEvent;
use Src\Domain\Customer\Events\ServiceReactivatedEvent;
use Throwable;

class FiberServiceProvisioningListener implements ShouldQueue
{
    public $queue = 'provisioning';

    public function __construct(
        protected OltRegistry $oltRegistry,
        protected GenieAcsProvisioningService $genieAcs,
        protected OdpOccupancyService $odpOccupancy,
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
            $onu = $customerService->onu;
            if (!$onu) {
                return;
            }

            DB::transaction(function () use ($customerService, $onu) {
                $onu->provision_status = 'provisioning';
                $onu->saveQuietly();

                if ($onu->olt && $onu->serial_number && $onu->pon_port) {
                    try {
                        $driver = $this->oltRegistry->forOlt($onu->olt);
                        $driver->setOnuAdminStatus($onu, 'enable');
                        $package = $customerService->service?->servicePackage;
                        if ($package) {
                            $down = (int)($package->download_speed_mbps ?? $package->bandwidth_download ?? 100);
                            $up   = (int)($package->upload_speed_mbps ?? $package->bandwidth_upload ?? 20);
                            if ($down > 0 && $up > 0) {
                                $driver->setOnuBandwidthLimit($onu, $down, $up);
                            }
                        }
                        $onu->provision_status = 'provisioned';
                        $onu->provisioned_at = now();
                        $onu->saveQuietly();
                    } catch (Throwable $e) {
                        Log::warning('OLT provision failed on activation', [
                            'onu_id' => $onu->id,
                            'msg' => $e->getMessage(),
                        ]);
                        $onu->provision_status = 'olt_failed';
                        $onu->saveQuietly();
                    }
                }

                try {
                    $this->genieAcs->syncDeviceFromBilling($onu);
                } catch (Throwable $e) {
                    Log::warning('GenieACS sync failed on activation', [
                        'onu_id' => $onu->id,
                        'msg' => $e->getMessage(),
                    ]);
                }

                if ($onu->odp_id) {
                    try {
                        $this->odpOccupancy->recalculateOne($onu->odp);
                    } catch (Throwable) {
                    }
                }
            });

            Log::info('Fiber provisioning done for service activation', [
                'uuid' => $uuid,
                'onu_id' => $onu->id ?? null,
                'provision_status' => $onu->provision_status ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('FiberServiceProvisioningListener failed', [
                'event' => $event::class,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
