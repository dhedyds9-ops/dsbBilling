<?php

return [
    'staff' => [
        'latest_version_code' => 10102,
        'latest_version_name' => '1.1.2',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.2:\n- Perbaikan integrasi SPK & Tugas Harian Teknisi\n- Fitur Speedtest Wi-Fi & Internet terintegrasi\n- Ketahanan ACS TR-069 & MikroTik real-time\n- Optimasi performa dan stabilitas koneksi.",
    ],
    'customer' => [
        'latest_version_code' => 10102,
        'latest_version_name' => '1.1.2',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.2:\n- Pembaruan stabilitas dan performa koneksi Android 15 (SDK 35)\n- Akses cepat Profil, Tagihan, dan Layanan Pelanggan\n- Pengaturan Hotspot & Wi-Fi mandiri\n- Dukungan perbaikan invoice dan notifikasi real-time.",
    ],
];
