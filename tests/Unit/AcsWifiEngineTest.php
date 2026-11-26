<?php

namespace Tests\Unit;

use App\Models\ISP\Onu;
use App\Services\ACS\AcsTelemetryParser;
use PHPUnit\Framework\TestCase;

class AcsWifiEngineTest extends TestCase
{
    public function test_single_band_ont_does_not_create_fictitious_5ghz_ssids(): void
    {
        // ZTE F609 single-band device parameter tree
        $rawParams = [
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    1 => [
                        'WLANConfiguration' => [
                            1 => [
                                'Enable' => ['_value' => true],
                                'SSID' => ['_value' => 'Rumah_Indah_2.4G'],
                                'Channel' => ['_value' => 6],
                                'PreSharedKey' => [
                                    1 => ['KeyPassphrase' => ['_value' => 'sandiRahasia123']],
                                ],
                                'BeaconType' => ['_value' => '11i'],
                                'SSIDAdvertisementEnabled' => ['_value' => true],
                            ],
                            2 => [
                                'Enable' => ['_value' => false],
                                'SSID' => ['_value' => 'Rumah_Tamu'],
                                'Channel' => ['_value' => 6],
                                'BeaconType' => ['_value' => 'Basic'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = AcsTelemetryParser::parseWifiDetailed($rawParams);

        $this->assertFalse($result['has_5ghz']);
        $this->assertCount(2, $result['band_24_ssids']);
        $this->assertCount(0, $result['band_5_ssids']);
        $this->assertEquals(2, $result['total_ssids_count']);

        // Check first SSID properties
        $ssid1 = $result['band_24_ssids'][0];
        $this->assertEquals('Rumah_Indah_2.4G', $ssid1['ssid']);
        $this->assertEquals('2.4GHz', $ssid1['band']);
        $this->assertEquals(1, $ssid1['display_index']);
        $this->assertEquals(1, $ssid1['instance']);
        $this->assertEquals('WPA2-PSK', $ssid1['security_mode']);
        $this->assertEquals('••••••••', $ssid1['password_masked']);
        $this->assertEquals('InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID', $ssid1['ssid_path']);
    }

    public function test_dual_band_ont_separates_24ghz_and_5ghz_accurately(): void
    {
        // ZTE F670L dual-band parameter tree with instances 1, 2 on 2.4G and 5 on 5G
        $rawParams = [
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    1 => [
                        'WLANConfiguration' => [
                            1 => [
                                'Enable' => ['_value' => true],
                                'SSID' => ['_value' => 'WiFi_Utama_2G'],
                                'Channel' => ['_value' => 1],
                                'X_ZTE-COM_RFBand' => ['_value' => '2.4G'],
                                'PreSharedKey' => [
                                    1 => ['KeyPassphrase' => ['_value' => 'pass2g123']],
                                ],
                                'BeaconType' => ['_value' => '11i'],
                            ],
                            5 => [
                                'Enable' => ['_value' => true],
                                'SSID' => ['_value' => 'WiFi_Utama_5G'],
                                'Channel' => ['_value' => 149],
                                'X_ZTE-COM_RFBand' => ['_value' => '5G'],
                                'PreSharedKey' => [
                                    1 => ['KeyPassphrase' => ['_value' => 'pass5g123']],
                                ],
                                'BeaconType' => ['_value' => '11i'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = AcsTelemetryParser::parseWifiDetailed($rawParams);

        $this->assertTrue($result['has_5ghz']);
        $this->assertCount(1, $result['band_24_ssids']);
        $this->assertCount(1, $result['band_5_ssids']);
        $this->assertEquals(2, $result['total_ssids_count']);

        // Check sequential display_index per group
        $ssid2g = $result['band_24_ssids'][0];
        $this->assertEquals(1, $ssid2g['display_index']);
        $this->assertEquals(1, $ssid2g['instance']);
        $this->assertEquals('2.4GHz', $ssid2g['band']);

        $ssid5g = $result['band_5_ssids'][0];
        $this->assertEquals(1, $ssid5g['display_index']); // sequential within 5G group!
        $this->assertEquals(5, $ssid5g['instance']); // original instance preserved!
        $this->assertEquals('5GHz', $ssid5g['band']);
        $this->assertEquals('InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID', $ssid5g['ssid_path']);
    }

    public function test_huawei_rfband_and_open_encryption(): void
    {
        $rawParams = [
            'InternetGatewayDevice' => [
                'LANDevice' => [
                    1 => [
                        'WLANConfiguration' => [
                            1 => [
                                'Enable' => ['_value' => true],
                                'SSID' => ['_value' => 'Huawei_Hotspot'],
                                'X_HW_RFBand' => ['_value' => '2.4G'],
                                'BeaconType' => ['_value' => 'Basic'],
                                'BasicEncryptionModes' => ['_value' => 'None'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = AcsTelemetryParser::parseWifiDetailed($rawParams);

        $this->assertCount(1, $result['band_24_ssids']);
        $this->assertEquals('Open', $result['band_24_ssids'][0]['security_mode']);
        $this->assertEmpty($result['band_24_ssids'][0]['password']);
    }
}
