<?php

namespace Tests\Unit;

use App\Models\ISP\Onu;
use App\Services\ACS\AcsTelemetryParser;
use PHPUnit\Framework\TestCase;

class AcsTelemetryParserTest extends TestCase
{
    /** Test ZTE F609 inverted RX and TX parameters are automatically swapped */
    public function test_zte_swapped_rx_and_tx_powers_are_corrected(): void
    {
        // ZTE F609 anomaly: RxPower reports +0.73 (Tx), TxPower reports -21.50 (Rx)
        $flat = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower' => '0.73',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower' => '-21.50',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-21.50, $optical['rx_power'], 'RX power should be swapped from TxPower parameter');
        $this->assertEquals(0.73, $optical['tx_power'], 'TX power should be swapped from RxPower parameter');
        $this->assertEquals('-21.50 dBm', $optical['rx_power_display']);
        $this->assertEquals('normal', $optical['rx_status']);
    }

    /** Test ZTE F609 single positive TX reading is not mistaken for RX */
    public function test_zte_single_positive_reading_is_assigned_to_tx_power(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower' => '1.08',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertNull($optical['rx_power'], 'Small positive value in Rx parameter is TX laser power, not RX');
        $this->assertEquals(1.08, $optical['tx_power'], 'TX power should receive the 1.08 dBm reading');
    }

    /** Test positive attenuation reported without minus sign is converted to negative */
    public function test_positive_attenuation_is_made_negative(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '22.45',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower' => '2.10',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-22.45, $optical['rx_power']);
        $this->assertEquals(2.10, $optical['tx_power']);
        $this->assertEquals('-22.45 dBm', $optical['rx_power_display']);
        $this->assertEquals('normal', $optical['rx_status']);
    }

    /** Test FiberHome milli-dBm scaling */
    public function test_fiberhome_milli_dbm_scaling(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower' => '-19630',
            'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower' => '2450',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-19.63, $optical['rx_power']);
        $this->assertEquals(2.45, $optical['tx_power']);
    }

    /** Test 0.01 dBm scale (e.g. 1963 -> 19.63) */
    public function test_hundredth_dbm_scale(): void
    {
        $flat = [
            'Device.Optical.1.Transceiver.RxPower' => '-2450',
            'Device.Optical.1.Transceiver.TxPower' => '180',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-24.50, $optical['rx_power']);
        $this->assertEquals(1.80, $optical['tx_power']);
    }

    /** Test Serial Number Hex conversions */
    public function test_serial_number_normalization_across_vendors(): void
    {
        // Huawei HEX 48575443...
        $flatHw = ['InternetGatewayDevice.DeviceInfo.SerialNumber' => '48575443A1B2C3D4'];
        $snHw = AcsTelemetryParser::parseSerialNumber($flatHw);
        $this->assertEquals('HWTCA1B2C3D4', $snHw);

        // ZTE HEX 5A544547...
        $flatZte = ['InternetGatewayDevice.DeviceInfo.SerialNumber' => '5A544547C408F020'];
        $snZte = AcsTelemetryParser::parseSerialNumber($flatZte);
        $this->assertEquals('ZTEGC408F020', $snZte);

        // FiberHome HEX 46485454...
        $flatFh = ['InternetGatewayDevice.DeviceInfo.SerialNumber' => '4648545499887766'];
        $snFh = AcsTelemetryParser::parseSerialNumber($flatFh);
        $this->assertEquals('FHTT99887766', $snFh);

        // VSOL dirty unprintable bytes
        $flatVsol = ['InternetGatewayDevice.DeviceInfo.SerialNumber' => "VSOL\x00\x1F00112233"];
        $snVsol = AcsTelemetryParser::parseSerialNumber($flatVsol);
        $this->assertEquals('VSOL00112233', $snVsol);
    }

    /** Test MAC Address normalization */
    public function test_mac_address_normalization(): void
    {
        $this->assertEquals('E4:47:83:83:5A:0C', AcsTelemetryParser::normalizeMac('e4:47:83:83:5a:0c'));
        $this->assertEquals('E4:47:83:83:5A:0C', AcsTelemetryParser::normalizeMac('e4-47-83-83-5a-0c'));
        $this->assertEquals('E4:47:83:83:5A:0C', AcsTelemetryParser::normalizeMac('E44783835A0C'));
        $this->assertNull(AcsTelemetryParser::normalizeMac('00:00:00:00:00:00'));
        $this->assertNull(AcsTelemetryParser::normalizeMac('FF:FF:FF:FF:FF:FF'));
    }

    /** Test Device Identity detection */
    public function test_device_identity_detection(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.Manufacturer' => 'ZTE Corporation',
            'InternetGatewayDevice.DeviceInfo.ModelName' => 'F609',
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion' => 'V7.0.10P1N18',
            'InternetGatewayDevice.DeviceInfo.HardwareVersion' => 'V7.0',
        ];

        $ident = AcsTelemetryParser::parseDeviceIdentity($flat, [], 'ZTEGC408F02');

        $this->assertEquals('ZTE', $ident['vendor']);
        $this->assertEquals('F609', $ident['model']);
        $this->assertEquals('V7.0.10P1N18', $ident['software_version']);
    }

    /** Test IP and PPPoE extraction */
    public function test_pppoe_and_ip_separation(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username' => 'user_test',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password' => 'secret123',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress' => '10.20.20.238',
        ];

        $res = AcsTelemetryParser::parsePppoeAndIp($flat, 'http://192.168.200.251:7547/');

        $this->assertEquals('user_test', $res['pppoe_username']);
        $this->assertEquals('secret123', $res['pppoe_password']);
        $this->assertEquals('10.20.20.238', $res['pppoe_ip']);
        $this->assertEquals('192.168.200.251', $res['tr069_ip']);
    }

    /** Test Wi-Fi Dual Band (2.4GHz & 5GHz) extraction */
    public function test_wifi_dual_band_extraction(): void
    {
        $flat = [
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID' => 'DsBilling_Home_2.4G',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase' => 'wifi12345',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID' => 'DsBilling_Home_5G',
        ];

        $wifi = AcsTelemetryParser::parseWifi($flat);

        $this->assertEquals('DsBilling_Home_2.4G', $wifi['ssid_1']);
        $this->assertEquals('DsBilling_Home_5G', $wifi['ssid_2']);
        $this->assertEquals('wifi12345', $wifi['wifi_password_1']);
    }

    /** Test OLT DDM fallback when TR-069 is missing */
    public function test_olt_ddm_fallback_when_tr069_is_empty(): void
    {
        $flat = []; // TR-069 has no optical data

        $onu = new Onu();
        $onu->rx_power_dbm = -18.75;
        $onu->tx_power_dbm = 2.30;

        $optical = AcsTelemetryParser::parseOptical($flat, $onu);

        $this->assertEquals(-18.75, $optical['rx_power'], 'Should fallback to OLT DDM rx_power_dbm');
        $this->assertEquals(2.30, $optical['tx_power'], 'Should fallback to OLT DDM tx_power_dbm');
        $this->assertEquals('-18.75 dBm', $optical['rx_power_display']);
    }

    /** Test formatUptime handles null, zero, and standard duration */
    public function test_format_uptime(): void
    {
        $this->assertEquals('Tidak Didukung', AcsTelemetryParser::formatUptime(null));
        $this->assertEquals('0d', AcsTelemetryParser::formatUptime(0));
        $this->assertEquals('45m 0d', AcsTelemetryParser::formatUptime(45 * 60));
        $this->assertEquals('3j 25m 0d', AcsTelemetryParser::formatUptime(3 * 3600 + 25 * 60));
        $this->assertEquals('2h 5j 32m 10d', AcsTelemetryParser::formatUptime(2 * 86400 + 5 * 3600 + 32 * 60 + 10));
    }

    /** Test ZTE Hardware metrics parsing */
    public function test_parse_hardware_metrics_zte(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_CPUUsage' => '35',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_MemoryUsage' => '48',
            'InternetGatewayDevice.DeviceInfo.UpTime' => '123456',
            'InternetGatewayDevice.DeviceInfo.HardwareVersion' => 'V7.0',
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion' => 'V7.0.10P1N18',
        ];

        $metrics = AcsTelemetryParser::parseHardwareMetrics($flat, null);

        $this->assertEquals(35.0, $metrics['cpu_usage']);
        $this->assertEquals('supported', $metrics['cpu_status']);
        $this->assertEquals(48.0, $metrics['ram_usage']);
        $this->assertEquals('supported', $metrics['ram_status']);
        $this->assertEquals(123456, $metrics['uptime_seconds']);
        $this->assertEquals('1h 10j 17m 36d', $metrics['uptime_display']);
        $this->assertEquals('V7.0', $metrics['hardware_version']);
        $this->assertEquals('V7.0.10P1N18', $metrics['software_version']);
    }

    /** Test TR-181 Hardware metrics parsing */
    public function test_parse_hardware_metrics_tr181(): void
    {
        $flat = [
            'Device.DeviceInfo.ProcessStatus.CPUUsage' => '22',
            'Device.DeviceInfo.MemoryStatus.Total' => '262144', // 256MB in KB
            'Device.DeviceInfo.MemoryStatus.Free' => '131072',  // 128MB in KB -> 50% usage
            'Device.DeviceInfo.TemperatureStatus.TemperatureSensor.1.Value' => '46',
            'Device.DeviceInfo.UpTime' => '86400',
        ];

        $metrics = AcsTelemetryParser::parseHardwareMetrics($flat, null);

        $this->assertEquals(22.0, $metrics['cpu_usage']);
        $this->assertEquals('supported', $metrics['cpu_status']);
        $this->assertEquals('256 MB', $metrics['ram_total_display']);
        $this->assertEquals('128 MB', $metrics['ram_free_display']);
        $this->assertEquals(50.0, $metrics['ram_usage']);
        $this->assertEquals('supported', $metrics['ram_status']);
        $this->assertEquals(46.0, $metrics['temperature']);
        $this->assertEquals('supported', $metrics['temperature_status']);
        $this->assertEquals('1h 0j 0m 0d', $metrics['uptime_display']);
    }

    /** Test WAN Tree parsing for TR-098 with ZTE Port Binding */
    public function test_parse_wan_tree_tr098_zte(): void
    {
        $params = [
            'InternetGatewayDevice' => [
                'WANDevice' => [
                    '1' => [
                        'WANConnectionDevice' => [
                            '1' => [
                                'WANPPPConnection' => [
                                    '1' => [
                                        'Name' => ['_value' => '1_INTERNET_R_VID_200'],
                                        'ConnectionType' => ['_value' => 'IP_Routed'],
                                        'Enable' => ['_value' => true],
                                        'NATEnabled' => ['_value' => true],
                                        'Username' => ['_value' => 'client_speedy'],
                                        'Password' => ['_value' => 'client_pass123'],
                                        'ExternalIPAddress' => ['_value' => '10.50.10.15'],
                                        'X_ZTE-COM_VLANID' => ['_value' => '200'],
                                        'X_ZTE-COM_VLANEnable' => ['_value' => '1'],
                                        'X_ZTE-COM_ServiceList' => ['_value' => 'INTERNET'],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'X_ZTE-COM_PortBinding' => [
                    '1' => [
                        'WANInterface' => ['_value' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1'],
                        'LANInterface' => ['_value' => 'InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1,InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.2'],
                    ]
                ]
            ]
        ];

        $wans = AcsTelemetryParser::parseWanTree($params, 'zte');

        $this->assertCount(1, $wans);
        $wan = $wans[0];
        $this->assertEquals('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1', $wan['fullPath']);
        $this->assertEquals('PPPoE', $wan['type']);
        $this->assertEquals('client_speedy', $wan['username']);
        $this->assertTrue($wan['has_password']);
        $this->assertEquals('10.50.10.15', $wan['ip_address']);
        $this->assertEquals(200, $wan['vlan']);
        $this->assertTrue($wan['vlan_enabled']);
        $this->assertTrue($wan['nat']);
        $this->assertEquals('INTERNET', $wan['service_list']);
        $this->assertStringContainsString('LANEthernetInterfaceConfig.1', $wan['port_bind']);
        $this->assertStringContainsString('LANEthernetInterfaceConfig.2', $wan['port_bind']);
    }

    /** Test WAN Tree parsing for TR-181 */
    public function test_parse_wan_tree_tr181(): void
    {
        $params = [
            'Device' => [
                'PPP' => [
                    'Interface' => [
                        '1' => [
                            'Name' => ['_value' => 'ppp0'],
                            'Enable' => ['_value' => true],
                            'Username' => ['_value' => 'tr181_user'],
                            'Password' => ['_value' => 'tr181_pass'],
                            'IPCP' => [
                                'LocalIPAddress' => ['_value' => '100.64.1.20'],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $wans = AcsTelemetryParser::parseWanTree($params, 'generic');

        $this->assertCount(1, $wans);
        $wan = $wans[0];
        $this->assertEquals('Device.PPP.Interface.1', $wan['fullPath']);
        $this->assertEquals('PPPoE', $wan['type']);
        $this->assertEquals('tr181_user', $wan['username']);
        $this->assertEquals('100.64.1.20', $wan['ip_address']);
    }
}

