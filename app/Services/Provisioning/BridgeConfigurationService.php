<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\ISP\ProvisioningProfile;

class BridgeConfigurationService
{
    private OnuConfigurationJobEngine $jobEngine;

    public function __construct(OnuConfigurationJobEngine $jobEngine)
    {
        $this->jobEngine = $jobEngine;
    }

    public function applyProfile(Onu $onu, ProvisioningProfile $profile, ?int $customerServiceId = null, ?int $requestedBy = null)
    {
        $bridgeConfig = [];

        if ($profile->vlan_id) {
            $bridgeConfig['vlan_id'] = (int)$profile->vlan_id;
        }

        if (is_array($profile->lan_mapping)) {
            $bridgeConfig['lan'] = array_map('strtolower', $profile->lan_mapping);
        }

        if (is_array($profile->wifi_mapping)) {
            $bridgeConfig['ssid'] = array_map('strtolower', $profile->wifi_mapping);
        }

        $desiredState = [
            'bridge' => $bridgeConfig
        ];

        return $this->jobEngine->dispatchProvisioningJob($onu, $desiredState, 'BRIDGE', $customerServiceId, $requestedBy);
    }
}
