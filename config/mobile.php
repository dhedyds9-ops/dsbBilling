<?php

return [
    'staff' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.12:\n- Menu Ganti ONT (Swap) untuk Akun Reseller: Tersedia langsung pada baris menu Dashboard Reseller dan halaman detail perangkat ONU\n- Portal Reseller Web: Penambahan menu Ganti ONT (Swap) di sidebar Network & GIS serta tombol aksi langsung pada tabel Data ONU\n- Migrasi Otomatis: Transfer konfigurasi PPPoE, nama Wi-Fi (SSID), dan password ke modem baru secara otomatis tanpa konfigurasi ulang perangkat pelanggan\n- Isolasi Tenant Reseller: Pemilihan pelanggan dan perangkat ONT dibatasi secara ketat hanya untuk pelanggan milik reseller yang bersangkutan.",
    ],
    'customer' => [
        'latest_version_code' => 10106,
        'latest_version_name' => '1.1.6',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.6:\n- Rincian Tagihan Interaktif: Menampilkan rincian paket, nominal real, periode tagihan akurat, dan batas jatuh tempo langsung dari server\n- Payment Gateway Lengkap: Pilihan pembayaran instan otomatis (QRIS Realtime & Virtual Account) dan transfer rekening bank manual\n- Konfirmasi WhatsApp Otomatis: Salin nomor rekening satu-klik dan kirim konfirmasi bayar otomatis ke WhatsApp Admin\n- Pengajuan Upgrade Paket: Perbaikan pengajuan upgrade paket dengan pembuatan tiket layanan operasional otomatis\n- Paket Dashboard & Multi-SSID: Resolusi paket langganan aktif multi-jalur dan pemilihan SSID 1-4.",
    ],
];
