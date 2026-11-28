<?php

namespace App\Services\Adapters\Monitoring;

use App\Models\ISP\Onu;
use App\Models\ISP\Vendor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Exception;

class GenieACSDriver
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private int $timeout;

        public function __construct()
    {
        $settings = [];
        try {
            $settings = \App\Models\Setting::getValue('connection.acs', []);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }
        $this->baseUrl = rtrim($settings['base_url'] ?? config('genieacs.base_url', 'http://localhost:7557'), '/');
        $this->username = $settings['connection_request_username'] ?? $settings['username'] ?? config('genieacs.username', 'admin');
        $this->password = $settings['connection_request_password'] ?? $settings['password'] ?? config('genieacs.password', 'admin');
        $this->timeout = (int)($settings['timeout'] ?? config('genieacs.timeout', 10));
    }

    private function getWifiParameterPath(string $vendor = 'default', string $parameter = 'wpa_passphrase'): string
    {
        $vendors = config('acs_vendors', []);
        $vendorKey = strtolower(trim($vendor));
        if (!isset($vendors[$vendorKey])) {
            $fuzzy = collect(array_keys($vendors))->first(fn ($k) => str_contains($vendorKey, $k) || str_contains($k, $vendorKey));
            $vendorKey = $fuzzy ?? 'default';
        }
        return $vendors[$vendorKey][$parameter] ?? $vendors['default'][$parameter] ?? '';
    }

    public function listDevices(array $query = [], int $limit = 100, int $skip = 0): array
    {
        try {
            $effectiveTimeout = max(15, (int) ($this->timeout ?: 15));
            $params = array_merge(['limit' => $limit, 'skip' => $skip], $query);
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($effectiveTimeout)
                ->asJson()
                ->get("{$this->baseUrl}/devices", $params);
                
            if ($response->successful()) {
                return $response->json() ?? [];
            }
            throw new Exception("GenieACS listDevices failed: HTTP " . $response->status());
        } catch (ConnectionException|RequestException $e) {
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

        public function getDeviceParameters(string $deviceId): array
    {
        $cleanId = trim($deviceId);
        if (empty($cleanId)) {
            return [];
        }

        $parts = explode('-', $cleanId);
        $candidateSn = end($parts);

        try {
            $cleanUpper = strtoupper($cleanId);
            $orConditions = [
                ['_id' => $cleanId],
                ['_deviceId._SerialNumber' => $cleanId],
                ['_deviceId._MACAddress' => $cleanId],
            ];
            if ($cleanUpper !== $cleanId) {
                $orConditions[] = ['_id' => $cleanUpper];
                $orConditions[] = ['_deviceId._SerialNumber' => $cleanUpper];
                $orConditions[] = ['_deviceId._MACAddress' => $cleanUpper];
            }
            if (!empty($candidateSn) && $candidateSn !== $cleanId) {
                $orConditions[] = ['_deviceId._SerialNumber' => $candidateSn];
                $candUpper = strtoupper($candidateSn);
                if ($candUpper !== $candidateSn) {
                    $orConditions[] = ['_deviceId._SerialNumber' => $candUpper];
                }
            }

            $queryObj = [
                '$or' => $orConditions
            ];
            $query = urlencode(json_encode($queryObj));
            $projectionFields = [
                '_id',
                '_deviceId',
                '_lastInform',
                'VirtualParameters',
                'VirtualParameters.rxPower',
                'VirtualParameters.RXPower',
                'VirtualParameters.redaman',
                'VirtualParameters.Redaman',
                'VirtualParameters.opticalRxPower',
                'VirtualParameters.optical_rx',
                'VirtualParameters.rx_power',
                'VirtualParameters.RxPower',
                'VirtualParameters.txPower',
                'VirtualParameters.TXPower',
                'VirtualParameters.opticalTxPower',
                'VirtualParameters.optical_tx',
                'VirtualParameters.tx_power',
                'VirtualParameters.TxPower',
                'VirtualParameters.OpticalInfo',
                'VirtualParameters.PonMac',
                'VirtualParameters.ponMac',
                'VirtualParameters.pppoeUsername',
                'VirtualParameters.pppoePassword',
                'VirtualParameters.pppoeIP',
                'VirtualParameters.WlanSSID',
                'VirtualParameters.WlanPassword',
                'VirtualParameters.superAdmin',
                'VirtualParameters.superPassword',
                'InternetGatewayDevice',
                'Device'
            ];
            $projection = urlencode(implode(',', $projectionFields));

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/devices?query={$query}&projection={$projection}");
                
            if ($response->successful()) {
                $data = $response->json();
                return $data[0] ?? [];
            }
            if ($response->status() === 404) {
                return [];
            }
            throw new Exception("Failed to get device parameters: HTTP " . $response->status());
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
                return [];
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function resolveDeviceId(string $deviceId): string
    {
        $cleanId = trim($deviceId);
        if (empty($cleanId)) {
            return $deviceId;
        }

        if (substr_count($cleanId, '-') >= 2) {
            return $cleanId;
        }

        // 1. Cek langsung ke database ACSDevice (paling cepat dan akurat untuk menyelesaikan SN -> UUID GenieACS)
        try {
            $cleanUpper = strtoupper($cleanId);
            $dbUuid = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->where(function ($q) use ($cleanUpper) {
                    $q->where('serial_number', $cleanUpper)
                      ->orWhere('mac_address', $cleanUpper)
                      ->orWhere('uuid', $cleanUpper);
                })->value('uuid');
            if (!empty($dbUuid) && substr_count($dbUuid, '-') >= 2) {
                return $dbUuid;
            }
        } catch (\Throwable) {}

        // 2. Fallback query langsung ke GenieACS NBI
        try {
            $params = $this->getDeviceParameters($cleanId);
            if (!empty($params['_id'])) {
                return (string)$params['_id'];
            }
        } catch (\Throwable) {}

        return $cleanId;
    }


    public function getDeviceSignal(string $deviceId): array
    {
        $params = $this->getDeviceParameters($deviceId);
        $rx = $tx = $snr = $temp = null;

        $extract = function (string $path) use ($params) {
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) {
                    return null;
                }
                $node = $node[$p];
            }
            if (is_array($node) && isset($node['_value'])) {
                return $node['_value'];
            }
            if (is_scalar($node)) {
                return $node;
            }
            return null;
        };

        $flat = \App\Services\ACS\AcsTelemetryParser::flatten($params);
        $optical = \App\Services\ACS\AcsTelemetryParser::parseOptical($flat);
        $identity = \App\Services\ACS\AcsTelemetryParser::parseDeviceIdentity($flat, $params);

        $lastInform = $params['_lastInform'] ?? null;
        $online = $lastInform && abs(now()->diffInMinutes(Carbon::parse($lastInform))) < 5;

        return [
            'device_id' => $deviceId,
            'online' => $online,
            'last_inform' => $lastInform,
            'rx_power_dbm' => $optical['rx_power'],
            'tx_power_dbm' => $optical['tx_power'],
            'snr_db' => $flat['Device.Optical.1.XPON.SNRMarginDownstream'] ?? null,
            'temperature' => $optical['temperature'] !== 'Tidak Didukung' ? $optical['temperature'] : null,
            'firmware_version' => $identity['software_version'],
            'hardware_version' => $identity['hardware_version'],
            'manufacturer' => $identity['manufacturer'],
            'raw' => $params,
        ];
    }

    public function getWifiCredentials(string $deviceId, string $vendor = 'default', int $wlanIndex = 1, ?string $pppoeUsername = null, bool $bypassCustomerBinding = false): array
    {
        $params = $this->getDeviceParameters($deviceId);
        if (empty($params)) {
            return [
                'ssid' => null,
                'password' => null,
                'security_mode' => 'WPA_WPA2',
                'is_enabled' => true,
                'real_device_id' => null,
                'all_ssids' => [],
                'wlan_index' => $wlanIndex,
                'allowed_wlan_indices' => [],
                'bridge_wlan_indices' => [],
            ];
        }

        // Resolve WAN-to-WLAN bindings: distinguish Customer PPPoE from Bridge/Hotspot SSIDs
        $bindingInfo = $this->resolveWanWlanBindings($params, $pppoeUsername);
        $allowedWlanIndices = $bindingInfo['allowed_wlan_indices'] ?? [];
        $bridgeWlanIndices = $bindingInfo['bridge_wlan_indices'] ?? [];

        // If requested $wlanIndex is explicitly bound to a Bridge WAN or not in PPPoE allowed list,
        // automatically redirect to the primary allowed PPPoE slot (e.g. slot 2 instead of slot 1).
        // HANYA berlaku untuk portal pelanggan mandiri (jika $bypassCustomerBinding = false).
        // Untuk Teknisi Lapangan / Mobile NOC, jangan redirect agar teknisi bebas melihat slot 1, 2, 3, 4!
        if (!$bypassCustomerBinding && !empty($allowedWlanIndices) && (!in_array($wlanIndex, $allowedWlanIndices, true) || in_array($wlanIndex, $bridgeWlanIndices, true))) {
            $wlanIndex = reset($allowedWlanIndices);
        }
        
        $extract = function (string $path) use ($params) {
            // 1. Try Laravel data_get (supports both dot notation and arrays)
            $val = data_get($params, $path . '._value') ?? data_get($params, $path);
            if ($val !== null && is_scalar($val) && $val !== '') {
                return (string)$val;
            }

            // 2. Try nested array traversal
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) {
                    $node = null;
                    break;
                }
                $node = $node[$p];
            }
            if ($node !== null) {
                if (is_array($node) && isset($node['_value']) && is_scalar($node['_value'])) {
                    return (string)$node['_value'];
                }
                if (is_scalar($node)) {
                    return (string)$node;
                }
            }

            // 3. Try direct key in flat params map
            if (isset($params[$path])) {
                $pNode = $params[$path];
                if (is_array($pNode) && isset($pNode['_value']) && is_scalar($pNode['_value'])) {
                    return (string)$pNode['_value'];
                }
                if (is_scalar($pNode)) {
                    return (string)$pNode;
                }
            }

            return null;
        };

        // Vendor-configured paths adjusted for wlanIndex
        $rawVendorSsidPath = $this->getWifiParameterPath($vendor, 'ssid');
        $rawVendorPassPath = $this->getWifiParameterPath($vendor, 'wpa_passphrase');
        $rawVendorSecPath = $this->getWifiParameterPath($vendor, 'security_mode');

        $vendorSsidPath = $this->wlanIndexedPath($rawVendorSsidPath, $wlanIndex);
        $vendorPassPath = $this->wlanIndexedPath($rawVendorPassPath, $wlanIndex);
        $vendorSecPath = $this->wlanIndexedPath($rawVendorSecPath, $wlanIndex);

        $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.";
        $tr181SsidPrefix = "Device.WiFi.SSID.{$wlanIndex}.";
        $tr181ApPrefix = "Device.WiFi.AccessPoint.{$wlanIndex}.";

        $ssidCandidates = array_unique(array_filter([
            $vendorSsidPath,
            $prefix . 'SSID',
            $tr181SsidPrefix . 'SSID',
            $tr181ApPrefix . 'SSID',
            $wlanIndex === 1 ? 'VirtualParameters.wifiSsid' : null,
            $wlanIndex === 1 ? 'VirtualParameters.WlanSSID' : null,
        ]));

        $passCandidates = array_unique(array_filter([
            $vendorPassPath,
            $prefix . 'PreSharedKey.1.PreSharedKey',
            $prefix . 'PreSharedKey.1.KeyPassphrase',
            $prefix . 'KeyPassphrase',
            $prefix . 'X_ZTE-COM_WPAKey',
            $prefix . 'X_HW_KeyPassphrase',
            $tr181ApPrefix . 'Security.KeyPassphrase',
            $tr181ApPrefix . 'Security.PreSharedKey',
            $wlanIndex === 1 ? 'VirtualParameters.wifiPassword' : null,
            $wlanIndex === 1 ? 'VirtualParameters.wifiPassphrase' : null,
            $wlanIndex === 1 ? 'VirtualParameters.WlanPassword' : null,
        ]));

        $ssid = null;
        foreach ($ssidCandidates as $path) {
            $val = $extract($path);
            if (!empty($val)) {
                $ssid = $val;
                break;
            }
        }

        $password = null;
        foreach ($passCandidates as $path) {
            $val = $extract($path);
            if (!empty($val) && $val !== '******') {
                $password = $val;
                break;
            }
        }

        // Dynamic WLAN traversal if SSID or Password is still missing
        if (empty($ssid) || empty($password)) {
            $wlanNode = $params['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration'][$wlanIndex] 
                ?? $params['Device']['WiFi']['SSID'][$wlanIndex] 
                ?? $params['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration'][(string)$wlanIndex] 
                ?? null;

            if (is_array($wlanNode)) {
                if (empty($ssid)) {
                    $s = $wlanNode['SSID']['_value'] ?? $wlanNode['SSID'] ?? null;
                    if (!empty($s) && is_scalar($s)) {
                        $ssid = (string)$s;
                    }
                }
                if (empty($password)) {
                    $p = $wlanNode['PreSharedKey']['1']['KeyPassphrase']['_value']
                        ?? $wlanNode['PreSharedKey']['1']['PreSharedKey']['_value']
                        ?? $wlanNode['KeyPassphrase']['_value']
                        ?? $wlanNode['PreSharedKey']['1']['KeyPassphrase']
                        ?? $wlanNode['KeyPassphrase']
                        ?? null;
                    if (!empty($p) && is_scalar($p) && $p !== '******') {
                        $password = (string)$p;
                    }
                }
            }
        }

        // Extract Security Mode (BeaconType / ModeEnabled)
        $beaconCandidates = array_unique(array_filter([
            $vendorSecPath,
            $prefix . 'BeaconType',
            $prefix . 'BasicEncryptionModes',
            $prefix . 'IEEE11iEncryptionModes',
            $tr181ApPrefix . 'Security.ModeEnabled',
        ]));
        $rawBeacon = null;
        foreach ($beaconCandidates as $path) {
            $val = $extract($path);
            if (!empty($val)) {
                $rawBeacon = (string)$val;
                break;
            }
        }
        $securityMode = 'WPA_WPA2';
        if ($rawBeacon !== null) {
            $uBeacon = strtoupper($rawBeacon);
            if (in_array($uBeacon, ['NONE', 'BASIC', 'OPEN', 'DISABLED', '0'], true)) {
                $securityMode = 'OPEN';
            } elseif (str_contains($uBeacon, 'WPA2') || $uBeacon === '11I' || str_contains($uBeacon, 'AES')) {
                $securityMode = (str_contains($uBeacon, 'WPAAND11I') || str_contains($uBeacon, 'MIXED')) ? 'WPA_WPA2' : 'WPA2';
            }
        }

        // Extract Radio/SSID Enable
        $enableCandidates = array_unique(array_filter([
            $prefix . 'Enable',
            $tr181SsidPrefix . 'Enable',
            $tr181ApPrefix . 'Enable',
        ]));
        $isEnabled = true;
        foreach ($enableCandidates as $path) {
            $val = $extract($path);
            if ($val !== null) {
                $isEnabled = !in_array((string)$val, ['0', 'false', 'Disabled'], true);
                break;
            }
        }

        // Extract All SSIDs (1-4) for Chip Tabs
        $allSsids = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!$bypassCustomerBinding) {
                // Exclude if bound to Bridge WAN (e.g. ISP voucher hotspot SSID) for customer self-service
                if (in_array($i, $bridgeWlanIndices, true)) {
                    continue;
                }
                // If explicit PPPoE WAN binding exists, only show SSIDs assigned to customer PPPoE
                if (!empty($allowedWlanIndices) && !in_array($i, $allowedWlanIndices, true)) {
                    continue;
                }
            }

            $sVal = $extract("InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$i}.SSID") 
                ?? $extract("Device.WiFi.SSID.{$i}.SSID")
                ?? ($i === $wlanIndex ? $ssid : null);

            $allSsids[] = [
                'index' => $i,
                'name' => "SSID {$i}" . ($i === 1 ? ' (Utama)' : ''),
                'ssid' => $sVal ?: "SSID {$i}",
                'is_pppoe_bound' => in_array($i, $allowedWlanIndices, true),
            ];
        }

        return [
            'ssid' => $ssid,
            'password' => $password,
            'security_mode' => $securityMode,
            'is_enabled' => $isEnabled,
            'real_device_id' => $params['_id'] ?? null,
            'all_ssids' => $allSsids,
            'wlan_index' => $wlanIndex,
            'allowed_wlan_indices' => $allowedWlanIndices,
            'bridge_wlan_indices' => $bridgeWlanIndices,
        ];
    }

    /**
     * Check if a specific WLAN index is authorized for customer self-service (rejecting Bridge/Hotspot slots).
     */
    public function isWlanAllowedForCustomer(string $deviceId, int $wlanIndex, ?string $pppoeUsername = null): bool
    {
        $params = $this->getDeviceParameters($deviceId);
        if (empty($params)) {
            return true; // Fallback if device parameters cannot be retrieved
        }

        $bindingInfo = $this->resolveWanWlanBindings($params, $pppoeUsername);
        $allowedWlanIndices = $bindingInfo['allowed_wlan_indices'] ?? [];
        $bridgeWlanIndices = $bindingInfo['bridge_wlan_indices'] ?? [];

        if (in_array($wlanIndex, $bridgeWlanIndices, true)) {
            return false;
        }

        if (!empty($allowedWlanIndices) && !in_array($wlanIndex, $allowedWlanIndices, true)) {
            return false;
        }

        return true;
    }

    /**
     * Resolve WAN-to-WLAN port bindings to distinguish Customer PPPoE from Bridge/Hotspot SSIDs.
     */
    public function resolveWanWlanBindings(array $params, ?string $pppoeUsername = null): array
    {
        $zteBindings = [];
        $bridgeWlanIndices = [];
        $pppoeWlanIndices = [];
        $hasBridgeWan = false;
        $pppoeWanFound = false;

        // 1. Parse ZTE PortBinding table if present (nested or flat)
        $zteNode = data_get($params, 'InternetGatewayDevice.X_ZTE-COM_PortBinding') 
            ?? $params['InternetGatewayDevice']['X_ZTE-COM_PortBinding'] 
            ?? [];
        if (is_array($zteNode)) {
            foreach ($zteNode as $k => $bNode) {
                if ($k === '_object' || !is_array($bNode)) continue;
                $wanIf = $bNode['WANInterface']['_value'] ?? $bNode['WANInterface'] ?? '';
                $lanIf = $bNode['LANInterface']['_value'] ?? $bNode['LANInterface'] ?? '';
                if ($wanIf && $lanIf) {
                    $zteBindings[$wanIf] = (string)$lanIf;
                }
            }
        }
        // Also check flat keys for X_ZTE-COM_PortBinding
        foreach ($params as $key => $val) {
            if (is_string($key) && preg_match('/InternetGatewayDevice\.X_ZTE-COM_PortBinding\.(\d+)\.WANInterface/i', $key, $m)) {
                $idx = $m[1];
                $wanVal = is_array($val) ? ($val['_value'] ?? '') : (string)$val;
                $lanKey = "InternetGatewayDevice.X_ZTE-COM_PortBinding.{$idx}.LANInterface";
                $lanVal = data_get($params, $lanKey . '._value') ?? data_get($params, $lanKey);
                if ($wanVal && $lanVal) {
                    $zteBindings[$wanVal] = is_array($lanVal) ? ($lanVal['_value'] ?? '') : (string)$lanVal;
                }
            }
        }

        // Helper to extract integer WLAN indices from a binding string
        $extractWlans = function (?string $bindStr): array {
            if (empty($bindStr)) return [];
            $wlans = [];
            $tokens = preg_split('/[,;\s]+/', $bindStr);
            foreach ($tokens as $token) {
                $token = trim($token);
                if (empty($token)) continue;
                if (preg_match('/WLANConfiguration\.(\d+)/i', $token, $m)) {
                    $w = (int)$m[1];
                    // Normalize ZTE dual-band: WLAN 5 is often 5GHz SSID 1 or SSID 2
                    $wlans[] = $w > 4 ? ($w === 5 ? 2 : 4) : $w;
                } elseif (preg_match('/WiFi\.(?:SSID|AccessPoint)\.(\d+)/i', $token, $m)) {
                    $w = (int)$m[1];
                    $wlans[] = $w > 4 ? 2 : $w;
                } elseif (preg_match('/\bSSID(\d+)\b/i', $token, $m)) {
                    $wlans[] = (int)$m[1];
                } elseif (preg_match('/\bWLAN(\d+)\b/i', $token, $m)) {
                    $wlans[] = (int)$m[1];
                }
            }
            return array_values(array_unique($wlans));
        };

        // 2. Discover WAN connections (both nested and flat)
        $wans = [];

        // Check nested tree
        $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? [];
        if (is_array($wanDevices)) {
            foreach ($wanDevices as $wdIndex => $wdNode) {
                if (!is_numeric($wdIndex) || !is_array($wdNode)) continue;
                $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                if (!is_array($connDevices)) continue;
                foreach ($connDevices as $cdIndex => $cdNode) {
                    if (!is_numeric($cdIndex) || !is_array($cdNode)) continue;
                    foreach (['WANPPPConnection', 'WANIPConnection'] as $slot) {
                        $conns = $cdNode[$slot] ?? [];
                        if (is_array($conns)) {
                            foreach ($conns as $cIndex => $cNode) {
                                if (!is_numeric($cIndex) || !is_array($cNode)) continue;
                                $fullPath = "InternetGatewayDevice.WANDevice.$wdIndex.WANConnectionDevice.$cdIndex.$slot.$cIndex";
                                $name = $cNode['Name']['_value'] ?? $cNode['Name'] ?? '';
                                $connType = $cNode['ConnectionType']['_value'] ?? $cNode['ConnectionType'] ?? '';
                                $uName = $cNode['Username']['_value'] ?? $cNode['Username'] ?? '';
                                $nat = $cNode['NATEnabled']['_value'] ?? $cNode['NATEnabled'] ?? null;
                                
                                $portBind = $cNode['X_ZTE-COM_LANInterface']['_value'] 
                                    ?? $cNode['X_ZTE-COM_LanInterface']['_value']
                                    ?? $cNode['X_ZTE-COM_PortBind']['_value']
                                    ?? $cNode['X_FH_LanInterface']['_value']
                                    ?? $cNode['X_HW_LANBinding']['_value']
                                    ?? ($zteBindings[$fullPath] ?? null);

                                $wans[] = [
                                    'path' => $fullPath,
                                    'name' => (string)$name,
                                    'connection_type' => (string)$connType,
                                    'username' => (string)$uName,
                                    'nat' => $nat,
                                    'port_bind' => (string)$portBind,
                                ];
                            }
                        }
                    }
                }
            }
        }

        // If no nested WANs found, scan flat keys
        if (empty($wans)) {
            $discoveredPaths = [];
            foreach ($params as $k => $v) {
                if (is_string($k) && preg_match('/^(InternetGatewayDevice\.WANDevice\.\d+\.WANConnectionDevice\.\d+\.(?:WANPPPConnection|WANIPConnection)\.\d+)/i', $k, $m)) {
                    $discoveredPaths[$m[1]] = true;
                }
            }
            foreach (array_keys($discoveredPaths) as $basePath) {
                $name = data_get($params, "{$basePath}.Name._value") ?? data_get($params, "{$basePath}.Name") ?? '';
                $connType = data_get($params, "{$basePath}.ConnectionType._value") ?? data_get($params, "{$basePath}.ConnectionType") ?? '';
                $uName = data_get($params, "{$basePath}.Username._value") ?? data_get($params, "{$basePath}.Username") ?? '';
                $nat = data_get($params, "{$basePath}.NATEnabled._value") ?? data_get($params, "{$basePath}.NATEnabled") ?? null;
                $portBind = data_get($params, "{$basePath}.X_ZTE-COM_LANInterface._value")
                    ?? data_get($params, "{$basePath}.X_ZTE-COM_LANInterface")
                    ?? data_get($params, "{$basePath}.X_FH_LanInterface._value")
                    ?? data_get($params, "{$basePath}.X_HW_LANBinding._value")
                    ?? ($zteBindings[$basePath] ?? null);

                $wans[] = [
                    'path' => $basePath,
                    'name' => (string)$name,
                    'connection_type' => (string)$connType,
                    'username' => (string)$uName,
                    'nat' => $nat,
                    'port_bind' => (string)$portBind,
                ];
            }
        }

        // 3. Classify WAN connections into PPPoE vs Bridge
        $cleanCustomerUser = !empty($pppoeUsername) ? strtolower(trim($pppoeUsername)) : null;

        foreach ($wans as $wan) {
            $nameLower = strtolower($wan['name']);
            $connType = $wan['connection_type'];
            $wanUser = strtolower(trim($wan['username']));
            $isBridge = false;
            $isCustomerPppoe = false;

            // Check if Bridge
            if (in_array($connType, ['PPPoE_Bridged', 'IP_Bridged'], true) 
                || str_contains($nameLower, 'bridge') 
                || str_contains($nameLower, 'hotspot') 
                || str_contains($nameLower, 'voucher')) {
                $isBridge = true;
            }

            // Check if Customer PPPoE
            if (!$isBridge && $cleanCustomerUser && !empty($wanUser) && $wanUser === $cleanCustomerUser) {
                $isCustomerPppoe = true;
            } elseif (!$isBridge && in_array($connType, ['IP_Routed', 'PPPoE_Routed'], true) && ($wan['nat'] === true || $wan['nat'] === 'true' || $wan['nat'] === 1)) {
                $isCustomerPppoe = true;
            }

            $boundWlans = $extractWlans($wan['port_bind']);

            if ($isBridge) {
                $hasBridgeWan = true;
                foreach ($boundWlans as $w) {
                    $bridgeWlanIndices[] = $w;
                }
            }

            if ($isCustomerPppoe) {
                $pppoeWanFound = true;
                foreach ($boundWlans as $w) {
                    $pppoeWlanIndices[] = $w;
                }
            }
        }

        $bridgeWlanIndices = array_values(array_unique($bridgeWlanIndices));
        $pppoeWlanIndices = array_values(array_unique($pppoeWlanIndices));

        // 4. Resolve Allowed WLAN Indices
        if (!empty($pppoeWlanIndices)) {
            // Explicit PPPoE binding found: only allow SSIDs explicitly bound to PPPoE and not in bridge
            $allowedWlanIndices = array_values(array_diff($pppoeWlanIndices, $bridgeWlanIndices));
        } elseif (!empty($bridgeWlanIndices)) {
            // Bridge WAN found with specific SSIDs: allow any SSID (1..4) except those bound to bridge
            $allowedWlanIndices = array_values(array_diff([1, 2, 3, 4], $bridgeWlanIndices));
        } else {
            // Normal setup without port-binding differentiation
            $allowedWlanIndices = [1, 2, 3, 4];
        }

        return [
            'allowed_wlan_indices' => $allowedWlanIndices,
            'bridge_wlan_indices' => $bridgeWlanIndices,
            'pppoe_wlan_indices' => $pppoeWlanIndices,
            'has_bridge_wan' => $hasBridgeWan,
            'pppoe_wan_found' => $pppoeWanFound,
        ];
    }

    public function updateWifiPassword(string $deviceId, string $newPassword, string $vendor = 'default'): bool
    {
        // Try to guess correct security value based on path
        $secPath = $this->getWifiParameterPath($vendor, 'security_mode');
        $secValue = str_contains($secPath, 'BeaconType') ? '11i' : 'WPA2-Personal';

        return $this->setParameterValues($deviceId, [
            $this->getWifiParameterPath($vendor, 'wpa_passphrase') => $newPassword,
            $this->getWifiParameterPath($vendor, 'wpa_pre_shared_key') => $newPassword,
            $secPath => $secValue,
        ]);
    }

    public function updateWifiSsid(string $deviceId, string $ssid, string $vendor = 'default'): bool
    {
        return $this->setParameterValues($deviceId, [
            $this->getWifiParameterPath($vendor, 'ssid') => $ssid,
        ]);
    }

    public function postTaskWithFallback(string $deviceId, array $payload, bool $tryConnectionRequest = true): \Illuminate\Http\Client\Response
    {
        $realDeviceId = $this->resolveDeviceId($deviceId);
        $encodedId = urlencode($realDeviceId);

        if ($tryConnectionRequest) {
            try {
                return Http::withBasicAuth($this->username, $this->password)
                    ->timeout(10)
                    ->asJson()
                    ->post("{$this->baseUrl}/devices/{$encodedId}/tasks?connection_request", $payload);
            } catch (ConnectionException $ce) {
                // Jika connection_request timeout (modem di balik NAT/firewall tidak merespons pemicu port 7547),
                // antrikan task TANPA ?connection_request ke GenieACS agar tetap tersimpan dan dieksekusi saat modem Inform.
                if (str_contains(strtolower($ce->getMessage()), 'timeout') || str_contains(strtolower($ce->getMessage()), 'timed out')) {
                    \Illuminate\Support\Facades\Log::warning("GenieACS connection_request timed out for device {$realDeviceId}. Fallbacking to queue task without connection_request.");
                    return Http::withBasicAuth($this->username, $this->password)
                        ->timeout(10)
                        ->asJson()
                        ->post("{$this->baseUrl}/devices/{$encodedId}/tasks", $payload);
                }
                throw $ce;
            }
        }

        return Http::withBasicAuth($this->username, $this->password)
            ->timeout(10)
            ->asJson()
            ->post("{$this->baseUrl}/devices/{$encodedId}/tasks", $payload);
    }

    public function setParameterValues(string $deviceId, array $keyValuePairs): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $parameterValues = [];
            foreach ($keyValuePairs as $path => $value) {
                if ($path === '' || $path === null) {
                    continue;
                }
                // Filter out non-TR069 virtual parameters and internal tags to prevent Fault 9005
                if (str_starts_with($path, 'VirtualParameters.') || str_starts_with($path, 'Tags.')) {
                    continue;
                }
                $type = is_bool($value) ? 'xsd:boolean' : (is_int($value) ? 'xsd:int' : (is_numeric($value) ? 'xsd:unsignedInt' : 'xsd:string'));
                if ($type === 'xsd:unsignedInt' && str_contains($path, 'SSID')) {
                    $type = 'xsd:string';
                }
                if (str_contains(strtoupper($path), 'VLAN') && !is_bool($value)) {
                    $type = 'xsd:unsignedInt';
                }
                if (str_ends_with($path, '.Mode')) {
                    $type = 'xsd:unsignedInt';
                }
                $parameterValues[] = [$path, $value, $type];
            }
            if (count($parameterValues) === 0) {
                return false;
            }
            $payload = ['name' => 'setParameterValues', 'parameterValues' => $parameterValues];
            $response = $this->postTaskWithFallback($realDeviceId, $payload, true);
                
            if ($response->successful()) {
                return true;
            }
            $body = (string)$response->body();
            throw new Exception("setParameterValues HTTP {$response->status()}: {$body}");
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
                throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function deleteObject(string $deviceId, string $objectName): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $payload = ['name' => 'deleteObject', 'objectName' => $objectName];
            $response = $this->postTaskWithFallback($realDeviceId, $payload, true);
            return $response->successful();
        } catch (\Throwable $e) {
            throw new Exception("Gagal menghapus objek di GenieACS: " . $e->getMessage(), 0, $e);
        }
    }

    public function addObject(string $deviceId, string $objectName): array
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $payload = ['name' => 'addObject', 'objectName' => $objectName];
            $response = $this->postTaskWithFallback($realDeviceId, $payload, true);
            if ($response->successful()) {
                return [
                    'success' => true,
                    'task_id' => $response->header('X-Task-ID') ?? ($response->json('_id') ?? null),
                    'data' => $response->json(),
                ];
            }
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refreshObject(string $deviceId, string $objectName): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $payload = ['name' => 'refreshObject', 'objectName' => $objectName];
            $response = $this->postTaskWithFallback($realDeviceId, $payload, true);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning("GenieACSDriver refreshObject failed for {$deviceId} on {$objectName}: " . $e->getMessage());
            return false;
        }
    }

    public function getParameterValues(string $deviceId, array $parameterNames): array
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $payload = ['name' => 'getParameterValues', 'parameterNames' => $parameterNames];
            $response = $this->postTaskWithFallback($realDeviceId, $payload, true);
            if ($response->successful()) {
                return [
                    'success' => true,
                    'task_id' => $response->header('X-Task-ID') ?? ($response->json('_id') ?? null),
                    'data' => $response->json(),
                ];
            }
            return [
                'success' => false,
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refreshHardwareTask(string $deviceId): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $this->refreshObject($realDeviceId, 'InternetGatewayDevice.DeviceInfo');
            $this->refreshObject($realDeviceId, 'Device.DeviceInfo');
            return true;
        } catch (\Throwable $e) {
            Log::warning("GenieACSDriver refreshHardwareTask failed for {$deviceId}: " . $e->getMessage());
            return false;
        }
    }

    public function refreshOpticalTask(string $deviceId): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);

            // Resolve vendor to target precise objects
            $ad = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->where('uuid', $realDeviceId)
                ->orWhere('serial_number', $deviceId)
                ->first();
            $vendor = strtolower($ad?->manufacturer ?? '');
            $sn = strtoupper($ad?->serial_number ?? $deviceId);
            $pc = strtoupper($ad?->model ?? '');

            $opticalObjects = [];
            if (str_contains($vendor, 'huawei') || str_starts_with($sn, 'HWTC') || str_starts_with($sn, '48575443') || str_starts_with($pc, 'HG8') || str_starts_with($pc, 'EG8') || str_starts_with($pc, 'HS8')) {
                $opticalObjects = [
                    'InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig',
                    'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.X_HW_DEBUG.AMP.OpticalInfo',
                    'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig',
                ];
            } elseif (str_contains($vendor, 'zte') || str_starts_with($sn, 'ZTEG') || str_starts_with($sn, '5A544547') || str_starts_with($pc, 'F6') || str_starts_with($pc, 'ZXHN')) {
                $opticalObjects = [
                    'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig',
                ];
            } elseif (str_contains($vendor, 'fiberhome') || str_starts_with($sn, 'FHTT') || str_starts_with($sn, '46485454') || str_starts_with($pc, 'HG6') || str_starts_with($pc, 'AN5506')) {
                $opticalObjects = [
                    'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.X_FH_PONInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig',
                ];
            } else {
                $opticalObjects = [
                    'InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig',
                    'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig',
                    'Device.Optical.Interface',
                    'Device.Optical',
                ];
            }

            foreach ($opticalObjects as $obj) {
                $this->refreshObject($realDeviceId, $obj);
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning("GenieACSDriver refreshOpticalTask failed for {$deviceId}: " . $e->getMessage());
            return false;
        }
    }

    public function provisionPppoe(string $deviceId, string $username, string $password, ?string $vlanId = null): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $cleanId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $realDeviceId);
            $provName = "temp_pppoe_" . $cleanId;

            $escUser = addslashes($username);
            $escPass = addslashes($password);
            $vlanVal = $vlanId ? (int)$vlanId : 0;

            $script = <<<JS
const now = Date.now();
let brandDecl = declare("DeviceID.Manufacturer", {value: 1});
let brand = (brandDecl.value !== undefined) ? brandDecl.value[0] : "";
let modelDecl = declare("DeviceID.ProductClass", {value: 1});
let model = (modelDecl.value !== undefined) ? modelDecl.value[0] : "";
let snDecl = declare("DeviceID.SerialNumber", {value: 1});
let sn = (snDecl.value !== undefined) ? snDecl.value[0] : "";

let isZte = (brand.indexOf("ZTE") !== -1 || brand.indexOf("ZICG") !== -1 || sn.indexOf("ZTEG") !== -1 || sn.indexOf("ZICG") !== -1 || model.indexOf("F6") !== -1 || model.indexOf("ZXHN") !== -1);
let isHuawei = (brand.indexOf("Huawei") !== -1 || brand.indexOf("EchoLife") !== -1 || brand.indexOf("EcomTech") !== -1 || sn.indexOf("HWTC") !== -1 || model.indexOf("HG8") !== -1 || model.indexOf("EG8") !== -1);
let isFiberHome = (brand.indexOf("FiberHome") !== -1 || sn.indexOf("FHTT") !== -1 || model.indexOf("AN5506") !== -1 || model.indexOf("HG6") !== -1);

let pppPath = "";
try {
    let wanConns = declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.*.WANPPPConnection.*.Username", {path: 1});
    if (wanConns && wanConns.path) {
        pppPath = wanConns.path.replace(".Username", "");
    }
} catch (e) {}

if (!pppPath) {
    try {
        let wanDevs = declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.*.WANPPPConnection.*", {path: 1});
        if (wanDevs && wanDevs.path) {
            pppPath = wanDevs.path;
        }
    } catch (e) {}
}

if (!pppPath) {
    pppPath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1";
    declare(pppPath, {path: 1}, {path: 1});
}

let connType = (isFiberHome ? "PPPoE_Routed" : "IP_Routed");
declare(pppPath + ".Enable", {value: now}, {value: true});
declare(pppPath + ".ConnectionType", {value: now}, {value: connType});
declare(pppPath + ".NATEnabled", {value: now}, {value: true});
declare(pppPath + ".Username", {value: now}, {value: "{$escUser}"});
declare(pppPath + ".Password", {value: now}, {value: "{$escPass}"});
declare(pppPath + ".ConnectionTrigger", {value: now}, {value: "AlwaysOn"});

if ({$vlanVal} > 0) {
    let match = pppPath.match(/WANConnectionDevice\.(\d+)\./);
    let wanIdx = match ? match[1] : "1";
    let basePath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice." + wanIdx;
    
    if (isZte) {
        declare(pppPath + ".X_ZTE-COM_VLANID", {value: now}, {value: {$vlanVal}});
        declare(pppPath + ".X_ZTE-COM_VLANEnable", {value: now}, {value: 1});
        declare(basePath + ".X_ZTE-COM_VLANID", {value: now}, {value: {$vlanVal}});
    } else if (isHuawei) {
        declare(basePath + ".WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: {$vlanVal}});
        declare(basePath + ".WANEthernetLinkConfig.Mode", {value: now}, {value: 2});
        declare(pppPath + ".X_HW_VLAN", {value: now}, {value: {$vlanVal}});
    } else if (isFiberHome) {
        declare(basePath + ".X_FH_WANGponLinkConfig.VLANIDMark", {value: now}, {value: {$vlanVal}});
        declare(basePath + ".X_FH_WANGponLinkConfig.VLANID", {value: now}, {value: {$vlanVal}});
        declare(basePath + ".X_FH_WANGponLinkConfig.Mode", {value: now}, {value: 1});
    } else {
        declare(basePath + ".WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: {$vlanVal}});
        declare(basePath + ".VLANID", {value: now}, {value: {$vlanVal}});
    }
}
JS;

            // 1. Direct SetParameterValues attempt for immediate WAN PPPoE push
            try {
                $directWanParams = [
                    'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Enable' => true,
                    'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ConnectionType' => 'IP_Routed',
                    'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.NATEnabled' => true,
                    'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username' => $username,
                    'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password' => $password,
                    'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ConnectionTrigger' => 'AlwaysOn',
                ];
                if ($vlanVal > 0) {
                    $directWanParams['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_ZTE-COM_VLANID'] = $vlanVal;
                    $directWanParams['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_ZTE-COM_VLANEnable'] = 1;
                    $directWanParams['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANEthernetLinkConfig.X_HW_VLAN'] = $vlanVal;
                }
                $this->setParameterValues($realDeviceId, $directWanParams);
            } catch (\Throwable $de) {
                \Illuminate\Support\Facades\Log::info("Direct WAN setParameterValues skipped or deferred: " . $de->getMessage());
            }

            // 2. Queue robust provision task for persistence
            $this->upsertProvision($provName, $script);
            $success = $this->addProvisionTask($realDeviceId, $provName);
            if ($success) {
                return true;
            }
            throw new Exception("Gagal mengantrikan task provisions ke GenieACS");
        } catch (Exception $e) {
            throw new Exception("GenieACS PPPoE Provisioning failed: " . $e->getMessage(), 0, $e);
        }
    }

    public function rebootDevice(string $deviceId): bool
    {
        try {
            $payload = ['name' => 'reboot'];
            $response = $this->postTaskWithFallback($deviceId, $payload, true);
                
            return $response->successful();
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
                throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function factoryResetDevice(string $deviceId): bool
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", ['name' => 'factoryReset']);
            return $response->successful();
        } catch (ConnectionException|RequestException $e) {
            throw new Exception("GenieACS factoryReset error: " . $e->getMessage(), 0, $e);
        }
    }

    public static function calculateStatus(array $params): string
    {
        $lastInform = $params['_lastInform'] ?? null;
        if (!$lastInform) return 'offline';

        $informInterval = 300;
        if (isset($params['InternetGatewayDevice']['ManagementServer']['PeriodicInformInterval']['_value'])) {
            $informInterval = (int)$params['InternetGatewayDevice']['ManagementServer']['PeriodicInformInterval']['_value'];
        } elseif (isset($params['Device']['ManagementServer']['PeriodicInformInterval']['_value'])) {
            $informInterval = (int)$params['Device']['ManagementServer']['PeriodicInformInterval']['_value'];
        }

        $diffInSeconds = abs(now()->diffInSeconds(\Carbon\Carbon::parse($lastInform)));
        $offlineThreshold = ($informInterval * 2) + 120;

        if ($diffInSeconds <= $informInterval + 60) return 'online';
        if ($diffInSeconds <= $offlineThreshold) return 'stale';
        return 'offline';
    }

    public function isDeviceOnline(string $deviceId): bool
    {
        $params = $this->getDeviceParameters($deviceId);
        if (empty($params)) {
            return false;
        }
        return self::calculateStatus($params) !== 'offline';
    }

    public function summonDeviceResult(string $deviceId): array
    {
        $result = [
            'task_queued' => false,
            'connection_request_success' => false,
            'error' => null,
        ];

        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            if (empty($realDeviceId)) {
                $result['error'] = 'Device ID tidak valid';
                return $result;
            }
            $encodedId = urlencode($realDeviceId);

            // 1. Antrikan tugas refresh parameter ke antrean GenieACS (tanpa pemicu langsung)
            // Ini SELALU berhasil (HTTP 200/202) terlepas modem online, offline, atau di balik NAT
            $refreshObjects = [
                'InternetGatewayDevice.LANDevice.1.Hosts',
                'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig',
                'InternetGatewayDevice.WANDevice.1.X_HW_DEBUG.AMP.OpticalInfo',
                'InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig',
                'InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig',
                'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig',
                'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig',
                'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig',
                'InternetGatewayDevice.WANDevice.1.X_FH_PONInterfaceConfig',
                'Device.Hosts',
                'Device.WiFi',
                'Device.Optical',
                'Device.Optical.Interface',
            ];
            foreach ($refreshObjects as $obj) {
                try {
                    $url = "{$this->baseUrl}/devices/{$encodedId}/tasks";
                    $resp = Http::timeout(4)
                        ->withBasicAuth($this->username, $this->password)
                        ->asJson()
                        ->post($url, ['name' => 'refreshObject', 'objectName' => $obj]);

                    if ($resp->successful() || in_array($resp->status(), [200, 201, 202, 409])) {
                        $result['task_queued'] = true;
                    }
                } catch (\Throwable) {}
            }

            // 2. Coba kirim Connection Request aktif (port 7547)
            // Catatan: Jika modem online tapi berada di balik IP Privat/CGNAT tanpa VPN publik,
            // GenieACS akan mengembalikan HTTP 502 ("Channel closed" / Connection request failed).
            // Kegagalan ini normal di ISP dan BUKAN error fatal karena tugas sudah terantre di langkah 1.
            try {
                $crUrl = "{$this->baseUrl}/devices/{$encodedId}/tasks?connection_request";
                $crResp = Http::timeout(4)
                    ->withBasicAuth($this->username, $this->password)
                    ->asJson()
                    ->post($crUrl, [
                        'name' => 'refreshObject',
                        'objectName' => 'InternetGatewayDevice.LANDevice.1.Hosts'
                    ]);

                if ($crResp->successful() || in_array($crResp->status(), [200, 201, 202])) {
                    $result['connection_request_success'] = true;
                    $result['task_queued'] = true;
                }
            } catch (\Throwable $ce) {
                // Connection request timeout / unreachable jika IP private
            }

            return $result;
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
            return $result;
        }
    }

    public function summonDevice(string $deviceId): bool
    {
        $res = $this->summonDeviceResult($deviceId);
        return $res['connection_request_success'] || $res['task_queued'];
    }

    public function getTasks(string $deviceId, ?string $status = null): array
    {
        $url = "{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks";
        if ($status !== null) {
            $url .= "?status=" . urlencode($status);
        }
        try {
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get($url);
            return $r->successful() ? $r->json() : [];
        } catch (Exception) {
            return [];
        }
    }

    public function listProvisions(): array
    {
        try {
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get("{$this->baseUrl}/provisions");
            return $r->successful() ? $r->json() : [];
        } catch (Exception) {
            return [];
        }
    }

    public function listPresets(): array
    {
        try {
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get("{$this->baseUrl}/presets");
            return $r->successful() ? $r->json() : [];
        } catch (Exception) {
            return [];
        }
    }

    public function getPreset(string $presetName): ?array
    {
        try {
            $url = "{$this->baseUrl}/presets/" . urlencode($presetName);
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get($url);
            return $r->successful() ? $r->json() : null;
        } catch (Exception) {
            return null;
        }
    }

    public function upsertPreset(string $presetName, array $presetData): bool
    {
        try {
            $url = "{$this->baseUrl}/presets/" . urlencode($presetName);
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->put($url, $presetData);
            
            return $response->successful();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function deleteDevice(string $deviceId): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $url = rtrim($this->baseUrl, '/') . '/devices/' . urlencode($realDeviceId);
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->delete($url);

            if ($response->successful() || $response->status() === 404) {
                return true;
            }

            if ($realDeviceId !== $deviceId) {
                $urlFallback = rtrim($this->baseUrl, '/') . '/devices/' . urlencode($deviceId);
                $respFallback = \Illuminate\Support\Facades\Http::withBasicAuth($this->username, $this->password)
                    ->timeout($this->timeout)
                    ->delete($urlFallback);
                return $respFallback->successful() || $respFallback->status() === 404;
            }

            return false;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("GenieACSDriver: Failed to delete device {$deviceId}: " . $e->getMessage());
            return false;
        }
    }

    public function deletePreset(string $presetName): bool
    {
        try {
            $url = "{$this->baseUrl}/presets/" . urlencode($presetName);
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->delete($url);
            
            return $response->successful();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function getEventBridgeProvisionScript(): string
    {
        return <<<'JS'
// dsBilling_Event_Ingestion Provision Script (v1.2.16)
// Safe, read-only event ingestion without modifying CPE configuration
const now = Date.now();

// 1. Identify Device
let idDecl = declare("DeviceID.ID", {value: 1});
let deviceId = (idDecl && idDecl.value && idDecl.value.length) ? idDecl.value[0] : "";
if (!deviceId) {
  let snDecl = declare("DeviceID.SerialNumber", {value: 1});
  deviceId = (snDecl && snDecl.value && snDecl.value.length) ? snDecl.value[0] : "";
}

let snDecl = declare("DeviceID.SerialNumber", {value: 1});
let serialNumber = (snDecl && snDecl.value && snDecl.value.length) ? snDecl.value[0] : "";

let mfgDecl = declare("DeviceID.Manufacturer", {value: 1});
let manufacturer = (mfgDecl && mfgDecl.value && mfgDecl.value.length) ? mfgDecl.value[0] : "";

let pcDecl = declare("DeviceID.ProductClass", {value: 1});
let productClass = (pcDecl && pcDecl.value && pcDecl.value.length) ? pcDecl.value[0] : "";

// 2. Collect Inform Events
let eventCodes = [];
let eventsDecl = declare("Events.*", {value: 1});
if (eventsDecl && eventsDecl.path) {
  for (let p of eventsDecl) {
    if (p.value && p.value[0]) {
      eventCodes.push(p.value[0]);
    }
  }
}

// 3. Optional metadata: SoftwareVersion & IP
let swDecl = declare("InternetGatewayDevice.DeviceInfo.SoftwareVersion", {value: 1});
if (!swDecl || !swDecl.value || !swDecl.value.length) {
  swDecl = declare("Device.DeviceInfo.SoftwareVersion", {value: 1});
}
let softwareVersion = (swDecl && swDecl.value && swDecl.value.length) ? swDecl.value[0] : "";

let ipDecl = declare("InternetGatewayDevice.ManagementServer.ConnectionRequestURL", {value: 1});
if (!ipDecl || !ipDecl.value || !ipDecl.value.length) {
  ipDecl = declare("Device.ManagementServer.ConnectionRequestURL", {value: 1});
}
let connUrl = (ipDecl && ipDecl.value && ipDecl.value.length) ? ipDecl.value[0] : "";
let ipAddress = "";
if (connUrl) {
  let match = connUrl.match(/:\/\/([^:\/]+)/);
  if (match) {
    ipAddress = match[1];
  }
}

// 3b. Multi-Vendor Optical Power Telemetry Polling on Inform
let mfgUpper = (manufacturer || "").toUpperCase();
let pcUpper = (productClass || "").toUpperCase();
let snUpper = (serialNumber || "").toUpperCase();

if (mfgUpper.includes("HUAWEI") || pcUpper.includes("HG8") || pcUpper.includes("EG8") || pcUpper.includes("HS8") || snUpper.startsWith("HWTC") || snUpper.startsWith("48575443")) {
  declare("InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig.RXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig.TXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig.RXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig.TXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.RxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.TxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_HW_DEBUG.AMP.OpticalInfo.RxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_HW_DEBUG.AMP.OpticalInfo.TxPower", {value: 1});
} else if (mfgUpper.includes("ZTE") || pcUpper.includes("F6") || pcUpper.includes("ZXHN") || snUpper.startsWith("ZTEG") || snUpper.startsWith("5A544547")) {
  declare("InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower", {value: 1});
} else if (mfgUpper.includes("FIBERHOME") || pcUpper.includes("HG6") || pcUpper.includes("AN5506") || snUpper.startsWith("FHTT") || snUpper.startsWith("46485454")) {
  declare("InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_FH_PONInterfaceConfig.RXPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.X_FH_PONInterfaceConfig.TXPower", {value: 1});
} else {
  declare("Device.Optical.Interface.1.RxPower", {value: 1});
  declare("Device.Optical.Interface.1.TxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.RxPower", {value: 1});
  declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.TxPower", {value: 1});
}

// 4. Send Event via Extension
if (deviceId) {
  ext("dsbilling-webhook", "inform", JSON.stringify({
    device_id: deviceId,
    serial_number: serialNumber,
    manufacturer: manufacturer,
    product_class: productClass,
    software_version: softwareVersion,
    ip_address: ipAddress,
    last_inform: new Date(now).toISOString(),
    event_codes: eventCodes
  }));
}
JS;
    }

    public function getEventBridgePresetData(): array
    {
        return [
            'weight' => 0,
            'events' => [
                '0 BOOTSTRAP' => true,
                '1 BOOT' => true,
                '2 PERIODIC' => true,
                '4 VALUE CHANGE' => true,
                '6 CONNECTION REQUEST' => true,
                'M Reboot' => true,
            ],
            'precondition' => '',
            'configurations' => [
                [
                    'type' => 'provision',
                    'name' => 'dsBilling_Event_Ingestion',
                ],
            ],
        ];
    }

    public function deployEventBridge(): array
    {
        $provisionName = 'dsBilling_Event_Ingestion';
        $presetName = 'dsBilling_Event_Ingestion';

        $provSuccess = $this->upsertProvision($provisionName, $this->getEventBridgeProvisionScript(), 0);
        $presetSuccess = $this->upsertPreset($presetName, $this->getEventBridgePresetData());

        return [
            'provision_deployed' => $provSuccess,
            'preset_deployed' => $presetSuccess,
            'success' => $provSuccess && $presetSuccess,
        ];
    }

    public function upsertProvision(string $provisionName, string $javascriptCode, int $weight = 0): bool
    {
        try {
            // In GenieACS v1.2, you simply PUT the raw javascript string to /provisions/{name}
            $url = "{$this->baseUrl}/provisions/{$provisionName}";
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->withBody($javascriptCode, 'text/plain')
                ->put($url);
            
            return $response->successful();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function upsertVirtualParameter(string $vpName, string $javascriptCode): bool
    {
        try {
            $url = "{$this->baseUrl}/virtual_parameters/{$vpName}";
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->withBody($javascriptCode, 'text/plain')
                ->put($url);
            
            return $response->successful();
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function addDevice(string $deviceId, array $metadata = []): bool
    {
        try {
            $payload = array_merge(['_id' => $deviceId], $metadata);
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->put("{$this->baseUrl}/devices/" . urlencode($deviceId), $payload);
            return $response->successful() || $response->status() === 409;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function refreshConnectedDevicesTask(string $deviceId): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $encodedId = urlencode($realDeviceId);

            // Antrikan task refresh spesifik untuk Hosts & WLAN Configuration
            $objects = [
                'InternetGatewayDevice.LANDevice.1.Hosts',
                'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                'Device.Hosts',
                'Device.WiFi'
            ];

            foreach ($objects as $obj) {
                try {
                    Http::timeout(5)
                        ->withBasicAuth($this->username, $this->password)
                        ->asJson()
                        ->post("{$this->baseUrl}/devices/{$encodedId}/tasks", [
                            'name' => 'refreshObject',
                            'objectName' => $obj,
                        ]);
                } catch (\Throwable) {}
            }

            // Kirim Connection Request agar modem segera mengeksekusi task dan push data terbaru
            try {
                Http::timeout(6)
                    ->withBasicAuth($this->username, $this->password)
                    ->asJson()
                    ->post("{$this->baseUrl}/devices/{$encodedId}/tasks?connection_request", [
                        'name' => 'refreshObject',
                        'objectName' => 'InternetGatewayDevice.LANDevice.1.Hosts',
                    ]);
            } catch (\Throwable) {}

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getConnectedDevices(string $deviceId): array
    {
        $realId = $this->resolveDeviceId($deviceId);
        $params = $this->getDeviceParameters($realId);
        if (empty($params) && $realId !== $deviceId) {
            $params = $this->getDeviceParameters($deviceId);
        }
        return $this->extractConnectedDevicesFromParams($params);
    }

    public function extractConnectedDevicesFromParams(array $params): array
    {
        $scalarVal = function ($node, $default = null) {
            if ($node === null) return $default;
            if (is_scalar($node)) return trim((string) $node);
            if (is_array($node)) {
                if (isset($node['_value']) && is_scalar($node['_value'])) {
                    return trim((string) $node['_value']);
                }
                if (isset($node[0]) && is_scalar($node[0])) {
                    return trim((string) $node[0]);
                }
            }
            return $default;
        };

        $cleanMac = function ($val) {
            if (empty($val) || !is_scalar($val)) return null;
            $val = trim(strtoupper((string) $val));
            if ($val === '' || $val === 'UNKNOWN' || $val === 'XSD:STRING' || $val === '00:00:00:00:00:00' || $val === 'FF:FF:FF:FF:FF:FF') {
                return null;
            }
            $hexOnly = preg_replace('/[^0-9A-F]/', '', $val);
            if (strlen($hexOnly) === 12) {
                return implode(':', str_split($hexOnly, 2));
            }
            return null;
        };
        
        // 1. Flatten all parameters to key-value map for robust multi-vendor lookups
        $flat = [];
        $flatten = function ($data, $prefix = '') use (&$flatten, &$flat, $scalarVal) {
            if (!is_array($data)) return;
            foreach ($data as $k => $v) {
                if ($k === '_timestamp' || $k === '_object' || $k === '_writable') continue;
                $key = $prefix ? "$prefix.$k" : (string) $k;
                if (is_array($v)) {
                    $s = $scalarVal($v);
                    if ($s !== null) {
                        $flat[$key] = $s;
                    }
                    $flatten($v, $key);
                } elseif (is_scalar($v)) {
                    $flat[$key] = (string) $v;
                }
            }
        };
        $flatten($params);

        // 2. Extract AssociatedDevice RSSI and Band mapping by MAC
        $assocRssiByMac = [];
        $assocBandByMac = [];
        foreach ($flat as $k => $v) {
            if (str_contains($k, 'AssociatedDevice.') && (str_contains($k, 'MACAddress') || str_contains($k, 'AssociatedDeviceMACAddress'))) {
                $cleanM = $cleanMac($v);
                if (!$cleanM) continue;

                $wlanIdx = 1;
                if (preg_match('/WLANConfiguration\.(\d+)/i', $k, $m)) {
                    $wlanIdx = (int) $m[1];
                } elseif (preg_match('/AccessPoint\.(\d+)/i', $k, $m)) {
                    $wlanIdx = (int) $m[1];
                }

                $prefix = substr($k, 0, strrpos($k, '.'));
                $rssiStr = $flat["{$prefix}.X_CMCC_RSSI"] 
                        ?? $flat["{$prefix}.X_HW_RSSI"] 
                        ?? $flat["{$prefix}.X_ZTE-COM_RSSI"] 
                        ?? $flat["{$prefix}.X_CT-COM_RSSI"] 
                        ?? $flat["{$prefix}.SignalStrength"] 
                        ?? $flat["{$prefix}.RSSI"] 
                        ?? null;

                if ($rssiStr !== null) {
                    $cleanRssi = (int) trim(str_ireplace('dBm', '', (string)$rssiStr));
                    if ($cleanRssi >= -110 && $cleanRssi <= -1) {
                        $assocRssiByMac[$cleanM] = $cleanRssi;
                    }
                }

                $bandLabel = ($wlanIdx === 5 || $wlanIdx === 2) ? "WiFi 5G (SSID {$wlanIdx})" : "WiFi 2.4G (SSID {$wlanIdx})";
                $assocBandByMac[$cleanM] = $bandLabel;
            }
        }

        // 3. Extract Hosts and merge with AssociatedDevices
        $hosts = [];
        $seenMacs = [];

        // Check standard TR-098 Hosts.Host
        $landevices = $params['InternetGatewayDevice']['LANDevice'] ?? [];
        if (is_array($landevices)) {
            foreach ($landevices as $lanIdx => $lanDev) {
                if (!is_array($lanDev) || $lanIdx === '_object') continue;
                
                $hostList = $lanDev['Hosts']['Host'] ?? [];
                if (is_array($hostList)) {
                    foreach ($hostList as $idx => $host) {
                        if (!is_array($host) || $idx === '_object') continue;
                        
                        $rawMac = $scalarVal($host['MACAddress'] ?? null);
                        $normMac = $cleanMac($rawMac);

                        $rawIp = $scalarVal($host['IPAddress'] ?? null);
                        $normIp = (filter_var($rawIp, FILTER_VALIDATE_IP) && $rawIp !== '0.0.0.0') ? $rawIp : '-';

                        // Buang entri dummy/kosong jika tidak punya MAC valid DAN tidak punya IP valid
                        if (!$normMac && $normIp === '-') {
                            continue;
                        }

                        if ($normMac && in_array($normMac, $seenMacs, true)) continue;
                        if ($normMac) $seenMacs[] = $normMac;

                        $rawName = $scalarVal($host['HostName'] ?? null);
                        $hostName = (!empty($rawName) && $rawName !== '-' && strtolower($rawName) !== 'unknown') 
                            ? $rawName 
                            : ($normMac ? 'Perangkat ' . substr($normMac, -5) : "Perangkat #{$idx}");

                        $rssi = $normMac ? ($assocRssiByMac[$normMac] ?? null) : null;
                        $rawIfType = $scalarVal($host['InterfaceType'] ?? null, 'Unknown');
                        $band = ($normMac && isset($assocBandByMac[$normMac])) 
                            ? $assocBandByMac[$normMac] 
                            : ($rawIfType === 'Ethernet' ? 'LAN (Kabel)' : ($rssi !== null ? 'WiFi' : $rawIfType));

                        $activeVal = $scalarVal($host['Active'] ?? null);
                        $isActive = ($activeVal === '1' || $activeVal === true || $activeVal === 'true');

                        $hosts[] = [
                            'MACAddress' => $normMac ?: 'UNKNOWN',
                            'IPAddress' => $normIp,
                            'HostName' => $hostName,
                            'Active' => $isActive,
                            'InterfaceType' => $band,
                            'Layer1Interface' => $scalarVal($host['Layer1Interface'] ?? null, 'WLAN'),
                            'RSSI' => $rssi,
                            'SignalStrength' => $rssi !== null ? "{$rssi} dBm" : null,
                        ];
                    }
                }
            }
        }

        // Check TR-181 Device.Hosts.Host
        $devHosts = $params['Device']['Hosts']['Host'] ?? [];
        if (is_array($devHosts)) {
            foreach ($devHosts as $idx => $host) {
                if (!is_array($host) || $idx === '_object') continue;

                $rawMac = $scalarVal($host['PhysAddress'] ?? $host['MACAddress'] ?? null);
                $normMac = $cleanMac($rawMac);

                $rawIp = $scalarVal($host['IPAddress'] ?? null);
                $normIp = (filter_var($rawIp, FILTER_VALIDATE_IP) && $rawIp !== '0.0.0.0') ? $rawIp : '-';

                if (!$normMac && $normIp === '-') continue;
                if ($normMac && in_array($normMac, $seenMacs, true)) continue;
                if ($normMac) $seenMacs[] = $normMac;

                $rawName = $scalarVal($host['HostName'] ?? null);
                $hostName = (!empty($rawName) && $rawName !== '-') ? $rawName : ($normMac ? 'Perangkat ' . substr($normMac, -5) : "Perangkat #{$idx}");
                $rssi = $normMac ? ($assocRssiByMac[$normMac] ?? null) : null;
                $band = ($normMac && isset($assocBandByMac[$normMac])) ? $assocBandByMac[$normMac] : 'LAN / WiFi';

                $hosts[] = [
                    'MACAddress' => $normMac ?: 'UNKNOWN',
                    'IPAddress' => $normIp,
                    'HostName' => $hostName,
                    'Active' => true,
                    'InterfaceType' => $band,
                    'Layer1Interface' => 'WLAN',
                    'RSSI' => $rssi,
                    'SignalStrength' => $rssi !== null ? "{$rssi} dBm" : null,
                ];
            }
        }

        // 4. Tambahkan juga AssociatedDevice WiFi yang belum ada di Hosts.Host
        foreach ($assocBandByMac as $mac => $band) {
            if (!in_array($mac, $seenMacs, true)) {
                $seenMacs[] = $mac;
                $rssi = $assocRssiByMac[$mac] ?? null;
                $hosts[] = [
                    'MACAddress' => $mac,
                    'IPAddress' => '-',
                    'HostName' => 'Klien Wi-Fi (' . substr($mac, -5) . ')',
                    'Active' => true,
                    'InterfaceType' => $band,
                    'Layer1Interface' => 'WLAN',
                    'RSSI' => $rssi,
                    'SignalStrength' => $rssi !== null ? "{$rssi} dBm" : null,
                ];
            }
        }
        
        return $hosts;
    }

    /**
     * Ekstraksi langsung seluruh WLAN instance (SSID 1 s/d 5+) dari in-memory params tanpa HTTP call.
     */
    public function extractAllWlansFromParams(array $params, ?\App\Models\ISP\Onu $onu = null): array
    {
        $scalarVal = function ($node, $default = null) {
            if ($node === null) return $default;
            if (is_scalar($node)) return trim((string) $node);
            if (is_array($node)) {
                if (isset($node['_value']) && is_scalar($node['_value'])) {
                    return trim((string) $node['_value']);
                }
                if (isset($node[0]) && is_scalar($node[0])) {
                    return trim((string) $node[0]);
                }
            }
            return $default;
        };

        $flat = [];
        $flatten = function ($data, $prefix = '') use (&$flatten, &$flat, $scalarVal) {
            if (!is_array($data)) return;
            foreach ($data as $k => $v) {
                if ($k === '_timestamp' || $k === '_object' || $k === '_writable') continue;
                $key = $prefix ? "$prefix.$k" : (string) $k;
                if (is_array($v)) {
                    $s = $scalarVal($v);
                    if ($s !== null) {
                        $flat[$key] = $s;
                    }
                    $flatten($v, $key);
                } elseif (is_scalar($v)) {
                    $flat[$key] = (string) $v;
                }
            }
        };
        $flatten($params);

        $wlanNodes = $params['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration'] 
            ?? $params['Device']['WiFi']['SSID'] 
            ?? [];

        // Deteksi seluruh indeks WLAN numerik (TR-098 & TR-181)
        $foundIndices = [];
        if (is_array($wlanNodes)) {
            foreach ($wlanNodes as $k => $v) {
                if (is_numeric($k) && is_array($v)) {
                    $foundIndices[] = (int) $k;
                }
            }
        }
        foreach ($flat as $k => $v) {
            if (preg_match('/WLANConfiguration\.(\d+)\.SSID/i', $k, $m)) {
                $foundIndices[] = (int) $m[1];
            } elseif (preg_match('/WiFi\.SSID\.(\d+)\.SSID/i', $k, $m)) {
                $foundIndices[] = (int) $m[1];
            }
        }
        $foundIndices = array_unique($foundIndices);
        sort($foundIndices);

        if (!in_array(1, $foundIndices, true)) {
            array_unshift($foundIndices, 1);
        }

        $wlans = [];
        $displayOrder = 1;
        foreach ($foundIndices as $w) {
            $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$w}.";
            $tr181Prefix = "Device.WiFi.SSID.{$w}.";

            $wSsid = $flat[$prefix . 'SSID'] 
                ?? $flat[$tr181Prefix . 'SSID']
                ?? ($wlanNodes[$w]['SSID']['_value'] ?? $wlanNodes[$w]['SSID'] ?? null)
                ?? ($w === 1 ? ($flat['VirtualParameters.wifiSsid'] ?? $flat['VirtualParameters.WlanSSID'] ?? $onu?->wifi_ssid) : null);

            $wPass = $flat[$prefix . 'PreSharedKey.1.KeyPassphrase']
                ?? $flat[$prefix . 'KeyPassphrase']
                ?? $flat[$prefix . 'X_ZTE-COM_WPAKey']
                ?? ($wlanNodes[$w]['PreSharedKey']['1']['KeyPassphrase']['_value'] ?? null)
                ?? ($w === 1 ? ($flat['VirtualParameters.wifiPassword'] ?? $flat['VirtualParameters.WlanPassword']) : null);

            $wEnabledVal = $flat[$prefix . 'Enable'] 
                ?? $flat[$tr181Prefix . 'Enable'] 
                ?? ($wlanNodes[$w]['Enable']['_value'] ?? $wlanNodes[$w]['Enable'] ?? true);
            $wEnabled = ($wEnabledVal === '1' || $wEnabledVal === true || $wEnabledVal === 'true');

            $rawBeacon = $flat[$prefix . 'BeaconType'] 
                ?? $flat[$tr181Prefix . 'BeaconType']
                ?? ($wlanNodes[$w]['BeaconType']['_value'] ?? $wlanNodes[$w]['BeaconType'] ?? 'WPA/WPA2');

            $basicEnc = $flat[$prefix . 'BasicEncryptionModes'] ?? null;
            if (in_array(strtolower((string)$rawBeacon), ['none', 'open'], true) || strtolower((string)$basicEnc) === 'none' || (empty($wPass) && !str_contains((string)$rawBeacon, 'WPA') && !str_contains((string)$rawBeacon, '11i'))) {
                $wBeacon = 'None';
            } else {
                $wBeacon = (string)$rawBeacon;
            }

            $ssidLower = strtolower((string)($wSsid ?? ''));
            $is5G = ($w === 5 || ($w >= 2 && str_contains($ssidLower, '5g')));
            $bandLabel = $is5G ? "5G" : "2.4G";

            // Hanya sertakan jika ada SSID terkonfigurasi, atau slot utama (1), atau radio 5G aktif (5)
            if (!empty($wSsid) || $w === 1 || ($wEnabled && $w === 5)) {
                $label = "SSID {$displayOrder} ({$bandLabel})";
                $wlans[] = [
                    'index' => $w,
                    'display_slot' => $displayOrder,
                    'label' => $label,
                    'ssid' => (string)($wSsid ?? ''),
                    'password' => (string)($wPass ?? ''),
                    'is_enabled' => $wEnabled,
                    'security_mode' => (string)$wBeacon,
                    'is_hidden' => false,
                ];
                $displayOrder++;
            }
        }

        // Jika hanya 1 WLAN dan modem belum melaporkan 5G, tambahkan slot 2 (5G) siap pakai jika dual band
        if (count($wlans) === 1 && $foundIndices && in_array(5, $foundIndices, true)) {
            $wlans[] = [
                'index' => 5,
                'display_slot' => 2,
                'label' => "SSID 2 (5G)",
                'ssid' => '',
                'password' => '',
                'is_enabled' => false,
                'security_mode' => 'WPA/WPA2',
                'is_hidden' => false,
            ];
        }

        return $wlans;
    }

    public function getWlanStats(string $deviceId, int $wlanIndex = 1): array
    {
        $params = $this->getDeviceParameters($deviceId);
        $wlan = $params['InternetGatewayDevice']['LANDevice'][1]['WLANConfiguration'][$wlanIndex] ?? null;
        if (!$wlan) return [];
        
        return [
            'TotalBytesSent' => $wlan['TotalBytesSent']['_value'] ?? $wlan['TotalBytesSent'] ?? 0,
            'TotalBytesReceived' => $wlan['TotalBytesReceived']['_value'] ?? $wlan['TotalBytesReceived'] ?? 0,
            'TotalPacketsSent' => $wlan['TotalPacketsSent']['_value'] ?? $wlan['TotalPacketsSent'] ?? 0,
            'TotalPacketsReceived' => $wlan['TotalPacketsReceived']['_value'] ?? $wlan['TotalPacketsReceived'] ?? 0,
        ];
    }

    public function blockMacAddress(string $deviceId, string $macToBlock, int $wlanIndex = 1): bool
    {
        $params = $this->getDeviceParameters($deviceId);
        $enabledPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlEnabled";
        $listPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlList";

        $extractPath = function($path) use ($params) {
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) return null;
                $node = $node[$p];
            }
            return $node;
        };

        if ($extractPath($enabledPath) === null) {
            throw new Exception("Fitur MAC Filtering tidak didukung atau path tidak ditemukan.");
        }

        $currentList = $extractPath($listPath)['_value'] ?? '';
        $macs = array_filter(array_map('trim', explode(',', $currentList)));

        if (!in_array($macToBlock, $macs)) {
            $macs[] = $macToBlock;
        }

        return $this->setParameterValues($deviceId, [
            $enabledPath => true,
            $listPath => implode(',', $macs)
        ]);
    }

    public function unblockMacAddress(string $deviceId, string $macToUnblock, int $wlanIndex = 1): bool
    {
        $params = $this->getDeviceParameters($deviceId);
        $enabledPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlEnabled";
        $listPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlList";

        $extractPath = function($path) use ($params) {
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) return null;
                $node = $node[$p];
            }
            return $node;
        };

        if ($extractPath($enabledPath) === null) {
            throw new Exception("Fitur MAC Filtering tidak didukung atau path tidak ditemukan.");
        }

        $currentList = $extractPath($listPath)['_value'] ?? '';
        $macs = array_filter(array_map('trim', explode(',', $currentList)));

        $macs = array_values(array_filter($macs, fn($m) => $m !== $macToUnblock));

        return $this->setParameterValues($deviceId, [
            $listPath => implode(',', $macs)
        ]);
    }



    public function updateWifiSsidAndPassword(
        string $deviceId, 
        string $ssid, 
        ?string $password = null, 
        string $vendor = 'default',
        int $wlanIndex = 1,
        string $securityMode = 'WPA_WPA2',
        ?bool $isHidden = null,
        ?bool $isEnabled = null
    ): bool {
        $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.";
        $params = [];

        // 1. Radio status (Enable)
        if ($isEnabled !== null) {
            $params[$prefix . 'Enable'] = (bool) $isEnabled;
        } else {
            $params[$prefix . 'Enable'] = true;
        }

        // 2. SSID Broadcast (Hidden / Visible)
        if ($isHidden !== null) {
            $params[$prefix . 'SSIDAdvertisementEnabled'] = !$isHidden;
        }

        // 3. SSID Name (Standard TR-098)
        $params[$prefix . 'SSID'] = trim($ssid);

        // 4. Security Mode & Password
        if ($password !== null && $password !== '') {
            $trimmedPass = (string) $password;
            $openBeacon = $this->resolveOpenBeaconValue($vendor, $deviceId);

            if ($securityMode === 'OPEN' || $securityMode === 'None' || $securityMode === 'none') {
                $params[$prefix . 'BeaconType'] = $openBeacon;
                if ($openBeacon === 'Basic') {
                    $params[$prefix . 'BasicEncryptionModes'] = 'None';
                }
            } elseif ($securityMode === 'WPA2') {
                $params[$prefix . 'BeaconType'] = '11i';
                $params[$prefix . 'WPAEncryptionModes'] = 'AESEncryption';
                $params[$prefix . 'KeyPassphrase'] = $trimmedPass;
            } elseif ($securityMode === 'WPA') {
                $params[$prefix . 'BeaconType'] = 'WPA';
                $params[$prefix . 'WPAEncryptionModes'] = 'TKIPEncryption';
                $params[$prefix . 'KeyPassphrase'] = $trimmedPass;
            } else { // WPA_WPA2 (Default Rekomendasi)
                $params[$prefix . 'BeaconType'] = '11i';
                $params[$prefix . 'WPAEncryptionModes'] = 'TKIPandAESEncryption';
                $params[$prefix . 'KeyPassphrase'] = $trimmedPass;
            }

            // Vendor-specific ZTE fallback if ZTE-specific WPAKey is configured
            if (strtolower($vendor) === 'zte' || str_contains(strtolower($deviceId), 'zte')) {
                $params[$prefix . 'X_ZTE-COM_WPAKey'] = $trimmedPass;
            }
        } elseif ($securityMode === 'OPEN' || $securityMode === 'None' || $securityMode === 'none') {
            $openBeacon = $this->resolveOpenBeaconValue($vendor, $deviceId);
            $params[$prefix . 'BeaconType'] = $openBeacon;
            if ($openBeacon === 'Basic') {
                $params[$prefix . 'BasicEncryptionModes'] = 'None';
            }
        }

        // Try primary batch with fallback to essential parameters if CPE rejects optional vendor keys
        try {
            return $this->setParameterValues($deviceId, $params);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("updateWifiSsidAndPassword primary batch failed for device {$deviceId}: {$e->getMessage()}, attempting fallback to core parameters.");
            $coreParams = [
                $prefix . 'SSID' => trim($ssid),
                $prefix . 'Enable' => true,
            ];
            if ($password !== null && $password !== '' && !in_array($securityMode, ['OPEN', 'None', 'none'], true)) {
                $coreParams[$prefix . 'KeyPassphrase'] = (string)$password;
            }
            return $this->setParameterValues($deviceId, $coreParams);
        }
    }

    /**
     * Nilai BeaconType untuk mode Open (tanpa sandi), per vendor:
     *  - ZTE              → 'None'
     *  - Huawei/FiberHome → 'Basic' (disertai BasicEncryptionModes = 'None')
     * Bisa di-override via config acs_vendors.{vendor}.open_beacon_value.
     * Vendor dideteksi dari nama vendor, lalu fallback ke Device ID GenieACS (OUI-ProductClass-Serial).
     */
    public function resolveOpenBeaconValue(string $vendor = 'default', ?string $deviceId = null): string
    {
        $vendorKey = strtolower(trim($vendor));
        $configured = $vendorKey !== 'default' ? config("acs_vendors.{$vendorKey}.open_beacon_value") : null;
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $haystack = $vendorKey . ' ' . strtolower((string) $deviceId);
        $signatures = [
            'zte' => ['zte'],
            'huawei' => ['huawei', 'hwtc', 'hg8'],
            'fiberhome' => ['fiberhome', 'fhtt', 'an55'],
        ];
        foreach ($signatures as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) {
                    $value = config("acs_vendors.{$key}.open_beacon_value");
                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }
            }
        }

        return (string) config('acs_vendors.default.open_beacon_value', 'None');
    }

    /**
     * Ganti segmen "WLANConfiguration.N." pada path vendor ke index WLAN target.
     */
    private function wlanIndexedPath(string $path, int $wlanIndex): string
    {
        if ($path === '') {
            return '';
        }
        return preg_replace('/\.WLANConfiguration\.\d+\./', ".WLANConfiguration.{$wlanIndex}.", $path) ?? $path;
    }

    public function addProvisionTask(string $deviceId, string $provisionName): bool
    {
        try {
            // In GenieACS (v1.2+ CWMP session engine), task name is 'provisions' (plural)
            // with array of provision names in 'provisions'.
            $payload = [
                'name' => 'provisions',
                'provisions' => [$provisionName],
            ];
            $response = $this->postTaskWithFallback($deviceId, $payload, true);
            return $response->successful();
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}


