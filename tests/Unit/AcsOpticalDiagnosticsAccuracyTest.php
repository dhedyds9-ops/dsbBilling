<?php

namespace Tests\Unit;

use App\Models\ISP\Onu;
use App\Services\ACS\AcsTelemetryParser;
use PHPUnit\Framework\TestCase;

class AcsOpticalDiagnosticsAccuracyTest extends TestCase
{
    /** Test normal negative RX and positive TX */
    public function test_standard_negative_rx_and_positive_tx(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '-21.40',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower' => '2.15',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-21.40, $optical['onu_rx_power']);
        $this->assertEquals(2.15, $optical['onu_tx_power']);
        $this->assertEquals('-21.40 dBm', $optical['onu_rx_display']);
        $this->assertEquals('2.15 dBm', $optical['onu_tx_display']);
        $this->assertEquals('normal', $optical['rx_status']);
        $this->assertFalse($optical['is_anomalous']);
    }

    /** Test optical power zero value (0.0) is preserved and not converted to null */
    public function test_zero_power_reading_is_preserved_as_zero(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '0.0',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower' => '0',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertSame(0.0, $optical['onu_rx_power'], 'Zero RX power should be preserved as 0.0 float');
        $this->assertSame(0.0, $optical['onu_tx_power'], 'Zero TX power should be preserved as 0.0 float');
        $this->assertStringContainsString('zero_power_or_laser_off', $optical['rx_formula']);
        $this->assertStringContainsString('zero_power_or_laser_off', $optical['tx_formula']);
    }

    /** Test null reading returns null and display 'Tidak Didukung' */
    public function test_null_optical_reading_handling(): void
    {
        $flat = [];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertNull($optical['onu_rx_power']);
        $this->assertNull($optical['onu_tx_power']);
        $this->assertEquals('Tidak Didukung', $optical['onu_rx_display']);
        $this->assertEquals('Tidak Didukung', $optical['onu_tx_display']);
        $this->assertEquals('unknown', $optical['rx_status']);
    }

    /** Test milli-Watt (mW) conversion formula: 10 * log10(val) */
    public function test_milliwatt_logarithmic_conversion(): void
    {
        // 1.25 mW -> 10 * log10(1.25) ≈ 0.97 dBm
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower' => '1.25 mW',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(0.97, $optical['onu_tx_power']);
        $this->assertEquals('mW', $optical['tx_unit']);
        $this->assertStringContainsString('10 * log10(val)', $optical['tx_formula']);
    }

    /** Test micro-Watt (uW) conversion formula: 10 * log10(val / 1000) */
    public function test_microwatt_logarithmic_conversion(): void
    {
        // 500 uW -> 10 * log10(500 / 1000) = 10 * log10(0.5) ≈ -3.01 dBm
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '500 uW',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-3.01, $optical['onu_rx_power']);
        $this->assertEquals('uW', $optical['rx_unit']);
        $this->assertStringContainsString('10 * log10(val / 1000)', $optical['rx_formula']);
    }

    /** Test ZTE verified swap rule vs Huawei non-swapping */
    public function test_zte_swapped_profile_vs_huawei_non_swapped(): void
    {
        // ZTE F670L: RxPower reports Tx (1.20), TxPower reports Rx (-22.50)
        $zteFlat = [
            'InternetGatewayDevice.DeviceInfo.Manufacturer' => 'ZTE',
            'InternetGatewayDevice.DeviceInfo.ModelName' => 'F670L',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower' => '1.20',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower' => '-22.50',
        ];

        $zteOptical = AcsTelemetryParser::parseOptical($zteFlat);
        $this->assertEquals(-22.50, $zteOptical['onu_rx_power'], 'ZTE F670L must swap inverted parameters');
        $this->assertEquals(1.20, $zteOptical['onu_tx_power']);
        $this->assertStringContainsString('Verified Vendor Profile Swap', $zteOptical['rx_formula']);

        // Huawei HG8245H: RxPower is Rx (-22.50), TxPower is Tx (2.10)
        $hwFlat = [
            'InternetGatewayDevice.DeviceInfo.Manufacturer' => 'Huawei',
            'InternetGatewayDevice.DeviceInfo.ModelName' => 'HG8245H',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '-22.50',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower' => '2.10',
        ];

        $hwOptical = AcsTelemetryParser::parseOptical($hwFlat);
        $this->assertEquals(-22.50, $hwOptical['onu_rx_power'], 'Huawei must never swap parameters');
        $this->assertEquals(2.10, $hwOptical['onu_tx_power']);
    }

    /** Test optical attenuation semantics (dB loss, not negative power) */
    public function test_optical_attenuation_semantics(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.OpticalAttenuation' => '18.40',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertTrue($optical['is_attenuation']);
        $this->assertStringContainsString('optical attenuation', $optical['rx_formula']);
    }

    /** Test anomaly detection for out-of-range readings */
    public function test_optical_anomaly_detection(): void
    {
        // Anomaly: RX > 0 dBm (impossible for GPON ONT)
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '5.50',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertTrue($optical['is_anomalous']);
    }

    /** Test data freshness detection based on _lastInform */
    public function test_data_freshness_detection(): void
    {
        // Stale inform (> 180 seconds ago)
        $staleFlat = [
            '_lastInform' => now()->subMinutes(10)->toIso8601String(),
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '-20.10',
        ];
        $staleOptical = AcsTelemetryParser::parseOptical($staleFlat);
        $this->assertEquals('stale', $staleOptical['freshness_status']);

        // Fresh inform (within 180 seconds)
        $freshFlat = [
            '_lastInform' => now()->subSeconds(30)->toIso8601String(),
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '-20.10',
        ];
        $freshOptical = AcsTelemetryParser::parseOptical($freshFlat);
        $this->assertEquals('fresh', $freshOptical['freshness_status']);
    }

    /** Test strict separation of ONU TR-069 and OLT DDM telemetry */
    public function test_strict_separation_of_onu_and_olt_telemetry(): void
    {
        $flat = [
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower' => '-22.30',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower' => '2.10',
        ];

        $onu = new Onu();
        $onu->rx_power_dbm = -19.50; // OLT SFP DDM reading
        $onu->tx_power_dbm = 3.40;  // OLT SFP DDM reading

        $optical = AcsTelemetryParser::parseOptical($flat, $onu);

        // ONU sensor strictly matches TR-069
        $this->assertEquals(-22.30, $optical['onu_rx_power']);
        $this->assertEquals(2.10, $optical['onu_tx_power']);

        // OLT sensor strictly matches database OLT DDM
        $this->assertEquals(-19.50, $optical['olt_rx_power']);
        $this->assertEquals(3.40, $optical['olt_tx_power']);
        $this->assertEquals('-19.50 dBm', $optical['olt_rx_display']);
        $this->assertEquals('3.40 dBm', $optical['olt_tx_display']);
    }

    /** Test ZTE positive TX magnitude swap (e.g. ZTEGCC408F02 with raw Rx: 2.23, Tx: 17.60) */
    public function test_zte_swapped_with_positive_tx_magnitude(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.SerialNumber' => 'ZTEGCC408F02',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower' => '223', // val / 100 = 2.23 dBm
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower' => '17600', // val / 1000 = 17.60 dBm
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-17.60, $optical['onu_rx_power'], 'ZTE with positive TX magnitude must swap and negate to -17.60 dBm');
        $this->assertEquals(2.23, $optical['onu_tx_power'], 'ZTE with positive TX magnitude must swap laser diode to +2.23 dBm');
        $this->assertEquals('-17.60 dBm', $optical['onu_rx_display']);
        $this->assertEquals('2.23 dBm', $optical['onu_tx_display']);
        $this->assertEquals('normal', $optical['rx_status']);
        $this->assertFalse($optical['is_anomalous']);
    }

    /** Test Huawei optical reading via X_GponInterafceConfig typo path */
    public function test_huawei_optical_reading_via_typo_interafce_path(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.Manufacturer' => 'Huawei',
            'InternetGatewayDevice.DeviceInfo.ModelName' => 'EG8141H5',
            'InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig.RXPower' => '-2280', // milli-dBm = -22.80
            'InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig.TXPower' => '245',   // 0.01 dBm = 2.45
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-22.80, $optical['onu_rx_power']);
        $this->assertEquals(2.45, $optical['onu_tx_power']);
        $this->assertEquals('-22.80 dBm', $optical['onu_rx_display']);
        $this->assertEquals('2.45 dBm', $optical['onu_tx_display']);
    }

    /** Test composite fallback to OLT DDM when TR-069 is completely absent */
    public function test_composite_fallback_to_olt_when_tr069_absent(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.SerialNumber' => 'HWTC2E1E1C9F',
        ];

        $onu = new Onu();
        $onu->rx_power_dbm = -15.78;
        $onu->tx_power_dbm = 2.50;

        $optical = AcsTelemetryParser::parseOptical($flat, $onu);

        $this->assertNull($optical['onu_rx_power']);
        $this->assertNull($optical['onu_tx_power']);
        $this->assertEquals('Tidak Didukung', $optical['onu_rx_display']);
        $this->assertEquals(-15.78, $optical['olt_rx_power']);
        $this->assertEquals('-15.78 dBm', $optical['olt_rx_display']);
        $this->assertEquals(-15.78, $optical['composite_rx_power']);
        $this->assertEquals('-15.78 dBm', $optical['composite_rx_display']);
    }

    /** Test ZTE with 0.01 dBm scale (e.g. Rx: 223 -> 2.23 dBm, Tx: 1760 -> 17.60 dBm) */
    public function test_zte_swapped_with_001_dbm_scale(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.Manufacturer' => 'ZTE',
            'InternetGatewayDevice.DeviceInfo.ModelName' => 'F609',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower' => '223',  // 2.23 dBm laser
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower' => '1760', // 17.60 dBm received power
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-17.60, $optical['onu_rx_power'], 'ZTE 1760 in 0.01 dBm scale must normalize to -17.60 dBm');
        $this->assertEquals(2.23, $optical['onu_tx_power'], 'ZTE 223 in 0.01 dBm scale must normalize to 2.23 dBm');
        $this->assertEquals('-17.60 dBm', $optical['onu_rx_display']);
        $this->assertEquals('2.23 dBm', $optical['onu_tx_display']);
    }

    /** Test ZTE with non-inverted firmware (RXPower is already RX, TXPower is already TX) */
    public function test_zte_standard_non_inverted_firmware(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.SerialNumber' => 'ZTEGC1234567',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower' => '-19.40',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower' => '2.10',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-19.40, $optical['onu_rx_power']);
        $this->assertEquals(2.10, $optical['onu_tx_power']);
        $this->assertEquals('-19.40 dBm', $optical['onu_rx_display']);
        $this->assertEquals('2.10 dBm', $optical['onu_tx_display']);
    }

    /** Test Huawei HG8245H5 profile resolution and optical reading */
    public function test_huawei_hg8245h5_optical_and_profile_resolution(): void
    {
        $flat = [
            'InternetGatewayDevice.DeviceInfo.Manufacturer' => 'Huawei',
            'InternetGatewayDevice.DeviceInfo.ModelName' => 'HG8245H5',
            'InternetGatewayDevice.DeviceInfo.SerialNumber' => 'HWTCD5CF84A3',
            'InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig.RXPower' => '-21.80',
            'InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig.TXPower' => '2.35',
        ];

        $optical = AcsTelemetryParser::parseOptical($flat);

        $this->assertEquals(-21.80, $optical['onu_rx_power'], 'Huawei HG8245H5 must read optical RX correctly');
        $this->assertEquals(2.35, $optical['onu_tx_power'], 'Huawei HG8245H5 must read optical TX correctly');
        $this->assertEquals('-21.80 dBm', $optical['onu_rx_display']);
        $this->assertEquals('2.35 dBm', $optical['onu_tx_display']);
    }
}
