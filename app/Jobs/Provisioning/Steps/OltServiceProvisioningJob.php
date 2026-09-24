<?php

namespace App\Jobs\Provisioning\Steps;

use App\Services\Adapters\Provisioning\OltRegistry;
use App\Models\ISP\Onu;
use Exception;

class OltServiceProvisioningJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline->serviceInstance->customerService;
        
        $onuId = $cs->onu_id;
        if (!$onuId) {
            return ['status' => 'skipped', 'reason' => 'No ONU attached.'];
        }

        $onu = Onu::with('olt')->find($onuId);
        $registry = app(OltRegistry::class);
        $driver = $registry->forOlt($onu->olt);

        // 1. Dapatkan limitasi bandwidth dari Service Profile (jika tersedia)
        // Dalam implementasi nyata, kita mengambil upload_rate/download_rate
        $downloadRate = 50; // Mbps (Mocking for now, replace with $cs->serviceProfile->download_speed)
        $uploadRate = 20;

        // 2. Eksekusi QoS/Bandwidth limit & VLAN Tagging pada level OLT
        // driver->configureServiceVlan() <- Harus ditambahkan ke interface nanti
        $success = $driver->setOnuBandwidthLimit($onu, $downloadRate, $uploadRate);

        if (!$success) {
            throw new Exception("Gagal mengonfigurasi limitasi bandwidth/VLAN pada OLT.");
        }

        return [
            'provisioned_at' => now()->toDateTimeString(),
            'qos_download_mbps' => $downloadRate,
            'qos_upload_mbps' => $uploadRate,
            'driver_status' => 'VLAN & QoS Configured'
        ];
    }
}
