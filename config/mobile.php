<?php

return [
    'staff' => [
        'latest_version_code' => 10120,
        'latest_version_name' => '1.1.20',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.20:\n- Permintaan Cetak Voucher Reseller: Pengajuan batch cetak voucher hotspot langsung dari aplikasi/portal dengan estimasi modal, kalkulasi margin profit, dan verifikasi saldo deposit otomatis.\n- Perbaikan Katalog Paket Voucher: Seluruh paket voucher master ISP kini tampil lengkap dan responsif pada formulir permintaan voucher.\n- Peta Jaringan & Marker GIS: Interaktivitas klik marker ODP, ODC, OLT, dan pelanggan dengan detail modal informatif.\n- Form Work Order & SPK: Perapihan formulir penugasan teknisi dan batas kapasitas tugas lapangan.",
    ],
    'customer' => [
        'latest_version_code' => 10107,
        'latest_version_name' => '1.1.7',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.7:\n- Tampilan Baru Segar: Desain identitas visual Fresh Emerald & Modern Teal ramah pelanggan.\n- Smart Auto-Cluster Login: Masuk langsung tanpa perlu mengetik atau mengatur URL server secara manual.\n- Kartu Kemitraan Resmi ISP: Menampilkan legalitas & izin upstream Kominfo RI resmi tersinkronisasi otomatis dari landing web.\n- Stabilitas & Performa: Optimalisasi koneksi TR-069 WiFi Self-Service, Speedtest, dan pembayaran tagihan.",
    ],
];
