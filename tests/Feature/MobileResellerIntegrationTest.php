<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\ISP\Onu;
use App\Models\ISP\ServiceProfile;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileResellerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $reseller;
    protected User $otherReseller;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $this->branch = Branch::firstOrCreate(['code' => 'BR-RESELLER'], ['name' => 'Cabang Reseller']);

        $this->reseller = User::factory()->create([
            'name' => 'Mitra Reseller Mandiri',
            'email' => 'mitra@reseller.test',
            'job_function' => 'RESELLER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->reseller->roles()->attach($resellerRole->id);
        $this->reseller->unsetRelation('roles');

        $this->otherReseller = User::factory()->create([
            'name' => 'Mitra Reseller Lain',
            'email' => 'lain@reseller.test',
            'job_function' => 'RESELLER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->otherReseller->roles()->attach($resellerRole->id);
        $this->otherReseller->unsetRelation('roles');
    }

    public function test_reseller_can_login_and_receives_operational_permissions(): void
    {
        $response = $this->postJson('/api/mobile/login', [
            'email' => $this->reseller->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $this->reseller->id,
                    'role' => 'RESELLER',
                    'reseller_id' => $this->reseller->id,
                ],
            ]);

        $permissions = $response->json('user.permissions');
        $this->assertContains('customer.create', $permissions);
        $this->assertContains('inventory.custody', $permissions);
        $this->assertContains('acs.view', $permissions);
    }

    public function test_reseller_can_query_and_is_isolated_to_own_customers(): void
    {
        // Create customer for this reseller
        Customer::create([
            'name' => 'Pelanggan Reseller 1',
            'code' => 'CUST-RES-01',
            'phone' => '081234567890',
            'reseller_id' => $this->reseller->id,
            'status' => 'active',
        ]);

        // Create customer for another reseller (without reseller session overriding ownership)
        Customer::create([
            'name' => 'Pelanggan Reseller Lain',
            'code' => 'CUST-RES-02',
            'phone' => '089876543210',
            'reseller_id' => $this->otherReseller->id,
            'status' => 'active',
        ]);

        Sanctum::actingAs($this->reseller, ['*']);

        $response = $this->getJson('/api/mobile/customers');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Pelanggan Reseller 1', $data[0]['name']);
    }

    public function test_reseller_can_view_own_onus(): void
    {
        Sanctum::actingAs($this->reseller, ['*']);

        Onu::create([
            'serial_number' => 'ZTEG11223344',
            'mac_address' => 'AA:BB:CC:11:22:33',
            'name' => 'ONT Reseller 1',
            'model' => 'F670L',
            'status' => 'active',
            'created_by' => $this->reseller->id,
        ]);

        Onu::create([
            'serial_number' => 'ZTEG99887766',
            'mac_address' => 'AA:BB:CC:99:88:77',
            'name' => 'ONT Reseller Lain',
            'model' => 'F670L',
            'status' => 'active',
            'created_by' => $this->otherReseller->id,
        ]);

        $response = $this->getJson('/api/mobile/network/devices');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('ZTEG11223344', $data[0]['serial_number']);
    }

    public function test_reseller_can_create_new_customer(): void
    {
        Sanctum::actingAs($this->reseller, ['*']);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $profile = ServiceProfile::create([
            'code' => 'PKG-RES-20M',
            'name' => 'Paket Reseller 20M',
            'service_profile_type_id' => $profileType->id,
            'price' => 150000,
            'speed_limit' => '20M',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Pak Joko Warga',
            'phone' => '082211334455',
            'email' => 'joko@warga.test',
            'address' => 'RT 02 RW 03 No 15',
            'username' => 'joko_pppoe',
            'password' => 'warga123',
            'service_profile_id' => $profile->id,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ];

        $response = $this->postJson('/api/mobile/customers', $payload);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Pak Joko Warga',
            'phone' => '082211334455',
            'reseller_id' => $this->reseller->id,
        ]);
    }
}
