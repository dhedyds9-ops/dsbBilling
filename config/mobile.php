<?php

return [
    'staff' => [
        'latest_version_code' => 10103,
        'latest_version_name' => '1.1.3',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.3:\n- Redesign Wizard Instalasi PSB INETS 7 Tahap sesuai standar ISP\n- Tampilan modern Hero Header dengan tower illustration\n- Memperbaiki daftar paket layanan & penyesuaian insets tombol kembali hp\n- Sinkronisasi instan kredensial PPPoE dan ODP.",
    ],
    'customer' => [
        'latest_version_code' => 10103,
        'latest_version_name' => '1.1.3',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.3:\n- Pembaruan stabilitas dan performa koneksi Android 15 (SDK 35)\n- Akses cepat Profil, Tagihan, dan Layanan Pelanggan\n- Pengaturan Hotspot & Wi-Fi mandiri\n- Dukungan perbaikan invoice dan notifikasi real-time.",
    ],
];
