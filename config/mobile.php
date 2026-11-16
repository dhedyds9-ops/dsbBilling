<?php

return [
    'staff' => [
        'latest_version_code' => 10109,
        'latest_version_name' => '1.1.9',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-mobile-release.apk',
        'release_notes' => "dsBilling Staff & Teknisi v1.1.9:\n- Tahap 4: Deteksi seluruh Port PON OLT (bukan hanya PON 1) saat scan modem unconfigured\n- Sanitasi cerdas Serial Number untuk mencegah teks status OLT terbaca sebagai SN\n- Tahap 5: Auto-detection vendor/model ONT (ZTE F609, Huawei, FiberHome, C-Data) & SSID Wi-Fi bawaan WINETS-XXXX\n- Tahap 1: Filter ketat akun tamu/voucher hotspot dari dialog pencarian pelanggan terdaftar\n- Tahap 1: Ekstraksi otomatis kontak telepon/WA dan alamat dari catatan SPK lapangan\n- Sinkronisasi otomatis pemotongan stok/custody barang teknisi & reseller saat aktivasi selesai.",
    ],
    'customer' => [
        'latest_version_code' => 10104,
        'latest_version_name' => '1.1.4',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.4:\n- Perbaikan bug force close / crash saat aplikasi dibuka\n- Perbaikan resolusi tema warna Material Components\n- Peningkatan kestabilan keamanan sesi dan token\n- Speed Test WiFiman Dark Theme dengan deteksi IP & ISP akurat.",
    ],
];
