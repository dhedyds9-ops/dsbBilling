<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;

class WanConfigurationService
{
    private OnuConfigurationJobEngine $jobEngine;

    public function __construct(OnuConfigurationJobEngine $jobEngine)
    {
        $this->jobEngine = $jobEngine;
    }

    public function configureWan(Onu $onu, int $wanIndex, string $mode, array $settings, ?int $customerServiceId = null, ?int $requestedBy = null)
    {
        $wanConfig = [
            'mode' => strtolower($mode),
        ];

        if (isset($settings['vlan'])) {
            $wanConfig['vlan_id'] = (int)$settings['vlan'];
        }

        if (strtolower($mode) === 'pppoe') {
            $wanConfig['pppoe'] = [];
            if (isset($settings['username'])) {
                $wanConfig['pppoe']['username'] = $settings['username'];
            }
            if (isset($settings['password'])) {
                $wanConfig['pppoe']['password'] = $settings['password'];
            }
        } elseif (strtolower($mode) === 'static') {
            $wanConfig['static'] = [];
            if (isset($settings['ip'])) {
                $wanConfig['static']['ip'] = $settings['ip'];
            }
            if (isset($settings['gateway'])) {
                $wanConfig['static']['gateway'] = $settings['gateway'];
            }
            if (isset($settings['subnet'])) {
                $wanConfig['static']['subnet'] = $settings['subnet'];
            }
        }

        $desiredState = [
            'wan' => [
                $wanIndex => $wanConfig
            ]
        ];

        return $this->jobEngine->dispatchProvisioningJob($onu, $desiredState, 'WAN', $customerServiceId, $requestedBy);
    }
}
