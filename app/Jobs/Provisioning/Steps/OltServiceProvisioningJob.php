<?php

namespace App\Jobs\Provisioning\Steps;

use App\Services\Provisioning\ProvisioningOrchestrator;
use Exception;

class OltServiceProvisioningJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline->serviceInstance->customerService;
        if (!$cs) {
            return ['status' => 'skipped', 'reason' => 'No CustomerService linked to pipeline.'];
        }

        // SSOT provisioning OLT (admin status + QoS dari ServiceProfile) ada di orkestrator.
        $result = app(ProvisioningOrchestrator::class)->provisionOltQos($cs);

        if (($result['status'] ?? null) === 'failed') {
            throw new Exception($result['error'] ?? 'Gagal mengonfigurasi limitasi bandwidth/VLAN pada OLT.');
        }

        return [
            'status' => $result['status'],
            'provisioned_at' => now()->toDateTimeString(),
            'qos_download_mbps' => $result['download_mbps'] ?? null,
            'qos_upload_mbps' => $result['upload_mbps'] ?? null,
            'reason' => $result['reason'] ?? null,
        ];
    }
}
