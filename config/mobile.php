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
        'latest_version_code' => 10106,
        'latest_version_name' => '1.1.6',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.6:\n- Rincian Tagihan Interaktif: Menampilkan rincian paket, nominal real, periode tagihan akurat, dan batas jatuh tempo langsung dari server\n- Payment Gateway Lengkap: Pilihan pembayaran instan otomatis (QRIS Realtime & Virtual Account) dan transfer rekening bank manual\n- Konfirmasi WhatsApp Otomatis: Salin nomor rekening satu-klik dan kirim konfirmasi bayar otomatis ke WhatsApp Admin\n- Pengajuan Upgrade Paket: Perbaikan pengajuan upgrade paket dengan pembuatan tiket layanan operasional otomatis\n- Paket Dashboard & Multi-SSID: Resolusi paket langganan aktif multi-jalur dan pemilihan SSID 1-4.",
    ],
];
