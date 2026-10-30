<?php

namespace App\Livewire\ResellerPortal\Network\AcsDevice;

use App\Livewire\AdminComponent;
use App\Models\ACS\ACSDevice;
use App\Services\Adapters\Monitoring\GenieACSDriver;

class Show extends AdminComponent
{
    public $deviceId;
    public $device;

    public $wifiEnabled = true;
    public $wifiSsid = '';
    public $wifiPassword = '';
    public $wifiSecurity = 'WPA2PSK';
    public $showWifiModal = false;
    public $wlanTarget = '1';
    public $availableWlans = [];
    public $wifiSaving = false;
    public $cachedDeviceParams = null;

    public $connectedDevices = [];
    public $deviceStatus = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'reseller';
        $this->activePage = 'network-acs';
        $this->deviceId = $id;

        $this->device = ACSDevice::forReseller()->with([
            'customerService', 
            'asset', 
            'onu', 
            'olt', 
            'pop', 
            'odp', 
            'vendor',
            'tasks' => function ($query) {
                $query->latest()->take(10);
            },
            'alarms' => function ($query) {
                $query->latest()->take(10);
            },
            'logs' => function ($query) {
                $query->latest()->take(10);
            }
        ])->findOrFail($id);
        
        $this->loadConnectedDevices();
        $this->loadDeviceStatus();
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Devices GenieACS', 'url' => route('reseller-portal.network.acs-devices.index')],
            ['label' => $this->device->serial_number],
        ];
    }

    public function loadDeviceStatus(): void
    {
        try {
            $driver = app(GenieACSDriver::class);
            $targetId = $this->device->uuid ?: $this->device->serial_number;
            $params = $driver->getDeviceParameters((string) $targetId);
            
            $extract = function($path) use ($params) {
                $parts = explode('.', $path);
                $node = $params;
                foreach ($parts as $p) {
                    if (!is_array($node) || !array_key_exists($p, $node)) return null;
                    $node = $node[$p];
                }
                return isset($node['_value']) ? $node['_value'] : null;
            };

            // PPPoE & WAN details
            $pppoeUser = null;
            $pppoePass = null;
            $wanIp = null;
            $wanMac = null;

            // PON RX/TX Power
            $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower')
                    ?? $extract('VirtualParameters.RXPower');
            $txPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower')
                    ?? $extract('VirtualParameters.TXPower');

            // Find WAN IP, MAC, PPPoE, and PON stats dynamically
            $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? $params['Device']['WANDevice'] ?? [];
            if (is_array($wanDevices)) {
                foreach ($wanDevices as $wdIndex => $wdNode) {
                    if (!is_numeric($wdIndex) || !is_array($wdNode)) continue;
                    
                    // Try to find RX/TX in WANPONInterfaceConfig dynamically
                    $ponConfig = $wdNode['WANPONInterfaceConfig'] ?? $wdNode['WANEponInterfaceConfig'] ?? [];
                    if (is_array($ponConfig)) {
                        foreach ($ponConfig as $pcIndex => $pcNode) {
                            if (!is_numeric($pcIndex) || !is_array($pcNode)) continue;
                            if ($rxPower === null) {
                                $rxPower = $pcNode['X_ZTE-COM_RxPower']['_value'] ?? $pcNode['X_HW_RxPower']['_value'] ?? $pcNode['RxPower']['_value'] ?? null;
                            }
                            if ($txPower === null) {
                                $txPower = $pcNode['X_ZTE-COM_TxPower']['_value'] ?? $pcNode['X_HW_TxPower']['_value'] ?? $pcNode['TxPower']['_value'] ?? null;
                            }
                        }
                    }

                    $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                    if (!is_array($connDevices)) continue;
                    foreach ($connDevices as $cdIndex => $cdNode) {
                        if (!is_numeric($cdIndex) || !is_array($cdNode)) continue;
                        
                        // Check PPPoE
                        $ppp = $cdNode['WANPPPConnection'] ?? [];
                        if (is_array($ppp)) {
                            foreach ($ppp as $pIndex => $pNode) {
                                if (!is_numeric($pIndex) || !is_array($pNode)) continue;
                                if (isset($pNode['ExternalIPAddress']['_value']) && $pNode['ExternalIPAddress']['_value'] && $pNode['ExternalIPAddress']['_value'] !== '0.0.0.0') {
                                    $wanIp = $pNode['ExternalIPAddress']['_value'];
                                }
                                if (isset($pNode['MACAddress']['_value']) && $pNode['MACAddress']['_value']) {
                                    $wanMac = $pNode['MACAddress']['_value'];
                                }
                                if (isset($pNode['Username']['_value']) && $pNode['Username']['_value']) {
                                    $pppoeUser = $pNode['Username']['_value'];
                                }
                                if (isset($pNode['Password']['_value']) && $pNode['Password']['_value']) {
                                    $pppoePass = $pNode['Password']['_value'];
                                }
                            }
                        }
                        
                        // Check IPoE
                        $ip = $cdNode['WANIPConnection'] ?? [];
                        if (is_array($ip)) {
                            foreach ($ip as $iIndex => $iNode) {
                                if (!is_numeric($iIndex) || !is_array($iNode)) continue;
                                if (isset($iNode['ExternalIPAddress']['_value']) && $iNode['ExternalIPAddress']['_value'] && $iNode['ExternalIPAddress']['_value'] !== '0.0.0.0') {
                                    $wanIp = $iNode['ExternalIPAddress']['_value'];
                                }
                                if (isset($iNode['MACAddress']['_value']) && $iNode['MACAddress']['_value']) {
                                    $wanMac = $iNode['MACAddress']['_value'];
                                }
                            }
                        }
                    }
                }
            }

            // TR-181 Transceiver fallback
            if ($rxPower === null) $rxPower = $extract('Device.Optical.1.Transceiver.RxPower');
            if ($txPower === null) $txPower = $extract('Device.Optical.1.Transceiver.TxPower');

            if ($rxPower !== null && is_numeric($rxPower)) {
                $rxPower = (float) $rxPower;
                if ($rxPower < -500 || $rxPower > 500) {
                    $rxPower /= 1000;
                } elseif ($rxPower < -50 || $rxPower > 50) {
                    $rxPower /= 100;
                }
            }

            if ($txPower !== null && is_numeric($txPower)) {
                $txPower = (float) $txPower;
                if ($txPower < -500 || $txPower > 500) {
                    $txPower /= 1000;
                } elseif ($txPower < -50 || $txPower > 50) {
                    $txPower /= 100;
                }
            }

            // SSIDs (Dynamic search)
            $ssid1 = null;
            $ssid2 = null;
            $wlanNode = $params['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration'] ?? $params['Device']['WiFi']['SSID'] ?? [];
            if (is_array($wlanNode)) {
                foreach ($wlanNode as $key => $node) {
                    if (is_numeric($key) && is_array($node)) {
                        if (isset($node['SSID']['_value']) && $node['SSID']['_value']) {
                            if ($ssid1 === null) {
                                $ssid1 = $node['SSID']['_value'];
                            } elseif ($ssid2 === null && $node['SSID']['_value'] !== $ssid1) {
                                $ssid2 = $node['SSID']['_value'];
                            }
                        }
                    }
                }
            }

            $this->deviceStatus = [
                'rx_power' => $rxPower !== null ? round((float) $rxPower, 2) . ' dBm' : '-',
                'tx_power' => $txPower !== null ? round((float) $txPower, 2) . ' dBm' : '-',
                'pppoe_username' => is_scalar($pppoeUser) && $pppoeUser ? (string) $pppoeUser : '-',
                'pppoe_password' => is_scalar($pppoePass) && $pppoePass ? (string) $pppoePass : '-',
                'ssid_1' => is_scalar($ssid1) && $ssid1 ? (string) $ssid1 : '-',
                'ssid_2' => is_scalar($ssid2) && $ssid2 ? (string) $ssid2 : '-',
                'wan_ip' => is_scalar($wanIp) && $wanIp ? (string) $wanIp : '-',
                'wan_mac' => is_scalar($wanMac) && $wanMac ? (string) $wanMac : '-',
            ];
        } catch (\Throwable $e) {
            $this->deviceStatus = [];
        }
    }

    public function loadConnectedDevices(): void
    {
        try {
            $driver = app(GenieACSDriver::class);
            $targetId = $this->device->uuid ?: $this->device->serial_number;
            $this->connectedDevices = $driver->getConnectedDevices((string) $targetId);
        } catch (\Throwable $e) {
            $this->connectedDevices = [];
        }
    }

    public function summonDevice(): void
    {
        try {
            $driver = app(GenieACSDriver::class);
            $targetId = $this->device->uuid ?: $this->device->serial_number;
            $success = $driver->summonDevice((string) $targetId);
            
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'connection_request',
                'status' => $success ? 'completed' : 'pending',
                'created_by' => auth()->id()
            ]);

            session()->flash('success', 'Perintah Summon (Connection Request) telah dikirim ke perangkat.');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Summon dikirim.']);
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengirim perintah Summon: ' . $e->getMessage());
        }
    }

    public function rebootDevice(): void
    {
        $user = auth()->user();
        if ($user && !$user->hasPermission('onu.reboot') && !$user->hasPermission('olt.view') && !$user->hasPermission('onu.view')) {
            session()->flash('error', 'Anda tidak memiliki izin untuk melakukan reboot perangkat.');
            return;
        }

        try {
            $driver = app(GenieACSDriver::class);
            $targetId = $this->device->uuid ?: $this->device->serial_number;
            $driver->rebootDevice((string) $targetId);
            
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'reboot',
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            session()->flash('success', 'Perintah reboot telah dikirim ke perangkat via TR-069.');
            $this->device->refresh();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengirim perintah reboot: ' . $e->getMessage());
        }
    }

    private function getAvailableWlans(): array
    {
        $wlans = [];
        $params = $this->cachedDeviceParams;
        if (!$params) return [];

        // Check TR-098
        $wlanNode = $params['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration'] ?? [];
        if (is_array($wlanNode)) {
            foreach ($wlanNode as $key => $node) {
                if (is_numeric($key) && is_array($node)) {
                    $ssid = $node['SSID']['_value'] ?? '';
                    $name = $ssid ? "WLAN $key ($ssid)" : "WLAN $key";
                    $wlans[$key] = $name;
                }
            }
        }

        // Check TR-181 if empty
        if (empty($wlans)) {
            $wlanNode = $params['Device']['WiFi']['SSID'] ?? [];
            if (is_array($wlanNode)) {
                foreach ($wlanNode as $key => $node) {
                    if (is_numeric($key) && is_array($node)) {
                        $ssid = $node['SSID']['_value'] ?? '';
                        $name = $ssid ? "WLAN $key ($ssid)" : "WLAN $key";
                        $wlans[$key] = $name;
                    }
                }
            }
        }

        if (empty($wlans)) {
            $wlans = [
                '1' => 'WLAN 1 (Utama - 2.4GHz)',
                '5' => 'WLAN 5 (Utama - 5GHz)'
            ];
        }

        return $wlans;
    }

    public function openWifiModal(): void
    {
        $this->showWifiModal = true;
        $this->cachedDeviceParams = null;
        $this->loadWifiCredentials();
        $this->availableWlans = $this->getAvailableWlans();
        if (!isset($this->availableWlans[$this->wlanTarget])) {
            $this->wlanTarget = array_key_first($this->availableWlans) ?? '1';
            $this->loadWifiCredentials();
        }
    }

    public function updatedWlanTarget(): void
    {
        $this->loadWifiCredentials();
    }

    private function extractParam(array $params, string $path)
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) return null;
            $node = $node[$p];
        }
        return isset($node['_value']) ? $node['_value'] : null;
    }

    private function pathExists(array $params, string $path): bool
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) return false;
            $node = $node[$p];
        }
        return true;
    }

    public function loadWifiCredentials(): void
    {
        try {
            $driver = app(GenieACSDriver::class);
            $targetId = $this->device->uuid ?: $this->device->serial_number;
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters((string) $targetId);
            }
            $params = $this->cachedDeviceParams;
            $wlanIndex = $this->wlanTarget;
            
            $this->wifiEnabled = (bool) ($this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.Enable") ?? true);
            
            $this->wifiSsid = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID")
                ?? $this->extractParam($params, "Device.WiFi.SSID.{$wlanIndex}.SSID")
                ?? '';
            
            $this->wifiPassword = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase")
                ?? $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase")
                ?? '';

            $rawSecurity = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.BeaconType")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.ModeEnabled")
                ?? '';
            
            if (in_array($rawSecurity, ['11i', 'WPA2-Personal'])) {
                $this->wifiSecurity = 'WPA2PSK';
            } elseif (in_array($rawSecurity, ['WPAand11i', 'WPA-WPA2-Personal', 'WPA-Personal', 'WPA'])) {
                $this->wifiSecurity = 'WPAPSKWPA2PSK';
            } elseif (in_array($rawSecurity, ['Basic', 'WEP-128', 'WEP-64'])) {
                $this->wifiSecurity = 'Basic';
            } elseif (in_array($rawSecurity, ['None', 'Open'])) {
                $this->wifiSecurity = 'None';
            } else {
                $this->wifiSecurity = $rawSecurity ? 'WPA2PSK' : 'None';
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengambil data WiFi saat ini: ' . $e->getMessage());
        }
    }

    public function saveWifi(): void
    {
        $this->validate([
            'wifiSsid' => 'required|string|min:3',
            'wifiPassword' => 'nullable|string|min:8',
            'wifiSecurity' => 'required|string',
        ]);

        try {
            $driver = app(GenieACSDriver::class);
            $targetId = $this->device->uuid ?: $this->device->serial_number;
            $wlanIndex = $this->wlanTarget;
            
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters((string) $targetId);
            }
            $params = $this->cachedDeviceParams;
            
            $paramsToSet = [];
            
            $ssidCandidates = [
                "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID",
                "Device.WiFi.SSID.{$wlanIndex}.SSID",
            ];
            foreach ($ssidCandidates as $path) {
                if ($this->pathExists($params, $path)) {
                    $paramsToSet[$path] = $this->wifiSsid;
                    break;
                }
            }
            if (!isset($paramsToSet[$ssidCandidates[0]]) && !isset($paramsToSet[$ssidCandidates[1]])) {
                $paramsToSet[$ssidCandidates[0]] = $this->wifiSsid;
            }
            
            if (!empty($this->wifiPassword) && !in_array($this->wifiSecurity, ['None', 'Basic', 'Open'])) {
                $passCandidates = [
                    "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase",
                    "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase",
                    "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase",
                ];
                $passSet = false;
                foreach ($passCandidates as $path) {
                    if ($this->pathExists($params, $path)) {
                        $paramsToSet[$path] = $this->wifiPassword;
                        $passSet = true;
                        break;
                    }
                }
                if (!$passSet && count($passCandidates) > 0) {
                    $paramsToSet[$passCandidates[0]] = $this->wifiPassword;
                }
            }
            
            $secMode = $this->wifiSecurity;
            $tr098Modes = [
                'WPA2PSK' => '11i',
                'WPAPSKWPA2PSK' => 'WPAand11i',
                'Basic' => 'Basic',
                'None' => 'None',
            ];
            $tr181Modes = [
                'WPA2PSK' => 'WPA2-Personal',
                'WPAPSKWPA2PSK' => 'WPA-WPA2-Personal',
                'Basic' => 'WEP-128',
                'None' => 'None',
            ];

            $secCandidates = [
                "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.BeaconType" => $tr098Modes,
                "Device.WiFi.AccessPoint.{$wlanIndex}.Security.ModeEnabled" => $tr181Modes,
            ];

            foreach ($secCandidates as $path => $mapping) {
                if ($this->pathExists($params, $path)) {
                    $paramsToSet[$path] = $mapping[$secMode] ?? $secMode;
                    break;
                }
            }
            if (!isset($paramsToSet[array_keys($secCandidates)[0]]) && !isset($paramsToSet[array_keys($secCandidates)[1]])) {
                $paramsToSet[array_keys($secCandidates)[0]] = $tr098Modes[$secMode] ?? $secMode;
            }
            
            $trueDeviceId = $params['_id'] ?? $targetId;
            $success = $driver->setParameterValues((string) $trueDeviceId, $paramsToSet);
            
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => "change_wifi_{$wlanIndex}",
                'status' => $success ? 'completed' : 'failed',
                'created_by' => auth()->id()
            ]);

            if ($success) {
                session()->flash('success', 'SSID dan Password WiFi berhasil dikirim dan diterapkan ke perangkat!');
            } else {
                session()->flash('error', 'Task terkirim ke GenieACS tapi belum dikonfirmasi. Periksa status task.');
            }
            
            $this->showWifiModal = false;
            $this->cachedDeviceParams = null;
            $this->device->refresh();
            $this->loadDeviceStatus();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengganti WiFi: ' . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal mengganti WiFi: ' . $e->getMessage()]);
            $this->showWifiModal = false;
        }
    }

    public function rendering($view, $data)
    {
        $view->layout('layouts.enterprise');
    }

    public function render()
    {
        return view('livewire.reseller-portal.network.acs-device.show');
    }
}
