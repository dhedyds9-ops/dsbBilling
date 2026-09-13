<?php

namespace App\Jobs\Provisioning\Steps;

use App\Services\ISP\PPPoEService;

class RadiusProvisioningJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline->serviceInstance->customerService;
        
        $pppoeUser = \App\Models\ISP\PPPoEUser::where('customer_service_id', $cs->id)->first();
        
        if (!$pppoeUser) {
            return ['status' => 'skipped', 'reason' => 'Not a PPPoE service'];
        }

        // Call the RADIUS component to create radcheck/radreply
        $pppoeService = app(PPPoEService::class);
        $pppoeService->activatePPPoEUser($pppoeUser->id, $cs->created_by);

        return [
            'radius_status' => 'CREATED',
            'username' => $pppoeUser->username
        ];
    }
}
