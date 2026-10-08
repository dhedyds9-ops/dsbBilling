<?php

namespace Database\Seeders;

use App\Models\ISP\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            "ZTE" => "Vendor perangkat jaringan dan OLT ZTE",
            "HUAWEI" => "Vendor perangkat jaringan dan OLT Huawei",
            "FIBERHOME" => "Vendor perangkat jaringan dan OLT Fiberhome",
            "NOKIA" => "Vendor perangkat jaringan dan OLT Nokia/Alcatel-Lucent",
            "MIKROTIK" => "Vendor perangkat jaringan MikroTik RouterBOARD",
            "TP-LINK" => "Vendor perangkat jaringan TP-Link",
            "TENDA" => "Vendor perangkat jaringan Tenda",
            "TOTOLINK" => "Vendor perangkat jaringan Totolink",
            "V-SOL" => "Vendor perangkat jaringan OLT dan ONU V-SOL",
            "HSGQ" => "Vendor perangkat jaringan OLT dan ONU HSGQ",
            "C-DATA" => "Vendor perangkat jaringan OLT dan ONU C-Data",
            "UBIQUITI" => "Vendor perangkat jaringan nirkabel Ubiquiti",
            "CISCO" => "Vendor perangkat jaringan Cisco",
        ];

        foreach ($brands as $name => $description) {
            $code = strtoupper(Str::slug($name));
            Vendor::firstOrCreate(
                ["name" => $name],
                [
                    "code" => $code,
                    "description" => $description,
                    "status" => "active",
                ]
            );
        }
    }
}

