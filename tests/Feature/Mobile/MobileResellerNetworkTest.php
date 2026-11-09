<?php

namespace Tests\Feature\Mobile;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileResellerNetworkTest extends TestCase
{
    use RefreshDatabase;

    protected User $resellerUser;
    protected User $otherUser;
    protected Olt $resellerOlt;
    protected Olt $otherOlt;
    protected PonPort $resellerPonPort;
    protected Onu $resellerOnu;
    protected Onu $otherOnu;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::where('name', 'reseller')->first();
        if (!$role) {
            $role = Role::create([
                'name' => 'reseller',
                'display_name' => 'Reseller',
                'description' => 'Reseller Role',
            ]);
        }

        $this->resellerUser = User::factory()->create([
            'name' => 'Reseller Test User',
            'email' => 'reseller.test@mstore.id',
            'job_function' => 'RESELLER',
        ]);
        $this->resellerUser->attachRole('reseller');

        $this->otherUser = User::factory()->create([
            'name' => 'Other ISP Admin',
            'email' => 'admin.other@mstore.id',
        ]);

        // Reseller OLT (created by reseller)
        $this->resellerOlt = Olt::create([
            'name' => 'OLT Reseller A',
            'code' => 'OLT-RSL-01',
            'ip_address' => '10.10.1.1',
            'vendor' => 'ZTE',
            'model' => 'C320',
            'status' => 'active',
            'created_by' => $this->resellerUser->id,
        ]);

        // Other OLT
        $this->otherOlt = Olt::create([
            'name' => 'OLT Central ISP',
            'code' => 'OLT-CTR-01',
            'ip_address' => '10.10.2.1',
            'vendor' => 'Huawei',
            'model' => 'MA5800',
            'status' => 'active',
            'created_by' => $this->otherUser->id,
        ]);

        // Reseller PON Port
        $this->resellerPonPort = PonPort::create([
            'olt_id' => $this->resellerOlt->id,
            'port_number' => 1,
            'port_index' => '1/1/1',
            'code' => 'PON-01',
            'name' => 'PON 1 Reseller',
            'status' => 'active',
            'created_by' => $this->resellerUser->id,
        ]);

        // Reseller ONU (created by reseller)
        $this->resellerOnu = Onu::create([
            'serial_number' => 'ZTEG11223344',
            'name' => 'ONT Customer Reseller A',
            'olt_id' => $this->resellerOlt->id,
            'pon_port_id' => $this->resellerPonPort->id,
            'created_by' => $this->resellerUser->id,
            'status' => 'online',
            'rx_power_dbm' => -19.5,
            'tx_power_dbm' => 2.3,
            'last_seen_at' => now(),
        ]);

        // Other ONU
        $this->otherOnu = Onu::create([
            'serial_number' => 'HWTC99887766',
            'name' => 'ONT Central ISP',
            'olt_id' => $this->otherOlt->id,
            'created_by' => $this->otherUser->id,
            'status' => 'online',
            'rx_power_dbm' => -21.0,
            'tx_power_dbm' => 2.1,
            'last_seen_at' => now(),
        ]);
    }

    public function test_reseller_can_list_only_scoped_olts(): void
    {
        Sanctum::actingAs($this->resellerUser);

        $response = $this->getJson('/api/mobile/network/olts');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $ids = array_column($data, 'id');

        $this->assertContains($this->resellerOlt->id, $ids);
        $this->assertNotContains($this->otherOlt->id, $ids);
    }

    public function test_reseller_cannot_access_unauthorized_olt(): void
    {
        Sanctum::actingAs($this->resellerUser);

        $response = $this->getJson("/api/mobile/network/olts/{$this->otherOlt->id}");
        $response->assertStatus(404);
    }

    public function test_reseller_can_view_own_olt_and_pon_ports(): void
    {
        Sanctum::actingAs($this->resellerUser);

        $response = $this->getJson("/api/mobile/network/olts/{$this->resellerOlt->id}/pon-ports");
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->resellerPonPort->id, $data[0]['id']);
    }

    public function test_reseller_devices_query_filters_by_ownership(): void
    {
        Sanctum::actingAs($this->resellerUser);

        $response = $this->getJson('/api/mobile/network/devices');
        $response->assertStatus(200);

        $sns = array_column($response->json('data'), 'serial_number');
        $this->assertContains('ZTEG11223344', $sns);
        $this->assertNotContains('HWTC99887766', $sns);
    }

    public function test_reseller_diagnostics_allowed_for_own_onu_and_denied_for_other(): void
    {
        Sanctum::actingAs($this->resellerUser);

        // Own ONU
        $resOwn = $this->getJson('/api/mobile/network/devices/ZTEG11223344/diagnostics');
        $resOwn->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'serial_number' => 'ZTEG11223344',
                    'optical_quality' => 'Optimal',
                ],
            ]);

        // Other ONU outside scope
        $resOther = $this->getJson('/api/mobile/network/devices/HWTC99887766/diagnostics');
        $resOther->assertStatus(403);
    }
}
