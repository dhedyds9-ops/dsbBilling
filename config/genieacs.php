<?php

return [
    'base_url' => env('GENIEACS_BASE_URL', 'http://localhost:7557'),
    'username' => env('GENIEACS_USERNAME', 'admin'),
    'password' => env('GENIEACS_PASSWORD', 'admin'),
    'timeout'  => (int)env('GENIEACS_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default CPE provisioning presets
    |--------------------------------------------------------------------------
    | Settingan default untuk ONT/ONU yang baru connect ke GenieACS:
    | - Management URL / ACS URL untuk redirect TR-069
    | - Connection request username/password (CPE ← GenieACS)
    */
    'cpe_defaults' => [
        'acs_url' => env('GENIEACS_PUBLIC_CWMP_URL', 'http://genieacs.example.com:7547'),
        'connection_request_username' => env('GENIEACS_CPE_USER', 'dsbilling_cwmp'),
        'connection_request_password' => env('GENIEACS_CPE_PASS', 'ChangeMePlease!123'),
        'periodic_inform_interval_seconds' => (int)env('GENIEACS_INFORM_INTERVAL', 300),
    ],
];
