<?php

namespace Tests\Unit;

use App\Models\ACS\ACSDevice;
use App\Services\ACS\AcsTelemetryParser;
use App\Services\ACS\AcsWanManagerService;
use App\Services\ACS\VendorCapabilityCatalog;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Services\ISP\GenieAcsProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AcsZteVlanProvisioningTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_detect_vendor_identifies_zicg_f663_as_zte()
    {
        $driver = Mockery::mock(GenieACSDriver::class);
        $service = new AcsWanManagerService($driver);

        $params = [
            'DeviceID' => [
                'Manufacturer' => ['_value' => 'ZICG'],
                'ProductClass' => ['_value' => 'F663NV3A'],
                'SerialNumber' => ['_value' => 'ZICGA061FAC9'],
            ],
            '_deviceId' => [
                '_OUI' => '28C01B',
                '_Manufacturer' => 'ZICG',
                '_ProductClass' => 'F663NV3A',
                '_SerialNumber' => 'ZICGA061FAC9',
            ],
        ];

        $vendorFromParser = AcsTelemetryParser::detectVendor($params);
        $this->assertEquals('zte', $vendorFromParser);

        $vendorFromService = $service->detectVendor('28C01B-F663NV3A-ZICGA061FAC9', $params);
        $this->assertEquals('zte', $vendorFromService);
    }

    public function test_vendor_capability_catalog_has_f663nv3a_under_zte()
    {
        $catalog = VendorCapabilityCatalog::getCatalog();
        $this->assertArrayHasKey('zte', $catalog);
        $this->assertContains('28C01B', $catalog['zte']['oui']);
        $this->assertContains('ZICG', $catalog['zte']['oui']);
        $this->assertArrayHasKey('F663NV3A', $catalog['zte']['models']);
    }

    public function test_parse_wan_tree_extracts_vlan_from_zte_connection_name()
    {
        $tree = [
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    1 => [
                        'WANConnectionDevice' => [
                            1 => [
                                'WANPPPConnection' => [
                                    1 => [
                                        'Name' => ['_value' => '2_INTERNET_R_VID_'],
                                        'Enable' => ['_value' => true],
                                        'ConnectionType' => ['_value' => 'IP_Routed'],
                                        'Username' => ['_value' => 'mamahlela'],
                                    ],
                                ],
                            ],
                            2 => [
                                'WANIPConnection' => [
                                    1 => [
                                        'Name' => ['_value' => '1_TR069_R_VID_901'],
                                        'Enable' => ['_value' => true],
                                        'ConnectionType' => ['_value' => 'IP_Routed'],
                                        'AddressingType' => ['_value' => 'DHCP'],
                                    ],
                                ],
                            ],
                            3 => [
                                'WANPPPConnection' => [
                                    1 => [
                                        'Name' => ['_value' => '3_Other_B_VID_111'],
                                        'Enable' => ['_value' => true],
                                        'ConnectionType' => ['_value' => 'PPPoE_Bridged'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $wans = AcsTelemetryParser::parseWanTree($tree, 'zte');
        $this->assertCount(3, $wans);

        // 1. PPPoE WAN with blank VID_
        $pppoeWan = collect($wans)->firstWhere('name', '2_INTERNET_R_VID_');
        $this->assertNotNull($pppoeWan);
        $this->assertFalse($pppoeWan['vlan_enabled']);
        $this->assertNull($pppoeWan['vlan']);

        // 2. TR069 WAN with VID_901
        $tr069Wan = collect($wans)->firstWhere('name', '1_TR069_R_VID_901');
        $this->assertNotNull($tr069Wan);
        $this->assertTrue($tr069Wan['vlan_enabled']);
        $this->assertEquals(901, $tr069Wan['vlan']);

        // 3. Hotspot Bridge WAN with VID_111
        $bridgeWan = collect($wans)->firstWhere('name', '3_Other_B_VID_111');
        $this->assertNotNull($bridgeWan);
        $this->assertTrue($bridgeWan['vlan_enabled']);
        $this->assertEquals(111, $bridgeWan['vlan']);
    }

    public function test_wan_manager_service_sets_zte_vlan_parameters_on_update()
    {
        $deviceId = '28C01B-F663NV3A-ZICGA061FAC9';
        ACSDevice::create([
            'device_id' => $deviceId,
            'uuid' => $deviceId,
            'serial_number' => 'ZICGA061FAC9',
            'manufacturer' => 'ZICG',
            'product_class' => 'F663NV3A',
        ]);

        $driver = Mockery::mock(GenieACSDriver::class);
        $driver->shouldReceive('resolveDeviceId')->andReturn($deviceId);
        $driver->shouldReceive('getDeviceParameters')->andReturn([
            '_deviceId' => [
                '_Manufacturer' => 'ZICG',
                '_ProductClass' => 'F663NV3A',
                '_SerialNumber' => 'ZICGA061FAC9',
            ],
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    1 => [
                        'WANConnectionDevice' => [
                            1 => [
                                'WANPPPConnection' => [
                                    1 => [
                                        'Enable' => ['_value' => true],
                                        'Username' => ['_value' => 'mamahlela'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $interceptedParams = [];
        $driver->shouldReceive('setParameterValues')
            ->once()
            ->with('28C01B-F663NV3A-ZICGA061FAC9', Mockery::on(function ($params) use (&$interceptedParams) {
                $interceptedParams = $params;
                return true;
            }))
            ->andReturn(true);

        $driver->shouldReceive('addTask')->andReturn(['_id' => 'task-123']);

        $service = new AcsWanManagerService($driver);
        $fullPath = 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1';

        $data = [
            'mode' => 'pppoe_route',
            'vlan_enabled' => true,
            'vlan_id' => 200,
            'service_list' => 'INTERNET',
            'nat_enabled' => true,
            'username' => 'mamahlela',
            'password' => 'pppoe',
        ];

        $res = $service->updateWanConnection('28C01B-F663NV3A-ZICGA061FAC9', $fullPath, $data);
        $this->assertTrue($res['success']);

        // Verify that X_ZTE-COM_VLANID and X_ZTE-COM_VLANEnable are correctly passed
        $this->assertArrayHasKey("{$fullPath}.X_ZTE-COM_VLANID", $interceptedParams);
        $this->assertEquals(200, $interceptedParams["{$fullPath}.X_ZTE-COM_VLANID"]);
        $this->assertArrayHasKey("{$fullPath}.X_ZTE-COM_VLANEnable", $interceptedParams);
        $this->assertEquals(1, $interceptedParams["{$fullPath}.X_ZTE-COM_VLANEnable"]);
        $this->assertEquals('mamahlela', $interceptedParams["{$fullPath}.Username"]);

        // Verify that Huawei X_HW_VLAN was NOT mistakenly set
        $this->assertArrayNotHasKey('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANEthernetLinkConfig.X_HW_VLAN', $interceptedParams);
    }

    public function test_genieacs_provisioning_service_contains_zicg_support_for_hotspot_and_wan()
    {
        $rawWanScript = GenieAcsProvisioningService::DEFAULT_PROVISIONS['dsBilling_Setup_WAN']['script'];
        $this->assertStringContainsString('ZICG', $rawWanScript);
        $this->assertStringContainsString('isZte', $rawWanScript);

        $rawHotspotScript = GenieAcsProvisioningService::DEFAULT_PROVISIONS['dsBilling_Setup_Hotspot_Bridge']['script'];
        $this->assertStringContainsString('ZICG', $rawHotspotScript);
        $this->assertStringContainsString('isZte', $rawHotspotScript);
        $this->assertStringContainsString('X_ZTE-COM_VLANID', $rawHotspotScript);
    }
}
