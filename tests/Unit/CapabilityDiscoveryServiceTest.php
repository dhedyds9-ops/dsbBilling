<?php

namespace Tests\Unit\Provisioning;

use App\Models\ISP\Onu;
use App\Models\ISP\OnuCapability;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Services\Provisioning\CapabilityDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CapabilityDiscoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CapabilityDiscoveryService $service;
    protected MockInterface $acsMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->acsMock = Mockery::mock(GenieACSDriver::class);
        $this->service = new CapabilityDiscoveryService($this->acsMock);
    }

    public function test_discovery_with_bridge_available()
    {
        $onuId = \Illuminate\Support\Facades\DB::table('onus')->insertGetId([
            'serial_number' => 'ZTEG12345678',
            'code' => 'ONU-TEST-'.rand(1000,9999),
            'name' => 'Test ONU',
            'mac_address' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $onu = Onu::find($onuId);

        $this->acsMock->shouldReceive('getDeviceParameters')->with('ZTEG12345678')->andReturn([
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'ZTE',
                    'ModelName' => 'F670L',
                    'HardwareVersion' => 'V1.0',
                    'SoftwareVersion' => 'V1.0.1'
                ],
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => []
                        ],
                        'WANIPConnection' => [],
                        'WANPPPConnection' => []
                    ]
                ],
                'LANDevice' => [
                    '1' => [
                        'WLANConfiguration' => []
                    ]
                ]
            ]
        ]);

        $mapping = \App\Models\ISP\OnuParameterMapping::create([
            'onu_id' => $onu->id,
            'semantic_key' => 'wan.bridge.type',
            'actual_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1'
        ]);

        $result = $this->service->discover($onu);

        $this->assertEquals('SUCCESS', $result['status']);
        $this->assertEquals('AVAILABLE', $result['capabilities']['WAN']['BRIDGE']['status']);
        $this->assertTrue($result['readiness']['BRIDGE_READY']);
    }

    public function test_discovery_with_bridge_unavailable()
    {
        $onuId = \Illuminate\Support\Facades\DB::table('onus')->insertGetId([
            'serial_number' => 'ZTEG12345678',
            'code' => 'ONU-TEST-'.rand(1000,9999),
            'name' => 'Test ONU',
            'mac_address' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $onu = Onu::find($onuId);

        $this->acsMock->shouldReceive('getDeviceParameters')->with('ZTEG12345678')->andReturn([
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'ZTE',
                    'ModelName' => 'F670L'
                ],
                'WANDevice' => [
                    '1' => [
                    ]
                ]
            ]
        ]);

        $result = $this->service->discover($onu);

        $this->assertEquals('SUCCESS', $result['status']);
        $this->assertEquals('UNAVAILABLE', $result['capabilities']['WAN']['BRIDGE']['status']);
        $this->assertFalse($result['readiness']['BRIDGE_READY']);
    }

    public function test_device_offline_returns_discovery_failed()
    {
        $onuId = \Illuminate\Support\Facades\DB::table('onus')->insertGetId([
            'serial_number' => 'OFFLINE123',
            'code' => 'ONU-OFFLINE-'.rand(1000,9999),
            'name' => 'Offline ONU',
            'mac_address' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $onu = Onu::find($onuId);

        $this->acsMock->shouldReceive('getDeviceParameters')->with('OFFLINE123')
             ->andThrow(new \Exception("Connection timeout"));

        $result = $this->service->discover($onu);

        $this->assertEquals('DISCOVERY_FAILED', $result['status']);
    }

    public function test_idempotency_no_change_returns_message()
    {
        $onuId = \Illuminate\Support\Facades\DB::table('onus')->insertGetId([
            'serial_number' => 'ZTEG12345678',
            'code' => 'ONU-TEST-'.rand(1000,9999),
            'name' => 'Test ONU',
            'mac_address' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $onu = Onu::find($onuId);

        $tree = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => ['Manufacturer' => 'ZTE', 'ModelName' => 'F670L'],
                'WANDevice' => []
            ]
        ];

        $this->acsMock->shouldReceive('getDeviceParameters')->with('ZTEG12345678')->andReturn($tree);

        // First discover
        $firstResult = $this->service->discover($onu);
        $this->assertArrayNotHasKey('message', $firstResult);
        
        // Second discover
        $secondResult = $this->service->discover($onu);
        $this->assertArrayHasKey('message', $secondResult);
        $this->assertEquals('NO_CAPABILITY_CHANGE', $secondResult['message']);
    }
}
