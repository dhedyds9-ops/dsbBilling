<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

// Add wifiEnabled property
$content = str_replace(
    "public \$wifiSsid = '';",
    "public \$wifiEnabled = true;\n    public \$wifiSsid = '';",
    $content
);

// In loadWifiCredentials, set wifiEnabled
$content = preg_replace(
    "/(public function loadWifiCredentials.*?\\\$this->wifiSsid =)/s",
    "$1\n                \$this->wifiEnabled = (bool) (\$this->extractParam(\$params, \"InternetGatewayDevice.LANDevice.1.WLANConfiguration.{\\\$wlanIndex}.Enable\") ?? true);\n                \$this->wifiSsid =",
    $content
);

// Add logic to save wifiEnabled inside updateWifi()
// We need to set InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable
$content = preg_replace(
    "/(public function updateWifi.*?\\\$paramsToSet = \[\];)/s",
    "$1\n\n            // Set WiFi Enable/Disable state\n            \$vendor = \$this->device->vendor ?? 'default';\n            \$enablePath = str_replace('.1.Enable', '.' . \$wlanIndex . '.Enable', \$driver->getVendorParameterPath(\$vendor, 'wlan_enable'));\n            if (\$enablePath) {\n                \$paramsToSet[\$enablePath] = (bool) \$this->wifiEnabled;\n            }",
    $content
);

file_put_contents($file, $content);
echo "Show.php updated with wifiEnabled logic.\n";
