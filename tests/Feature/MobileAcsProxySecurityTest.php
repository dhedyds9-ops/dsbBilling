<?php

namespace Tests\Feature;

use App\Enums\UserPermission;
use App\Models\ACS\ACSDevice;
use App\Models\Customer\CustomerService;
use App\Models\Master\Branch;
use App\Models\ISP\Onu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class MobileAcsProxySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $technicianBranch1;
    protected User $technicianBranch2;
    protected User $resellerA;
    protected User $resellerB;
    protected User $warehouseStaff;
    protected Branch $branch1;
    protected Branch $branch2;

    protected ACSDevice $deviceResellerA;
    protected ACSDevice $deviceResellerB;
    protected ACSDevice $deviceBranch2;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);
        $techRole = Role::firstOrCreate(['name' => 'teknisi'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Mitra Reseller', 'guard_name' => 'web']);
        $warehouseRole = Role::firstOrCreate(['name' => 'admin_gudang'], ['display_name' => 'Admin Gudang', 'guard_name' => 'web']);

        $this->branch1 = Branch::firstOrCreate(['code' => 'BR-01'], ['name' => 'Cabang Barat']);
        $this->branch2 = Branch::firstOrCreate(['code' => 'BR-02'], ['name' => 'Cabang Timur']);

        // Admin User
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Sistem',
            'email' => 'admin@dsbilling.test',
            'is_active' => true,
        ]);
        $this->adminUser->roles()->attach($adminRole->id);

        // Technician 1 (Branch 1)
        $this->technicianBranch1 = User::factory()->create([
            'name' => 'Teknisi Barat',
            'email' => 'tech1@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);
        $this->technicianBranch1->roles()->attach($techRole->id);

        // Technician 2 (Branch 2)
        $this->technicianBranch2 = User::factory()->create([
            'name' => 'Teknisi Timur',
            'email' => 'tech2@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch2->id,
            'is_active' => true,
        ]);
        $this->technicianBranch2->roles()->attach($techRole->id);

        // Reseller A (Branch 1)
        $this->resellerA = User::factory()->create([
            'name' => 'Mitra Reseller A',
            'email' => 'reseller_a@dsbilling.test',
            'job_function' => 'RESELLER',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);
        $this->resellerA->roles()->attach($resellerRole->id);

        // Reseller B (Branch 1)
        $this->resellerB = User::factory()->create([
            'name' => 'Mitra Reseller B',
            'email' => 'reseller_b@dsbilling.test',
            'job_function' => 'RESELLER',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);
        $this->resellerB->roles()->attach($resellerRole->id);

        // Warehouse Staff
        $this->warehouseStaff = User::factory()->create([
            'name' => 'Staff Gudang',
            'email' => 'gudang@dsbilling.test',
            'job_title' => 'Admin Gudang',
            'is_active' => true,
        ]);
        $this->warehouseStaff->roles()->attach($warehouseRole->id);

        // Setup Catalog & Service
        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Katalog Layanan',
        ]);

        $service = \App\Models\ServiceCatalog\Service::create([
            'uuid' => (string) Str::uuid(),
            'service_catalog_id' => $catalog->id,
            'name' => 'Internet 50 Mbps',
            'price' => 250000,
        ]);

        // Setup Customer & Contract for Reseller A
        $custA = \App\Models\CRM\Customer::create([
            'code' => 'CUST-RES-A',
            'name' => 'Pelanggan Reseller A',
            'phone' => '081234567891',
            'address' => 'Alamat Reseller A',
            'reseller_id' => $this->resellerA->id,
            'branch_id' => $this->branch1->id,
            'created_by' => $this->resellerA->id,
        ]);

        $contractA = \App\Models\CRM\Contract::create([
            'customer_id' => $custA->id,
            'start_date' => now(),
            'created_by' => $this->resellerA->id,
        ]);

        // Setup Device for Reseller A via CustomerService
        $onuA = Onu::create([
            'code' => 'ONU-RESELLER-A',
            'name' => 'ONU Reseller A',
            'serial_number' => 'ZTEGCRESELLER_A',
            'branch_id' => $this->branch1->id,
            'created_by' => $this->resellerA->id,
        ]);

        $csA = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $custA->id,
            'contract_id' => $contractA->id,
            'service_id' => $service->id,
            'username' => 'cs_user_a',
            'password' => 'secret_a',
            'onu_id' => $onuA->id,
            'reseller_id' => $this->resellerA->id,
            'branch_id' => $this->branch1->id,
            'created_by' => $this->resellerA->id,
        ]);

        $this->deviceResellerA = ACSDevice::create([
            'uuid' => (string) Str::uuid(),
            'serial_number' => 'ZTEGCRESELLER_A',
            'customer_service_id' => $csA->id,
            'onu_id' => $onuA->id,
            'created_by' => $this->resellerA->id,
        ]);

        // Setup Customer & Contract for Reseller B
        $custB = \App\Models\CRM\Customer::create([
            'code' => 'CUST-RES-B',
            'name' => 'Pelanggan Reseller B',
            'phone' => '081234567892',
            'address' => 'Alamat Reseller B',
            'reseller_id' => $this->resellerB->id,
            'branch_id' => $this->branch1->id,
            'created_by' => $this->resellerB->id,
        ]);

        $contractB = \App\Models\CRM\Contract::create([
            'customer_id' => $custB->id,
            'start_date' => now(),
            'created_by' => $this->resellerB->id,
        ]);

        // Setup Device for Reseller B via CustomerService
        $onuB = Onu::create([
            'code' => 'ONU-RESELLER-B',
            'name' => 'ONU Reseller B',
            'serial_number' => 'ZTEGCRESELLER_B',
            'branch_id' => $this->branch1->id,
            'created_by' => $this->resellerB->id,
        ]);

        $csB = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $custB->id,
            'contract_id' => $contractB->id,
            'service_id' => $service->id,
            'username' => 'cs_user_b',
            'password' => 'secret_b',
            'onu_id' => $onuB->id,
            'reseller_id' => $this->resellerB->id,
            'branch_id' => $this->branch1->id,
            'created_by' => $this->resellerB->id,
        ]);

        $this->deviceResellerB = ACSDevice::create([
            'uuid' => (string) Str::uuid(),
            'serial_number' => 'ZTEGCRESELLER_B',
            'customer_service_id' => $csB->id,
            'onu_id' => $onuB->id,
            'created_by' => $this->resellerB->id,
        ]);

        // Setup Device in Branch 2 (belonging to no reseller)
        $onuBranch2 = Onu::create([
            'code' => 'ONU-BRANCH2',
            'name' => 'ONU Branch 2',
            'serial_number' => 'ZTEGCBRANCH2',
            'branch_id' => $this->branch2->id,
        ]);

        $this->deviceBranch2 = ACSDevice::create([
            'uuid' => (string) Str::uuid(),
            'serial_number' => 'ZTEGCBRANCH2',
            'onu_id' => $onuBranch2->id,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper to give a permission to a user.
     */
    protected function grantPermission(User $user, string $permissionName): void
    {
        $perm = Permission::firstOrCreate(['name' => $permissionName], [
            'display_name' => $permissionName,
            'guard_name' => 'web',
        ]);
        $user->directPermissions()->syncWithoutDetaching([$perm->id]);
        $user->load('directPermissions');
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/reboot');
        $response->assertStatus(401);
    }

    public function test_unauthorized_warehouse_staff_is_rejected(): void
    {
        $token = $this->warehouseStaff->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/reboot');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak: Staf gudang tidak memiliki izin untuk mengelola jaringan TR-069.',
            ]);
    }

    /**
     * Requirement 1: Reseller A with permission WAN can create WAN configuration on own ONU.
     */
    public function test_reseller_a_with_wan_permission_can_configure_wan(): void
    {
        $this->grantPermission($this->resellerA, UserPermission::OnuConfigureWan->value);
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('setParameterValues')
            ->once()
            ->with('ZTEGCRESELLER_A', Mockery::on(function ($kv) {
                return isset($kv['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username'])
                    && $kv['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username'] === 'user_mitra_a';
            }))
            ->andReturn(true);

        $this->app->instance(GenieACSDriver::class, $driverMock);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/wan', [
                'wan_index' => 1,
                'connection_type' => 'PPPoE_Routed',
                'username' => 'user_mitra_a',
                'password' => 'secret_mitra',
                'vlan_id' => 200,
                'vlan_enabled' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Konfigurasi WAN berhasil diterapkan.',
            ]);
    }

    /**
     * Requirement 2: Reseller A without permission WAN is rejected.
     */
    public function test_reseller_a_without_wan_permission_is_rejected(): void
    {
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/wan', [
                'wan_index' => 1,
                'username' => 'user_mitra_a',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => "Akses ditolak: Anda tidak memiliki permission untuk operasi 'wan'.",
            ]);
    }

    /**
     * Requirement 3: Reseller A cannot modify ONU belonging to Reseller B.
     */
    public function test_reseller_a_cannot_modify_onu_belonging_to_reseller_b(): void
    {
        $this->grantPermission($this->resellerA, UserPermission::OnuConfigureWan->value);
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        // Reseller A tries to configure Reseller B's device
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_B/wan', [
                'wan_index' => 1,
                'username' => 'hijack_user',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak: Perangkat ini terdaftar pada pelanggan Mitra Reseller lain.',
            ]);
    }

    /**
     * Requirement 4: Reseller A cannot access other tenant/branch device.
     */
    public function test_reseller_a_cannot_access_other_branch_device(): void
    {
        $this->grantPermission($this->resellerA, UserPermission::OnuConfigureWifi->value);
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCBRANCH2/wifi', [
                'ssid' => 'CrossBranchWiFi',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak: Perangkat ini berada di luar cakupan kepemilikan akun Reseller Anda.',
            ]);
    }

    /**
     * Requirement 5: Reseller A can manage WiFi if permission available.
     */
    public function test_reseller_a_can_manage_wifi_with_permission(): void
    {
        $this->grantPermission($this->resellerA, UserPermission::OnuConfigureWifi->value);
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('setParameterValues')
            ->once()
            ->with('ZTEGCRESELLER_A', Mockery::on(function ($kv) {
                return isset($kv['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID'])
                    && $kv['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID'] === 'Mitra-WiFi-OK';
            }))
            ->andReturn(true);

        $this->app->instance(GenieACSDriver::class, $driverMock);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/wifi', [
                'wlan_index' => 1,
                'ssid' => 'Mitra-WiFi-OK',
                'password' => 'secret12345',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pengaturan Wi-Fi berhasil diterapkan.',
            ]);
    }

    /**
     * Requirement 6: Reseller A without admin permission cannot change ONU admin account.
     */
    public function test_reseller_a_without_admin_permission_cannot_change_onu_admin_account(): void
    {
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/admin-account', [
                'username' => 'superadmin',
                'password' => 'newpassword123',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => "Akses ditolak: Anda tidak memiliki permission untuk operasi 'admin'.",
            ]);
    }

    /**
     * Requirement 7: Reseller cannot delete task without permission.
     */
    public function test_reseller_cannot_delete_task_without_permission(): void
    {
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/tasks/task-999');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => "Akses ditolak: Anda tidak memiliki permission untuk operasi 'tasks.delete'.",
            ]);
    }

    /**
     * Requirement 8: Technician follows assignment and branch scope.
     */
    public function test_technician_cross_branch_access_is_blocked(): void
    {
        $this->grantPermission($this->technicianBranch1, UserPermission::OnuReboot->value);
        $token = $this->technicianBranch1->createToken('test-token')->plainTextToken;

        // Technician Branch 1 attempts to reboot device belonging to Branch 2
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCBRANCH2/reboot');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak: Perangkat ini terdaftar pada cabang/tenant lain.',
            ]);
    }

    /**
     * Requirement 8 (cont): Technician can manage device within own branch.
     */
    public function test_technician_can_manage_device_within_own_branch(): void
    {
        $this->grantPermission($this->technicianBranch1, UserPermission::OnuReboot->value);
        $token = $this->technicianBranch1->createToken('test-token')->plainTextToken;

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('rebootDevice')
            ->once()
            ->with('ZTEGCRESELLER_A')
            ->andReturn(true);

        $this->app->instance(GenieACSDriver::class, $driverMock);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER_A/reboot');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Perintah restart berhasil dikirim ke perangkat.',
            ]);
    }

    /**
     * Requirement 9: Administrator has full access to all operations.
     */
    public function test_administrator_has_full_access_to_all_operations(): void
    {
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('setParameterValues')->once()->andReturn(true);
        $this->app->instance(GenieACSDriver::class, $driverMock);

        // Administrator can configure WAN on any device without explicit direct permissions
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCBRANCH2/wan', [
                'wan_index' => 1,
                'username' => 'admin_wan',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Requirement 10: Device ID manipulation cannot bypass ownership validation.
     */
    public function test_device_id_manipulation_cannot_bypass_ownership(): void
    {
        $this->grantPermission($this->resellerA, UserPermission::OnuConfigureWifi->value);
        $token = $this->resellerA->createToken('test-token')->plainTextToken;

        // Case A: Wildcard 'ALL' is blocked
        $resAll = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ALL/wifi', ['ssid' => 'Hacked']);
        $resAll->assertStatus(403);

        // Case B: Partial substring spoofing (e.g. 'ZTEGCRESELLER' prefix targeting non-existent or other device)
        $resPartial = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/ZTEGCRESELLER/wifi', ['ssid' => 'Hacked']);
        $resPartial->assertStatus(403);

        // Case C: Unregistered spoofed device ID is blocked
        $resFake = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/acs/devices/FAKE_DEVICE_ID_9999/wifi', ['ssid' => 'Hacked']);
        $resFake->assertStatus(403);
    }
}
