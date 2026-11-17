<?php

namespace Tests\Feature;

use App\Models\ACS\ACSDevice;
use App\Models\ISP\Onu;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Services\ISP\GenieAcsProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class GenieAcsRoleAccessOptionATest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createUserWithRole(string $roleName, ?int $branchId = null): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], [
            'display_name' => ucfirst($roleName),
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => "{$roleName}_" . Str::random(5) . '@dsbilling.test',
            'is_active' => true,
            'branch_id' => $branchId,
        ]);
        $user->roles()->sync([$role->id]);
        $user->load('roles');

        return $user;
    }

    public function test_customer_role_is_strictly_forbidden_from_acs_web_routes(): void
    {
        $customer = $this->createUserWithRole('customer');

        // Customer attempting to access /acs is intercepted and redirected away from backoffice
        $this->actingAs($customer)
            ->get('/acs/dashboard')
            ->assertRedirect(route('customer-portal.dashboard'));

        $this->actingAs($customer)
            ->get('/acs/devices')
            ->assertRedirect(route('customer-portal.dashboard'));
    }

    public function test_customer_role_is_forbidden_from_provisioning_ont(): void
    {
        $customer = $this->createUserWithRole('customer');
        $onu = Onu::create([
            'serial_number' => 'ZTEGCTEST001',
            'name' => 'Test ONU',
        ]);

        $this->actingAs($customer);

        $service = app(GenieAcsProvisioningService::class);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('Pelanggan tidak memiliki izin untuk mengonfigurasi perangkat GenieACS.');

        $service->provisionOnt($onu);
    }

    public function test_all_staff_and_reseller_roles_can_access_acs_devices_web(): void
    {
        $allowedRoles = [
            'administrator',
            'manager',
            'admin_gudang',
            'technician',
            'teknisi',
            'noc',
            'customer_service',
            'kasir',
            'reseller',
        ];

        foreach ($allowedRoles as $roleName) {
            $user = $this->createUserWithRole($roleName);

            $response = $this->actingAs($user)->get('/acs/devices');
            $this->assertEquals(200, $response->status(), "Role '{$roleName}' should be able to access /acs/devices.");
        }
    }

    public function test_reseller_can_see_unassigned_devices_in_acs(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        // Unassigned device (newly discovered via GenieACS)
        $unassignedDevice = ACSDevice::create([
            'uuid' => (string) Str::uuid(),
            'serial_number' => 'ZTEGCUNASSIGNED99',
            'customer_service_id' => null,
            'onu_id' => null,
            'status' => 'online',
        ]);

        $visibleDevices = ACSDevice::forReseller($reseller)->pluck('serial_number')->toArray();

        $this->assertContains('ZTEGCUNASSIGNED99', $visibleDevices, 'Unassigned device must be visible to Reseller under Option A.');
    }

    public function test_customer_cannot_see_any_device_via_for_reseller_scope(): void
    {
        $customer = $this->createUserWithRole('customer');

        ACSDevice::create([
            'uuid' => (string) Str::uuid(),
            'serial_number' => 'ZTEGCUNASSIGNED88',
            'customer_service_id' => null,
            'onu_id' => null,
            'status' => 'online',
        ]);

        $visibleDevices = ACSDevice::forReseller($customer)->get();

        $this->assertCount(0, $visibleDevices, 'Customer must not see any devices.');
    }

    public function test_technician_and_reseller_can_provision_ont_cross_branch(): void
    {
        $branch1 = Branch::firstOrCreate(['code' => 'BR-01'], ['name' => 'Cabang 1']);
        $branch2 = Branch::firstOrCreate(['code' => 'BR-02'], ['name' => 'Cabang 2']);

        $techBranch1 = $this->createUserWithRole('teknisi', $branch1->id);

        $onuBranch2 = Onu::create([
            'serial_number' => 'ZTEGCCROSSBRANCH',
            'name' => 'ONU Cabang 2',
            'branch_id' => $branch2->id,
            'provision_status' => 'pending',
        ]);

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('isDeviceOnline')->andReturn(true);
        $driverMock->shouldReceive('getDevice')->andReturn(['_id' => 'ZTEGCCROSSBRANCH']);
        $driverMock->shouldReceive('provisionPppoe')->once()->andReturn(true);
        $driverMock->shouldReceive('updateWifiSsidAndPassword')->andReturn(true);
        $this->app->instance(GenieACSDriver::class, $driverMock);

        $this->actingAs($techBranch1);

        $service = app(GenieAcsProvisioningService::class);
        $res = $service->provisionOnt($onuBranch2, [
            'pppoe_username' => 'user_cross_test',
            'pppoe_password' => 'secret123',
        ]);

        $this->assertContains($res['status'], ['success', 'waiting_device'], 'Cross-branch provisioning should succeed under Option A without branch restriction.');
    }

    public function test_settings_and_firmware_restricted_to_admin_and_manager(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        // Reseller is an external partner and is redirected away from admin/manager settings routes
        $this->actingAs($reseller)->get('/acs/settings')->assertRedirect(route('reseller-portal.dashboard'));
        $this->actingAs($reseller)->get('/acs/firmware')->assertRedirect(route('reseller-portal.dashboard'));

        $admin = $this->createUserWithRole('administrator');
        $this->actingAs($admin)->get('/acs/settings')->assertStatus(200);
        $this->actingAs($admin)->get('/acs/firmware')->assertStatus(200);
    }
}
