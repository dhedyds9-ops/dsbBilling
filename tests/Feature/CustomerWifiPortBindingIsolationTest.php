<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\Master\Branch;
use App\Models\User;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class CustomerWifiPortBindingIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_wifi_filters_out_bridge_hotspot_ssid_and_binds_to_pppoe_slot(): void
    {
        $branch = Branch::create(['name' => 'Cabang Test', 'code' => 'TEST']);
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'code' => 'CUST-PPPOE-01',
            'name' => 'Tes Customer',
            'phone' => '081234567890',
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);

        $onu = Onu::create([
            'name' => 'ONU ZTE F609',
            'serial_number' => 'ZTEGC85E2D31',
            'model' => 'ZTE F609',
            'wifi_ssid' => 'Nabila',
            'wifi_password' => 'secret1234',
            'status' => 'active',
            'operational_status' => 'online',
            'last_seen_at' => now(),
            'branch_id' => $branch->id,
        ]);

        CustomerService::create([
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'username' => 'Tes@dsbilling',
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);

        // Mock GenieACSDriver returning dual WAN (Bridge on WLAN 1, PPPoE on WLAN 2)
        $mockDriver = Mockery::mock(GenieACSDriver::class)->makePartial();
        $mockDriver->shouldReceive('isDeviceOnline')->andReturn(true);
        $mockDriver->shouldReceive('getDeviceParameters')->andReturn([
            '_id' => 'E4CA12-F609-ZTEGC85E2D31',
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => ['_value' => 'bridge'],
                                        'ConnectionType' => ['_value' => 'PPPoE_Bridged'],
                                        'NATEnabled' => ['_value' => false],
                                        'X_ZTE-COM_LANInterface' => [
                                            '_value' => 'InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1,InternetGatewayDevice.LANDevice.1.WLANConfiguration.1'
                                        ],
                                    ],
                                ],
                            ],
                            '2' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => ['_value' => 'pppoe'],
                                        'ConnectionType' => ['_value' => 'IP_Routed'],
                                        'Username' => ['_value' => 'Tes@dsbilling'],
                                        'NATEnabled' => ['_value' => true],
                                        'X_ZTE-COM_LANInterface' => [
                                            '_value' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.2'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'LANDevice' => [
                    '1' => [
                        'WLANConfiguration' => [
                            '1' => [
                                'SSID' => ['_value' => 'AGAM AP'],
                                'BeaconType' => ['_value' => 'None'],
                            ],
                            '2' => [
                                'SSID' => ['_value' => 'Nabila'],
                                'KeyPassphrase' => ['_value' => 'nabila12345'],
                                'BeaconType' => ['_value' => '11i'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->app->instance(GenieACSDriver::class, $mockDriver);

        Sanctum::actingAs($user);

        // 1. Calling GET /api/customer/wifi (even with default or requested wlan_index=1)
        // Should automatically redirect to allowed PPPoE slot (SSID 2: Nabila)
        $response = $this->getJson('/api/customer/wifi?wlan_index=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wlan_index', 2)
            ->assertJsonPath('data.ssid', 'Nabila');

        // Verify available_ssids ONLY contains index 2, and SSID 1 (Bridge Hotspot) is omitted
        $availableSsids = $response->json('data.available_ssids');
        $this->assertCount(1, $availableSsids);
        $this->assertEquals(2, $availableSsids[0]['index']);
        $this->assertEquals('Nabila', $availableSsids[0]['ssid']);

        // 2. Customer attempting to POST /api/customer/wifi targeting slot 1 (Bridge Hotspot)
        // Must be rejected with 403 Forbidden
        $failResponse = $this->postJson('/api/customer/wifi', [
            'wlan_index' => 1,
            'ssid' => 'HACKED_HOTSPOT',
            'password' => 'hack123456',
        ]);
        $failResponse->assertStatus(403);
        $this->assertStringContainsString('Hotspot / Bridge publik', $failResponse->json('message'));

        // 3. Customer updating their legitimate PPPoE slot 2
        // Should succeed
        $successResponse = $this->postJson('/api/customer/wifi', [
            'wlan_index' => 2,
            'ssid' => 'Nabila_New',
            'password' => 'nabila5678',
            'security_mode' => 'WPA2',
        ]);
        $successResponse->assertOk()
            ->assertJsonPath('success', true);
    }
}
