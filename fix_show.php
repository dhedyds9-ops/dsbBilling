<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

// 1. Add $wifiSecurity property
$content = str_replace(
    'public $wifiPassword;',
    "public \$wifiPassword;\n    public \$wifiSecurity = 'WPA2PSK';",
    $content
);

// 2. Add wifiSecurity to loadWifiCredentials
$loadSearch = <<<'PHP'
            // Coba beberapa path untuk Password
            $this->wifiPassword = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase")
                ?? $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase")
                ?? '';
PHP;
$loadSearch = str_replace("\r\n", "\n", $loadSearch);

$loadReplace = <<<'PHP'
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
PHP;
$content = str_replace($loadSearch, $loadReplace, $content);

// 3. Add wifiSecurity validation and saving
$saveSearch = <<<'PHP'
        $this->validate([
            'wifiSsid' => 'required|string|min:3',
            'wifiPassword' => 'nullable|string|min:8',
        ]);
PHP;
$saveSearch = str_replace("\r\n", "\n", $saveSearch);

$saveReplace = <<<'PHP'
        $this->validate([
            'wifiSsid' => 'required|string|min:3',
            'wifiPassword' => 'nullable|string|min:8',
            'wifiSecurity' => 'required|string',
        ]);
PHP;
$content = str_replace($saveSearch, $saveReplace, $content);

$passSearch = <<<'PHP'
            // Deteksi path Password yang benar jika password diisi
            if (!empty($this->wifiPassword)) {
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
PHP;
$passSearch = str_replace("\r\n", "\n", $passSearch);

$passReplace = <<<'PHP'
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
            
            // Set Security Mode
            $secCandidates = [
                "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.BeaconType",
                "Device.WiFi.AccessPoint.{$wlanIndex}.Security.ModeEnabled",
            ];
            foreach ($secCandidates as $path) {
                if ($this->pathExists($params, $path)) {
                    $paramsToSet[$path] = $this->wifiSecurity;
                    break;
                }
            }
            if (!isset($paramsToSet[$secCandidates[0]]) && !isset($paramsToSet[$secCandidates[1]])) {
                $paramsToSet[$secCandidates[0]] = $this->wifiSecurity;
            }
PHP;
$content = str_replace($passSearch, $passReplace, $content);

file_put_contents($file, $content);
echo "Show.php updated.\n";
