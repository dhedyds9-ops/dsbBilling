<?php

namespace App\Services\ACS;

use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AcsWanManagerService
{
    public function __construct(
        protected GenieACSDriver $driver,
        protected ?AcsVerificationEngine $verificationEngine = null
    ) {}

    public function getVerificationEngine(): AcsVerificationEngine
    {
        return $this->verificationEngine ?? new AcsVerificationEngine($this->driver);
    }

    /**
     * Detect vendor string from device record or parameter tree.
     */
    public function detectVendor(string $deviceId, array $params = []): string
    {
        $cleanId = strtoupper(trim(urldecode($deviceId)));
        $cuParts = explode('-', $cleanId);
        $candidateSn = end($cuParts);

        $device = ACSDevice::withoutGlobalScopes()
            ->with(['vendor', 'onu.vendor'])
            ->where(function ($q) use ($cleanId, $candidateSn) {
                $q->where('uuid', $cleanId)
                  ->orWhere('serial_number', $cleanId)
                  ->orWhere('serial_number', $candidateSn)
                  ->orWhere('mac_address', $cleanId);
            })->first();

        $candidates = [
            $cleanId,
            $candidateSn,
            $device?->vendor?->name ?? '',
            $device?->manufacturer ?? '',
            $device?->product_class ?? '',
            $device?->model ?? '',
            $device?->serial_number ?? '',
            $device?->onu?->vendor?->name ?? '',
            $device?->onu?->serial_number ?? '',
            $params['DeviceID']['Manufacturer']['_value'] ?? '',
            $params['_deviceId']['_Manufacturer'] ?? '',
            $params['InternetGatewayDevice']['DeviceInfo']['Manufacturer']['_value'] ?? '',
            $params['Device']['DeviceInfo']['Manufacturer']['_value'] ?? '',
        ];

        $combined = strtolower(implode(' ', array_filter($candidates)));

        if (str_contains($combined, 'huawei') || str_contains($combined, 'hwtc') || str_contains($combined, 'eg8') || str_contains($combined, 'hg8') || str_contains($combined, 'ws70')) {
            return 'huawei';
        }
        if (str_contains($combined, 'zte') || str_contains($combined, 'zteg') || str_contains($combined, 'f609') || str_contains($combined, 'f670') || str_contains($combined, 'f660')) {
            return 'zte';
        }
        if (str_contains($combined, 'fiberhome') || str_contains($combined, 'fhtt') || str_contains($combined, 'an5506') || str_contains($combined, 'hg6')) {
            return 'fiberhome';
        }
        if (str_contains($combined, 'vsol') || str_contains($combined, 'v280')) {
            return 'vsol';
        }
        if (str_contains($combined, 'nokia') || str_contains($combined, 'alcatel') || str_contains($combined, 'alcl')) {
            return 'nokia';
        }

        return 'generic';
    }

    /**
     * Retrieve all configured WAN connections and port bindings on the ONT.
     */
    public function getWanConnections(string $deviceId): array
    {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);
        $params = $this->driver->getDeviceParameters($realDeviceId);
        $vendor = $this->detectVendor($realDeviceId, $params);

        // Fetch desired state if device is linked to customerService or pppoeUser
        $deviceModel = ACSDevice::withoutGlobalScopes()
            ->with(['customerService.pppoeUser', 'onu.customerService.pppoeUser'])
            ->where('uuid', $realDeviceId)
            ->orWhere('serial_number', $realDeviceId)
            ->first();

        $desiredState = null;
        if ($deviceModel) {
            $cs = $deviceModel->customerService ?? $deviceModel->onu?->customerService;
            if ($cs) {
                $desiredState = [
                    'username' => $cs->username ?? $cs->pppoeUser?->username,
                    'vlan_id' => $cs->serviceProfile?->vlan_id,
                ];
            }
        }

        $connections = AcsTelemetryParser::parseWanTree($params, $vendor, $desiredState);
        $portBindings = AcsPortBindingEngine::discover($params, $connections, $vendor);

        return [
            'vendor' => $vendor,
            'connections' => $connections,
            'port_bindings' => $portBindings,
            'raw_parameters' => $params,
        ];
    }

    /**
     * Create a new WAN Connection on the ONT.
     */
    public function addWanConnection(string $deviceId, array $data, ?int $userId = null): array
    {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);
        $params = $this->driver->getDeviceParameters($realDeviceId);
        $vendor = $this->detectVendor($realDeviceId, $params);

        $isFiberHome = str_contains($vendor, 'fiberhome');
        $isZte = str_contains($vendor, 'zte');
        $isHuawei = str_contains($vendor, 'huawei');

        $mode = $data['mode'] ?? 'pppoe_route';
        $vlanEnabled = (bool)($data['vlan_enabled'] ?? true);
        $vlanId = $vlanEnabled ? (int)($data['vlan_id'] ?? 200) : 0;
        $natEnabled = (bool)($data['nat_enabled'] ?? true);
        $serviceList = $data['service_list'] ?? ($isFiberHome ? 'TR069,INTERNET' : 'INTERNET');
        if (empty($serviceList)) {
            $serviceList = $isFiberHome ? 'TR069,INTERNET' : 'INTERNET';
        }

        $username = addslashes((string)($data['username'] ?? ''));
        $password = addslashes((string)($data['password'] ?? ''));

        // Determine slot object and connection mode
        if ($mode === 'pppoe_route') {
            $connObj = "WANPPPConnection";
            $connType = $isFiberHome ? "PPPoE_Routed" : "IP_Routed";
            $natStr = $natEnabled ? 'true' : 'false';
        } elseif ($mode === 'dhcp_route') {
            $connObj = "WANIPConnection";
            $connType = "IP_Routed";
            $natStr = $natEnabled ? 'true' : 'false';
        } elseif ($mode === 'bridge_ppp') {
            $connObj = "WANPPPConnection";
            $connType = "PPPoE_Bridged";
            $natStr = 'false';
        } elseif ($mode === 'bridge_ip') {
            $connObj = "WANIPConnection";
            $connType = "IP_Bridged";
            $natStr = 'false';
        } else {
            $connObj = "WANIPConnection";
            $connType = "IP_Routed";
            $natStr = 'true';
        }

        // Port Binding string construction
        $bindString = $this->buildBindString($data);
        $escapedBind = addslashes($bindString);

        $credScript = '';
        if ($mode === 'pppoe_route') {
            $credScript = <<<CRED
declare(pppPath + ".Username", {value: now}, {value: "{$username}"});
declare(pppPath + ".Password", {value: now}, {value: "{$password}"});
CRED;
        }

        $staticScript = '';
        if ($mode === 'static_route' && !empty($data['ip_address'])) {
            $ip = addslashes($data['ip_address']);
            $mask = addslashes($data['subnet_mask'] ?? '255.255.255.0');
            $gw = addslashes($data['gateway'] ?? '');
            $dns = addslashes($data['dns'] ?? '8.8.8.8,1.1.1.1');
            $staticScript = <<<STATIC
declare(pppPath + ".AddressingType", {value: now}, {value: "Static"});
declare(pppPath + ".ExternalIPAddress", {value: now}, {value: "{$ip}"});
declare(pppPath + ".SubnetMask", {value: now}, {value: "{$mask}"});
if ("{$gw}" !== "") declare(pppPath + ".DefaultGateway", {value: now}, {value: "{$gw}"});
if ("{$dns}" !== "") declare(pppPath + ".DNSServers", {value: now}, {value: "{$dns}"});
STATIC;
        }

        $vlanEnabledStr = ($vlanEnabled && $vlanId > 0) ? 'true' : 'false';

        $script = <<<JS
const now = Date.now();
// 1. Search existing empty slot or determine next index
let wanConns = declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.*", {path: 1});
let basePath = null;
let maxIdx = 0;

for (let p of wanConns) {
    let idx = parseInt(p.path.split(".").pop());
    if (idx > maxIdx) maxIdx = idx;

    let ppps = declare(p.path + ".WANPPPConnection.*", {path: 1});
    let ips = declare(p.path + ".WANIPConnection.*", {path: 1});

    let hasPpp = false;
    let hasIp = false;
    for (let x of ppps) hasPpp = true;
    for (let x of ips) hasIp = true;

    if (!hasPpp && !hasIp && !basePath) {
        basePath = p.path;
    }
}

if (!basePath) {
    basePath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice." + (maxIdx + 1);
    declare(basePath, {path: 1}, {path: 1});
}

let pppPath = basePath + ".{$connObj}.1";
declare(pppPath, {path: 1}, {path: 1});

declare(pppPath + ".ConnectionType", {value: now}, {value: "{$connType}"});
declare(pppPath + ".NATEnabled", {value: now}, {value: {$natStr}});
declare(pppPath + ".Enable", {value: now}, {value: true});
declare(pppPath + ".Name", {value: now}, {value: "dsBilling_{$connType}_{$vlanId}"});

if ("{$connObj}" === "WANIPConnection" && "{$connType}" === "IP_Routed" && "{$mode}" !== "static_route") {
    declare(pppPath + ".AddressingType", {value: now}, {value: "DHCP"});
}

// Vendor VLAN configuration
if ({$vlanEnabledStr} && {$vlanId} > 0) {
    if ("{$vendor}".includes("fiberhome")) {
        declare(basePath + ".X_FH_WANGponLinkConfig.VLANIDMark", {value: now}, {value: {$vlanId}});
        declare(basePath + ".X_FH_WANGponLinkConfig.VLANID", {value: now}, {value: {$vlanId}});
        declare(basePath + ".X_FH_WANGponLinkConfig.Mode", {value: now}, {value: 1});
    } else if ("{$vendor}".includes("huawei")) {
        declare(basePath + ".WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: {$vlanId}});
        declare(basePath + ".WANEthernetLinkConfig.Mode", {value: now}, {value: 2});
        declare(pppPath + ".X_HW_VLAN", {value: now}, {value: {$vlanId}});
    } else if ("{$vendor}".includes("zte")) {
        declare(pppPath + ".X_ZTE-COM_VLANID", {value: now}, {value: {$vlanId}});
        declare(pppPath + ".X_ZTE-COM_VLANEnable", {value: now}, {value: 1});
    } else {
        declare(basePath + ".WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: {$vlanId}});
        declare(basePath + ".VLANID", {value: now}, {value: {$vlanId}});
    }
} else {
    if ("{$vendor}".includes("huawei")) {
        declare(basePath + ".WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: 0});
        declare(basePath + ".WANEthernetLinkConfig.Mode", {value: now}, {value: 1});
    } else if ("{$vendor}".includes("zte")) {
        declare(pppPath + ".X_ZTE-COM_VLANEnable", {value: now}, {value: 0});
        declare(pppPath + ".X_ZTE-COM_VLANID", {value: now}, {value: 0});
    }
}

// Service List
if ("{$vendor}".includes("fiberhome")) {
    declare(pppPath + ".X_FH_ServiceList", {value: now}, {value: "{$serviceList}"});
} else if ("{$vendor}".includes("huawei")) {
    declare(pppPath + ".X_HW_SERVICELIST", {value: now}, {value: "{$serviceList}"});
} else if ("{$vendor}".includes("zte")) {
    declare(pppPath + ".X_ZTE-COM_ServiceList", {value: now}, {value: "{$serviceList}"});
}

// Port Binding
if ("{$escapedBind}" !== "") {
    if ("{$vendor}".includes("fiberhome")) {
        declare(pppPath + ".X_FH_LanInterface", {value: now}, {value: "{$escapedBind}"});
    } else if ("{$vendor}".includes("huawei")) {
        declare(pppPath + ".X_HW_LANBIND", {value: now}, {value: "{$escapedBind}"});
        declare(pppPath + ".X_HW_LANBinding", {value: now}, {value: "{$escapedBind}"});
    } else if ("{$vendor}".includes("zte")) {
        declare(pppPath + ".X_ZTE-COM_LanInterface", {value: now}, {value: "{$escapedBind}"});
        declare(pppPath + ".X_ZTE-COM_PortBind", {value: now}, {value: "{$escapedBind}"});
        let bindings = declare("InternetGatewayDevice.X_ZTE-COM_PortBinding.*", {path: 1});
        let bindIdx = 1;
        for (let b of bindings) {
            let bIdx = parseInt(b.path.split(".").pop());
            if (bIdx >= bindIdx) bindIdx = bIdx + 1;
        }
        let bindPath = "InternetGatewayDevice.X_ZTE-COM_PortBinding." + bindIdx;
        declare(bindPath, {path: 1}, {path: 1});
        declare(bindPath + ".WANInterface", {value: now}, {value: pppPath});
        declare(bindPath + ".LANInterface", {value: now}, {value: "{$escapedBind}"});
    }
}

{$credScript}
{$staticScript}
JS;

        $cleanUuid = preg_replace('/[^a-zA-Z0-9_-]/', '_', $realDeviceId);
        $provName = "temp_add_wan_" . $cleanUuid;
        $this->driver->upsertProvision($provName, $script);

        $expected = [];
        if (!empty($username)) {
            $expected['username'] = $username;
        }
        if ($vlanEnabled && $vlanId > 0) {
            $expected['vlan_id'] = $vlanId;
        }

        return $this->getVerificationEngine()->executeWithVerification(
            deviceId: $realDeviceId,
            taskType: 'wan_create',
            expectedChanges: $expected,
            writeCallback: function () use ($realDeviceId, $provName) {
                $dispatched = $this->driver->addProvisionTask($realDeviceId, $provName);
                return [
                    'success' => $dispatched,
                    'message' => $dispatched ? 'Task pembuatan WAN berhasil dikirim ke GenieACS.' : 'Gagal mengirim task ke GenieACS.',
                ];
            },
            userId: $userId,
            refreshPath: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice',
            autoVerifyImmediately: false
        );
    }

    /**
     * Update an existing WAN Connection.
     */
    public function updateWanConnection(string $deviceId, string $fullPath, array $data, ?int $userId = null): array
    {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);
        $params = $this->driver->getDeviceParameters($realDeviceId);
        $vendor = $this->detectVendor($realDeviceId, $params);

        $isFiberHome = str_contains($vendor, 'fiberhome');
        $isZte = str_contains($vendor, 'zte');
        $isHuawei = str_contains($vendor, 'huawei');

        $pathParts = explode('.', $fullPath);
        array_pop($pathParts);
        array_pop($pathParts);
        $parentPath = implode('.', $pathParts);

        $mode = $data['mode'] ?? 'pppoe_route';
        $vlanEnabled = (bool)($data['vlan_enabled'] ?? true);
        $vlanId = $vlanEnabled ? (int)($data['vlan_id'] ?? 200) : 0;
        $serviceVal = $data['service_list'] ?? ($isFiberHome ? 'TR069,INTERNET' : 'INTERNET');
        $bindString = $this->buildBindString($data);

        $isPppoeMode = ($mode === 'pppoe_route');
        $isSlotIp = str_contains($fullPath, 'WANIPConnection');

        // CASE 1: Migrating WANIPConnection slot to PPPoE Dial-up via safe provision
        if ($isPppoeMode && $isSlotIp) {
            $username = addslashes((string)($data['username'] ?? ''));
            $password = addslashes((string)($data['password'] ?? ''));
            $connType = $isFiberHome ? "PPPoE_Routed" : "IP_Routed";
            $escapedBind = addslashes($bindString);
            $vlanEnabledStr = ($vlanEnabled && $vlanId > 0) ? 'true' : 'false';

            $targetPppPath = $parentPath . '.WANPPPConnection.1';

            $script = <<<JS
const now = Date.now();
declare("{$fullPath}.Enable", {value: now}, {value: false});

let pppPath = "{$targetPppPath}";
declare(pppPath, {path: 1}, {path: 1});
declare(pppPath + ".Enable", {value: now}, {value: true});
declare(pppPath + ".ConnectionType", {value: now}, {value: "{$connType}"});
declare(pppPath + ".NATEnabled", {value: now}, {value: true});
declare(pppPath + ".Name", {value: now}, {value: "1_INTERNET_R_VID_{$vlanId}"});
declare(pppPath + ".Username", {value: now}, {value: "{$username}"});
if ("{$password}" !== "") {
    declare(pppPath + ".Password", {value: now}, {value: "{$password}"});
}

if ({$vlanEnabledStr} && {$vlanId} > 0) {
    if ("{$vendor}".includes("fiberhome")) {
        declare("{$parentPath}.X_FH_WANGponLinkConfig.VLANIDMark", {value: now}, {value: {$vlanId}});
        declare("{$parentPath}.X_FH_WANGponLinkConfig.VLANID", {value: now}, {value: {$vlanId}});
        declare("{$parentPath}.X_FH_WANGponLinkConfig.Mode", {value: now}, {value: 1});
    } else if ("{$vendor}".includes("huawei")) {
        declare("{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: {$vlanId}});
        declare("{$parentPath}.WANEthernetLinkConfig.Mode", {value: now}, {value: 2});
        declare(pppPath + ".X_HW_VLAN", {value: now}, {value: {$vlanId}});
    } else if ("{$vendor}".includes("zte")) {
        declare(pppPath + ".X_ZTE-COM_VLANID", {value: now}, {value: {$vlanId}});
        declare(pppPath + ".X_ZTE-COM_VLANEnable", {value: now}, {value: 1});
    }
}
JS;

            $cleanUuid = preg_replace('/[^a-zA-Z0-9_-]/', '_', $realDeviceId);
            $provName = "temp_mig_wan_" . $cleanUuid;
            $this->driver->upsertProvision($provName, $script);

            return $this->getVerificationEngine()->executeWithVerification(
                deviceId: $realDeviceId,
                taskType: 'wan_migrate_pppoe',
                expectedChanges: [
                    'target_ppp' => $targetPppPath,
                    'username' => $username,
                    'vlan_id' => $vlanId,
                ],
                writeCallback: function () use ($realDeviceId, $provName) {
                    $dispatched = $this->driver->addProvisionTask($realDeviceId, $provName);
                    return [
                        'success' => $dispatched,
                        'message' => $dispatched ? 'Task migrasi ke PPPoE berhasil dikirim ke antrean GenieACS.' : 'Gagal mengirim migrasi ke GenieACS.',
                    ];
                },
                userId: $userId,
                refreshPath: $parentPath,
                autoVerifyImmediately: false
            );
        }

        // CASE 2: Parameter set via setParameterValues
        $parameters = [];
        $parameters["{$fullPath}.Enable"] = true;

        if ($isPppoeMode) {
            $parameters["{$fullPath}.ConnectionType"] = $isFiberHome ? "PPPoE_Routed" : "IP_Routed";
        } elseif ($mode === 'bridge_ppp') {
            $parameters["{$fullPath}.ConnectionType"] = "PPPoE_Bridged";
        } elseif ($mode === 'bridge_ip') {
            $parameters["{$fullPath}.ConnectionType"] = "IP_Bridged";
        } elseif ($mode === 'dhcp_route' || $mode === 'static_route') {
            $parameters["{$fullPath}.ConnectionType"] = "IP_Routed";
        }

        if (!empty($data['username'])) {
            $parameters["{$fullPath}.Username"] = trim((string)$data['username']);
        }
        if (!empty($data['password'])) {
            $parameters["{$fullPath}.Password"] = (string)$data['password'];
        }

        $isBridge = in_array($mode, ['bridge_ppp', 'bridge_ip']);
        $parameters["{$fullPath}.NATEnabled"] = $isBridge ? false : (bool)($data['nat_enabled'] ?? true);

        if (!empty($serviceVal)) {
            if ($isFiberHome) {
                $parameters["{$fullPath}.X_FH_ServiceList"] = $serviceVal;
            } elseif ($isZte) {
                $parameters["{$fullPath}.X_ZTE-COM_ServiceList"] = $serviceVal;
            } elseif ($isHuawei) {
                $parameters["{$fullPath}.X_HW_SERVICELIST"] = $serviceVal;
            }
        }

        // Port Binding
        if (!empty($bindString)) {
            if ($isFiberHome) {
                $parameters["{$fullPath}.X_FH_LanInterface"] = $bindString;
            } elseif ($isHuawei) {
                $parameters["{$fullPath}.X_HW_LANBIND"] = $bindString;
                $parameters["{$fullPath}.X_HW_LANBinding"] = $bindString;
            } elseif ($isZte) {
                $parameters["{$fullPath}.X_ZTE-COM_LanInterface"] = $bindString;
                $parameters["{$fullPath}.X_ZTE-COM_PortBind"] = $bindString;
            }
        }

        // VLAN Handling
        if ($vlanEnabled && $vlanId > 0) {
            if ($isHuawei) {
                $parameters["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = $vlanId;
                $parameters["{$parentPath}.WANEthernetLinkConfig.Mode"] = 2;
                $parameters["{$fullPath}.X_HW_VLAN"] = $vlanId;
            } elseif ($isZte) {
                $parameters["{$fullPath}.X_ZTE-COM_VLANID"] = $vlanId;
                $parameters["{$fullPath}.X_ZTE-COM_VLANEnable"] = 1;
            } elseif ($isFiberHome) {
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANIDMark"] = $vlanId;
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANID"] = $vlanId;
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.Mode"] = 1;
            } else {
                $parameters["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = $vlanId;
                $parameters["{$parentPath}.WANEthernetLinkConfig.Mode"] = 2;
                $parameters["{$parentPath}.VLANID"] = $vlanId;
            }
        } else {
            if ($isHuawei) {
                $parameters["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = 0;
                $parameters["{$parentPath}.WANEthernetLinkConfig.Mode"] = 1;
                $parameters["{$fullPath}.X_HW_VLAN"] = 0;
            } elseif ($isZte) {
                $parameters["{$fullPath}.X_ZTE-COM_VLANEnable"] = 0;
                $parameters["{$fullPath}.X_ZTE-COM_VLANID"] = 0;
            } elseif ($isFiberHome) {
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANIDMark"] = 0;
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANID"] = 0;
            }
        }

        $expectedParams = [];
        if (!empty($data['username'])) {
            $expectedParams["{$fullPath}.Username"] = trim((string)$data['username']);
        }
        if ($vlanEnabled && $vlanId > 0) {
            if ($isHuawei) {
                $expectedParams["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = $vlanId;
            } elseif ($isZte) {
                $expectedParams["{$fullPath}.X_ZTE-COM_VLANID"] = $vlanId;
            } elseif ($isFiberHome) {
                $expectedParams["{$parentPath}.X_FH_WANGponLinkConfig.VLANID"] = $vlanId;
            }
        }

        return $this->getVerificationEngine()->executeWithVerification(
            deviceId: $realDeviceId,
            taskType: 'wan_update',
            expectedChanges: $expectedParams,
            writeCallback: function () use ($realDeviceId, $parameters) {
                $success = $this->driver->setParameterValues($realDeviceId, $parameters);
                return [
                    'success' => $success,
                    'message' => $success ? 'Perubahan WAN berhasil dikirim ke perangkat.' : 'Gagal mengirim konfigurasi ke GenieACS.',
                ];
            },
            userId: $userId,
            refreshPath: $fullPath,
            autoVerifyImmediately: true
        );
    }

    /**
     * Delete a WAN Connection.
     */
    public function deleteWanConnection(string $deviceId, string $fullPath, ?int $userId = null): array
    {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);

        return $this->getVerificationEngine()->executeWithVerification(
            deviceId: $realDeviceId,
            taskType: 'wan_delete',
            expectedChanges: [],
            writeCallback: function () use ($realDeviceId, $fullPath) {
                $cleanUuid = preg_replace('/[^a-zA-Z0-9_-]/', '_', $realDeviceId);
                $provName = "temp_del_wan_" . $cleanUuid;
                $script = <<<JS
const now = Date.now();
declare("{$fullPath}.Enable", {value: now}, {value: false});
declare("{$fullPath}", null, {path: 0});
JS;
                $this->driver->upsertProvision($provName, $script);
                $dispatched = $this->driver->addProvisionTask($realDeviceId, $provName);

                try {
                    $this->driver->deleteObject($realDeviceId, $fullPath);
                } catch (\Throwable) {}

                return [
                    'success' => $dispatched,
                    'message' => $dispatched ? 'Perintah hapus WAN berhasil dikirim ke GenieACS.' : 'Gagal mengirim perintah hapus ke GenieACS.',
                ];
            },
            userId: $userId,
            refreshPath: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice',
            autoVerifyImmediately: false
        );
    }

    /**
     * Build standard LAN and WLAN binding string.
     */
    public function buildBindString(array $data): string
    {
        if (!empty($data['custom_binding'])) {
            return trim($data['custom_binding']);
        }

        $paths = [];
        $lans = (array)($data['bind_lan'] ?? [1, 2, 3, 4]);
        sort($lans);
        foreach ($lans as $l) {
            if ($l) $paths[] = "InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.{$l}";
        }

        $wlans = (array)($data['bind_wlan'] ?? [1, 5]);
        sort($wlans);
        foreach ($wlans as $w) {
            if ($w) $paths[] = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$w}";
        }

        return implode(',', $paths);
    }
}
