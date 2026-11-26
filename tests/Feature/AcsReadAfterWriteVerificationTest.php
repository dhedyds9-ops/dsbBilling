<?php

namespace Tests\Feature;

use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Models\ISP\Onu;
use App\Models\Role;
use App\Models\User;
use App\Services\ACS\AcsVerificationEngine;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AcsReadAfterWriteVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ACSDevice $device;
    protected Onu $onu;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);
        $this->admin = User::factory()->create([
            'email' => 'admin_acs_test@local.test',
        ]);
        $this->admin->roles()->sync([$role->id]);

        $this->device = ACSDevice::create([
            'device_id' => 'ZTEG-F670L-ZTEGC11223344',
            'uuid' => 'ZTEG-F670L-ZTEGC11223344',
            'serial_number' => 'ZTEGC11223344',
            'manufacturer' => 'ZTE',
            'model_name' => 'F670L',
            'status' => 'online',
        ]);

        $this->onu = Onu::create([
            'name' => 'ONU ZTE F670L',
            'serial_number' => 'ZTEGC11223344',
            'model_name' => 'F670L',
            'wifi_ssid' => 'Original_SSID',
            'wifi_password' => 'Original_Pass',
            'rx_power_dbm' => -19.45,
            'tx_power_dbm' => 2.30,
        ]);

        $this->device->update(['onu_id' => $this->onu->id]);
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_verification_engine_records_task_and_dispatches_with_lifecycle(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);
        $driverMock->shouldReceive('getDeviceParameters')->andReturn([
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    1 => [
                        'WLANConfiguration' => [
                            1 => ['SSID' => ['_value' => 'New_Verified_SSID']],
                        ],
                    ],
                ],
            ],
        ]);
        $driverMock->shouldReceive('refreshObject')->andReturn(true);

        $engine = new AcsVerificationEngine($driverMock);

        $result = $engine->executeWithVerification(
            deviceId: $this->device->uuid,
            taskType: 'wifi_update',
            expectedChanges: [
                'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID' => 'New_Verified_SSID',
            ],
            writeCallback: function () {
                return ['success' => true, 'task_id' => 'genie_task_999'];
            },
            userId: $this->admin->id,
            refreshPath: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1',
            autoVerifyImmediately: true
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(AcsVerificationEngine::STATUS_VERIFIED, $result['status']);
        $this->assertNotNull($result['task_id']);

        $task = DeviceTask::where('uuid', $result['task_id'])->first();
        $this->assertNotNull($task);
        $this->assertEquals(AcsVerificationEngine::STATUS_VERIFIED, $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_mobile_device_detail_includes_phase2_telemetry(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);
        $driverMock->shouldReceive('getDeviceParameters')->andReturn([
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    1 => [
                        'X_ZTE-COM_WANPONInterfaceConfig' => [
                            'RXPower' => ['_value' => -2050], // -20.50 dBm
                            'TXPower' => ['_value' => 240],   // 2.40 dBm
                        ],
                        'WANPONInterfaceConfig' => [
                            1 => [
                                'X_ZTE-COM_TransceiverTemperature' => ['_value' => 4500], // 45.0 °C
                                'X_ZTE-COM_TransceiverVoltage' => ['_value' => 3300],     // 3.3 V
                                'X_ZTE-COM_TransceiverBiasCurrent' => ['_value' => 1500], // 15.0 mA
                            ],
                        ],
                        'WANConnectionDevice' => [
                            1 => [
                                'WANPPPConnection' => [
                                    1 => [
                                        'Name' => ['_value' => '1_INTERNET_R_VID_200'],
                                        'ConnectionType' => ['_value' => 'IP_Routed'],
                                        'X_ZTE-COM_VLANID' => ['_value' => 200],
                                        'Username' => ['_value' => 'user_actual'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'LANDevice' => [
                    1 => [
                        'WLANConfiguration' => [
                            1 => [
                                'Enable' => ['_value' => true],
                                'SSID' => ['_value' => 'Home_WiFi_2.4G'],
                                'Channel' => ['_value' => 6],
                                'BeaconType' => ['_value' => '11i'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $driverMock->shouldReceive('extractConnectedDevicesFromParams')->andReturn([]);
        $driverMock->shouldReceive('extractAllWlansFromParams')->andReturn([]);

        $this->app->instance(GenieACSDriver::class, $driverMock);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/mobile/acs/devices/{$this->device->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
                'optical_diagnostics' => [
                    'rx_power',
                    'tx_power',
                    'rx_formula',
                    'voltage',
                    'bias_current',
                    'temp_celsius',
                ],
                'wifi_details' => [
                    'band_24_ssids',
                    'band_5_ssids',
                    'total_ssids_count',
                    'has_5ghz',
                ],
                'port_bindings' => [
                    'lan_ports',
                    'total_lan_ports',
                ],
                'wan_connections',
            ]);

        $optical = $response->json('optical_diagnostics');
        $this->assertEquals(-20.5, $optical['rx_power']);
        $this->assertEquals(2.4, $optical['tx_power']);
        $this->assertEquals(3.3, $optical['voltage']);
        $this->assertEquals(1.5, $optical['bias_current']);
        $this->assertEquals(45.0, $optical['temp_celsius']);
    }

    public function test_mobile_set_wifi_executes_with_verification(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);
        $driverMock->shouldReceive('getDeviceParameters')->andReturn([
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    1 => [
                        'WLANConfiguration' => [
                            1 => [
                                'Enable' => ['_value' => true],
                                'SSID' => ['_value' => 'Old_WiFi'],
                            ],
                        ],
                    ],
                ],
            ],
        ])->zeroOrMoreTimes();

        $driverMock->shouldReceive('setParameterValues')->once()->andReturn(true);
        $driverMock->shouldReceive('refreshObject')->zeroOrMoreTimes()->andReturn(true);

        $this->app->instance(GenieACSDriver::class, $driverMock);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/mobile/acs/devices/{$this->device->uuid}/wifi", [
                'wlan_index' => 1,
                'ssid' => 'Updated_Home_WiFi',
                'password' => 'superSecretPassword',
                'security_mode' => 'WPA2',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotNull($response->json('task_id'));
    }
}
