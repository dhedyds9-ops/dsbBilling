<?php

return [
    'staff' => [
        'latest_version_code' => 10104,
        'latest_version_name' => '1.1.4',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.4:\n- Normalisasi KPI Dashboard Teknisi (Stok Tangan bersih dari label Suspend/Billing)\n- Penjelasan dan pemisahan tegas SPK Berjalan vs SPK Selesai pada Tugas Harian\n- Integrasi langsung detail SPK Lapangan dengan Wizard Instalasi PSB 7 Tahap\n- Pemindai Barcode Kamera In-App (ZXing embedded) tanpa butuh aplikasi luar.",
    ],
    'customer' => [
        'latest_version_code' => 10103,
        'latest_version_name' => '1.1.3',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.3:\n- Pembaruan stabilitas dan performa koneksi Android 15 (SDK 35)\n- Akses cepat Profil, Tagihan, dan Layanan Pelanggan\n- Pengaturan Hotspot & Wi-Fi mandiri\n- Dukungan perbaikan invoice dan notifikasi real-time.",
    ],
];
