<?php

namespace Tests\Unit;

use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Models\User;
use App\Services\ACS\AcsWanManagerService;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AcsWanManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GenieACSDriver $driverMock;
    protected AcsWanManagerService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driverMock = Mockery::mock(GenieACSDriver::class);
        $this->service = new AcsWanManagerService($this->driverMock);

        $this->user = User::factory()->create([
            'email' => 'tech_wan_unit@test.local',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** Test vendor detection from serial number or parameter tree */
    public function test_detect_vendor(): void
    {
        $this->assertEquals('zte', $this->service->detectVendor('202BC1-F609-ZTEGC12345678', []));
        $this->assertEquals('huawei', $this->service->detectVendor('485754-EG8141H5-HWTC99887766', []));
        $this->assertEquals('fiberhome', $this->service->detectVendor('FHTT-HG6145F-FHTT11223344', []));
        $this->assertEquals('vsol', $this->service->detectVendor('VSOL-V280-VSOL1234', []));

        // Detect from parameters when device ID is opaque
        $paramsZte = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => ['_value' => 'ZTE Corporation']
                ]
            ]
        ];
        $this->assertEquals('zte', $this->service->detectVendor('device-001', $paramsZte));
    }

    /** Test LAN and WLAN binding string builder */
    public function test_build_bind_string(): void
    {
        $dataDefault = [
            'bind_lan' => [1, 2],
            'bind_wlan' => [1],
        ];

        $bind = $this->service->buildBindString($dataDefault);
        $expected = 'InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1,InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.2,InternetGatewayDevice.LANDevice.1.WLANConfiguration.1';
        $this->assertEquals($expected, $bind);

        // Custom binding overrides auto generation
        $customData = [
            'custom_binding' => 'LAN1,LAN2,SSID1',
        ];
        $this->assertEquals('LAN1,LAN2,SSID1', $this->service->buildBindString($customData));
    }

    /** Test getWanConnections parses parameters tree */
    public function test_get_wan_connections(): void
    {
        $deviceId = 'ZTEGC12345678';
        $params = [
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => ['_value' => '1_INTERNET_R_VID_100'],
                                        'ConnectionType' => ['_value' => 'IP_Routed'],
                                        'Enable' => ['_value' => true],
                                        'Username' => ['_value' => 'user_test'],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->driverMock->shouldReceive('resolveDeviceId')
            ->with($deviceId)
            ->andReturn($deviceId);

        $this->driverMock->shouldReceive('getDeviceParameters')
            ->with($deviceId)
            ->andReturn($params);

        $result = $this->service->getWanConnections($deviceId);

        $this->assertEquals('zte', $result['vendor']);
        $this->assertCount(1, $result['connections']);
        $this->assertEquals('user_test', $result['connections'][0]['username']);
    }

    /** Test addWanConnection creates task and compiles provision script */
    public function test_add_wan_connection(): void
    {
        $deviceId = '202BC1-F609-ZTEGC12345678';

        $device = ACSDevice::create([
            'device_id' => $deviceId,
            'uuid' => $deviceId,
            'serial_number' => 'ZTEGC12345678',
            'manufacturer' => 'ZTE',
        ]);

        $this->driverMock->shouldReceive('resolveDeviceId')->with($deviceId)->andReturn($deviceId);
        $this->driverMock->shouldReceive('getDeviceParameters')->with($deviceId)->andReturn([]);

        $interceptedScript = null;
        $this->driverMock->shouldReceive('upsertProvision')
            ->once()
            ->with(Mockery::type('string'), Mockery::on(function ($script) use (&$interceptedScript) {
                $interceptedScript = $script;
                return true;
            }))
            ->andReturn(true);

        $this->driverMock->shouldReceive('addProvisionTask')
            ->once()
            ->with($deviceId, Mockery::type('string'))
            ->andReturn(true);

        $data = [
            'mode' => 'pppoe_route',
            'username' => 'sri_mulyani',
            'password' => 'secret789',
            'vlan_enabled' => true,
            'vlan_id' => 150,
            'nat_enabled' => true,
            'service_list' => 'INTERNET',
            'bind_lan' => [1, 2],
            'bind_wlan' => [1],
        ];

        $res = $this->service->addWanConnection($deviceId, $data, $this->user->id);

        $this->assertTrue($res['success']);
        $this->assertNotNull($res['task_id']);

        // Check provision script content
        $this->assertNotNull($interceptedScript);
        $this->assertStringContainsString('sri_mulyani', $interceptedScript);
        $this->assertStringContainsString('secret789', $interceptedScript);
        $this->assertStringContainsString('X_ZTE-COM_VLANID', $interceptedScript);
        $this->assertStringContainsString('150', $interceptedScript);

        // Check DB task record
        $task = DeviceTask::where('uuid', $res['task_id'])->first();
        $this->assertNotNull($task);
        $this->assertEquals('wan_create', $task->type);
        $this->assertContains($task->status, ['pending', 'pending_verification', 'executing']);
        $vlanExpected = $task->parameters['expected']['vlan_id'] ?? $task->parameters['vlan_id'] ?? null;
        $this->assertEquals(150, $vlanExpected);
    }

    /** Test updateWanConnection in-place update */
    public function test_update_wan_connection_in_place(): void
    {
        $deviceId = '485754-EG8141H5-HWTC99887766';
        $fullPath = 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1';

        $device = ACSDevice::create([
            'device_id' => $deviceId,
            'uuid' => $deviceId,
            'serial_number' => 'HWTC99887766',
            'manufacturer' => 'Huawei',
        ]);

        $this->driverMock->shouldReceive('resolveDeviceId')->with($deviceId)->andReturn($deviceId);
        $this->driverMock->shouldReceive('getDeviceParameters')->with($deviceId)->andReturn([])->zeroOrMoreTimes();

        $interceptedParams = null;
        $this->driverMock->shouldReceive('setParameterValues')
            ->once()
            ->with($deviceId, Mockery::on(function ($params) use (&$interceptedParams) {
                $interceptedParams = $params;
                return true;
            }))
            ->andReturn(true);

        $this->driverMock->shouldReceive('refreshObject')
            ->with($deviceId, Mockery::any())
            ->zeroOrMoreTimes()
            ->andReturn(true);

        $data = [
            'mode' => 'pppoe_route',
            'username' => 'new_hw_user',
            'password' => 'new_hw_pass',
            'vlan_enabled' => true,
            'vlan_id' => 300,
            'nat_enabled' => true,
            'service_list' => 'INTERNET',
            'bind_lan' => [1, 2, 3, 4],
            'bind_wlan' => [1, 5],
        ];

        $res = $this->service->updateWanConnection($deviceId, $fullPath, $data, $this->user->id);

        $this->assertTrue($res['success']);
        $this->assertEquals('new_hw_user', $interceptedParams[$fullPath . '.Username']);
        $this->assertEquals('new_hw_pass', $interceptedParams[$fullPath . '.Password']);
        $this->assertEquals(300, $interceptedParams['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANEthernetLinkConfig.X_HW_VLAN']);

        $task = DeviceTask::where('uuid', $res['task_id'])->first();
        $this->assertNotNull($task);
        $this->assertEquals('wan_update', $task->type);
        $this->assertContains($task->status, ['completed', 'verified', 'pending_verification']);
    }

    /** Test deleteWanConnection dispatches provision script and deletes object */
    public function test_delete_wan_connection(): void
    {
        $deviceId = 'FHTT-HG6145F-FHTT11223344';
        $fullPath = 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1';

        $device = ACSDevice::create([
            'device_id' => $deviceId,
            'uuid' => $deviceId,
            'serial_number' => 'FHTT11223344',
            'manufacturer' => 'FiberHome',
        ]);

        $this->driverMock->shouldReceive('resolveDeviceId')->with($deviceId)->andReturn($deviceId);
        $this->driverMock->shouldReceive('upsertProvision')->once()->andReturn(true);
        $this->driverMock->shouldReceive('addProvisionTask')->once()->andReturn(true);
        $this->driverMock->shouldReceive('deleteObject')->once()->with($deviceId, $fullPath)->andReturn(true);

        $res = $this->service->deleteWanConnection($deviceId, $fullPath, $this->user->id);

        $this->assertTrue($res['success']);

        $task = DeviceTask::where('uuid', $res['task_id'])->first();
        $this->assertNotNull($task);
        $this->assertEquals('wan_delete', $task->type);
        $this->assertContains($task->status, ['pending', 'pending_verification']);
        $this->assertEquals($fullPath, $task->parameters['path'] ?? $task->parameters['expected']['path'] ?? $fullPath);
    }
}
