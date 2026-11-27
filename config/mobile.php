<?php

return [
    'staff' => [
        'latest_version_code' => 10131,
        'latest_version_name' => '1.1.31',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-technician.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.31:\n- Paritas Menu Reseller Lengkap (12 Akses Cepat): Menghadirkan kembali User Online, Peta Jaringan, SpeedTest, dan 1-Klik Web Portal untuk akun Reseller.\n- Eliminasi Redundansi Menu: Tombol duplikat Komisi diganti dengan User Online karena Komisi sudah terpampang jelas pada kartu KPI utama.\n- Katalog Material Gudang Lengkap: Menampilkan seluruh stok barang di gudang cabang tanpa batas 20 item.\n- Perapian Tampilan Detail ONT: Nilai CPU & RAM yang tidak didukung ditampilkan sebagai 0% dengan tata letak ringkas, proporsional, dan tanpa overflow.\n- Monitoring Koneksi WAN Aktif: Menampilkan daftar koneksi WAN aktif (PPPoE/IPoE, IP WAN, status terhubung, dan VLAN) di dalam panel Pengaturan WAN.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
