<?php

namespace Database\Seeders;

use App\Models\Inventory\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'CAT-SPLICER', 'name' => 'Mesin Splicing (Fusion Splicer)'],
            ['code' => 'CAT-TANGGA',  'name' => 'Tangga & Keselamatan Kerja (K3)'],
            ['code' => 'CAT-TESTER',  'name' => 'Alat Ukur & Test (OTDR / OPM / VFL)'],
            ['code' => 'CAT-TOOLKIT', 'name' => 'Paket Tool Kit Perkakas (Tas)'],
            ['code' => 'CAT-HANDTOOL','name' => 'Perkakas Tangan Satuan (Tang / Kater)'],
            ['code' => 'CAT-NETWORK', 'name' => 'Perangkat Aktif Jaringan & Server'],
            ['code' => 'CAT-CPE',     'name' => 'Modem & Perangkat Pelanggan (CPE)'],
        ];

        foreach ($categories as $cat) {
            AssetCategory::firstOrCreate(
                ['code' => $cat['code']],
                ['name' => $cat['name']]
            );
        }
    }
}
