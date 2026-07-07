<?php

namespace Database\Seeders;

use App\Models\ISP\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'code' => 'VEND-001',
                'name' => 'Huawei',
                'description' => 'Vendor perangkat jaringan Huawei',
                'status' => 'active',
            ],
            [
                'code' => 'VEND-002',
                'name' => 'MikroTik',
                'description' => 'Vendor perangkat jaringan MikroTik',
                'status' => 'active',
            ],
            [
                'code' => 'VEND-003',
                'name' => 'Cisco',
                'description' => 'Vendor perangkat jaringan Cisco',
                'status' => 'active',
            ],
            [
                'code' => 'VEND-004',
                'name' => 'ZTE',
                'description' => 'Vendor perangkat jaringan ZTE',
                'status' => 'active',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(
                ['code' => $vendor['code']],
                $vendor
            );
        }
    }
}
