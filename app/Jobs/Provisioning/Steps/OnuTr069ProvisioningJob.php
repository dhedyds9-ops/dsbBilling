<?php

namespace App\Jobs\Provisioning\Steps;

use App\Models\ISP\Onu;
use App\Services\ISP\GenieAcsProvisioningService;
use Exception;

class OnuTr069ProvisioningJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline?->serviceInstance?->customerService;
        if (!$cs) {
            return ['status' => 'skipped', 'reason' => 'No CustomerService linked to pipeline.'];
        }

        $onu = $cs->onu ?? ($cs->onu_id ? Onu::withoutGlobalScopes()->find($cs->onu_id) : null);
        if (!$onu) {
            return ['status' => 'skipped', 'reason' => 'No ONU attached. Configuration will rely on OMCI/RADIUS.'];
        }

        $provisioningService = app(GenieAcsProvisioningService::class);
        $result = $provisioningService->provisionOnt($onu, [
            'customer_service' => $cs,
        ]);

        if ($result['status'] === 'failed') {
            throw new Exception($result['error'] ?? 'GenieACS NBI provisioning failed.');
        }

        return [
            'tr069_status' => $result['status'] === 'success' ? 'completed' : $result['status'],
            'device_id' => $result['device_id'] ?? $onu->serial_number,
            'message' => $result['message'] ?? null,
        ];
    }
}
