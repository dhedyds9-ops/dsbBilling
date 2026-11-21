<?php

return [
    'staff' => [
        'latest_version_code' => 10121,
        'latest_version_name' => '1.1.21',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.21:\n- Khusus Paket Voucher: Filter katalog diperketat sehingga hanya paket bertipe Voucher yang tampil (paket PPPoE dan Hotspot member otomatis disaring keluar).\n- Dialog Form Permintaan Voucher Baru (Mobile): Tampilan popup permintaan cetak voucher dirombak menjadi BottomSheet interaktif lengkap setara web portal reseller, dilengkapi pemilihan paket, preset jumlah (50/100/200/500/custom), kartu estimasi modal & margin keuntungan realtime, jenis penyerahan fisik/digital, metode pembayaran deposit, dan catatan permohonan.\n- Sinkronisasi Status & Riwayat: Pengajuan batch voucher terhubung langsung ke dashboard review Admin ISP.",
    ],
    'customer' => [
        'latest_version_code' => 10111,
        'latest_version_name' => '1.1.11',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.11:\n- Pemilih & Indikator Server ISP: Tampilan status server ISP aktif di halaman login untuk mencegah akun tertukar pada cluster multi-tenant (WiNETS vs MStore).\n- Presisi Otentikasi Akun: Pencocokan ID Pelanggan presisi mencegah data akun tertukar.\n- Prioritas Layanan Aktif: Layanan utama yang online diprioritaskan saat pelanggan memiliki lebih dari satu jenis layanan.\n- Perbaikan Metode Pembayaran & Perangkat Tersambung.",
    ],
];
