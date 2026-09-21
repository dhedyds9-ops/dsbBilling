<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ACS Vendor Parameter Mappings
    |--------------------------------------------------------------------------
    |
    | Pemetaan TR-069 path untuk berbagai merek modem (OUI/Vendor).
    | Kunci utama di bawah ini menggunakan nama vendor (huruf kecil).
    |
    */

    'default' => [
        'wlan_enable' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable',
        'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
        'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
        
        // WAN Paths Base
        'wan_device' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice',
    ],

    'zte' => [
        'wlan_enable' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable',
        'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
        'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
        
        'wan_device' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice',
        'port_bind' => 'X_ZTE-COM_PortBind',
        'service_list' => 'X_ZTE-COM_ServiceList',
    ],

    'huawei' => [
        'wlan_enable' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable',
        'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
        'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',

        'wan_device' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice',
        'port_bind' => 'X_HW_LANBinding',
        'service_list' => 'X_HW_ServiceList',
    ],

    'fiberhome' => [
        'wlan_enable' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable',
        'ssid' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
        'wpa_passphrase' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'wpa_pre_shared_key' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
        'security_mode' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType', // Seringkali WPAEncryptionModes di FH

        'wan_device' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice',
        'port_bind' => 'X_FH_PortBind', // Asumsi path FiberHome
        'service_list' => 'X_FH_ServiceList',
    ],
];
