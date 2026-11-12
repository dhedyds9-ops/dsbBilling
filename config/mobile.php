<?php

return [
    'staff' => [
        'latest_version_code' => 10101,
        'latest_version_name' => '1.1.1',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.1:\n- Sinkronisasi real-time TR-069 GenieACS & MikroTik\n- Perbaikan modul SPK Lapangan, Diagnosa ONT, & Peta Jaringan\n- Dukungan Dark Mode & Light Mode yang konsisten\n- Optimasi performa dan stabilitas koneksi.",
    ],
    'customer' => [
        'latest_version_code' => 10101,
        'latest_version_name' => '1.1.1',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.1:\n- Navigasi 6 tab lengkap (Home, Tagihan, Notifikasi, Tiket, Profile, Logout)\n- Akses cepat Profil & Logout akun langsung\n- Menu Perangkat: Deteksi klien Wi-Fi ONU (PPPoE) & Sesi MikroTik (Hotspot)\n- Pengaturan Hotspot: Ubah nama pengguna & sandi login Radius mandiri\n- Informasi tagihan, invoice riil, dan pembaruan paket internet.",
    ],
];
