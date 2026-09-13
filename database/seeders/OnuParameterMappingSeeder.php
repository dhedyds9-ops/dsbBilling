<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OnuParameterMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parameter mappings are usually mapped by Vendor + Model + Hardware_Version
        // ZTE Devices use 'InternetGatewayDevice'
        // FiberHome sometimes uses 'Device' depending on firmware, or different indexing.
        
        // Buat dummy ONU jika belum ada
        $onuId = DB::table('onus')->first()->id ?? DB::table('onus')->insertGetId([
            'serial_number' => 'ZTEG12345678',
            'code' => 'ONU-TEST-1',
            'name' => 'Test ONU',
            'mac_address' => '00:11:22:33:44:55',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mappings = [
            [
                'onu_id' => $onuId,
                'semantic_key' => 'wan.pppoe.username',
                'actual_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
            ],
            [
                'onu_id' => $onuId,
                'semantic_key' => 'wan.pppoe.password',
                'actual_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password',
            ],
            [
                'onu_id' => $onuId,
                'semantic_key' => 'wan.pppoe.vlan_id',
                'actual_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANCableLinkConfig.X_CT-COM_VLANIDMark',
            ]
        ];

        foreach ($mappings as $mapping) {
            DB::table('onu_parameter_mappings')->updateOrInsert(
                [
                    'onu_id' => $mapping['onu_id'],
                    'semantic_key' => $mapping['semantic_key'],
                ],
                array_merge($mapping, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
