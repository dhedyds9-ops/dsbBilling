<?php

namespace Tests\Feature;

use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Models\Role;
use App\Models\User;
use App\Services\ACS\AcsVerificationEngine;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class AcsReadAfterWriteHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ACSDevice $device;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);
        $this->admin = User::factory()->create([
            'email' => 'admin_raw_test@local.test',
        ]);
        $this->admin->roles()->sync([$role->id]);

        $this->device = ACSDevice::create([
            'device_id' => 'ZTEG-F670L-TEST998877',
            'uuid' => 'ZTEG-F670L-TEST998877',
            'serial_number' => 'ZTEGTEST998877',
            'manufacturer' => 'ZTE',
            'model_name' => 'F670L',
            'status' => 'online',
        ]);
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    /** Test that stale Inform timestamp prevents premature verification against cached data */
    public function test_stale_inform_gate_blocks_premature_verification(): void
    {
        $taskStartTime = Carbon::parse('2026-11-26 12:00:00');
        Carbon::setTestNow($taskStartTime);

        $task = DeviceTask::create([
            'uuid' => 'task-stale-gate-001',
            'acs_device_id' => $this->device->id,
            'type' => 'wifi_update',
            'status' => AcsVerificationEngine::STATUS_PENDING_VERIFY,
            'started_at' => $taskStartTime,
            'parameters' => [
                'device_id' => $this->device->uuid,
                'expected' => [
                    'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID' => 'New_WiFi_SSID',
                ],
            ],
            'created_by' => $this->admin->id,
        ]);

        // GenieACS returns cached parameter with _lastInform BEFORE task dispatch
        $staleInformTime = Carbon::parse('2026-11-26 11:55:00')->toIso8601String();
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);
        $driverMock->shouldReceive('getDeviceParameters')->andReturn([
            '_lastInform' => $staleInformTime,
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    '1' => [
                        'WLANConfiguration' => [
                            '1' => [
                                'SSID' => ['_value' => 'New_WiFi_SSID'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $engine = new AcsVerificationEngine($driverMock);
        $result = $engine->verifyTask($task);

        $this->assertEquals(AcsVerificationEngine::STATUS_PENDING_VERIFY, $result['status'], 'Task must NOT be verified when Inform is stale');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Menunggu Inform terbaru', $result['message']);

        // Assert database record remained pending_verification
        $task->refresh();
        $this->assertEquals(AcsVerificationEngine::STATUS_PENDING_VERIFY, $task->status);
        $this->assertNull($task->completed_at);

        Carbon::setTestNow(); // reset
    }

    /** Test that fresh Inform received after task dispatch enables verified status */
    public function test_fresh_inform_allows_successful_verification(): void
    {
        $taskStartTime = Carbon::parse('2026-11-26 12:00:00');
        Carbon::setTestNow($taskStartTime->copy()->addSeconds(5));

        $task = DeviceTask::create([
            'uuid' => 'task-fresh-inform-002',
            'acs_device_id' => $this->device->id,
            'type' => 'wifi_update',
            'status' => AcsVerificationEngine::STATUS_PENDING_VERIFY,
            'started_at' => $taskStartTime,
            'parameters' => [
                'device_id' => $this->device->uuid,
                'expected' => [
                    'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID' => 'New_WiFi_SSID',
                ],
            ],
            'created_by' => $this->admin->id,
        ]);

        // Inform arrived AFTER task start time
        $freshInformTime = Carbon::parse('2026-11-26 12:00:04')->toIso8601String();
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);
        $driverMock->shouldReceive('getDeviceParameters')->andReturn([
            '_lastInform' => $freshInformTime,
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    '1' => [
                        'WLANConfiguration' => [
                            '1' => [
                                'SSID' => ['_value' => 'New_WiFi_SSID'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $engine = new AcsVerificationEngine($driverMock);
        $result = $engine->verifyTask($task);

        $this->assertEquals(AcsVerificationEngine::STATUS_VERIFIED, $result['status']);
        $this->assertTrue($result['success']);

        $task->refresh();
        $this->assertEquals(AcsVerificationEngine::STATUS_VERIFIED, $task->status);
        $this->assertNotNull($task->completed_at);

        Carbon::setTestNow();
    }

    /** Test floating point comparison tolerance of ±0.01 */
    public function test_floating_point_tolerance_handling(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $engine = new AcsVerificationEngine($driverMock);

        // Within ±0.01 tolerance (diff 0.005) -> Should MATCH
        $expected = ['Device.Optical.Interface.1.TxPower' => 2.10];
        $actualWithin = ['Device.Optical.Interface.1.TxPower' => 2.105];
        $resWithin = $engine->compareParameters($expected, $actualWithin);
        $this->assertTrue($resWithin['matched'], 'Diff 0.005 must be accepted within 0.01 tolerance');
        $this->assertEmpty($resWithin['mismatches']);

        // Exceeding ±0.01 tolerance (diff 0.05) -> Should MISMATCH
        $actualExceeding = ['Device.Optical.Interface.1.TxPower' => 2.15];
        $resExceeding = $engine->compareParameters($expected, $actualExceeding);
        $this->assertFalse($resExceeding['matched'], 'Diff 0.05 must be rejected as float mismatch');
        $this->assertArrayHasKey('Device.Optical.Interface.1.TxPower', $resExceeding['mismatches']);
        $this->assertEquals(0.01, $resExceeding['mismatches']['Device.Optical.Interface.1.TxPower']['tolerance']);
    }

    /** Test strict integer matching */
    public function test_strict_integer_matching(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $engine = new AcsVerificationEngine($driverMock);

        $expected = [
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_ZTE-COM_VLANID' => 200,
        ];

        // Exact integer match
        $actualMatch = [
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_ZTE-COM_VLANID' => 200,
        ];
        $this->assertTrue($engine->compareParameters($expected, $actualMatch)['matched']);

        // Integer mismatch
        $actualMismatch = [
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_ZTE-COM_VLANID' => 201,
        ];
        $res = $engine->compareParameters($expected, $actualMismatch);
        $this->assertFalse($res['matched']);
        $this->assertEquals('Integer mismatch', $res['mismatches']['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_ZTE-COM_VLANID']['reason']);
    }

    /** Test boolean normalization and case-insensitive enum matching */
    public function test_boolean_and_case_insensitive_enum_normalization(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $engine = new AcsVerificationEngine($driverMock);

        $expected = [
            'Enable' => true,
            'ConnectionType' => 'IP_Routed',
            'AddressingType' => 'DHCP',
        ];

        $actual = [
            'Enable' => '1',           // boolean normalized
            'ConnectionType' => 'ip_routed', // case-insensitive enum
            'AddressingType' => 'dhcp',      // case-insensitive enum
        ];

        $res = $engine->compareParameters($expected, $actual);
        $this->assertTrue($res['matched'], 'Booleans and enums must normalize accurately');
    }

    /** Test verification timeout after 180 seconds transitions task to failed */
    public function test_verification_timeout_after_180_seconds(): void
    {
        $past = now()->subSeconds(200);

        $task = new DeviceTask([
            'uuid' => 'task-timeout-003',
            'acs_device_id' => $this->device->id,
            'type' => 'wan_update',
            'status' => AcsVerificationEngine::STATUS_PENDING_VERIFY,
            'started_at' => $past,
            'created_at' => $past,
            'parameters' => [
                'device_id' => $this->device->uuid,
                'expected' => [
                    'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username' => 'user_target',
                ],
            ],
            'created_by' => $this->admin->id,
        ]);
        $task->timestamps = false;
        $task->created_at = $past;
        $task->started_at = $past;
        $task->save();

        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);
        $driverMock->shouldReceive('getDeviceParameters')->andReturn([
            '_lastInform' => now()->toIso8601String(),
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Username' => ['_value' => 'user_different'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $engine = new AcsVerificationEngine($driverMock);
        $result = $engine->verifyTask($task);

        $this->assertEquals(AcsVerificationEngine::STATUS_FAILED, $result['status']);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Verifikasi timeout', $result['message']);

        $task->refresh();
        $this->assertEquals(AcsVerificationEngine::STATUS_FAILED, $task->status);
        $this->assertNotNull($task->failed_at);
        $this->assertNotNull($task->error_message);
    }
}
