<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HotspotUserTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'username' => 'rapi555777',
                'password' => 'rapi1234',
                'service_profile' => 'Member Gold 2 User',
                'customer' => 'Bilal',
                'phone' => '081234567890',
                'router' => 'CCR1009-Pusat',
                'mac_address' => '-',
                'static_ip' => '-',
                'reseller' => '-',
                'status' => 'active',
            ],
            [
                'username' => 'hs_warkop01',
                'password' => 'warkop88',
                'service_profile' => 'Paket Hotspot 5M',
                'customer' => 'Pak Slamet',
                'phone' => '085678901234',
                'router' => 'CCR1009-Pusat',
                'mac_address' => '-',
                'static_ip' => '-',
                'reseller' => '-',
                'status' => 'active',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'username',
            'password',
            'service_profile',
            'customer',
            'phone',
            'router',
            'mac_address',
            'static_ip',
            'reseller',
            'status',
        ];
    }
}
