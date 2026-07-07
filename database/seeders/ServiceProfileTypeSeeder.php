<?php

namespace Database\Seeders;

use App\Models\ISP\ServiceProfileType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceProfileTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'PPPOE',
                'name' => 'PPPoE Profile',
                'description' => 'Profile untuk layanan PPPoE',
                'status' => 'active'
            ],
            [
                'code' => 'HOTSPOT',
                'name' => 'Hotspot Profile',
                'description' => 'Profile untuk layanan Hotspot',
                'status' => 'active'
            ],
            [
                'code' => 'VOUCHER',
                'name' => 'Voucher Profile',
                'description' => 'Profile untuk layanan Voucher',
                'status' => 'active'
            ],
            [
                'code' => 'DHCP',
                'name' => 'DHCP Profile',
                'description' => 'Profile untuk layanan DHCP',
                'status' => 'active'
            ],
            [
                'code' => 'STATIC_IP',
                'name' => 'Static IP Profile',
                'description' => 'Profile untuk layanan Static IP',
                'status' => 'active'
            ],
            [
                'code' => 'RADIUS',
                'name' => 'Radius Profile',
                'description' => 'Profile untuk layanan Radius',
                'status' => 'active'
            ],
            [
                'code' => 'QUEUE',
                'name' => 'Queue Profile',
                'description' => 'Profile untuk layanan Queue',
                'status' => 'active'
            ]
        ];

        foreach ($types as $type) {
            ServiceProfileType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
