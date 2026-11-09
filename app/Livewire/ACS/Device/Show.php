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

    // PPPoE WAN Configuration State
    public $showPppoeModal = false;
    public $pppoeEditUsername = '';
    public $pppoeEditPassword = '';

    // Superadmin Configuration State
    public $showSuperadminModal = false;
    public $superadminEditUser = '';
    public $superadminEditPassword = '';

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

            // Helper untuk mengambil nilai string pertama yang tidak kosong
            $firstNonEmpty = function (...$candidates) {
                foreach ($candidates as $cand) {
                    if (is_scalar($cand)) {
                        $cand = trim((string) $cand);
                        if ($cand !== '' && $cand !== '-' && strtolower($cand) !== 'null' && strtolower($cand) !== 'undefined') {
                            return $cand;
                        }
                    }
                }
                return null;
            };

            // Heuristic scan untuk WANPPPConnection.*.Username
            $scanPppoeUser = collect($flat)->first(function ($v, $k) {
                return (str_contains($k, 'WANPPPConnection') || str_contains($k, 'PPP.Interface')) 
                    && str_ends_with($k, 'Username') 
                    && is_scalar($v) 
                    && trim((string)$v) !== '';
            });

            // Fallback database dsBilling (CustomerService / PPPoEUser / ONU)
            $dbUsername = $this->device->customerService?->username
                ?? $this->device->customerService?->pppoeUser?->username
                ?? $this->device->onu?->customerService?->username
                ?? $this->device->onu?->customerService?->pppoeUser?->username
                ?? null;

            if (!$dbUsername && !empty($this->device->customer_service_id)) {
                $cs = \App\Models\Customer\CustomerService::with('pppoeUser')->find($this->device->customer_service_id);
                $dbUsername = $cs?->username ?? $cs?->pppoeUser?->username;
            }

            if (!$dbUsername && !empty($this->device->serial_number)) {
                $onu = \App\Models\ISP\Onu::with('customerService.pppoeUser')->where('serial_number', $this->device->serial_number)->first();
                $dbUsername = $onu?->customerService?->username ?? $onu?->customerService?->pppoeUser?->username;
            }

            if (!$dbUsername && !empty($this->device->mac_address)) {
                $pppoeByMac = \App\Models\ISP\PPPoEUser::where('mac_address', $this->device->mac_address)->first();
                $dbUsername = $pppoeByMac?->username;
            }

            // 2. PPPoE Credentials Extraction
            $pppoeUser = $firstNonEmpty(
                $flat['VirtualParameters.pppoeUsername'] ?? null,
                $flat['VirtualParameters.pppoeUsername2'] ?? null,
                $flat['VirtualParameters.pppUsername'] ?? null,
                $flat['VirtualParameters.pppoe_username'] ?? null,
                $flat['VirtualParameters.Username'] ?? null,
                $flat['VirtualParameters.getpppoeuser'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Username'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.4.WANPPPConnection.1.Username'] ?? null,
                $flat['Device.PPP.Interface.1.Username'] ?? null,
                $flat['Device.PPP.Interface.2.Username'] ?? null,
                $scanPppoeUser,
                $dbUsername,
                '-'
            );

            // Heuristic scan untuk WANPPPConnection.*.Password
            $scanPppoePass = collect($flat)->first(function ($v, $k) {
                return (str_contains($k, 'WANPPPConnection') || str_contains($k, 'PPP.Interface')) 
                    && str_ends_with($k, 'Password') 
                    && is_scalar($v) 
                    && trim((string)$v) !== '';
            });

            // Fallback database dsBilling untuk Password
            $dbPassword = $this->device->customerService?->pppoeUser?->cleartext_password
                ?? $this->device->customerService?->pppoeUser?->password
                ?? $this->device->customerService?->password
                ?? $this->device->onu?->customerService?->pppoeUser?->cleartext_password
                ?? $this->device->onu?->customerService?->pppoeUser?->password
                ?? null;

            if (!$dbPassword && !empty($this->device->customer_service_id)) {
                $cs = \App\Models\Customer\CustomerService::with('pppoeUser')->find($this->device->customer_service_id);
                $dbPassword = $cs?->pppoeUser?->cleartext_password ?? $cs?->pppoeUser?->password ?? $cs?->password;
            }

            if (!$dbPassword && !empty($this->device->serial_number)) {
                $onu = \App\Models\ISP\Onu::with('customerService.pppoeUser')->where('serial_number', $this->device->serial_number)->first();
                $dbPassword = $onu?->customerService?->pppoeUser?->cleartext_password ?? $onu?->customerService?->pppoeUser?->password ?? $onu?->customerService?->password;
            }

            if (!$dbPassword && !empty($this->device->mac_address)) {
                $pppoeByMac = \App\Models\ISP\PPPoEUser::where('mac_address', $this->device->mac_address)->first();
                $dbPassword = $pppoeByMac?->cleartext_password ?? $pppoeByMac?->password;
            }

            $pppoePass = $firstNonEmpty(
                $flat['VirtualParameters.pppoePassword'] ?? null,
                $flat['VirtualParameters.pppoePassword2'] ?? null,
                $flat['VirtualParameters.pppPassword'] ?? null,
                $flat['VirtualParameters.pppoe_password'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Password'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Password'] ?? null,
                $flat['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.4.WANPPPConnection.1.Password'] ?? null,
                $flat['Device.PPP.Interface.1.Password'] ?? null,
                $flat['Device.PPP.Interface.2.Password'] ?? null,
                $scanPppoePass,
                $dbPassword,
                '-'
            );

            // 3. SuperAdmin (Web GUI) Account Extraction
            $adminUser = $firstNonEmpty(
                $flat['VirtualParameters.superAdmin'] ?? null,
                $flat['InternetGatewayDevice.DeviceInfo.X_CMCC_TeleComAccount.Username'] ?? null,
                $flat['InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.UserName'] ?? null,
                $flat['InternetGatewayDevice.DeviceInfo.X_CT-COM_TeleComAccount.Username'] ?? null,
                $flat['InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminName'] ?? null,
                $flat['InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperUsername'] ?? null,
                'admin'
            );

            $adminPass = $firstNonEmpty(
                $flat['VirtualParameters.superPassword'] ?? null,
                $flat['InternetGatewayDevice.DeviceInfo.X_CMCC_TeleComAccount.Password'] ?? null,
                $flat['InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.Password'] ?? null,
                $flat['InternetGatewayDevice.DeviceInfo.X_CT-COM_TeleComAccount.Password'] ?? null,
                $flat['InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminPassword'] ?? null,
                $flat['InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperPassword'] ?? null,
                $flat['InternetGatewayDevice.UserInterface.Password'] ?? null,
                'admin'
            );

            // Sanitize: Jika adminUser ternyata berisi string password FiberHome default (%0|F?H@f!berh03e) atau sama dengan adminPass,
            // itu berarti VirtualParameter superAdmin salah mapping ke field password di GenieACS. Kembalikan ke 'admin'.
            if ($adminUser === '%0|F?H@f!berh03e' || (strlen($adminUser) > 10 && $adminUser === $adminPass)) {
                $fhUser = $flat['InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperUsername'] ?? null;
                $adminUser = ($fhUser && $fhUser !== '%0|F?H@f!berh03e') ? $fhUser : 'admin';
            }

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
            
            // 1. Kirim instruksi refresh Hosts & WLAN ke modem via TR-069 Connection Request
            $driver->refreshConnectedDevicesTask($this->device->uuid);

            // 2. Ambil data klien terhubung dari GenieACS
            $this->connectedDevices = $driver->getConnectedDevices($this->device->uuid);

            // Catat task
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'refresh_hosts',
                'status' => 'completed',
                'created_by' => auth()->id()
            ]);

            $count = count($this->connectedDevices);
            session()->flash('success', "📡 Permintaan refresh klien berhasil dikirim ke modem! ({$count} klien aktif)");
            $this->dispatch('notify', [
                'type' => 'success', 
                'message' => "📡 Permintaan refresh klien berhasil dikirim ke modem! ({$count} klien aktif)"
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal merefresh klien: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error', 
                'message' => 'Gagal merefresh klien: ' . $e->getMessage()
            ]);
        }
    }

    public function summonDevice()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $res = $driver->summonDeviceResult($this->device->uuid);
            
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'connection_request',
                'status' => ($res['connection_request_success'] || $res['task_queued']) ? 'completed' : 'pending',
                'created_by' => auth()->id()
            ]);

            if ($res['connection_request_success']) {
                session()->flash('success', '📡 Summon live berhasil dikirim! Modem sedang mengirim data live terbaru...');
                // Jeda 2 detik agar Inform TR-069 masuk ke GenieACS lalu perbarui data di layar (sama seperti dsbilling-mobile)
                usleep(2000000);
                $this->cachedDeviceParams = null;
                $this->loadDeviceStatus();
                $this->loadConnectedDevices();
            } elseif ($res['task_queued']) {
                session()->flash('info', 'Perintah refresh parameter berhasil diantrikan ke antrean GenieACS.');
            } else {
                session()->flash('warning', 'Summon dikirim ke GenieACS. Tunggu modem merespons.');
            }
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Summon selesai diproses.']);
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

    public function openPppoeModal()
    {
        $this->pppoeEditUsername = (!empty($this->deviceStatus['pppoe_username']) && $this->deviceStatus['pppoe_username'] !== '-') 
            ? $this->deviceStatus['pppoe_username'] 
            : '';
        $this->pppoeEditPassword = (!empty($this->deviceStatus['pppoe_password']) && $this->deviceStatus['pppoe_password'] !== '***' && $this->deviceStatus['pppoe_password'] !== '-') 
            ? $this->deviceStatus['pppoe_password'] 
            : '';
        $this->showPppoeModal = true;
    }

    private function findPppoePaths(array $params): array
    {
        $candidates = [
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.4.WANPPPConnection.1',
            'InternetGatewayDevice.WANDevice.2.WANConnectionDevice.1.WANPPPConnection.1',
            'Device.PPP.Interface.1',
            'Device.PPP.Interface.2',
        ];

        foreach ($candidates as $cand) {
            if ($this->pathExists($params, $cand . '.Username') || $this->pathExists($params, $cand . '.Enable')) {
                return [
                    'username' => $cand . '.Username',
                    'password' => $cand . '.Password',
                ];
            }
        }

        // Jika FiberHome, WAN PPP route default umumnya berada di WANConnectionDevice.2 atau .1
        $isFh = str_contains(strtolower($this->device->manufacturer ?? ''), 'fiberhome') 
             || str_starts_with(strtoupper($this->device->serial_number ?? ''), 'FHTT');
        $candIndex = $isFh ? '2' : '1';

        return [
            'username' => "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.{$candIndex}.WANPPPConnection.1.Username",
            'password' => "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.{$candIndex}.WANPPPConnection.1.Password",
        ];
    }

    public function savePppoe()
    {
        $this->validate([
            'pppoeEditUsername' => 'required|string|min:2|max:64',
            'pppoeEditPassword' => 'required|string|min:3|max:64',
        ]);

        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($this->device->uuid);
            }
            $params = $this->cachedDeviceParams;
            $paths = $this->findPppoePaths($params);

            $paramsToSet = [
                $paths['username'] => trim($this->pppoeEditUsername),
                $paths['password'] => (string) $this->pppoeEditPassword,
            ];

            $success = $driver->setParameterValues($this->device->uuid, $paramsToSet);

            // Record task
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'change_pppoe_credentials',
                'status' => $success ? 'completed' : 'pending',
                'created_by' => auth()->id()
            ]);

            // Sync to PPPoE User & CustomerService record in database
            if ($this->device->customerService) {
                $this->device->customerService->update([
                    'username' => trim($this->pppoeEditUsername),
                    'password' => (string) $this->pppoeEditPassword,
                ]);
                if ($this->device->customerService->pppoeUser) {
                    $this->device->customerService->pppoeUser->update([
                        'username' => trim($this->pppoeEditUsername),
                        'password' => (string) $this->pppoeEditPassword,
                        'cleartext_password' => (string) $this->pppoeEditPassword,
                    ]);
                }
            } elseif (!empty($this->device->customer_service_id)) {
                $cs = \App\Models\Customer\CustomerService::with('pppoeUser')->find($this->device->customer_service_id);
                if ($cs) {
                    $cs->update([
                        'username' => trim($this->pppoeEditUsername),
                        'password' => (string) $this->pppoeEditPassword,
                    ]);
                    $cs->pppoeUser?->update([
                        'username' => trim($this->pppoeEditUsername),
                        'password' => (string) $this->pppoeEditPassword,
                        'cleartext_password' => (string) $this->pppoeEditPassword,
                    ]);
                }
            }

            $this->showPppoeModal = false;
            $this->cachedDeviceParams = null;
            $this->loadDeviceStatus();
            session()->flash('success', '✅ Konfigurasi PPPoE WAN berhasil dikirim ke modem via TR-069!');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Konfigurasi PPPoE WAN berhasil dikirim ke modem!']);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah PPPoE: ' . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal mengubah PPPoE: ' . $e->getMessage()]);
        }
    }

    public function openSuperadminModal()
    {
        $this->superadminEditUser = (!empty($this->deviceStatus['superadmin_user']) && $this->deviceStatus['superadmin_user'] !== '-') 
            ? $this->deviceStatus['superadmin_user'] 
            : 'admin';
        $this->superadminEditPassword = (!empty($this->deviceStatus['superadmin_password']) && $this->deviceStatus['superadmin_password'] !== '-') 
            ? $this->deviceStatus['superadmin_password'] 
            : '';
        $this->showSuperadminModal = true;
    }

    public function saveSuperadmin()
    {
        $this->validate([
            'superadminEditUser' => 'required|string|min:2|max:32',
            'superadminEditPassword' => 'required|string|min:3|max:32',
        ]);

        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $manufacturer = strtolower((string)($this->device->manufacturer ?? ''));

            $paramsToSet = [];
            $u = trim($this->superadminEditUser);
            $p = (string) $this->superadminEditPassword;

            if (str_contains($manufacturer, 'fiberhome') || str_contains($manufacturer, 'fh') || str_starts_with(strtoupper($this->device->serial_number ?? ''), 'FHTT')) {
                $paramsToSet['InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperUsername'] = $u;
                $paramsToSet['InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperPassword'] = $p;
            } elseif (str_contains($manufacturer, 'huawei') || str_contains($manufacturer, 'hw') || str_starts_with(strtoupper($this->device->serial_number ?? ''), 'HWTC')) {
                $paramsToSet['InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.UserName'] = $u;
                $paramsToSet['InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.Password'] = $p;
            } elseif (str_contains($manufacturer, 'zte') || str_starts_with(strtoupper($this->device->serial_number ?? ''), 'ZTEG')) {
                $paramsToSet['InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminName'] = $u;
                $paramsToSet['InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminPassword'] = $p;
            } else {
                $paramsToSet['InternetGatewayDevice.UserInterface.Password'] = $p;
            }

            $success = $driver->setParameterValues($this->device->uuid, $paramsToSet);

            // Record task
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => 'change_superadmin_credentials',
                'status' => $success ? 'completed' : 'pending',
                'created_by' => auth()->id()
            ]);

            $this->showSuperadminModal = false;
            $this->cachedDeviceParams = null;
            $this->loadDeviceStatus();
            session()->flash('success', '✅ Kredensial Web SuperAdmin berhasil dikirim ke modem via TR-069!');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Kredensial Web SuperAdmin berhasil dikirim ke modem!']);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah SuperAdmin: ' . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal mengubah SuperAdmin: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.acs.device.show');
    }
}
