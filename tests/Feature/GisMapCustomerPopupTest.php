<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\PPPoEUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GisMapCustomerPopupTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected \App\Models\ISP\ServiceProfile $profile;
    protected \App\Models\ISP\Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'name' => 'Admin GIS',
            'email' => 'admin_gis@dsbilling.test',
            'is_active' => true,
        ]);
        $this->adminUser->roles()->attach($adminRole->id);

        $this->router = \App\Models\ISP\Router::create([
            'code' => 'RTR-TEST-01',
            'name' => 'Router Test GIS',
            'ip_address' => '10.0.0.1',
            'username' => 'admin',
            'password' => 'admin',
            'status' => 'active',
        ]);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $this->profile = \App\Models\ISP\ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'PKG-20M',
            'name' => 'PAKET 20 MBPS',
            'price' => 150000,
            'service_type' => 'pppoe',
        ]);
    }

    /**
     * Test wlanStatus returns accurate OFFLINE status and does not fake ONLINE or SSID.
     */
    public function test_customer_without_active_session_reports_offline_with_honest_reason(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-OFFLINE',
            'name' => 'Pelanggan Offline',
            'phone' => '081234567800',
            'address' => 'Desa Cibadak',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'status' => 'active',
        ]);

        $onu = Onu::create([
            'code' => 'ONU-TEST-01',
            'name' => 'ONT Pelanggan',
            'serial_number' => 'ZTEGC1234567',
            'mac_address' => 'AA:BB:CC:DD:EE:01',
            'wifi_ssid' => null, // Belum sinkron TR-069
            'rx_power_dbm' => -21.5,
            'tx_power_dbm' => 2.3,
            'status' => 'inactive',
        ]);

        $cs = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'status' => 'active', // Langganan aktif tapi modem offline
            'username' => 'user_offline_01',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/map/wlan-status/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_online' => false,
                'reason' => 'Modem Offline / Sesi Terputus',
                'serial_number' => 'ZTEGC1234567',
                'mac_address' => 'AA:BB:CC:DD:EE:01',
                'rx_power' => '-21.5 dBm',
                'rdm_power' => '2.3 dBm',
                'ssid_name' => null,
            ]);

        // Verifikasi tidak ada fallback palsu "dsBilling_WiFi_1"
        $this->assertNotEquals('dsBilling_WiFi_1', $response->json('ssid_name'));
    }

    /**
     * Test wlanStatus returns ONLINE when PPPoE session is actively dialled in.
     */
    public function test_customer_with_active_pppoe_session_reports_online(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-ONLINE',
            'name' => 'Pelanggan Online',
            'phone' => '081234567801',
            'address' => 'Desa Binuangeun',
            'latitude' => -6.21,
            'longitude' => 106.81,
            'status' => 'active',
        ]);

        $onu = Onu::create([
            'code' => 'ONU-TEST-02',
            'name' => 'ONT Pelanggan Online',
            'serial_number' => 'HWTC8899AABB',
            'mac_address' => '11:22:33:44:55:66',
            'wifi_ssid' => 'Rumah_Dede_5G',
            'rx_power_dbm' => -18.2,
            'tx_power_dbm' => 2.1,
            'status' => 'active',
        ]);

        $cs = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'service_profile_id' => $this->profile->id,
            'status' => 'active',
            'username' => 'dede_gofur',
        ]);

        $pppoe = PPPoEUser::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'customer_service_id' => $cs->id,
            'service_profile_id' => $this->profile->id,
            'username' => 'dede_gofur',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        // Mock active session in PppActiveSession
        PppActiveSession::create([
            'router_id' => $this->router->id,
            'name' => 'dede_gofur',
            'address' => '10.10.10.55',
            'uptime' => '2h15m',
            'bytes_in' => 5000000,
            'bytes_out' => 20000000,
            'session_started_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/map/wlan-status/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_online' => true,
                'reason' => 'PPPoE Active Session',
                'serial_number' => 'HWTC8899AABB',
                'mac_address' => '11:22:33:44:55:66',
                'rx_power' => '-18.2 dBm',
                'rdm_power' => '2.1 dBm',
                'ssid_name' => 'Rumah_Dede_5G',
            ]);
    }
}
