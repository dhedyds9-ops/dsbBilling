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
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
        'huawei' => [
            'name' => 'Huawei',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
        'alcl' => [
            'name' => 'Nokia/Alcatel-Lucent',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
        'hsgq' => [
            'name' => 'HSGQ',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
        'fiberhome' => [
            'name' => 'Fiberhome',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
        'dlink' => [
            'name' => 'D-Link',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
        'zioncom' => [
            'name' => 'ZIONCOM',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
        'default' => [
            'name' => 'Default',
            'wifi_parameters' => [
                'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
                'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            ],
        ],
    ],
];

