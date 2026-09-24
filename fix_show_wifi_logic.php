<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

// 1. Fix loadWifiCredentials
$loadWifiRegex = '/public function loadWifiCredentials\(\).*?catch \(\\\Exception \$e\) \{/s';

$loadWifiReplacement = <<<'PHP'
public function loadWifiCredentials()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($this->device->uuid);
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

            $this->wifiSecurity = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.BeaconType")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.ModeEnabled")
                ?? 'WPA2PSK';
            
        } catch (\Exception $e) {
PHP;

$content = preg_replace($loadWifiRegex, $loadWifiReplacement, $content);

// 2. Fix saveWifi $wlanIndex
$content = preg_replace("/\\\$wlanIndex = \\\$this->wlanTarget === '1' \? 1 : 5;/", "\$wlanIndex = \$this->wlanTarget;", $content);

file_put_contents($file, $content);
echo "Show.php loadWifiCredentials and saveWifi fixed.\n";
