<?php

namespace Tests\Unit;

use App\Services\ACS\AcsPortBindingEngine;
use PHPUnit\Framework\TestCase;

class AcsPortBindingHardeningTest extends TestCase
{
    /** Test FiberHome HG6045F3 (1 GE Port) does not invent fake LAN2-LAN4 */
    public function test_single_port_onu_discovers_strictly_one_port(): void
    {
        // FiberHome HG6045F3 with only 1 LAN Ethernet interface in TR-098
        $deviceData = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'FiberHome',
                    'ModelName' => 'HG6045F3',
                ],
                'LANDevice' => [
                    '1' => [
                        'LANEthernetInterfaceConfig' => [
                            '1' => [
                                'Status' => 'Up',
                                'MACAddress' => 'E4:47:83:83:5A:01',
                            ],
                        ],
                    ],
                ],
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => 'INTERNET_PPPOE',
                                        'X_FH_LanInterface' => 'LAN1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $ports = AcsPortBindingEngine::discoverPhysicalPorts($deviceData);

        $this->assertCount(1, $ports, 'Single-port ONU must discover strictly 1 physical port');
        $this->assertEquals('LAN1', $ports[0]['name']);
        $this->assertEquals('Link Up', $ports[0]['physical_status']);
        $this->assertEquals('Bound', $ports[0]['binding_status']);
        $this->assertEquals('Bound (Link Up)', $ports[0]['status_display']);
    }

    /** Test ZTE F670L discovers all 4 physical ports */
    public function test_four_port_onu_discovers_all_four_ports(): void
    {
        $deviceData = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'ZTE',
                    'ModelName' => 'F670L',
                ],
                'LANDevice' => [
                    '1' => [
                        'LANEthernetInterfaceConfig' => [
                            '1' => ['Status' => 'Up'],
                            '2' => ['Status' => 'Down'],
                            '3' => ['Status' => 'Down'],
                            '4' => ['Status' => 'Down'],
                        ],
                    ],
                ],
            ],
        ];

        $ports = AcsPortBindingEngine::discoverPhysicalPorts($deviceData);

        $this->assertCount(4, $ports, '4-port ONU must discover 4 ports');
        $portNames = array_column($ports, 'name');
        $this->assertEquals(['LAN1', 'LAN2', 'LAN3', 'LAN4'], $portNames);
    }

    /** Test physical link down does NOT mean unbound */
    public function test_link_down_does_not_mean_unbound(): void
    {
        $deviceData = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'ZTE',
                    'ModelName' => 'F609',
                ],
                'LANDevice' => [
                    '1' => [
                        'LANEthernetInterfaceConfig' => [
                            '1' => ['Status' => 'Down'], // Physical cable disconnected
                        ],
                    ],
                ],
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => '1_TR069_INTERNET_R_VID_100',
                                        'X_ZTE-COM_PortBind' => 'LAN1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $ports = AcsPortBindingEngine::discoverPhysicalPorts($deviceData);

        $lan1 = $ports[0];
        $this->assertEquals('Link Down', $lan1['physical_status']);
        $this->assertEquals('Bound', $lan1['binding_status'], 'LAN1 is bound in WAN config even if physical cable is disconnected');
        $this->assertEquals('Bound (Link Down)', $lan1['status_display']);
        $this->assertEquals('1_TR069_INTERNET_R_VID_100', $lan1['bound_wan_name']);
    }

    /** Test connected port without WAN binding is Unbound (Link Up) */
    public function test_connected_port_without_wan_binding_is_unbound_link_up(): void
    {
        $deviceData = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'Huawei',
                    'ModelName' => 'EG8141H5',
                ],
                'LANDevice' => [
                    '1' => [
                        'LANEthernetInterfaceConfig' => [
                            '1' => ['Status' => 'Up'], // PC plugged in
                        ],
                    ],
                ],
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => 'INTERNET',
                                        // No port binding parameter set
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $ports = AcsPortBindingEngine::discoverPhysicalPorts($deviceData);

        $lan1 = $ports[0];
        $this->assertEquals('Link Up', $lan1['physical_status']);
        $this->assertEquals('Unbound', $lan1['binding_status']);
        $this->assertEquals('Unbound', $lan1['bound_wan']);
        $this->assertFalse($lan1['is_bound']);
    }

    /** Test TR-181 Device.Ethernet.Interface discovery */
    public function test_tr181_ethernet_interface_discovery(): void
    {
        $deviceData = [
            'Device' => [
                'Ethernet' => [
                    'Interface' => [
                        '1' => ['Status' => 'Up', 'Name' => 'eth0'],
                        '2' => ['Status' => 'Down', 'Name' => 'eth1'],
                    ],
                ],
            ],
        ];

        $ports = AcsPortBindingEngine::discoverPhysicalPorts($deviceData);

        $this->assertCount(2, $ports);
        $this->assertEquals('LAN1', $ports[0]['name']);
        $this->assertEquals('Link Up', $ports[0]['physical_status']);
        $this->assertEquals('LAN2', $ports[1]['name']);
        $this->assertEquals('Link Down', $ports[1]['physical_status']);
    }

    /** Test catalog fallback when TR-069 tree lacks interface config */
    public function test_catalog_fallback_resolves_correct_port_count(): void
    {
        // FiberHome HG6045F3 with empty LANDevice
        $fhData = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'FiberHome',
                    'ModelName' => 'HG6045F3',
                ],
            ],
        ];
        $fhPorts = AcsPortBindingEngine::discoverPhysicalPorts($fhData);
        $this->assertCount(1, $fhPorts, 'Catalog specifies exactly 1 port for HG6045F3');

        // ZTE F670L with empty LANDevice
        $zteData = [
            'InternetGatewayDevice' => [
                'DeviceInfo' => [
                    'Manufacturer' => 'ZTE',
                    'ModelName' => 'F670L',
                ],
            ],
        ];
        $ztePorts = AcsPortBindingEngine::discoverPhysicalPorts($zteData);
        $this->assertCount(4, $ztePorts, 'Catalog specifies 4 ports for F670L');
    }
}
