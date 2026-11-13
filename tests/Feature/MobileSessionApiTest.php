<?php

namespace Tests\Feature;

use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\Router;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileSessionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseller_can_list_active_sessions_and_stats(): void
    {
        $role = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $reseller = User::factory()->create([
            'is_active' => true,
            'wilayah' => 'Cibinong Raya',
        ]);
        $reseller->roles()->attach($role->id);

        $router = Router::create([
            'name' => 'MikroTik Core Test',
            'code' => 'RTR-01',
            'ip_address' => '10.0.0.1',
            'username' => 'admin',
            'password' => 'secret',
            'status' => 'online',
        ]);

        $pppoeSession = PppActiveSession::create([
            'router_id' => $router->id,
            'name' => 'user_test_pppoe',
            'service' => 'pppoe',
            'caller_id' => '00:11:22:33:44:55',
            'address' => '10.10.10.15',
            'uptime' => '1h 20m',
            'session_started_at' => now()->subHour(),
        ]);

        $hotspotSession = HotspotActiveSession::create([
            'router_id' => $router->id,
            'user' => 'user_test_hotspot',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'address' => '192.168.88.50',
            'uptime' => '30m',
            'session_started_at' => now()->subMinutes(30),
        ]);

        $response = $this->actingAs($reseller, 'sanctum')
            ->getJson('/api/mobile/sessions');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'stats' => ['total', 'pppoe', 'hotspot', 'voucher'],
                'data',
            ]);
    }
}
