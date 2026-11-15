<?php

return [
    'staff' => [
        'latest_version_code' => 10107,
        'latest_version_name' => '1.1.7',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.7:\n- Penyempurnaan tombol SPK menjadi ringkas 'Mulai Install' & 'Lanjutkan Install'\n- Perbaikan safe area navigation bar pada detail SPK agar tidak tertutup tombol navigasi Android\n- Tahap 1 Wizard: nama pelanggan, kontak WhatsApp, dan alamat instalasi kini dapat diedit fleksibel\n- Dukungan tombol 'Cari Pelanggan' untuk memilih pelanggan yang sudah terdaftar di dsBilling\n- Sinkronisasi otomatis paket layanan dan pembuatan akun pengguna ke tabel Pelanggan PPPoE & Hotspot.",
    ],
    'customer' => [
        'latest_version_code' => 10103,
        'latest_version_name' => '1.1.3',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.3:\n- Pembaruan stabilitas dan performa koneksi Android 15 (SDK 35)\n- Akses cepat Profil, Tagihan, dan Layanan Pelanggan\n- Pengaturan Hotspot & Wi-Fi mandiri\n- Dukungan perbaikan invoice dan notifikasi real-time.",
    ],
];
