<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Default Roles (Single Source of Truth)
    |--------------------------------------------------------------------------
    |
    | Daftar role ini adalah role dasar sistem yang TIDAK BOLEH dihapus.
    |
    */
    'system_roles' => [
        'administrator',
        'manager',
        'reseller',
        'customer'
    ],

    /*
    |--------------------------------------------------------------------------
    | Hidden Roles in UI
    |--------------------------------------------------------------------------
    |
    | Role ini tidak boleh dimunculkan atau di-assign sembarangan lewat UI
    | manajemen pengguna.
    */
    'hidden_roles' => [
        'customer'
    ]
];
