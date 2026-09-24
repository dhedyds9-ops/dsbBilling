<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;

class WifiConfigurationService
{
    private OnuConfigurationJobEngine $jobEngine;

    public function __construct(OnuConfigurationJobEngine $jobEngine)
    {
        $this->jobEngine = $jobEngine;
    }

    public function configureWifi(Onu $onu, array $config, ?int $customerServiceId = null, ?int $requestedBy = null)
    {
        $desiredState = ['wifi' => ['ssid' => []]];

        foreach ($config as $radioIndex => $settings) {
            $ssidConfig = [];
            if (isset($settings['ssid'])) {
                $ssidConfig['name'] = $settings['ssid'];
            }
            if (isset($settings['password'])) {
                $ssidConfig['password'] = $settings['password'];
            }
            if (isset($settings['security'])) {
                $ssidConfig['security'] = $settings['security'];
            }
            if (isset($settings['enable'])) {
                $ssidConfig['enabled'] = (bool)$settings['enable'];
            }
            
            if (!empty($ssidConfig)) {
                $desiredState['wifi']['ssid'][$radioIndex] = $ssidConfig;
            }
        }

        if (empty($desiredState['wifi']['ssid'])) {
            throw new \Exception("No valid WiFi configuration provided.");
        }

        return $this->jobEngine->dispatchProvisioningJob($onu, $desiredState, 'WIFI', $customerServiceId, $requestedBy);
    }
}
