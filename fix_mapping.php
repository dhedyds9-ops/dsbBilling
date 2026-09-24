<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

$search = <<<'PHP'
    public function getWifiParameterPath(string $vendor, string $parameter): string
    {
        $vendor = strtolower($vendor);
        
        $maps = [
            'zte' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
            ],
            'huawei' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
            ],
            'fiberhome' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
            ],
        ];

        return $maps[$vendor][$parameter] ?? $maps['zte'][$parameter] ?? '';
    }
PHP;

$replace = <<<'PHP'
    public function getVendorParameterPath(string $vendor, string $parameter): string
    {
        $vendor = strtolower($vendor);
        // Map vendor name from OUI if it's not straightforward
        if (str_contains($vendor, 'zte')) $vendor = 'zte';
        elseif (str_contains($vendor, 'huawei')) $vendor = 'huawei';
        elseif (str_contains($vendor, 'fiberhome')) $vendor = 'fiberhome';
        
        $config = config('acs_vendors.' . $vendor, config('acs_vendors.default', []));
        
        return $config[$parameter] ?? config('acs_vendors.default.' . $parameter, '');
    }

    public function getWifiParameterPath(string $vendor, string $parameter): string
    {
        return $this->getVendorParameterPath($vendor, $parameter);
    }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated to use config mapping.\n";
