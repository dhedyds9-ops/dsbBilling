<?php

return [
    'staff' => [
        'latest_version_code' => 10127,
        'latest_version_name' => '1.1.27',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-technician.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.27:\n- Operational Control Center: Monitoring pekerjaan lapangan real-time untuk Admin & NOC langsung dari aplikasi (Tiket, SPK, QC, SLA Breached, dan Teknisi aktif).\n- PSB Mandiri Reseller: Registrasi ONT mandiri untuk Reseller tanpa kewajiban SPK/presensi, sedangkan Teknisi tetap terjaga integritas SPK dan presensi aktif.\n- Pencarian Pelanggan Tiket Gangguan: Pemilih pelanggan interaktif dengan pencarian instan pada pembuatan tiket gangguan oleh Reseller & Admin.\n- Eliminasi Redundansi Menu: Penataan grid 12 menu presisi untuk setiap role (Reseller, Teknisi, Admin) bebas duplikasi aksi.\n- Tombol Pintas SPK: Akses langsung pengerjaan SPK lapangan dari detail tiket gangguan.\n- Push Notification FCM HTTP v1: Notifikasi real-time penugasan SPK, verifikasi QC, dan eskalasi SLA.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
