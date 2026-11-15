<?php

return [
    'staff' => [
        'latest_version_code' => 10106,
        'latest_version_name' => '1.1.6',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.6:\n- Penuntasan status SPK otomatis menjadi Selesai (Completed) saat aktivasi Wizard PSB berhasil\n- Resolusi otomatis tiket gangguan/penugasan dan SPK CRM saat instalasi tuntas\n- Auto-create dan aktivasi pelanggan baru (Customer & CustomerService) langsung dari pengerjaan wizard\n- Pencatatan otomatis pemakaian material kabel dropcore dan fast connector ke inventori operasional.",
    ],
    'customer' => [
        'latest_version_code' => 10103,
        'latest_version_name' => '1.1.3',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.3:\n- Pembaruan stabilitas dan performa koneksi Android 15 (SDK 35)\n- Akses cepat Profil, Tagihan, dan Layanan Pelanggan\n- Pengaturan Hotspot & Wi-Fi mandiri\n- Dukungan perbaikan invoice dan notifikasi real-time.",
    ],
];
