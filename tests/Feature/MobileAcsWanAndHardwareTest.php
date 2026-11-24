<?php

namespace Tests\Feature;

use App\Enums\UserPermission;
use App\Models\ACS\ACSDevice;
use App\Models\Master\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ACS\AcsWanManagerService;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MobileAcsWanAndHardwareTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $technician;
    protected Branch $branch;
    protected ACSDevice $device;
    protected string $deviceId = '202BC1-F670L-ZTEGC12345678';

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);
        $techRole = Role::firstOrCreate(['name' => 'teknisi'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->branch = Branch::firstOrCreate(['code' => 'BR-01'], ['name' => 'Cabang Utama']);

        // Admin User
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Sistem',
            'email' => 'admin_mobile_wan@test.local',
            'is_active' => true,
        ]);
        $this->adminUser->roles()->attach($adminRole->id);

        // Technician User
        $this->technician = User::factory()->create([
            'name' => 'Teknisi Lapangan',
            'email' => 'tech_mobile_wan@test.local',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);

        // Required Attendance check-in for technicians
        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        $this->device = ACSDevice::create([
            'uuid' => $this->deviceId,
            'serial_number' => 'ZTEGC12345678',
            'manufacturer' => 'ZTE',
            'product_class' => 'F670L',
            'branch_id' => $this->branch->id,
            'status' => 'online',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function grantPermission(User $user, string $permissionName): void
    {
        $perm = Permission::firstOrCreate(['name' => $permissionName], ['display_name' => $permissionName, 'guard_name' => 'web']);
        if (!$user->permissions()->where('permissions.id', $perm->id)->exists()) {
            $user->permissions()->attach($perm->id);
        }
    }

    /** Test get WAN list returns parsed connections */
    public function test_get_wan_list_endpoint(): void
    {
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $wanMock = Mockery::mock(AcsWanManagerService::class);
        $wanMock->shouldReceive('getWanConnections')
            ->once()
            ->with($this->deviceId)
            ->andReturn([
                'vendor' => 'zte',
                'connections' => [
                    [
                        'id' => '1.1.1',
                        'name' => '1_INTERNET_R_VID_200',
                        'type' => 'PPPoE',
                        'mode_label' => 'PPPoE Route',
                        'enable' => true,
                        'username' => 'user_test_mobile',
                        'vlan' => 200,
                        'fullPath' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
                    ]
                ],
                'raw_parameters' => [],
            ]);
        $this->app->instance(AcsWanManagerService::class, $wanMock);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/mobile/acs/devices/{$this->deviceId}/wan");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'vendor' => 'zte',
            ])
            ->assertJsonPath('data.0.username', 'user_test_mobile');
    }

    /** Test add WAN endpoint */
    public function test_add_wan_endpoint(): void
    {
        $this->grantPermission($this->technician, UserPermission::OnuConfigureWan->value);
        $token = $this->technician->createToken('test-token')->plainTextToken;

        $wanMock = Mockery::mock(AcsWanManagerService::class);
        $wanMock->shouldReceive('addWanConnection')
            ->once()
            ->with($this->deviceId, Mockery::type('array'), $this->technician->id)
            ->andReturn([
                'success' => true,
                'task_id' => 'task-uuid-123',
                'message' => 'Task pembuatan WAN berhasil dikirim ke GenieACS.',
            ]);
        $this->app->instance(AcsWanManagerService::class, $wanMock);

        $payload = [
            'mode' => 'pppoe_route',
            'username' => 'client_speedy',
            'password' => 'pass123',
            'vlan_enabled' => true,
            'vlan_id' => 200,
            'nat_enabled' => true,
            'service_list' => 'INTERNET',
            'bind_lan' => [1, 2],
            'bind_wlan' => [1],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/mobile/acs/devices/{$this->deviceId}/wan/add", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'task_id' => 'task-uuid-123',
            ]);
    }

    /** Test update WAN endpoint */
    public function test_update_wan_endpoint(): void
    {
        $this->grantPermission($this->technician, UserPermission::OnuConfigureWan->value);
        $token = $this->technician->createToken('test-token')->plainTextToken;

        $wanMock = Mockery::mock(AcsWanManagerService::class);
        $wanMock->shouldReceive('updateWanConnection')
            ->once()
            ->with(
                $this->deviceId,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
                Mockery::type('array'),
                $this->technician->id
            )
            ->andReturn([
                'success' => true,
                'task_id' => 'task-uuid-456',
                'message' => 'Perubahan WAN berhasil dikirim ke perangkat.',
            ]);
        $this->app->instance(AcsWanManagerService::class, $wanMock);

        $payload = [
            'full_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
            'mode' => 'pppoe_route',
            'username' => 'updated_speedy',
            'password' => 'new_pass123',
            'vlan_enabled' => true,
            'vlan_id' => 250,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/mobile/acs/devices/{$this->deviceId}/wan/update", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'task_id' => 'task-uuid-456',
            ]);
    }

    /** Test delete WAN endpoint */
    public function test_delete_wan_endpoint(): void
    {
        $this->grantPermission($this->technician, UserPermission::OnuConfigureWan->value);
        $token = $this->technician->createToken('test-token')->plainTextToken;

        $wanMock = Mockery::mock(AcsWanManagerService::class);
        $wanMock->shouldReceive('deleteWanConnection')
            ->once()
            ->with(
                $this->deviceId,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
                $this->technician->id
            )
            ->andReturn([
                'success' => true,
                'task_id' => 'task-uuid-789',
                'message' => 'Perintah hapus WAN berhasil dikirim ke GenieACS.',
            ]);
        $this->app->instance(AcsWanManagerService::class, $wanMock);

        $payload = [
            'full_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/mobile/acs/devices/{$this->deviceId}/wan/delete", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'task_id' => 'task-uuid-789',
            ]);
    }

    /** Test get hardware diagnostics endpoint */
    public function test_get_hardware_endpoint(): void
    {
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->with($this->deviceId)->andReturn($this->deviceId);
        $driverMock->shouldReceive('getDeviceParameters')->with($this->deviceId)->andReturn([
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'X_ZTE-COM_CPUUsage' => ['_value' => '42'],
                    'X_ZTE-COM_MemoryUsage' => ['_value' => '55'],
                    'UpTime' => ['_value' => '3600'],
                ]
            ]
        ]);
        $this->app->instance(GenieACSDriver::class, $driverMock);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/mobile/acs/devices/{$this->deviceId}/hardware");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'cpu_usage' => 42.0,
                    'cpu_status' => 'supported',
                    'ram_usage' => 55.0,
                    'ram_status' => 'supported',
                    'uptime_seconds' => 3600,
                ]
            ]);
    }

    /** Test deviceDetail endpoint returns hardware_metrics and wan_connections */
    public function test_device_detail_includes_hardware_and_wan(): void
    {
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->with($this->deviceId)->andReturn($this->deviceId);
        $driverMock->shouldReceive('getDeviceParameters')->with($this->deviceId)->andReturn([
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'X_ZTE-COM_CPUUsage' => ['_value' => '15'],
                ],
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => ['_value' => '1_INTERNET_R_VID_100'],
                                        'Username' => ['_value' => 'pppoe_user'],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $driverMock->shouldReceive('extractConnectedDevicesFromParams')->andReturn([]);
        $driverMock->shouldReceive('extractAllWlansFromParams')->andReturn([]);
        $this->app->instance(GenieACSDriver::class, $driverMock);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/mobile/acs/devices/{$this->deviceId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.hardware_metrics.cpu_usage', 15)
            ->assertJsonPath('data.hardware_metrics.cpu_status', 'supported')
            ->assertJsonPath('data.wan_connections.0.username', 'pppoe_user');
    }

    /** Test unauthenticated user is rejected */
    public function test_unauthenticated_requests_are_rejected(): void
    {
        $res = $this->getJson("/api/mobile/acs/devices/{$this->deviceId}/wan");
        $res->assertStatus(401);

        $res2 = $this->getJson("/api/mobile/acs/devices/{$this->deviceId}/hardware");
        $res2->assertStatus(401);
    }
}
