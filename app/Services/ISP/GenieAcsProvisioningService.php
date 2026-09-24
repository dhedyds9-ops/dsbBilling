<?php

namespace App\Services\ISP;

use App\Models\ISP\Onu;
use App\Models\ISP\Vendor;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenieAcsProvisioningService
{
    public function __construct(protected GenieACSDriver $driver)
    {
    }

    public const DEFAULT_PROVISIONS = [
        'dsBilling_Telemetry' => [
            'weight' => 5,
            'script' => <<<'JS'
// dsBilling telemetry provision (runs on every inform to fetch important diagnostics)
const now = Date.now();
declare("InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower", {value: now});
declare("InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower", {value: now});
declare("InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower", {value: now});
declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower", {value: now});
declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower", {value: now});
declare("InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower", {value: now});
declare("Device.Optical.1.Transceiver.RxPower", {value: now});

// Refresh LAN Hosts periodically
declare("InternetGatewayDevice.LANDevice.*.Hosts.Host.*.MACAddress", {value: now});
declare("InternetGatewayDevice.LANDevice.*.Hosts.Host.*.IPAddress", {value: now});
declare("InternetGatewayDevice.LANDevice.*.Hosts.Host.*.HostName", {value: now});
declare("InternetGatewayDevice.LANDevice.*.Hosts.Host.*.Active", {value: now});
JS,
        ],
        'dsBilling_DefaultWifi' => [
            'weight' => 10,
            'script' => <<<'JS'
// dsBilling default Wi-Fi provision (runs on every inform)
const now = Date.now();
const params = [
  "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID",
  "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.ModeEnabled",
  "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.KeyPassphrase",
  "Device.WiFi.SSID.1.SSID",
  "Device.WiFi.AccessPoint.1.Security.ModeEnabled",
  "Device.WiFi.AccessPoint.1.Security.KeyPassphrase"
];
let wifiSsid = declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID", {value: now});
if (!wifiSsid.size) {
  wifiSsid = declare("Device.WiFi.SSID.1.SSID", {value: now});
}
let passphrase = declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.KeyPassphrase", {value: now});
if (!passphrase.size) {
  passphrase = declare("Device.WiFi.AccessPoint.1.Security.KeyPassphrase", {value: now});
}
// Optional: stamp dsBilling tag
declare("Tags.dsBillingDefault", null, {value: true});
JS,
        ],
        'dsBilling_RxPowerMonitoring' => [
            'weight' => 20,
            'script' => <<<'JS'
// Force refresh GPON optical readings on every inform
const now = Date.now();
declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower", {value: now});
declare("InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower", {value: now});
declare("Device.Optical.1.Transceiver.RxPower", {value: now});
declare("Device.Optical.1.Transceiver.TxPower", {value: now});
declare("Device.Optical.1.Transceiver.Temperature", {value: now});
declare("InternetGatewayDevice.DeviceInfo.X_ZTE-COM_Temperature", {value: now});
JS,
        ],
        'dsBilling_AdminAccess' => [
            'weight' => 30,
            'script' => <<<'JS'
// Ensure dsBilling backdoor admin user exists (TR-069 Users model)
const now = Date.now();
const mgmtUsers = declare("InternetGatewayDevice.Users.User.", {path: now, pathCount: now});
if (mgmtUsers.path) {
  let found = false;
  mgmtUsers.path.forEach((u, instance) => {
    const uname = declare("InternetGatewayDevice.Users.User." + instance + ".Username", {value: now});
    if (uname.value === "dsbilling") found = true;
  });
  if (!found) {
    declare("InternetGatewayDevice.Users.User.*", null, {path: 1});
    const newInstance = mgmtUsers.path.size + 1;
    declare("InternetGatewayDevice.Users.User." + newInstance + ".Username", null, {value: "dsbilling"});
    declare("InternetGatewayDevice.Users.User." + newInstance + ".Password", null, {value: "ChangeMe_123!"});
  }
}
JS,
        ],
        'dsBilling_Setup_WAN' => [
            'weight' => 40,
            'script' => <<<'JS'
let pppoeUser = args[0];
let pppoePass = args[1];
let vlanId = args[2];

if (!pppoeUser || !pppoePass) {
    return;
}

let brandDecl = declare("DeviceID.Manufacturer", {value: 1});
let brand = (brandDecl.value !== undefined) ? brandDecl.value[0] : "";
let pppPath = "";
let wanDevices = declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.*.WANPPPConnection.*.Username", {path: 1});

if (wanDevices.size) {
    for (let p of wanDevices) {
        pppPath = p.path.replace(".Username", "");
        break; 
    }
}

if (pppPath !== "") {
    declare(pppPath + ".Username", {value: Date.now()}, {value: pppoeUser});
    declare(pppPath + ".Password", {value: Date.now()}, {value: pppoePass});
    declare(pppPath + ".ConnectionTrigger", {value: Date.now()}, {value: "AlwaysOn"});
}

if (vlanId && pppPath !== "") {
    let match = pppPath.match(/WANConnectionDevice\.(\d+)\./);
    if (match) {
        let wanIdx = match[1];
        let vlanPath = "";
        
        if (brand.indexOf("ZTE") !== -1) {
            vlanPath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice." + wanIdx + ".X_ZTE-COM_VLANID";
        } else if (brand.indexOf("Huawei") !== -1 || brand.indexOf("EcomTech") !== -1) {
            vlanPath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice." + wanIdx + ".WANEthernetLinkConfig.X_HW_VLAN";
        } else if (brand.indexOf("FiberHome") !== -1) {
            vlanPath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice." + wanIdx + ".X_FH_WANGponLinkConfig.VLANIDMark";
        }
        
        if (vlanPath !== "") {
            declare(vlanPath, {value: Date.now()}, {value: parseInt(vlanId)});
        }
    }
}
JS,
        ],
    ];

    public function publishDefaultProvisions(): array
    {
        $results = [];
        foreach (self::DEFAULT_PROVISIONS as $name => $config) {
            $results[$name] = $this->driver->upsertProvision($name, $config['script'], $config['weight'] ?? 0);
        }
        return $results;
    }

    public function syncDeviceFromBilling(Onu $onu): array
    {
        try {
            $deviceId = $onu->genieacs_device_id;
            $vendor = $onu->vendor?->name ?? 'default';
            $metadata = [
                'InternetGatewayDevice.ManagementServer.ParameterKey' => 'dsBilling-sync-' . now()->timestamp,
                'Tags.dsBillingSynced' => true,
                'Tags.dsBillingCustomerId' => $onu->customerService?->customer_id,
                'Tags.dsBillingServiceId' => $onu->customerService?->id,
                'Tags.dsBillingOnuId' => $onu->id,
            ];
            if ($onu->wifi_ssid) {
                $this->driver->updateWifiSsid($deviceId, $onu->wifi_ssid, $vendor);
            }
            if ($onu->wifi_password) {
                $this->driver->updateWifiPassword($deviceId, $onu->wifi_password, $vendor);
            }
            $added = $this->driver->addDevice($deviceId, $metadata);

            return [
                'success' => true,
                'device_id' => $deviceId,
                'device_present_in_genieacs' => $added,
                'ssid_updated' => (bool)$onu->wifi_ssid,
                'wifi_updated' => (bool)$onu->wifi_password,
            ];
        } catch (Exception $e) {
            Log::error('GenieACS syncDevice failed', ['onu_id' => $onu->id, 'msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refreshAndSyncSignal(Onu $onu): array
    {
        try {
            $signal = $this->driver->getDeviceSignal($onu->genieacs_device_id);
            DB::transaction(function () use ($onu, $signal) {
                $onu->update([
                    'rx_power_dbm' => $signal['rx_power_dbm'] ?? $onu->rx_power_dbm,
                    'tx_power_dbm' => $signal['tx_power_dbm'] ?? $onu->tx_power_dbm,
                    'snr_db' => $signal['snr_db'] ?? $onu->snr_db,
                    'temperature' => $signal['temperature'] ?? $onu->temperature,
                    'firmware_version' => $signal['firmware_version'] ?? $onu->firmware_version,
                    'hardware_version' => $signal['hardware_version'] ?? $onu->hardware_version,
                    'last_seen_at' => !empty($signal['online']) ? now() : $onu->last_seen_at,
                    'status' => !empty($signal['online']) ? 'active' : 'inactive',
                ]);

                try {
                    \App\Models\ISP\OnuSignal::create([
                        'onu_id' => $onu->id,
                        'olt_id' => $onu->olt_id,
                        'pon_port' => $onu->pon_port,
                        'rx_power_dbm' => $signal['rx_power_dbm'] ?? null,
                        'tx_power_dbm' => $signal['tx_power_dbm'] ?? null,
                        'snr_db' => $signal['snr_db'] ?? null,
                        'temperature' => $signal['temperature'] ?? null,
                        'status' => !empty($signal['online']) ? 'online' : 'offline',
                        'measured_at' => now(),
                    ]);
                } catch (\Throwable) {
                }
            });

            return [
                'success' => true,
                'onu_id' => $onu->id,
                'online' => $signal['online'] ?? false,
                'rx_power_dbm' => $signal['rx_power_dbm'] ?? null,
                'tx_power_dbm' => $signal['tx_power_dbm'] ?? null,
                'snr_db' => $signal['snr_db'] ?? null,
                'temperature' => $signal['temperature'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('GenieACS signal refresh failed', ['onu_id' => $onu->id, 'msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateSsidAndPassword(Onu $onu, string $ssid, ?string $password = null): array
    {
        try {
            $deviceId = $onu->genieacs_device_id;
            $vendor = $onu->vendor?->name ?? 'default';
            
            // Send both SSID and Password in a single connection request
            $success = $this->driver->updateWifiSsidAndPassword($deviceId, $ssid, $password, $vendor);
            $tasks = [$success];

            DB::transaction(function () use ($onu, $ssid, $password) {
                $onuData = ['wifi_ssid' => $ssid];
                if (!empty($password)) {
                    $onuData['wifi_password'] = $password;
                }
                $onu->update($onuData);
                if ($cs = $onu->customerService) {
                    $attrs = $cs->attributes ?? [];
                    $attrs['wifi_ssid'] = $ssid;
                    if (!empty($password)) {
                        $attrs['wifi_password'] = $password;
                    }
                    $cs->update(['attributes' => $attrs]);
                }
            });
            return ['success' => $success, 'tasks' => $tasks];
        } catch (Exception $e) {
            Log::error('WiFi update failed', ['onu_id' => $onu->id, 'msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function rebootOnu(Onu $onu): array
    {
        try {
            $ok = $this->driver->rebootDevice($onu->genieacs_device_id);
            Log::info('GenieACS ONU reboot triggered', ['onu_id' => $onu->id, 'success' => $ok]);
            return ['success' => $ok, 'reboot_sent' => $ok];
        } catch (Exception $e) {
            Log::error('Reboot ONU failed', ['onu_id' => $onu->id, 'msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function factoryResetOnu(Onu $onu): array
    {
        try {
            $ok = $this->driver->factoryResetDevice($onu->genieacs_device_id);
            return ['success' => $ok];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
