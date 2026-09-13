<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

// Remove the public property
$content = preg_replace('/public \$cachedDeviceParams = null;\s*/', '', $content);

// Replace $this->cachedDeviceParams with local variable
// In loadWifiCredentials:
$content = str_replace(
    'if (!$this->cachedDeviceParams) {
            $this->cachedDeviceParams = $driver->getDeviceParameters($device->uuid);
        }
        $params = $this->cachedDeviceParams;',
    '$params = $driver->getDeviceParameters($device->uuid);',
    $content
);

// In saveWifi:
$content = str_replace(
    'if (!$this->cachedDeviceParams) {
            $this->cachedDeviceParams = $driver->getDeviceParameters($device->uuid);
        }
        $params = $this->cachedDeviceParams;',
    '$params = $driver->getDeviceParameters($device->uuid);',
    $content
);

file_put_contents($file, $content);
echo "Fixed UTF-8 error by removing cachedDeviceParams from public state.";
?>
