<?php

return [
    'staff' => [
        'latest_version_code' => 10116,
        'latest_version_name' => '1.1.16',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://mstore.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.16:\n- Smart Auto-Cluster Discovery: Login langsung tanpa perlu input/ganti URL server (otomatis deteksi cluster MStore & Winets)\n- Dukungan Multi-Subdomain: Akun teknisi Winets & MStore dapat masuk secara mulus dari aplikasi yang sama\n- ODP & ODC Fiber Detail: Penyelarasan manajemen port dan topologi visual interaktif NOC\n- Perbaikan Foto Dokumentasi Jaringan: Pengunggahan foto Base64 permanen & sinkronisasi peta GIS.",
    ],
    'customer' => [
        'latest_version_code' => 10106,
        'latest_version_name' => '1.1.6',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.6:\n- Rincian Tagihan Interaktif: Menampilkan rincian paket, nominal real, periode tagihan akurat, dan batas jatuh tempo langsung dari server\n- Payment Gateway Lengkap: Pilihan pembayaran instan otomatis (QRIS Realtime & Virtual Account) dan transfer rekening bank manual\n- Konfirmasi WhatsApp Otomatis: Salin nomor rekening satu-klik dan kirim konfirmasi bayar otomatis ke WhatsApp Admin\n- Pengajuan Upgrade Paket: Perbaikan pengajuan upgrade paket dengan pembuatan tiket layanan operasional otomatis\n- Paket Dashboard & Multi-SSID: Resolusi paket langganan aktif multi-jalur dan pemilihan SSID 1-4.",
    ],
];
