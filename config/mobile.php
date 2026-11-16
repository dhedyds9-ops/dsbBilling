<?php

return [
    'staff' => [
        'latest_version_code' => 10108,
        'latest_version_name' => '1.1.8',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.8:\n- Tahap 4: Deteksi otomatis SN ONT unconfigured langsung dari OLT & scan barcode fisik dengan auto-fill ke Tahap 5\n- Tahap 5: Pemilihan Mode Keamanan Wi-Fi (WPA2-Personal, Open/Tanpa Password untuk Hotspot, WPA/WPA2 Mixed)\n- Otomasi Cerdas: Paket Hotspot otomatis mengaktifkan mode Wi-Fi Open tanpa password dan menyamakan kredensial\n- Sinkronisasi lengkap profil PPPoE dan Hotspot ke sistem dsBilling.",
    ],
    'customer' => [
        'latest_version_code' => 10104,
        'latest_version_name' => '1.1.4',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.4:\n- Perbaikan bug force close / crash saat aplikasi dibuka\n- Perbaikan resolusi tema warna Material Components\n- Peningkatan kestabilan keamanan sesi dan token\n- Speed Test WiFiman Dark Theme dengan deteksi IP & ISP akurat.",
    ],
];
