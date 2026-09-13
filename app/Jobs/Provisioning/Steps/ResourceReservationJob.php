<?php

namespace App\Jobs\Provisioning\Steps;

use App\Models\Customer\CustomerService;
use Exception;
use Illuminate\Support\Facades\Cache;

class ResourceReservationJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $serviceInstance = $this->step->provisionPipeline->serviceInstance;
        $customerService = $serviceInstance->customerService;
        
        $onuId = $customerService->onu_id;
        if (!$onuId) {
            // Hotspot or non-FTTH, no reservation needed
            return ['status' => 'skipped', 'reason' => 'No ONU attached'];
        }

        $lockKey = "provisioning_lock_onu_{$onuId}";
        
        // Attempt to get a distributed lock for 10 minutes
        if (!Cache::lock($lockKey, 600)->get()) {
            throw new Exception("ONU sedang diprovisioning oleh proses lain.");
        }

        return [
            'reserved_at' => now()->toDateTimeString(),
            'lock_key' => $lockKey
        ];
    }
}
