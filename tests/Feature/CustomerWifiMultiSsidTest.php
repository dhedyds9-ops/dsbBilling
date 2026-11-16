<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\Master\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerWifiMultiSsidTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_retrieve_multi_ssid_and_online_status(): void
    {
        $branch = Branch::create(['name' => 'Cabang Test', 'code' => 'TEST']);
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'code' => 'CUST001',
            'name' => 'Agam Abdillah',
            'phone' => '08123456789',
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);

        $onu = Onu::create([
            'name' => 'ONU Agam',
            'serial_number' => 'ZTEG12345678',
            'model' => 'ZTE F609',
            'wifi_ssid' => 'AGAM AP',
            'wifi_password' => 'secret1234',
            'status' => 'active',
            'operational_status' => 'online',
            'last_seen_at' => now(),
            'branch_id' => $branch->id,
        ]);

        CustomerService::create([
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        // 1. Test query for SSID 1
        $response1 = $this->getJson('/api/customer/wifi?wlan_index=1');
        $response1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wlan_index', 1)
            ->assertJsonPath('data.ssid', 'AGAM AP')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.is_online', true)
            ->assertJsonPath('data.status', 'online');

        // 2. Test query for SSID 2
        $response2 = $this->getJson('/api/customer/wifi?wlan_index=2');
        $response2->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wlan_index', 2)
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.is_online', true);

        // 3. Test update SSID 2 with Open security
        $updateRes = $this->postJson('/api/customer/wifi', [
            'wlan_index' => 2,
            'ssid' => 'AGAM_GUEST',
            'security_mode' => 'OPEN',
        ]);
        $updateRes->assertOk()->assertJsonPath('success', true);
    }
}
