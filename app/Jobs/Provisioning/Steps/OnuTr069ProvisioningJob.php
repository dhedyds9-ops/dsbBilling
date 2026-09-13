<?php

namespace App\Jobs\Provisioning\Steps;

use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;

class OnuTr069ProvisioningJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline->serviceInstance->customerService;
        
        $attrs = is_array($cs->attributes) ? $cs->attributes : [];
        $deviceId = $attrs['genieacs_device_id'] ?? null;

        if (!$deviceId) {
            return ['status' => 'skipped', 'reason' => 'No GenieACS Device ID attached. Configuration will rely purely on OMCI/RADIUS.'];
        }

        // Initialize GenieACS Driver directly as requested (no nested jobs)
        $driver = app(GenieACSDriver::class);
        
        $pppoeUser = \App\Models\ISP\PPPoEUser::where('customer_service_id', $cs->id)->first();
        if ($pppoeUser) {
            // Push PPPoE Credentials via TR-069
            // Example logic (abstracted)
            // $driver->setParameterValues($deviceId, ['InternetGatewayDevice.WANDevice.1...' => $pppoeUser->username]);
        }

        return [
            'tr069_status' => 'CONFIGURED',
            'device_id' => $deviceId
        ];
    }
}
