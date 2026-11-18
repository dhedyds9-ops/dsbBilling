<?php

return [
    'staff' => [
        'latest_version_code' => 10117,
        'latest_version_name' => '1.1.17',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://mstore.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.17:\n- Fitur Keamanan Akun: Ubah profil/username login dan ganti kata sandi akun staf/teknisi/reseller langsung dari aplikasi mobile.\n- Smart Auto-Cluster Discovery: Deteksi otomatis multi-server cluster MStore & Winets.\n- ODP & ODC Fiber Detail: Penyelarasan manajemen port dan topologi visual interaktif GIS.\n- Perbaikan Foto Dokumentasi Jaringan & Stabilitas TR-069 ACS.",
    ],
    'customer' => [
        'latest_version_code' => 10106,
        'latest_version_name' => '1.1.6',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.6:\n- Rincian Tagihan Interaktif: Menampilkan rincian paket, nominal real, periode tagihan akurat, dan batas jatuh tempo langsung dari server\n- Payment Gateway Lengkap: Pilihan pembayaran instan otomatis (QRIS Realtime & Virtual Account) dan transfer rekening bank manual\n- Konfirmasi WhatsApp Otomatis: Salin nomor rekening satu-klik dan kirim konfirmasi bayar otomatis ke WhatsApp Admin\n- Pengajuan Upgrade Paket: Perbaikan pengajuan upgrade paket dengan pembuatan tiket layanan operasional otomatis\n- Paket Dashboard & Multi-SSID: Resolusi paket langganan aktif multi-jalur dan pemilihan SSID 1-4.",
    ],
];
