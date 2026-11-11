<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ServiceProfileTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'nama_paket' => 'Paket Home 20 Mbps',
                'deskripsi' => 'Paket Internet Rumah 20M Unlimited',
                'jenis_layanan' => 'pppoe',
                'tipe_paket' => 'unlimited',
                'download_speed' => 20,
                'upload_speed' => 20,
                'harga_dasar' => 150000,
                'harga_owner' => 150000,
                'harga_reseller' => 165000,
                'masa_aktif' => 30,
                'shared_user' => 1,
                'status' => 'active',
            ],
            [
                'nama_paket' => 'Voucher 1 Hari',
                'deskripsi' => 'Akses Hotspot 24 Jam Kuota 2GB',
                'jenis_layanan' => 'hotspot',
                'tipe_paket' => 'quota',
                'download_speed' => 10,
                'upload_speed' => 10,
                'harga_dasar' => 5000,
                'harga_owner' => 5000,
                'harga_reseller' => 6000,
                'masa_aktif' => 1,
                'shared_user' => 1,
                'status' => 'active',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'nama_paket',
            'deskripsi',
            'jenis_layanan',
            'tipe_paket',
            'download_speed',
            'upload_speed',
            'harga_dasar',
            'harga_owner',
            'harga_reseller',
            'masa_aktif',
            'shared_user',
            'status',
        ];
    }
}
