<?php

return [
    'staff' => [
        'latest_version_code' => 10135,
        'latest_version_name' => '1.1.35',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-technician.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.35:\n- Perbaikan Kritis Crash Bukti Kerja (Work Evidence): Memperbaiki bug InflateException (ClassNotFoundException android.view.div) pada dialog upload evidensi dan foto pekerjaan teknisi.\n- Perbaikan Kritis Provisioning TR-069 GenieACS: Konfigurasi SSID Wi-Fi, sandi radio, WAN PPPoE, dan Hotspot Bridge 100% terkirim dan diaplikasikan ke hardware modem ONU fisik (mengeliminasi CWMP Fault 9005 dan 9007).\n- Akses Terpadu Sensus Area: Menu Sensus Area (Audit Fisik Lapangan, Cek Port ODP, Validasi GPS) kini resmi dapat diakses oleh Teknisi, Reseller, dan Administrator di Android & Web Portal.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
