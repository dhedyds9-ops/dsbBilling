<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ONU Vendor Wi-Fi Parameters
    |--------------------------------------------------------------------------
    |
    | This file contains the TR-069 parameter paths for Wi-Fi configuration
    | for different ONU vendors. You can add more vendors as needed.
    |
    */
    'vendors' => [
        'zte' => [
            'name' => 'ZTE',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.ModeEnabled',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.KeyPassphrase',
            ],
        ],
        'huawei' => [
            'name' => 'Huawei',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.ModeEnabled',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Security.KeyPassphrase',
            ],
        ],
        'fiberhome' => [
            'name' => 'Fiberhome',
            'wifi_parameters' => [
                'ssid' => 'Device.WiFi.SSID.1.SSID',
                'security_mode' => 'Device.WiFi.AccessPoint.1.Security.ModeEnabled',
                'wpa_pre_shared_key' => 'Device.WiFi.AccessPoint.1.Security.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'Device.WiFi.AccessPoint.1.Security.KeyPassphrase',
            ],
        ],
        'dlink' => [
            'name' => 'D-Link',
            'wifi_parameters' => [
                'ssid' => 'Device.WiFi.SSID.1.SSID',
                'security_mode' => 'Device.WiFi.AccessPoint.1.Security.ModeEnabled',
                'wpa_pre_shared_key' => 'Device.WiFi.AccessPoint.1.Security.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'Device.WiFi.AccessPoint.1.Security.KeyPassphrase',
            ],
        ],
        'ggclink' => [
            'name' => 'GGClink',
            'wifi_parameters' => [
                'ssid' => 'Device.WiFi.SSID.1.SSID',
                'security_mode' => 'Device.WiFi.AccessPoint.1.Security.ModeEnabled',
                'wpa_pre_shared_key' => 'Device.WiFi.AccessPoint.1.Security.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'Device.WiFi.AccessPoint.1.Security.KeyPassphrase',
            ],
        ],
        'default' => [
            'name' => 'Default',
            'wifi_parameters' => [
                'ssid' => 'Device.WiFi.SSID.1.SSID',
                'security_mode' => 'Device.WiFi.AccessPoint.1.Security.ModeEnabled',
                'wpa_pre_shared_key' => 'Device.WiFi.AccessPoint.1.Security.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'Device.WiFi.AccessPoint.1.Security.KeyPassphrase',
            ],
        ],
    ],
];
