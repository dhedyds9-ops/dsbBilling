<?php

namespace App\Jobs\Provisioning\Steps;

use App\Models\Customer\CustomerService;
use App\Models\Provisioning\NetworkProfile;
use App\Models\ISP\ServiceProfile;
use Exception;

class CustomerServiceValidationJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $serviceInstance = $this->step->provisionPipeline->serviceInstance;
        $customerService = $serviceInstance->customerService;

        if (!$customerService) {
            throw new Exception("CustomerService tidak ditemukan untuk Instance {$serviceInstance->id}");
        }

        // Validate Service Profile
        $serviceProfile = $customerService->serviceProfile;
        if (!$serviceProfile) {
            throw new Exception("ServiceProfile tidak ditemukan untuk layanan ini.");
        }

        // Validate Network Profile
        $networkProfile = \App\Models\Provisioning\NetworkProfile::find($customerService->network_profile_id);
        if (!$networkProfile) {
            throw new Exception("NetworkProfile tidak ditentukan. Harap pilih Network Profile terlebih dahulu.");
        }

        // Ensure ONU is selected if this is an FTTH profile
        if ($networkProfile->type === 'pppoe' || $networkProfile->type === 'combined') {
            if (empty($customerService->onu_id)) {
                throw new Exception("ONU ID tidak valid. FTTH Provisioning membutuhkan perangkat ONU.");
            }
        }

        return [
            'validated_at' => now()->toDateTimeString(),
            'service_type' => $serviceProfile->service_type,
            'network_type' => $networkProfile->type,
        ];
    }
}
