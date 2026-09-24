<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OLT Vendor Driver Mapping
    |--------------------------------------------------------------------------
    |
    | Pemetaan nama vendor OLT (lowercase, slug) ke driver class yang sesuai.
    | Nama vendor di tabel vendors harus cocok dengan key di bawah ini.
    |
    */
    'drivers' => [
        'zte'       => \App\Services\Adapters\Provisioning\Drivers\ZteOltDriver::class,
        'ztexia'    => \App\Services\Adapters\Provisioning\Drivers\ZteOltDriver::class,
        'hsgq'      => \App\Services\Adapters\Provisioning\Drivers\HsgqOltDriver::class,
        'hao-seek'  => \App\Services\Adapters\Provisioning\Drivers\HsgqOltDriver::class,
        'haoseek'   => \App\Services\Adapters\Provisioning\Drivers\HsgqOltDriver::class,
        'v-sol'     => \App\Services\Adapters\Provisioning\Drivers\VsolOltDriver::class,
        'vsol'      => \App\Services\Adapters\Provisioning\Drivers\VsolOltDriver::class,
        'bt-pon'    => \App\Services\Adapters\Provisioning\Drivers\BtPonOltDriver::class,
        'btpon'     => \App\Services\Adapters\Provisioning\Drivers\BtPonOltDriver::class,
        'fujitomo'  => \App\Services\Adapters\Provisioning\Drivers\FujitomoOltDriver::class,
        'c-data'    => \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver::class,
        'cdata'     => \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver::class,
        'cdat'      => \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver::class,
        'fiberhome' => \App\Services\Adapters\Provisioning\Drivers\HsgqOltDriver::class,
        'huawei'    => \App\Services\Adapters\Provisioning\Drivers\HsgqOltDriver::class,
        'default'   => \App\Services\Adapters\Provisioning\Drivers\HsgqOltDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default OLT Connection Parameters
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'snmp_version'       => '2c',
        'snmp_community_read'  => 'public',
        'snmp_community_write' => 'private',
        'cli_mode'           => 'telnet',
        'timeout_seconds'    => 10,
        'polling_interval_minutes' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Alarm Thresholds (dBm / Celsius)
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'onu_rx_power_critical_low'  => -28.0,
        'onu_rx_power_warning_low'   => -25.0,
        'onu_rx_power_warning_high'  => -8.0,
        'onu_rx_power_critical_high' => -5.0,
        'olt_temperature_warning'    => 60.0,
        'olt_temperature_critical'   => 75.0,
    ],
];
