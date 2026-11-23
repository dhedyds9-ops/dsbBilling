<?php

return [
    'staff' => [
        'latest_version_code' => 10126,
        'latest_version_name' => '1.1.26',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-technician.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.26:\n- Redaman Optik Presisi: Normalisasi nilai daya RX/TX optik ZTE TR-069 dan integrasi nilai DDM OLT yang konsisten.\n- Multi-SSID Wi-Fi 5GHz: Sinkronisasi akurat pengaturan SSID 1 (2.4G) dan SSID 2 (5G) beserta deteksi mode keamanan (Open / WPA2).\n- Toleransi Summon TR-069: Menghilangkan kendala Server Error pada modem online melalui task-queue non-blocking.\n- Sinkronisasi C-DATA OLT: Perbaikan pembacaan status ONU online via SNMP dan akselerasi proses hapus ONU.\n- Menu Web Portal Teknisi: Pengaktifan kembali tombol 1-Klik SSO Web Portal di dashboard dan profil teknisi.\n- Peta & ODP: Integrasi tombol Ambil Titik GPS, Buka Peta Jaringan, dan Hubungkan ke ODP.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
