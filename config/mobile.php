<?php

return [
    'staff' => [
        'latest_version_code' => 10111,
        'latest_version_name' => '1.1.11',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.11:\n- Enterprise ONT Lifecycle (Sensus, Swap ONT, Dismantle ONT): Integrasi penuh idempotensi transaction_id, mutasi stok teknisi (InventoryCustody), rekalkulasi port ODP, dan antrean GenieACS\n- Speedtest WiFiman Akurat: Internet Node menampilkan real Public IP & ISP tanpa data dummy\n- Access Point Node: Menampilkan default gateway aktif jaringan yang dipakai\n- Device Node: Menampilkan nama model perangkat fisik asli dan IP lokal akurat\n- Network Latency Live Ping: Pengukuran latensi real-time ke Google, Facebook, X, Gateway, YouTube, TikTok, WhatsApp & Mobile Legends\n- Auto Server Detection: Otomatis mendeteksi node server ISP riil\n- Struk Thermal Bluetooth: Menampilkan logo company web dan nama brand ISP\n- Header Pill Wilayah: Menyederhanakan tampilan indikator hanya menampilkan nama wilayah operasional\n- Diagnosa GenieACS: Perbaikan notifikasi error/snackbar mengambang di atas navbar dan perbaikan otorisasi Summon teknisi.",
    ],
    'customer' => [
        'latest_version_code' => 10105,
        'latest_version_name' => '1.1.5',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.5:\n- Pemilih Multi-SSID (SSID 1-4) & Mode Keamanan WiFi (WPA2, Mixed, Open Tanpa Password)\n- Upgrade Paket: filter kategori PPPoE & Hotspot, pembersihan paket voucher\n- Perangkat Terhubung: indikator SSID yang terhubung untuk setiap perangkat\n- Tampilan Dashboard: perbaikan tombol & status tagihan serta logo company\n- Kontak Bantuan: integrasi nomor resmi WhatsApp & Telepon admin ISP\n- Speed Test: optimasi antarmuka WiFiman dan deteksi akurat ISP.",
    ],
];
