<?php

namespace Tests\Unit;

use App\Services\ACS\AcsPortBindingEngine;
use PHPUnit\Framework\TestCase;

class AcsPortBindingEngineTest extends TestCase
{
    public function test_zte_port_binding_table_discovery(): void
    {
        $rawParams = [
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    1 => [
                        'LANEthernetInterfaceConfig' => [
                            1 => ['Enable' => ['_value' => true], 'Status' => ['_value' => 'Up'], 'MaxBitRate' => ['_value' => 1000]],
                            2 => ['Enable' => ['_value' => true], 'Status' => ['_value' => 'Down'], 'MaxBitRate' => ['_value' => 100]],
                            3 => ['Enable' => ['_value' => true], 'Status' => ['_value' => 'Down'], 'MaxBitRate' => ['_value' => 100]],
                            4 => ['Enable' => ['_value' => true], 'Status' => ['_value' => 'Down'], 'MaxBitRate' => ['_value' => 100]],
                        ],
                    ],
                ],
                'X_ZTE-COM_PortBinding' => [
                    1 => [
                        'WANInterface' => ['_value' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1'],
                        'LANInterface' => ['_value' => 'LAN1,LAN2'],
                    ],
                ],
            ],
        ];

        $wanConnections = [
            [
                'full_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
                'name' => '1_INTERNET_R_VID_200',
                'vlan_id' => 200,
            ],
        ];

        $result = AcsPortBindingEngine::discover($rawParams, $wanConnections);

        $this->assertCount(4, $result['lan_ports']);
        $this->assertEquals(4, $result['total_lan_ports']);

        // LAN1 should be Up and bound to 1_INTERNET_R_VID_200
        $lan1 = $result['lan_ports'][0];
        $this->assertEquals(1, $lan1['port']);
        $this->assertEquals('LAN1', $lan1['name']);
        $this->assertEquals('Up', $lan1['status']);
        $this->assertEquals('1000M', $lan1['speed']);
        $this->assertEquals('1_INTERNET_R_VID_200', $lan1['bound_wan']);
        $this->assertEquals(200, $lan1['vlan_id']);

        // LAN2 should be Down but bound to 1_INTERNET_R_VID_200
        $lan2 = $result['lan_ports'][1];
        $this->assertEquals(2, $lan2['port']);
        $this->assertEquals('Down', $lan2['status']);
        $this->assertEquals('1_INTERNET_R_VID_200', $lan2['bound_wan']);

        // LAN3 should be unbound
        $lan3 = $result['lan_ports'][2];
        $this->assertEquals(3, $lan3['port']);
        $this->assertEquals('Unbound', $lan3['bound_wan']);
    }

    public function test_huawei_inline_lanbind_discovery(): void
    {
        $rawParams = [
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    1 => [
                        'WANConnectionDevice' => [
                            1 => [
                                'WANPPPConnection' => [
                                    1 => [
                                        'Name' => ['_value' => 'HW_INTERNET_VID_100'],
                                        'X_HW_LANBIND' => ['_value' => 'LAN1,LAN3,SSID1'],
                                        'X_HW_VLAN' => ['_value' => 100],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $wanConnections = [
            [
                'full_path' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1',
                'name' => 'HW_INTERNET_VID_100',
                'vlan_id' => 100,
            ],
        ];

        $result = AcsPortBindingEngine::discover($rawParams, $wanConnections);

        $this->assertCount(4, $result['lan_ports']);
        $this->assertEquals('HW_INTERNET_VID_100', $result['lan_ports'][0]['bound_wan']); // LAN1
        $this->assertEquals('Unbound', $result['lan_ports'][1]['bound_wan']); // LAN2
        $this->assertEquals('HW_INTERNET_VID_100', $result['lan_ports'][2]['bound_wan']); // LAN3
        $this->assertEquals('Unbound', $result['lan_ports'][3]['bound_wan']); // LAN4
    }
}
