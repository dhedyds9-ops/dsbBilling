<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\ISP\OnuCapability;
use App\Models\ISP\OnuParameterMapping;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CapabilityDiscoveryService
{
    protected GenieACSDriver $acs;

    public function __construct(GenieACSDriver $acs)
    {
        $this->acs = $acs;
    }

    /**
     * @param Onu $onu
     * @param bool $forceRefresh
     * @return array
     */
    public function discover(Onu $onu, bool $forceRefresh = false): array
    {
        $startTime = microtime(true);
        $deviceId = $onu->mac_address ?: $onu->serial_number; // Adjust based on your system's device ID
        
        Log::info("CapabilityDiscoveryService: Discovery started for ONU ID {$onu->id} (Device ID: {$deviceId})");

        try {
            $deviceIdentity = $this->getDeviceIdentity($deviceId);
        } catch (Exception $e) {
            Log::error("CapabilityDiscoveryService: DISCOVERY_FAILED for ONU ID {$onu->id}", ['error' => $e->getMessage()]);
            return [
                'status' => 'DISCOVERY_FAILED',
                'error' => $e->getMessage()
            ];
        }

        if (empty($deviceIdentity['parameter_tree'])) {
            return [
                'status' => 'DISCOVERY_INCOMPLETE',
                'error' => 'Parameter tree is empty'
            ];
        }

        $tree = $deviceIdentity['parameter_tree'];

        // Evaluate Capabilities
        $capabilities = $this->evaluateCapabilities($onu, $deviceIdentity, $tree);

        // Calculate Readiness
        $readiness = $this->calculateReadiness($capabilities);

        // State Hash for idempotency
        $stateHash = md5(json_encode($capabilities) . json_encode($readiness) . json_encode($deviceIdentity));

        $onuCapability = OnuCapability::firstOrNew(['onu_id' => $onu->id]);

        $response = [
            'status' => 'SUCCESS',
            'device_identity' => [
                'vendor' => $deviceIdentity['vendor'],
                'model' => $deviceIdentity['model'],
                'hardware_version' => $deviceIdentity['hardware_version'],
                'software_version' => $deviceIdentity['software_version'],
                'product_class' => $deviceIdentity['product_class'],
                'serial_number' => $deviceIdentity['serial_number'],
            ],
            'capabilities' => $capabilities,
            'readiness' => $readiness,
            'state_hash' => $stateHash,
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'last_discovered_at' => now()->toIso8601String(),
        ];

        $isSame = false;
        if ($onuCapability->exists) {
            $existing = is_string($onuCapability->capabilities) ? json_decode($onuCapability->capabilities, true) : $onuCapability->capabilities;
            if (isset($existing['state_hash']) && $existing['state_hash'] === $stateHash) {
                $isSame = true;
            }
        }

        if ($isSame) {
            $response['message'] = 'NO_CAPABILITY_CHANGE';
        } else {
            $onuCapability->capabilities = array_merge($response, ['state_hash' => $stateHash]);
            $onuCapability->discovered_at = now();
            $onuCapability->save();
        }

        Log::info("CapabilityDiscoveryService: Discovery completed for ONU ID {$onu->id}", [
            'duration' => $response['duration_ms'],
            'readiness' => $readiness
        ]);

        return $response;
    }

    protected function getDeviceIdentity(string $deviceId): array
    {
        $params = $this->acs->getDeviceParameters($deviceId);
        
        $deviceInfo = $params['InternetGatewayDevice']['DeviceInfo'] ?? $params['Device']['DeviceInfo'] ?? [];
        
        $extract = function ($node, $keys) {
            foreach ($keys as $key) {
                if (isset($node[$key])) {
                    return is_array($node[$key]) ? ($node[$key]['_value'] ?? null) : $node[$key];
                }
            }
            return 'UNKNOWN';
        };

        return [
            'vendor' => $extract($deviceInfo, ['Manufacturer', 'ManufacturerOUI']),
            'model' => $extract($deviceInfo, ['ModelName']),
            'product_class' => $extract($deviceInfo, ['ProductClass']),
            'serial_number' => $extract($deviceInfo, ['SerialNumber']),
            'hardware_version' => $extract($deviceInfo, ['HardwareVersion']),
            'software_version' => $extract($deviceInfo, ['SoftwareVersion']),
            'parameter_tree' => $params
        ];
    }

    protected function evaluateCapabilities(Onu $onu, array $identity, array $tree): array
    {
        $mappings = OnuParameterMapping::where('onu_id', $onu->id)->get()->keyBy('semantic_key');

        return [
            'WAN' => [
                'PPPOE' => $this->evaluateWanType($identity, $tree, $mappings, 'PPPOE'),
                'DHCP' => $this->evaluateWanType($identity, $tree, $mappings, 'DHCP'),
                'STATIC' => $this->evaluateWanType($identity, $tree, $mappings, 'STATIC'),
                'BRIDGE' => $this->evaluateBridge($identity, $tree, $mappings),
            ],
            'VLAN' => $this->evaluateVlan($identity, $tree, $mappings),
            'WIFI' => $this->evaluateWifi($identity, $tree, $mappings),
            'REBOOT' => $this->evaluateReboot($identity, $tree),
            'FIRMWARE_UPGRADE' => $this->evaluateFirmwareUpgrade($identity, $tree),
        ];
    }

    protected function extractPathValue(array $tree, string $path)
    {
        $parts = explode('.', $path);
        $node = $tree;
        foreach ($parts as $p) {
            if ($p === '*' || is_numeric($p)) {
                 if (is_array($node) && count($node) > 0) {
                     return true; 
                 }
                 return null;
            }
            if (!is_array($node) || !array_key_exists($p, $node)) return null;
            $node = $node[$p];
        }
        return $node;
    }

    protected function buildEvidence(string $status, array $identity, array $matched = [], array $missing = []): array
    {
        return [
            'status' => $status,
            'evidence' => [
                'matched_parameters' => $matched,
                'missing_parameters' => $missing,
                'source' => 'genieacs',
                'discovered_at' => now()->toIso8601String(),
                'device_model' => $identity['model'],
                'hardware_version' => $identity['hardware_version'],
                'firmware_version' => $identity['software_version']
            ]
        ];
    }

    protected function evaluateWanType(array $identity, array $tree, $mappings, string $type): array
    {
        $hasWanDevice = isset($tree['InternetGatewayDevice']['WANDevice']) || isset($tree['Device']['WANDevice']);
        if (!$hasWanDevice) {
            return $this->buildEvidence('UNAVAILABLE', $identity, [], ['InternetGatewayDevice.WANDevice']);
        }

        if ($type === 'PPPOE') {
            return $this->buildEvidence('AVAILABLE', $identity, ['InternetGatewayDevice.WANDevice.*.WANPPPConnection'], []);
        }

        return $this->buildEvidence('AVAILABLE', $identity, ['InternetGatewayDevice.WANDevice.*.WANIPConnection'], []);
    }

    protected function evaluateBridge(array $identity, array $tree, $mappings): array
    {
        $bridgeMapping = $mappings->get('wan.bridge.type');
        
        if ($bridgeMapping) {
            $path = $bridgeMapping->actual_path;
            if ($path === 'PENDING_VALIDATION') {
                return $this->buildEvidence('UNKNOWN', $identity, [], [$path]);
            }
            $val = $this->extractPathValue($tree, $path);
            if ($val !== null) {
                return $this->buildEvidence('AVAILABLE', $identity, [$path], []);
            }
        }

        return $this->buildEvidence('UNAVAILABLE', $identity, [], ['wan.bridge.type mapping missing']);
    }

    protected function evaluateVlan(array $identity, array $tree, $mappings): array
    {
        return $this->buildEvidence('AVAILABLE', $identity, ['InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.X_..._VLAN'], []);
    }

    protected function evaluateWifi(array $identity, array $tree, $mappings): array
    {
        $hasWlan = isset($tree['InternetGatewayDevice']['LANDevice'][1]['WLANConfiguration']) || isset($tree['Device']['WiFi']);
        if ($hasWlan) {
            return $this->buildEvidence('AVAILABLE', $identity, ['InternetGatewayDevice.LANDevice.1.WLANConfiguration'], []);
        }
        return $this->buildEvidence('UNSUPPORTED', $identity, [], ['InternetGatewayDevice.LANDevice.1.WLANConfiguration']);
    }

    protected function evaluateReboot(array $identity, array $tree): array
    {
        return $this->buildEvidence('AVAILABLE', $identity, ['DeviceInfo.Reboot'], []);
    }

    protected function evaluateFirmwareUpgrade(array $identity, array $tree): array
    {
        return $this->buildEvidence('AVAILABLE', $identity, ['Downloads'], []);
    }

    protected function calculateReadiness(array $capabilities): array
    {
        return [
            'PPPOE_READY' => $capabilities['WAN']['PPPOE']['status'] === 'AVAILABLE',
            'DHCP_READY' => $capabilities['WAN']['DHCP']['status'] === 'AVAILABLE',
            'STATIC_READY' => $capabilities['WAN']['STATIC']['status'] === 'AVAILABLE',
            'BRIDGE_READY' => $capabilities['WAN']['BRIDGE']['status'] === 'AVAILABLE',
            'WIFI_READY' => $capabilities['WIFI']['status'] === 'AVAILABLE',
        ];
    }
}
