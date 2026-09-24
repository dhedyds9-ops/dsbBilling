<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

// Add $availableWlans property
$content = str_replace(
    "public \$wlanTarget = '1';",
    "public \$wlanTarget = '1';\n    public \$availableWlans = [];",
    $content
);

// Add getAvailableWlans method
$getAvailableWlansHtml = <<<'PHP'
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
PHP;

// Inject method
$content = preg_replace(
    "/(public function openWifiModal\(\)\s*\{)/",
    $getAvailableWlansHtml . "\n\n    $1",
    $content
);

// Call it in openWifiModal
$content = preg_replace(
    "/(public function openWifiModal\(\)\s*\{\s*\\\$this->showWifiModal = true;\s*\\\$this->cachedDeviceParams = null;\s*\\\$this->loadWifiCredentials\(\);)/s",
    "public function openWifiModal()\n    {\n        \$this->showWifiModal = true;\n        \$this->cachedDeviceParams = null;\n        \$this->loadWifiCredentials();\n        \$this->availableWlans = \$this->getAvailableWlans();\n        if (!isset(\$this->availableWlans[\$this->wlanTarget])) {\n            \$this->wlanTarget = array_key_first(\$this->availableWlans) ?? '1';\n            \$this->loadWifiCredentials();\n        }\n    }",
    $content
);

file_put_contents($file, $content);
echo "Show.php updated with dynamic WLAN lists.\n";
