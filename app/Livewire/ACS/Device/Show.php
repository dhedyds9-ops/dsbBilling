<?php

namespace App\Livewire\ACS\Device;

use App\Livewire\AdminComponent;
use App\Models\ACS\ACSDevice;

class Show extends AdminComponent
{
    public $deviceId;
    public $device;

    public $wifiSsid = '';
    public $wifiPassword = '';
    public $showWifiModal = false;
    public $connectedDevices = [];
    public $deviceStatus = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'devices';
        $this->deviceId = $id;
        $this->device = ACSDevice::with([
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
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Devices', 'url' => route('acs.devices.index')],
            ['label' => $this->device->serial_number],
        ];
    }

    public function loadDeviceStatus()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $params = $driver->getDeviceParameters($this->device->uuid);
            
            $extract = function($path) use ($params) {
                $parts = explode('.', $path);
                $node = $params;
                foreach ($parts as $p) {
                    if (!is_array($node) || !array_key_exists($p, $node)) return null;
                    $node = $node[$p];
                }
                return isset($node['_value']) ? $node['_value'] : null;
            };

            // Common TR-069 Paths for PON
            $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                    ?? $extract('VirtualParameters.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower') 
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower')
                    ?? $extract('Device.Optical.1.Transceiver.RxPower');
            if ($rxPower !== null && is_numeric($rxPower)) {
                $rxPower = (float)$rxPower;
                if ($rxPower > 1000 || $rxPower < -1000) $rxPower /= 1000;
            }

            $txPower = $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.TxPower')
                    ?? $extract('Device.Optical.1.Transceiver.TxPower');
            if ($txPower !== null && is_numeric($txPower)) {
                $txPower = (float)$txPower;
                if ($txPower > 1000 || $txPower < -1000) $txPower /= 1000;
            }

            // PPPoE
            $pppoeUser = $extract('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username')
                      ?? $extract('Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username');
            $pppoePass = $extract('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password')
                      ?? $extract('Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password');
                      
            // SSIDs
            $ssid1 = $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID') ?? $extract('Device.WiFi.SSID.1.SSID');
            $ssid2 = $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID') ?? $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID') ?? $extract('Device.WiFi.SSID.2.SSID');

            $this->deviceStatus = [
                'rx_power' => $rxPower !== null ? round($rxPower, 2) . ' dBm' : '-',
                'tx_power' => $txPower !== null ? round($txPower, 2) . ' dBm' : '-',
                'pppoe_username' => $pppoeUser ?: '-',
                'pppoe_password' => $pppoePass ?: '-',
                'ssid_1' => $ssid1 ?: '-',
                'ssid_2' => $ssid2 ?: '-',
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
    public $wifiSaving = false;
    public $cachedDeviceParams = null;

    public function openWifiModal()
    {
        $this->showWifiModal = true;
        $this->cachedDeviceParams = null;
        $this->loadWifiCredentials();
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
            
            // Cache params agar tidak perlu fetch ulang saat save
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($this->device->uuid);
            }
            $params = $this->cachedDeviceParams;
            
            $wlanIndex = $this->wlanTarget === '1' ? '1' : '5';
            
            // Coba beberapa path untuk SSID
            $this->wifiSsid = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID")
                ?? $this->extractParam($params, "Device.WiFi.SSID.{$wlanIndex}.SSID")
                ?? '';
            
            // Coba beberapa path untuk Password
            $this->wifiPassword = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase")
                ?? $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase")
                ?? '';

            // Coba path Security
            $this->wifiSecurity = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.BeaconType")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.ModeEnabled")
                ?? 'WPA2PSK';
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengambil data WiFi saat ini: ' . $e->getMessage());
        }
    }

    public function saveWifi()
    {
        $this->validate([
            'wifiSsid' => 'required|string|min:3',
            'wifiPassword' => 'nullable|string|min:8',
            'wifiSecurity' => 'required|string',
        ]);

        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $wlanIndex = $this->wlanTarget === '1' ? 1 : 5;
            
            // Fetch parameter device untuk deteksi path yang benar
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($this->device->uuid);
            }
            $params = $this->cachedDeviceParams;
            
            // === KIRIM SSID + PASSWORD DALAM 1 TASK (1 connection_request) ===
            // Ini jauh lebih cepat daripada 2 task terpisah!
            $paramsToSet = [];
            
            // Deteksi path SSID yang benar
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
            // Fallback: tetap coba path pertama
            if (!isset($paramsToSet[$ssidCandidates[0]]) && !isset($paramsToSet[$ssidCandidates[1]])) {
                $paramsToSet[$ssidCandidates[0]] = $this->wifiSsid;
            }
            
            // Deteksi path Password yang benar jika password diisi (dan bukan Open)
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
                // Fallback
                if (!$passSet && count($passCandidates) > 0) {
                    $paramsToSet[$passCandidates[0]] = $this->wifiPassword;
                }
            }
            
            // Translasi Security Mode berdasarkan versi TR
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
            // Fallback
            if (!isset($paramsToSet[array_keys($secCandidates)[0]]) && !isset($paramsToSet[array_keys($secCandidates)[1]])) {
                $paramsToSet[array_keys($secCandidates)[0]] = $tr098Modes[$secMode] ?? $secMode;
            }
            
            // Dapatkan ID device yang sebenarnya (karena bisa jadi uuid/mac bukan OUI-PC-SN)
            $trueDeviceId = $params['_id'] ?? $this->device->uuid;
            
            // Kirim SSID + Password sekaligus dalam 1 task!
            $success = $driver->setParameterValues($trueDeviceId, $paramsToSet);
            
            // Record task dengan status yang sesuai
            \App\Models\ACS\DeviceTask::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'acs_device_id' => $this->device->id,
                'type' => "change_wifi_{$wlanIndex}",
                'status' => $success ? 'completed' : 'failed',
                'created_by' => auth()->id()
            ]);

            if ($success) {
                session()->flash('success', '✅ SSID dan Password WiFi berhasil dikirim dan diterapkan ke perangkat!');
            } else {
                session()->flash('error', 'Task terkirim ke GenieACS tapi belum dikonfirmasi. Periksa status task di GenieACS.');
            }
            
            $this->showWifiModal = false;
            $this->cachedDeviceParams = null; // Reset cache
            $this->device->refresh();
            $this->loadDeviceStatus(); // Refresh status display
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengganti WiFi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.acs.device.show');
    }
}
