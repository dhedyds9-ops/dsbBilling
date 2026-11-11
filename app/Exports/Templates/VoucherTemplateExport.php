<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VoucherTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'kode_voucher' => 'VCH-1001',
                'service_profile' => 'Voucher 1 Hari',
                'voucher_pool' => 'Batch Harian Warung',
                'router' => 'CCR1009-Pusat',
                'reseller' => '-',
                'validity_days' => 1,
                'status' => 'available',
                'notes' => 'Voucher batch 1',
            ],
            [
                'kode_voucher' => 'VCH-1002',
                'service_profile' => 'Voucher 1 Jam',
                'voucher_pool' => 'Batch Jam-Jaman',
                'router' => 'CCR1009-Pusat',
                'reseller' => '-',
                'validity_days' => 1,
                'status' => 'available',
                'notes' => 'Voucher batch 1',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'kode_voucher',
            'service_profile',
            'voucher_pool',
            'router',
            'reseller',
            'validity_days',
            'status',
            'notes',
        ];
    }
}
