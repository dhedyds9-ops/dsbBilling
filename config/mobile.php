<?php

return [
    'staff' => [
        'latest_version_code' => 10118,
        'latest_version_name' => '1.1.18',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://mstore.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.18:\n- Integrasi SPK & Wizard PSB: Pemisahan jelas tombol status SPK (Mulai Pengerjaan / Selesaikan SPK) dan tombol teknis (Buka Wizard PSB 7 Tahap).\n- Sinkronisasi Paket Layanan: Tampilan paket internet riil pelanggan dan alamat/koordinat akurat tanpa dummy.\n- Pembaruan Otomatis & Performa TR-069: Stabilitas integrasi GenieACS port 7547 dan sinkronisasi tugas lapangan.",
    ],
    'customer' => [
        'latest_version_code' => 10107,
        'latest_version_name' => '1.1.7',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.7:\n- Tampilan Baru Segar: Desain identitas visual Fresh Emerald & Modern Teal ramah pelanggan.\n- Smart Auto-Cluster Login: Masuk langsung tanpa perlu mengetik atau mengatur URL server secara manual.\n- Kartu Kemitraan Resmi ISP: Menampilkan legalitas & izin upstream Kominfo RI resmi tersinkronisasi otomatis dari landing web.\n- Stabilitas & Performa: Optimalisasi koneksi TR-069 WiFi Self-Service, Speedtest, dan pembayaran tagihan.",
    ],
];
