<?php

return [
    'staff' => [
        'latest_version_code' => 10105,
        'latest_version_name' => '1.1.5',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.5:\n- Penyesuaian penuh navigasi sistem Android (Diagnosa ONT, Stok Material, Tiket Gangguan & Speedtest)\n- Redesain tombol aksi Diagnosa ONT (Segarkan, Reboot, Wi-Fi, Swap, Speedtest) rapi 2-baris\n- Brand Logo ISP dinamis di header Wizard PSB\n- Filter cerdas paket wizard: hanya PPPoE & Hotspot (tanpa voucher)\n- Cerdas Hotspot: single input Username = Password otomatis.",
    ],
    'customer' => [
        'latest_version_code' => 10103,
        'latest_version_name' => '1.1.3',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.3:\n- Pembaruan stabilitas dan performa koneksi Android 15 (SDK 35)\n- Akses cepat Profil, Tagihan, dan Layanan Pelanggan\n- Pengaturan Hotspot & Wi-Fi mandiri\n- Dukungan perbaikan invoice dan notifikasi real-time.",
    ],
];
