<?php

return [
    'staff' => [
        'latest_version_code' => 10130,
        'latest_version_name' => '1.1.30',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-technician.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.30:\n- Optimalisasi Dasbor Reseller: 4 Kartu KPI resmi (Pelanggan Aktif, Omzet Layanan, Komisi Reseller, Tagihan Belum Lunas), 8 Akses Cepat Grid 2x4 responsif tanpa teks terpotong, dan isolasi ketat menu teknisi.\n- Penyelarasan Penuh Komisi Reseller Web-Mobile: Integrasi SSOT IncomeReportService, pemisahan Omzet Kotor, Komisi Bersih, Setoran Modal Pusat, Rasio Margin, filter periode & kategori, serta rincian struk per transaksi.\n- Discovery Lengkap Multi-SSID ONT: Penemuan seluruh SSID (1..4 pada 2.4GHz dan 5..8 pada 5GHz) pada ZTE, Huawei, FiberHome TR-098 & TR-181 dengan proteksi tabrakan indeks dan verifikasi Read-After-Write.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
