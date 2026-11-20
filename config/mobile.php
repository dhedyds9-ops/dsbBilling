<?php

return [
    'staff' => [
        'latest_version_code' => 10119,
        'latest_version_name' => '1.1.19',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.19:\n- Peta Jaringan HD & Foto ODP: Dukungan pratinjau foto langsung dalam peta (Lightbox preview) tanpa popup error dan resolusi Base64/Storage path yang aman.\n- Pengelolaan Perangkat TR-069: Tombol Hapus berfungsi optimal, sinkronisasi penghapusan perangkat soft-delete, dan aksi diagnosa/ganti ONT.\n- Keamanan Stok Material & Custody: Alokasi modem Wizard PSB eksklusif untuk stok di tangan teknisi/reseller (mencegah pemilihan stok gudang pusat tanpa otorisasi).\n- Perbaikan SPK Lapangan: Memperbaiki kendala pemuatan daftar SPK/Work Order dan sinkronisasi tugas aktif.\n- Cakupan Detail Modem Reseller: Izin melihat detail diagnostik modem baru/unassigned dari OLT untuk kemudahan aktivasi mitra reseller.",
    ],
    'customer' => [
        'latest_version_code' => 10107,
        'latest_version_name' => '1.1.7',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.7:\n- Tampilan Baru Segar: Desain identitas visual Fresh Emerald & Modern Teal ramah pelanggan.\n- Smart Auto-Cluster Login: Masuk langsung tanpa perlu mengetik atau mengatur URL server secara manual.\n- Kartu Kemitraan Resmi ISP: Menampilkan legalitas & izin upstream Kominfo RI resmi tersinkronisasi otomatis dari landing web.\n- Stabilitas & Performa: Optimalisasi koneksi TR-069 WiFi Self-Service, Speedtest, dan pembayaran tagihan.",
    ],
];
