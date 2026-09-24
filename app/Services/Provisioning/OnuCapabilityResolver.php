<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\ISP\OnuCapability;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Support\Arr;

class OnuCapabilityResolver
{
    private GenieACSDriver $acsDriver;

    public function __construct(GenieACSDriver $acsDriver)
    {
        $this->acsDriver = $acsDriver;
    }

    public function resolve(Onu $onu): OnuCapability
    {
        $deviceId = $onu->genieacs_device_id;
        
        try {
            $params = $this->acsDriver->getDeviceParameters($deviceId);
        } catch (\Exception $e) {
            $params = [];
        }

        $capabilities = [
            'wifi_2_4ghz' => $this->detectWifiBand($params, 1),
            'wifi_5ghz' => $this->detectWifiBand($params, 2) || $this->hasExplicit5GhzPath($params),
            'lan_ports' => $this->detectLanPorts($params),
            'wan_pppoe' => true,
            'wan_dhcp' => true,
            'wan_static' => true,
            'wan_bridge' => true,
            'vlan' => true,
            'reboot' => true,
            'factory_reset' => true,
        ];

        return OnuCapability::updateOrCreate(
            ['onu_id' => $onu->id],
            [
                'capabilities' => $capabilities,
                'discovered_at' => now()
            ]
        );
    }

    private function pathExists(array $params, string $path): bool
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) {
                return false;
            }
            $node = $node[$p];
        }
        return true;
    }

    private function detectWifiBand(array $params, int $radioIndex): bool
    {
        if ($this->pathExists($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$radioIndex}")) {
            return true;
        }
        if ($this->pathExists($params, "Device.WiFi.Radio.{$radioIndex}")) {
            return true;
        }
        return false;
    }

    private function hasExplicit5GhzPath(array $params): bool
    {
        return $this->pathExists($params, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5G') ||
               $this->pathExists($params, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5') ||
               $this->pathExists($params, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.9');
    }

    private function detectLanPorts(array $params): int
    {
        $count = 0;
        $lanPath = 'InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig';
        if ($this->pathExists($params, $lanPath)) {
            $parts = explode('.', $lanPath);
            $node = $params;
            foreach ($parts as $p) {
                $node = $node[$p] ?? [];
            }
            foreach ($node as $key => $value) {
                if (is_numeric($key) && $key > 0) {
                    $count++;
                }
            }
        }
        
        $tr181Path = 'Device.Ethernet.Interface';
        if ($count === 0 && $this->pathExists($params, $tr181Path)) {
            $parts = explode('.', $tr181Path);
            $node = $params;
            foreach ($parts as $p) {
                $node = $node[$p] ?? [];
            }
            foreach ($node as $key => $value) {
                if (is_numeric($key) && $key > 0) {
                    $count++;
                }
            }
        }

        return $count > 0 ? $count : 4;
    }
}
