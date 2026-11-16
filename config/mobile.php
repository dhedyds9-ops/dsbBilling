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
        'latest_version_code' => 10105,
        'latest_version_name' => '1.1.5',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'http://localhost') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.5:\n- Pemilih Multi-SSID (SSID 1-4) & Mode Keamanan WiFi (WPA2, Mixed, Open Tanpa Password)\n- Upgrade Paket: filter kategori PPPoE & Hotspot, pembersihan paket voucher\n- Perangkat Terhubung: indikator SSID yang terhubung untuk setiap perangkat\n- Tampilan Dashboard: perbaikan tombol & status tagihan serta logo company\n- Kontak Bantuan: integrasi nomor resmi WhatsApp & Telepon admin ISP\n- Speed Test: optimasi antarmuka WiFiman dan deteksi akurat ISP.",
    ],
];
