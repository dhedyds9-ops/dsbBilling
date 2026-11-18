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
        'latest_version_code' => 10107,
        'latest_version_name' => '1.1.7',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.7:\n- Tampilan Baru Segar: Desain identitas visual Fresh Emerald & Modern Teal ramah pelanggan.\n- Smart Auto-Cluster Login: Masuk langsung tanpa perlu mengetik atau mengatur URL server secara manual.\n- Kartu Kemitraan Resmi ISP: Menampilkan legalitas & izin upstream Kominfo RI resmi tersinkronisasi otomatis dari landing web.\n- Stabilitas & Performa: Optimalisasi koneksi TR-069 WiFi Self-Service, Speedtest, dan pembayaran tagihan.",
    ],
];
