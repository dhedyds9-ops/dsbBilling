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
        'hisfocus'  => \App\Services\Adapters\Provisioning\Drivers\HisfocusOltDriver::class,
        'his-focus' => \App\Services\Adapters\Provisioning\Drivers\HisfocusOltDriver::class,
        'hisfocu'   => \App\Services\Adapters\Provisioning\Drivers\HisfocusOltDriver::class,
        'c-data'    => \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver::class,
        'cdata'     => \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver::class,
        'cdat'      => \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver::class,
        'default'   => \App\Services\Adapters\Provisioning\Drivers\HsgqOltDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Unsupported OLT Vendors (Fail-Fast)
    |--------------------------------------------------------------------------
    |
    | Vendor yang BELUM memiliki driver native. Sebelumnya vendor ini dipetakan
    | secara senyap ke driver HSGQ sehingga menghasilkan OID/MIB yang salah.
    | Kini OltRegistry akan menolak (fail-fast) agar kekeliruan tidak tersembunyi.
    |
    | Jika perangkat Anda sebenarnya adalah rebrand/OEM HSGQ, ubah vendor OLT
    | tersebut menjadi 'HSGQ' agar ditangani driver yang tepat.
    |
    */
    'unsupported_vendors' => [
        'huawei',
        'fiberhome',
        'nokia',
    ],

    /*
    |--------------------------------------------------------------------------
    | Raw CLI Guard (Endpoint /execute-command)
    |--------------------------------------------------------------------------
    |
    | Daftar prefix perintah CLI yang diizinkan dieksekusi melalui endpoint
    | API execute-command. Peran Super Admin dikecualikan dari pembatasan ini.
    | Default dibatasi pada perintah inspeksi (read-only) demi keamanan.
    |
    */
    'cli' => [
        'allowed_command_prefixes' => [
            'show ',
            'display ',
            'dis ',
        ],
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
