<?php

return [
    'staff' => [
        'latest_version_code' => 10123,
        'latest_version_name' => '1.1.23',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.21:\n- Khusus Paket Voucher: Filter katalog diperketat sehingga hanya paket bertipe Voucher yang tampil (paket PPPoE dan Hotspot member otomatis disaring keluar).\n- Dialog Form Permintaan Voucher Baru (Mobile): Tampilan popup permintaan cetak voucher dirombak menjadi BottomSheet interaktif lengkap setara web portal reseller, dilengkapi pemilihan paket, preset jumlah (50/100/200/500/custom), kartu estimasi modal & margin keuntungan realtime, jenis penyerahan fisik/digital, metode pembayaran deposit, dan catatan permohonan.\n- Sinkronisasi Status & Riwayat: Pengajuan batch voucher terhubung langsung ke dashboard review Admin ISP.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
