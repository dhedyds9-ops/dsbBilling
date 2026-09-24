<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnuRemediationProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ========================================================
        // 1. Base Configuration Profiles (Low Risk)
        // ========================================================
        
        $configProfileId = DB::table('acs_configuration_profiles')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'ZTE-F670L-BASE-CONFIG',
            'description' => 'Unihide WAN settings and enable TR-069 full access on ZTE F670L',
            'vendor' => 'ZTE',
            'model' => 'F670L',
            'type' => 'REMEDIATION', // The new column added in Phase 2
            'config' => json_encode([
                'InternetGatewayDevice.X_ZTE-COM_TeleManagement.Enable' => 'true',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Enable' => 'true'
            ]),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $fhConfigProfileId = DB::table('acs_configuration_profiles')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'FIBERHOME-HG6243C-WAN-ENABLE',
            'description' => 'Enable WAN port manipulation on FiberHome',
            'vendor' => 'FiberHome',
            'model' => 'HG6243C',
            'type' => 'REMEDIATION',
            'config' => json_encode([
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.2.Enable' => 'true'
            ]),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ========================================================
        // 2. Unlock Profiles (High Risk)
        // ========================================================

        // ZTE F670L usually locks the bridge mode behind a telnet factory command or hidden parameter
        DB::table('onu_unlock_profiles')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'ZTE-F670L-TELNET-BRIDGE-UNLOCK',
            'vendor' => 'ZTE',
            'model' => 'F670L',
            'hardware_version' => 'V1.0',
            'firmware_range' => json_encode(['V1.0.1', 'V1.0.2']),
            'method' => 'PARAMETER', // Or TELNET/SSH depending on GenieACS extension
            'required_parameters' => json_encode([
                'steps' => [
                    [
                        'path' => 'InternetGatewayDevice.X_ZTE-COM_Telnet.Enable',
                        'value' => 'true',
                        'type' => 'boolean'
                    ],
                    [
                        'path' => 'InternetGatewayDevice.X_ZTE-COM_Security.BridgeMacBind',
                        'value' => 'false',
                        'type' => 'boolean'
                    ]
                ]
            ]),
            'verification_rules' => json_encode(['X_ZTE-COM_Telnet.Enable' => 'true']),
            'reboot_required' => true,
            'risk_level' => 'HIGH',
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // FiberHome HG6243C Unlock
        DB::table('onu_unlock_profiles')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'FIBERHOME-SUPERADMIN-UNLOCK',
            'vendor' => 'FiberHome',
            'model' => 'HG6243C',
            'hardware_version' => 'RP25xx',
            'firmware_range' => json_encode(['*']), // Wildcard support
            'method' => 'PARAMETER',
            'required_parameters' => json_encode([
                'steps' => [
                    [
                        'path' => 'InternetGatewayDevice.DeviceInfo.X_FIBERHOME_SuperAdmin',
                        'value' => 'true',
                        'type' => 'boolean'
                    ]
                ]
            ]),
            'verification_rules' => json_encode([]),
            'reboot_required' => false,
            'risk_level' => 'MEDIUM',
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
