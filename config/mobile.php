<?php

return [
    'staff' => [
        'latest_version_code' => 10136,
        'latest_version_name' => '1.1.36',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-technician.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.36:\n- OTA Auto-Update In-App: Pengunduhan dan instalasi pembaruan otomatis langsung di dalam aplikasi dilengkapi indikator persentase real-time, bebas hambatan perizinan OS.\n- Perbaikan Crash Bukti Kerja (Work Evidence): Mengatasi error ClassNotFoundException android.view.div pada dialog bukti kerja & evidensi teknisi.\n- Integrasi Penuh TR-069 GenieACS: Sinkronisasi SSID Wi-Fi, sandi radio, WAN PPPoE, Hotspot Bridge ke hardware modem ONU fisik (CWMP Fault 9005 & 9007 tuntas terselesaikan).\n- Akses Terpadu Sensus Area: Menu Sensus Area (Audit Fisik Lapangan, Cek Port ODP, Validasi GPS) resmi untuk Teknisi, Reseller, dan Administrator.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
