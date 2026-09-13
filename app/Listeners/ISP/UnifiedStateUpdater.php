<?php

namespace App\Listeners\ISP;

use App\Events\ISP\OnuStatusChanged;
use App\Events\ISP\ServiceStatusChanged;
use App\Events\ISP\Tr069StatusChanged;
use App\Events\ISP\DeviceDiagnosisChanged;
use App\Models\Customer\CustomerService;
use App\Models\ACS\ACSDevice;
use App\Models\ISP\Onu;
use App\Services\ISP\UnifiedDeviceStateEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class UnifiedStateUpdater implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(mixed $event): void
    {
        if ($event instanceof OnuStatusChanged) {
            $this->handleOnuStatusChanged($event);
        } elseif ($event instanceof Tr069StatusChanged) {
            $this->handleTr069StatusChanged($event);
        } elseif ($event instanceof ServiceStatusChanged) {
            $this->handleServiceStatusChanged($event);
        }
    }

    protected function handleOnuStatusChanged(OnuStatusChanged $event): void
    {
        $onu = Onu::find($event->onuId);
        if (!$onu) return;

        // Find customer service
        $cs = CustomerService::where('onu_id', $onu->id)->first();
        if (!$cs) return;

        // Idempotency / Ordering Check
        if ($cs->last_state_change_at && $event->occurredAt->lt($cs->last_state_change_at)) {
            Log::info("UnifiedStateUpdater: Ignored stale OnuStatusChanged event {$event->eventId} for CS {$cs->id}");
            return;
        }

        app(UnifiedDeviceStateEngine::class)->evaluateAndSave($cs);
    }

    protected function handleTr069StatusChanged(Tr069StatusChanged $event): void
    {
        $acsDevice = \App\Models\ACS\ACSDevice::where('serial_number', $event->genieacsDeviceId)
            ->orWhere('mac_address', $event->genieacsDeviceId)
            ->orWhere('uuid', $event->genieacsDeviceId)
            ->first();

        if (!$acsDevice) return;
        
        $cs = CustomerService::where('attributes->genieacs_device_id', $event->genieacsDeviceId)
            ->orWhereHas('onu', function($q) use ($event) {
                $q->where('serial_number', $event->genieacsDeviceId)
                  ->orWhere('mac_address', $event->genieacsDeviceId);
            })->first();

        if (!$cs) return;

        if ($cs->last_state_change_at && $event->occurredAt->lt($cs->last_state_change_at)) {
            Log::info("UnifiedStateUpdater: Ignored stale Tr069StatusChanged event {$event->eventId} for CS {$cs->id}");
            return;
        }

        app(UnifiedDeviceStateEngine::class)->evaluateAndSave($cs);
    }

    protected function handleServiceStatusChanged(ServiceStatusChanged $event): void
    {
        $cs = CustomerService::find($event->customerServiceId);
        if (!$cs) return;

        if ($cs->last_state_change_at && $event->occurredAt->lt($cs->last_state_change_at)) {
            Log::info("UnifiedStateUpdater: Ignored stale ServiceStatusChanged event {$event->eventId} for CS {$cs->id}");
            return;
        }

        app(UnifiedDeviceStateEngine::class)->evaluateAndSave($cs);
    }
}
