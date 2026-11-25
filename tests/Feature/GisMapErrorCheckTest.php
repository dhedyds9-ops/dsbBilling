<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\CRM\Customer;
use App\Models\ISP\Onu;
use App\Models\Customer\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GisMapErrorCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_gis_map_as_admin(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole->id);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'CUST-001',
            'name' => 'Pelanggan Uji Coba',
            'phone' => '08123456789',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'status' => 'active',
        ]);

        $onu = Onu::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'ONU-001',
            'name' => 'ONU Test Pelanggan',
            'serial_number' => 'ZTEGC11223344',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'status' => 'active',
            'operational_status' => 'online',
        ]);

        CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/gis/map');
        $response->assertStatus(200);
        $response->assertSee('Peta Jaringan');
    }

    public function test_network_map_redirect_for_technician(): void
    {
        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician', 'guard_name' => 'web']);
        $tech = User::factory()->create([
            'name' => 'Teknisi 1',
            'is_active' => true,
            'job_function' => 'TECHNICIAN',
        ]);
        $tech->roles()->attach($techRole->id);

        $response = $this->actingAs($tech)->get('/network/map');
        $response->assertRedirect(route('technician.odp.nearest'));
    }

    public function test_network_map_redirect_for_reseller(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $reseller = User::factory()->create([
            'name' => 'Reseller 1',
            'is_active' => true,
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $response = $this->actingAs($reseller)->get('/network/map');
        $response->assertRedirect(route('reseller-portal.network.map'));
    }
}
