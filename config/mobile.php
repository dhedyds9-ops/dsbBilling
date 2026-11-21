<?php

return [
    'staff' => [
        'latest_version_code' => 10125,
        'latest_version_name' => '1.1.25',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-mobile.apk',
        'release_notes' => "dsBilling Staff & Reseller v1.1.25:\n- Perbaikan Peta Jaringan: Foto titik tiang/ODP kini terbuka jernih dengan modal preview (bebas dari error net::ERR_FILE_NOT_FOUND).\n- Perbaikan Server GenieACS TR-069: Tombol Hapus kini terhubung langsung menghapus perangkat dari TR-069 GenieACS dan basis data lokal.\n- Isolasi Stok PSB: Teknisi dan Reseller yang tidak memegang stok fisik modem dilarang mengambil stok dari gudang pusat.\n- Perbaikan SPK Lapangan: Memperbaiki resolusi akun pelanggan sehingga daftar SPK terbuka stabil tanpa server error.\n- Penyelarasan Izin Reseller: Akun Reseller kini dapat melihat dan mendiagnosa detail modem unassigned/baru colok di OLT sesuai hak aksesnya.",
    ],
    'customer' => [
        'latest_version_code' => 10112,
        'latest_version_name' => '1.1.12',
        'minimum_supported_code' => 1,
        'download_url' => env('APP_URL', 'https://winets.mstore.id') . '/downloads/dsbilling-customer-release.apk',
        'release_notes' => "dsBilling Customer v1.1.12:\n- Logo Mitra Resmi ISP: Menampilkan logo resmi penyedia layanan internet pada kartu informasi legalitas dan dialog kemitraan.\n- Privasi Penuh & Eksklusivitas Tenant ISP: Server login dikunci secara dedicated dan privat ke ISP Anda, menjaga kerahasiaan identitas tenant antar-ISP.\n- Antarmuka Bersih: Layar masuk ringkas, aman, dan tanpa risiko tersasar ke server ISP lain.\n- Presisi Otentikasi & Prioritas Layanan Online.",
    ],
];
