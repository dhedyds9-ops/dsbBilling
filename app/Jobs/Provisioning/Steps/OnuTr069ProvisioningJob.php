<?php

namespace App\Jobs\Provisioning\Steps;

use App\Services\Provisioning\ProvisioningOrchestrator;
use Exception;

class OnuTr069ProvisioningJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline?->serviceInstance?->customerService;
        if (!$cs) {
            return ['status' => 'skipped', 'reason' => 'No CustomerService linked to pipeline.'];
        }

        // SSOT provisioning ACS/TR-069 (WAN PPPoE + Wi-Fi) ada di orkestrator.
        $result = app(ProvisioningOrchestrator::class)->provisionAcs($cs);

        if (($result['status'] ?? null) === 'failed') {
            throw new Exception($result['error'] ?? 'GenieACS NBI provisioning failed.');
        }

        return [
            'tr069_status' => ($result['status'] ?? null) === 'success' ? 'completed' : ($result['status'] ?? null),
            'device_id' => $result['device_id'] ?? null,
            'message' => $result['message'] ?? null,
        ];
    }
}
