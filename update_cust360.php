<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

// Add ACS properties if not exist
if (strpos($content, '$showWifiModal') === false) {
    $props = <<<'PHP'
    // ACS WiFi Modal state
    public $showWifiModal = false;
    public $acsDeviceIdForWifi = null;
    public $wlanTarget = '1';
    public $wifiSsid = '';
    public $wifiPassword = '';
    public $cachedDeviceParams = null;
PHP;
    $content = preg_replace('/(public \$edit_service_password_mode = \'custom\';)/', "$1\n\n$props", $content);
}

// Ensure acsDevice is loaded
$content = str_replace("'branch',", "'branch', 'customerServices.acsDevice',", $content);

// Add ACS methods
if (strpos($content, 'rebootModem(') === false) {
    $methods = <<<'PHP'
    // === ACS / TR-069 FUNCTIONS ===
    public function rebootModem($deviceId)
    {
        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($deviceId);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $driver->rebootDevice($device->uuid);
            session()->flash('success', 'Perintah reboot telah dikirim ke modem.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim perintah reboot: ' . $e->getMessage());
        }
    }

    public function resetModem($deviceId)
    {
        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($deviceId);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $driver->factoryResetDevice($device->uuid);
            session()->flash('success', 'Perintah Factory Reset telah dikirim ke modem.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal reset modem: ' . $e->getMessage());
        }
    }

    public function openWifiModal($deviceId)
    {
        $this->acsDeviceIdForWifi = $deviceId;
        $this->wlanTarget = '1';
        $this->cachedDeviceParams = null;
        $this->showWifiModal = true;
        $this->loadWifiCredentials();
    }
    
    public function updatedWlanTarget()
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

    public function loadWifiCredentials()
    {
        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($this->acsDeviceIdForWifi);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($device->uuid);
            }
            $params = $this->cachedDeviceParams;
            $wlanIndex = $this->wlanTarget === '1' ? '1' : '5';
            
            $this->wifiSsid = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID")
                ?? $this->extractParam($params, "Device.WiFi.SSID.{$wlanIndex}.SSID") ?? '';
            
            $this->wifiPassword = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase")
                ?? $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase") ?? '';
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengambil data WiFi: ' . $e->getMessage());
        }
    }

    public function saveWifi()
    {
        $this->validate([
            'wifiSsid' => 'required|string|min:3',
            'wifiPassword' => 'nullable|string|min:8',
        ]);

        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($this->acsDeviceIdForWifi);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $wlanIndex = $this->wlanTarget === '1' ? 1 : 5;
            
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($device->uuid);
            }
            $params = $this->cachedDeviceParams;
            $paramsToSet = [];
            
            $ssidCandidates = ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID", "Device.WiFi.SSID.{$wlanIndex}.SSID"];
            foreach ($ssidCandidates as $path) {
                if ($this->pathExists($params, $path)) {
                    $paramsToSet[$path] = $this->wifiSsid;
                    break;
                }
            }
            if (!isset($paramsToSet[$ssidCandidates[0]]) && !isset($paramsToSet[$ssidCandidates[1]])) {
                $paramsToSet[$ssidCandidates[0]] = $this->wifiSsid;
            }
            
            if (!empty($this->wifiPassword)) {
                $passCandidates = ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase", "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase", "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase"];
                $passSet = false;
                foreach ($passCandidates as $path) {
                    if ($this->pathExists($params, $path)) {
                        $paramsToSet[$path] = $this->wifiPassword;
                        $passSet = true;
                        break;
                    }
                }
                if (!$passSet) $paramsToSet[$passCandidates[0]] = $this->wifiPassword;
            }
            
            $trueDeviceId = $params['_id'] ?? $device->uuid;
            $driver->setParameterValues($trueDeviceId, $paramsToSet);

            $this->showWifiModal = false;
            session()->flash('success', 'Task perubahan WiFi berhasil dikirim ke modem!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim task WiFi: ' . $e->getMessage());
        }
    }
PHP;
    $content = preg_replace('/(public function render\(\))/', "$methods\n\n    $1", $content);
}

file_put_contents($file, $content);
echo "Updated Customer360.php";
?>
