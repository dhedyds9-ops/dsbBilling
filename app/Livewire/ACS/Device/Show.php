<?php

namespace App\Livewire\ACS\Device;

use App\Livewire\AdminComponent;
use App\Models\ACS\ACSDevice;

class Show extends \App\Livewire\ACS\BaseACSComponent
{
    public $deviceId;
    public $device;

    public $wifiEnabled = true;
    public $wifiHidden = false;
    public $wifiSsid = '';
    public $wifiPassword = '';
    public $wifiSecurity = 'WPA_WPA2'; // WPA_WPA2, WPA2, WPA, OPEN
    public $showWifiModal = false;
    public $connectedDevices = [];
    public $deviceStatus = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'devices';

        $relations = [
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
        ];

        $device = null;
        if (is_numeric($id)) {
            $device = ACSDevice::withoutGlobalScopes()->with($relations)->find($id);
        }

        if (!$device) {
            $device = ACSDevice::withoutGlobalScopes()->with($relations)
                ->where(function ($q) use ($id) {
                    $q->where('serial_number', $id)
                      ->orWhere('uuid', $id);
                })
                ->first();
        }

        // Cek apakah ada record yang ter-soft delete (trashed), jika ada restore kembali
        if (!$device) {
            $trashed = ACSDevice::withoutGlobalScopes()->withTrashed()
                ->where(function ($q) use ($id) {
                    $q->where('serial_number', $id)
                      ->orWhere('uuid', $id);
                })
                ->first();

            if ($trashed) {
                if ($trashed->trashed()) {
                    $trashed->restore();
                }
                $device = ACSDevice::withoutGlobalScopes()->with($relations)->find($trashed->id);
            }
        }

        if (!$device) {
            $onu = \App\Models\ISP\Onu::withoutGlobalScopes()
                ->where('serial_number', $id)
                ->orWhere('id', $id)
                ->first();

            if ($onu && !empty($onu->serial_number)) {
                // Cek apakah perangkat sudah ada di acs_devices (termasuk trashed/global scope)
                $existingDevice = ACSDevice::withoutGlobalScopes()->withTrashed()
                    ->where(function ($q) use ($onu) {
                        $q->where('serial_number', $onu->serial_number)
                          ->orWhere('uuid', $onu->serial_number)
                          ->orWhere('onu_id', $onu->id);
                    })
                    ->first();

                if ($existingDevice) {
                    if ($existingDevice->trashed()) {
                        $existingDevice->restore();
                    }
                    $existingDevice->update([
                        'serial_number' => $onu->serial_number,
                        'mac_address' => $onu->mac_address ?? $existingDevice->mac_address,
                        'onu_id' => $onu->id,
                        'olt_id' => $onu->olt_id ?? $existingDevice->olt_id,
                        'odp_id' => $onu->odp_id ?? $existingDevice->odp_id,
                        'customer_service_id' => $onu->customerService?->id ?? $existingDevice->customer_service_id,
                        'status' => $existingDevice->status ?? ($onu->status === 'active' ? 'online' : 'offline'),
                    ]);
                    $device = ACSDevice::withoutGlobalScopes()->with($relations)->find($existingDevice->id);
                } else {
                    $newDevice = ACSDevice::create([
                        'uuid' => $onu->serial_number,
                        'serial_number' => $onu->serial_number,
                        'mac_address' => $onu->mac_address,
                        'onu_id' => $onu->id,
                        'olt_id' => $onu->olt_id,
                        'odp_id' => $onu->odp_id,
                        'customer_service_id' => $onu->customerService?->id,
                        'status' => $onu->status === 'active' ? 'online' : 'offline',
                    ]);
                    $device = ACSDevice::withoutGlobalScopes()->with($relations)->find($newDevice->id);
                }
            }
        }

        if (!$device) {
            abort(404, 'Perangkat GenieACS tidak ditemukan.');
        }

        $this->device = $device;
        $this->deviceId = $device->id;
        
        $this->loadConnectedDevices();
        $this->loadDeviceStatus();
        
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Devices', 'url' => route('acs.devices.index')],
            ['label' => $this->device->serial_number],
        ];
    }

    private function parseOpticalPower($val): ?float
    {
        if ($val === null || $val === '' || $val === false) return null;
        $clean = trim(str_ireplace('dBm', '', (string)$val));
        if (!is_numeric($clean)) return null;
        $num = (float)$clean;
        if ($num == 0.0) return null;
        if ($num < -500.0 || $num > 500.0) {
            return round($num / 1000.0, 2);
        }
        if ($num < -60.0 || $num > 60.0) {
            return round($num / 100.0, 2);
        }
        if ($num > 0.0 && $num < 10000.0) {
            // Micro-watts to dBm: 10 * log10(uW / 1000)
            return round(10.0 * log10($num / 1000.0), 2);
        }
        return round($num, 2);
    }

    public function loadDeviceStatus()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $params = $driver->getDeviceParameters($this->device->uuid);
            
            // Flatten all parameters for multi-vendor heuristic lookup (matching genieacs-mobile)
            $flat = [];
            $flatten = function ($data, $prefix = '') use (&$flatten, &$flat) {
                if (!is_array($data)) return;
                foreach ($data as $k => $v) {
                    if ($k === '_timestamp' || $k === '_object' || $k === '_writable') continue;
                    $key = $prefix ? "$prefix.$k" : (string) $k;
                    if (is_array($v)) {
                        if (isset($v['_value']) && is_scalar($v['_value'])) {
                            $flat[$key] = (string) $v['_value'];
                        }
                        $flatten($v, $key);
                    } elseif (is_scalar($v)) {
                        $flat[$key] = (string) $v;
                    }
                }
            };
            $flatten($params);

            // 1. RX & TX Optical Power (Multi-Vendor + VirtualParameters + uW log10 conversion)
            $rawRx = $flat['VirtualParameters.RXPower']
                ?? $flat['VirtualParameters.redaman']
                ?? $flat['InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower']
                ?? $flat['InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower']
                ?? $flat['InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower']
                ?? $flat['Device.Optical.1.Transceiver.RxPower']
                ?? collect($flat)->first(fn($v, $k) => (str_contains($k, 'RXPower') || str_contains($k, 'OpticalRxPower') || str_contains($k, 'RxPower')) && !empty($v));
            $rxPower = $this->parseOpticalPower($rawRx);

            $rawTx = $flat['VirtualParameters.TXPower']
                ?? $flat['InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower']
                ?? $flat['InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower']
                ?? $flat['InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower']
                ?? $flat['Device.Optical.1.Transceiver.TxPower']
                ?? collect($flat)->first(fn($v, $k) => (str_contains($k, 'TXPower') || str_contains($k, 'OpticalTxPower') || str_contains($k, 'TxPower')) && !empty($v));
            $txPower = $this->parseOpticalPower($rawTx);

            // 2. PPPoE Credentials
            $pppoeUser = $flat['VirtualParameters.pppoeUsername']
                ?? $flat['VirtualParameters.pppUsername']
                ?? $flat['VirtualParameters.pppoe_username']
                ?? $flat['VirtualParameters.Username']
                ?? $flat['VirtualParameters.getpppoeuser']
                ?? collect($flat)->first(fn($v, $k) => str_contains($k, 'WANPPPConnection') && str_ends_with($k, 'Username') && !empty($v))
                ?? collect($flat)->first(fn($v, $k) => str_contains($k, 'PPP.Interface') && str_ends_with($k, 'Username') && !empty($v))
                ?? '-';

            $pppoePass = $flat['VirtualParameters.pppoePassword']
                ?? $flat['VirtualParameters.pppPassword']
                ?? collect($flat)->first(fn($v, $k) => str_contains($k, 'WANPPPConnection') && str_ends_with($k, 'Password') && !empty($v))
                ?? '-';

            // 3. SuperAdmin (Web GUI) Account Extraction
            $adminUser = $flat['VirtualParameters.superAdmin']
                ?? $flat['InternetGatewayDevice.DeviceInfo.X_CMCC_TeleComAccount.Username']
                ?? $flat['InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.UserName']
                ?? $flat['InternetGatewayDevice.DeviceInfo.X_CT-COM_TeleComAccount.Username']
                ?? $flat['InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminName']
                ?? $flat['InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperUsername']
                ?? 'admin';

            $adminPass = $flat['VirtualParameters.superPassword']
                ?? $flat['InternetGatewayDevice.DeviceInfo.X_CMCC_TeleComAccount.Password']
                ?? $flat['InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.Password']
                ?? $flat['InternetGatewayDevice.DeviceInfo.X_CT-COM_TeleComAccount.Password']
                ?? $flat['InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminPassword']
                ?? $flat['InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperPassword']
                ?? $flat['InternetGatewayDevice.UserInterface.Password']
                ?? 'admin';

            // 4. Customer Name
            $customerName = $flat['VirtualParameters.customerName']
                ?? $flat['VirtualParameters.CustomerName']
                ?? $flat['VirtualParameters.namaPelanggan']
                ?? $flat['VirtualParameters.nama']
                ?? $this->device->customerService?->customer?->name
                ?? '-';

            // 5. WAN IP & PON MAC
            $wanIp = $flat['VirtualParameters.pppoeIP']
                ?? collect($flat)->first(fn($v, $k) => str_contains($k, 'ExternalIPAddress') && $v !== '0.0.0.0' && !empty($v))
                ?? '-';

            $cleanMac = function ($val) {
                if (empty($val) || !is_string($val)) return null;
                $val = trim($val);
                if ($val === '' || $val === '00:00:00:00:00:00' || $val === 'ff:ff:ff:ff:ff:ff' || strtoupper($val) === 'FF:FF:FF:FF:FF:FF') {
                    return null;
                }
                $hexOnly = preg_replace('/[^0-9A-Fa-f]/', '', $val);
                if (strlen($hexOnly) === 12) {
                    return strtoupper(implode(':', str_split($hexOnly, 2)));
                }
                if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $val)) {
                    return strtoupper(str_replace('-', ':', $val));
                }
                return null;
            };

            $wanMac = $cleanMac($flat['VirtualParameters.PonMac'] ?? null)
                   ?? $cleanMac($flat['VirtualParameters.ponMac'] ?? null)
                   ?? $cleanMac($flat['VirtualParameters.pppoeMac'] ?? null)
                   ?? $cleanMac($flat['VirtualParameters.MacAddress'] ?? null)
                   ?? $cleanMac($flat['InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.PonMac'] ?? null)
                   ?? $cleanMac(collect($flat)->first(fn($v, $k) => str_contains($k, 'MACAddress') && !str_contains($k, 'Host') && !empty($v)))
                   ?? $cleanMac($this->device->mac_address);

            if ($wanMac && empty($this->device->mac_address)) {
                $this->device->update(['mac_address' => $wanMac]);
            }

            // 6. SSIDs (2.4G & 5G)
            $ssid1 = $flat['VirtualParameters.SSID']
                ?? $flat['VirtualParameters.WlanSSID']
                ?? $flat['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID']
                ?? collect($flat)->first(fn($v, $k) => str_contains($k, 'WLANConfiguration.1.SSID') && !empty($v))
                ?? '-';

            $ssid2 = $flat['InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID']
                ?? $flat['InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID']
                ?? collect($flat)->first(fn($v, $k) => (str_contains($k, 'WLANConfiguration.5.SSID') || str_contains($k, 'WLANConfiguration.2.SSID')) && !empty($v))
                ?? '-';

            $this->deviceStatus = [
                'rx_power' => $rxPower !== null ? round((float)$rxPower, 2) . ' dBm' : '-',
                'tx_power' => $txPower !== null ? round((float)$txPower, 2) . ' dBm' : '-',
                'pppoe_username' => (string) $pppoeUser,
                'pppoe_password' => (string) $pppoePass,
                'superadmin_user' => (string) $adminUser,
                'superadmin_password' => (string) $adminPass,
                'customer_name' => (string) $customerName,
                'ssid_1' => (string) $ssid1,
                'ssid_2' => (string) $ssid2,
                'wan_ip' => (string) $wanIp,
                'wan_mac' => (string) ($wanMac ?? '-'),
            ];
        } catch (\Exception $e) {
            $this->deviceStatus = [];
        }
    }

    public function loadConnectedDevices()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $this->connectedDevices = $driver->getConnectedDevices($this->device->uuid);
        } catch (\Exception $e) {
            $this->connectedDevices = [];
        }
    }

    public function summonDevice()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $driver->summonDevice($this->device->uuid);
            
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'connection_request',
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            session()->flash('success', 'Perintah Summon (Connection Request) telah dikirim ke perangkat.');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Summon dikirim.']);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim perintah Summon: ' . $e->getMessage());
        }
    }

    public function rebootDevice()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $driver->rebootDevice($this->device->uuid);
            
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'reboot',
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            session()->flash('success', 'Perintah reboot telah dikirim ke perangkat.');
            $this->device->refresh();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim perintah reboot: ' . $e->getMessage());
        }
    }

    public function factoryResetDevice()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $driver->factoryResetDevice($this->device->uuid);
            
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'factory_reset',
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            session()->flash('success', 'Perintah factory reset telah dikirim ke perangkat.');
            $this->device->refresh();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim perintah factory reset: ' . $e->getMessage());
        }
    }

    public $wlanTarget = '1';
    public $availableWlans = [];
    public $wifiSaving = false;
    public $cachedDeviceParams = null;

        private function getAvailableWlans()
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

    public function openWifiModal()
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

    public function updatedWlanTarget()
    {
        $this->loadWifiCredentials();
    }

    /**
     * Helper: extract value dari parameter tree GenieACS
     */
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

    /**
     * Helper: cek apakah path ada di parameter tree device
     */
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

    public function loadWifiCredentials()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($this->device->uuid);
            }
            $params = $this->cachedDeviceParams;
            
            $wlanIndex = $this->wlanTarget;
            $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.";
            
            $this->wifiEnabled = (bool) ($this->extractParam($params, $prefix . "Enable") ?? true);
            
            $hiddenVal = $this->extractParam($params, $prefix . "SSIDAdvertisementEnabled");
            $this->wifiHidden = ($hiddenVal === false || $hiddenVal === '0' || $hiddenVal === 0 || $hiddenVal === 'false');
            
            $this->wifiSsid = 
                $this->extractParam($params, $prefix . "SSID")
                ?? $this->extractParam($params, "Device.WiFi.SSID.{$wlanIndex}.SSID")
                ?? ($wlanIndex == 1 ? ($this->extractParam($params, "VirtualParameters.WlanSSID") ?? $this->extractParam($params, "VirtualParameters.SSID")) : '')
                ?? '';
            
            $this->wifiPassword = 
                $this->extractParam($params, $prefix . "PreSharedKey.1.KeyPassphrase")
                ?? $this->extractParam($params, $prefix . "KeyPassphrase")
                ?? $this->extractParam($params, $prefix . "PreSharedKey.1.PreSharedKey")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase")
                ?? ($wlanIndex == 1 ? $this->extractParam($params, "VirtualParameters.WlanPassword") : '')
                ?? '';

            $rawSecurity = 
                $this->extractParam($params, $prefix . "BeaconType")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.ModeEnabled")
                ?? '';
            
            if (in_array($rawSecurity, ['11i', 'WPA2-Personal', 'WPA2', 'WPA2PSK'])) {
                $this->wifiSecurity = 'WPA2';
            } elseif (in_array($rawSecurity, ['WPAand11i', 'WPA-WPA2-Personal', 'WPA/WPA2', 'WPAand11i_Personal', 'WPAPSKWPA2PSK'])) {
                $this->wifiSecurity = 'WPA_WPA2';
            } elseif (in_array($rawSecurity, ['WPA', 'WPA-Personal'])) {
                $this->wifiSecurity = 'WPA';
            } elseif (in_array($rawSecurity, ['None', 'Open', 'Basic'])) {
                $this->wifiSecurity = 'OPEN';
            } else {
                $this->wifiSecurity = 'WPA_WPA2';
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengambil data WiFi saat ini: ' . $e->getMessage());
        }
    }

    public function saveWifi()
    {
        $this->validate([
            'wifiSsid' => 'required|string|min:2|max:32',
            'wifiPassword' => $this->wifiSecurity === 'OPEN' ? 'nullable' : 'required|string|min:8|max:63',
            'wifiSecurity' => 'required|in:WPA_WPA2,WPA2,WPA,OPEN',
        ]);

        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $wlanIndex = (int) $this->wlanTarget;
            $vendor = $this->device->vendor?->name ?? 'default';

            $success = $driver->updateWifiSsidAndPassword(
                $this->device->uuid,
                $this->wifiSsid,
                $this->wifiSecurity === 'OPEN' ? '' : $this->wifiPassword,
                $vendor,
                $wlanIndex,
                $this->wifiSecurity,
                $this->wifiHidden,
                $this->wifiEnabled
            );

            // Record task
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => "change_wifi_{$wlanIndex}",
                'status' => $success ? 'completed' : 'failed',
                'created_by' => auth()->id()
            ]);

            if ($success) {
                session()->flash('success', "✅ Konfigurasi WiFi SSID {$wlanIndex} ({$this->wifiSsid}) berhasil dikirim ke perangkat modem!");
            } else {
                session()->flash('warning', 'Task terkirim ke antrean GenieACS. Modem akan memperbarui konfigurasi saat check-in.');
            }
            
            $this->showWifiModal = false;
            $this->cachedDeviceParams = null;
            $this->device->refresh();
            $this->loadDeviceStatus();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengganti WiFi: ' . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal mengganti WiFi: ' . $e->getMessage()]);
            $this->showWifiModal = false;
        }
    }

    public function render()
    {
        return view('livewire.acs.device.show');
    }
}
