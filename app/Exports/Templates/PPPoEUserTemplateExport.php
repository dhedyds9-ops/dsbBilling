<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PPPoEUserTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'username' => 'budi_net01',
                'password' => 'pass1234',
                'nama_pelanggan' => 'Budi Santoso',
                'phone' => '081234567890',
                'email' => 'budi@gmail.com',
                'address' => 'Jl. Mawar No. 12 RT 01 RW 02',
                'paket_langganan' => 'Paket Home 20 Mbps',
                'router' => 'CCR1009-Pusat',
                'reseller' => '-',
                'mac_address' => '-',
                'static_ip' => '-',
                'status' => 'active',
            ],
            [
                'username' => 'siti_wifi02',
                'password' => 'siti2026',
                'nama_pelanggan' => 'Siti Aminah',
                'phone' => '085712345678',
                'email' => 'siti@yahoo.com',
                'address' => 'Jl. Melati No. 5',
                'paket_langganan' => 'Paket Home 10 Mbps',
                'router' => 'CCR1009-Pusat',
                'reseller' => '-',
                'mac_address' => '-',
                'static_ip' => '-',
                'status' => 'active',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'username',
            'password',
            'nama_pelanggan',
            'phone',
            'email',
            'address',
            'paket_langganan',
            'router',
            'reseller',
            'mac_address',
            'static_ip',
            'status',
        ];
    }
}
