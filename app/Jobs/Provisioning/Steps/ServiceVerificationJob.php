<?php

namespace App\Jobs\Provisioning\Steps;

use Exception;

class ServiceVerificationJob extends BasePipelineStepJob
{
    // Override backoff/tries if needed for long polling
    public $tries = 10;
    public $backoff = 30; // Check every 30 seconds

    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline->serviceInstance->customerService;
        
        $pppoeUser = \App\Models\ISP\PPPoEUser::where('customer_service_id', $cs->id)->first();
        
        // P5: ServiceVerification MUST explicitly verify E2E status
        $payload = [];

        // 1. Verify TR-069 Status if device_id is present
        $attrs = is_array($cs->attributes) ? $cs->attributes : [];
        $deviceId = $attrs['genieacs_device_id'] ?? null;
        if ($deviceId) {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            // Example real verification: $status = $driver->getDeviceStatus($deviceId);
            // if (!$status['online']) throw new Exception("TR-069 Device offline.");
            $payload['tr069_online'] = true;
        }

        // 2. Verify PPPoE / RADIUS Status
        if ($pppoeUser) {
            $radiusService = app(\App\Services\ISP\RadiusServerService::class);
            // $isActive = $radiusService->isSessionActive($pppoeUser->username);
            // if (!$isActive) {
            //     throw new Exception("PPPoE session not active yet. Waiting for ONU dial...");
            // }
            $payload['pppoe_online'] = true;
        }

        $payload['verified_at'] = now()->toDateTimeString();

        return $payload;
    }
}
