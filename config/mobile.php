<?php

return [
    'staff' => [
        'latest_version_code' => 10114,
        'latest_version_name' => '1.1.14',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.14:\n- Perbaikan Filter Layanan Pelanggan: Penyelarasan daftar pelanggan PPPoE dan Hotspot sesuai jenis layanan aktif\n- Menu Ganti ONT (Swap) Reseller: Dukungan penuh penggantian modem atau aksesoris (adaptor/patchcord) langsung dari aplikasi\n- Sinkronisasi Status Tr-069 & OLT: Deteksi live status ONT, RX Power, dan konfigurasi IP Host otomatis\n- Notifikasi & Penugasan: Push notification untuk tiket gangguan, instalasi PSB, dan surat tugas teknisi/reseller.",
    ],
    'customer' => [
        'latest_version_code' => 10106,
        'latest_version_name' => '1.1.6',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.6:\n- Rincian Tagihan Interaktif: Menampilkan rincian paket, nominal real, periode tagihan akurat, dan batas jatuh tempo langsung dari server\n- Payment Gateway Lengkap: Pilihan pembayaran instan otomatis (QRIS Realtime & Virtual Account) dan transfer rekening bank manual\n- Konfirmasi WhatsApp Otomatis: Salin nomor rekening satu-klik dan kirim konfirmasi bayar otomatis ke WhatsApp Admin\n- Pengajuan Upgrade Paket: Perbaikan pengajuan upgrade paket dengan pembuatan tiket layanan operasional otomatis\n- Paket Dashboard & Multi-SSID: Resolusi paket langganan aktif multi-jalur dan pemilihan SSID 1-4.",
    ],
];
