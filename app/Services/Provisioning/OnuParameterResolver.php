<?php
namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\ISP\OnuParameterMapping;
use App\Services\Adapters\Monitoring\GenieACSDriver;

class OnuParameterResolver
{
    private GenieACSDriver $acsDriver;

    public function __construct(GenieACSDriver $acsDriver)
    {
        $this->acsDriver = $acsDriver;
    }

    public function resolve(Onu $onu, string $semanticKey, array $params = null): ?string
    {
        $deviceId = $onu->genieacs_device_id;
        if ($params === null) {
            try {
                $params = $this->acsDriver->getDeviceParameters($deviceId);
            } catch (\Exception $e) {
                return null;
            }
        }

        // We don't use cache for WAN PPPoE because instance numbers can be dynamically added.
        // Or we could flush cache if not found. Let's just resolve dynamically.
        $path = $this->discoverPath($params, $semanticKey);

        if ($path) {
            OnuParameterMapping::updateOrCreate([
                'onu_id' => $onu->id,
                'semantic_key' => $semanticKey,
            ], [
                'actual_path' => $path
            ]);
        }

        return $path;
    }

    private function discoverPath(array $params, string $semanticKey): ?string
    {
        if (preg_match('/^wifi\.ssid\.(\d+)\.name$/', $semanticKey, $matches)) {
            $idx = $matches[1];
            $candidates = [
                "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$idx}.SSID",
                "Device.WiFi.SSID.{$idx}.SSID",
            ];
            return $this->findFirstExistingPath($params, $candidates);
        }

        if (preg_match('/^wifi\.ssid\.(\d+)\.password$/', $semanticKey, $matches)) {
            $idx = $matches[1];
            $candidates = [
                "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$idx}.PreSharedKey.1.KeyPassphrase",
                "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$idx}.KeyPassphrase",
                "Device.WiFi.AccessPoint.{$idx}.Security.KeyPassphrase",
            ];
            return $this->findFirstExistingPath($params, $candidates);
        }
        
        if (preg_match('/^wifi\.ssid\.(\d+)\.enabled$/', $semanticKey, $matches)) {
            $idx = $matches[1];
            $candidates = [
                "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$idx}.Enable",
                "Device.WiFi.Radio.{$idx}.Enable",
            ];
            return $this->findFirstExistingPath($params, $candidates);
        }

        // WAN Mapping
        if (preg_match('/^wan\.(\d+)\.(mode|pppoe\.username|pppoe\.password|vlan_id)$/', $semanticKey, $matches)) {
            $wanIndex = $matches[1];
            $prop = $matches[2];
            
            $basePath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.{$wanIndex}";
            
            // Find the first WANPPPConnection instance
            $pppInstances = $this->extractInstances($params, "{$basePath}.WANPPPConnection");
            if (!empty($pppInstances)) {
                $pppIdx = $pppInstances[0];
                if ($prop === 'vlan_id') {
                    return $this->findFirstExistingPath($params, [
                        "{$basePath}.WANPPPConnection.{$pppIdx}.VLANID", // FiberHome & standard
                        "{$basePath}.WANPPPConnection.{$pppIdx}.X_ZTE-COM_VLANID", // ZTE
                        "{$basePath}.X_ZTE-COM_VLANID" // Older ZTE
                    ]);
                }
                if ($prop === 'mode') return "{$basePath}.WANPPPConnection.{$pppIdx}.ConnectionType";
                if ($prop === 'pppoe.username') return "{$basePath}.WANPPPConnection.{$pppIdx}.Username";
                if ($prop === 'pppoe.password') return "{$basePath}.WANPPPConnection.{$pppIdx}.Password";
            }
        }

        return null;
    }

    public function resolveValue(string $semanticKey, $value)
    {
        if (preg_match('/^wan\.(\d+)\.mode$/', $semanticKey) && $value === 'pppoe') {
            return 'IP_Routed'; // ZTE expects IP_Routed for PPPoE
        }
        return is_bool($value) ? ($value ? '1' : '0') : $value;
    }

    private function findFirstExistingPath(array $params, array $candidates): ?string
    {
        foreach ($candidates as $path) {
            if ($this->pathExists($params, $path)) {
                return $path;
            }
        }
        return null;
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

    private function extractInstances(array $params, string $path): array
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) {
                return [];
            }
            $node = $node[$p];
        }
        
        $instances = [];
        if (is_array($node)) {
            foreach (array_keys($node) as $key) {
                if (is_numeric($key)) {
                    $instances[] = $key;
                }
            }
        }
        return $instances;
    }
}
