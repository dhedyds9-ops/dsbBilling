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
        'latest_version_code' => 10109,
        'latest_version_name' => '1.1.9',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.9:\n- Proteksi Port Binding Wi-Fi ONT: Filter otomatis slot SSID berdasarkan port binding WAN PPPoE pelanggan. Slot SSID yang digunakan untuk Bridge Hotspot/Voucher publik ISP otomatis disembunyikan dan diproteksi sehingga pelanggan hanya mengelola Wi-Fi pribadi miliknya sendiri.\n- Perbaikan Ubah Kata Sandi Portal: Sinkronisasi rute endpoint otentikasi ganti sandi dan validasi konfirmasi kata sandi ganda.\n- Fitur Mandiri Ganti Akun Hotspot: Pelanggan hotspot/member dapat mengubah username & password akun secara mandiri atau beralih ke Single-Code login.\n- Smart Service Router: Deteksi tipe layanan otomatis (Hotspot vs FTTH/PPPoE) saat membuka menu pengaturan jaringan.\n- Stabilitas & Keamanan: Enkripsi AES-256 GCM pada penyimpanan kredensial.",
    ],
];
