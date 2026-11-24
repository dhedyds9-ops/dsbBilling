<?php

namespace App\Services\ACS;

use App\Models\ACS\ACSDevice;
use App\Models\ISP\Onu;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * AcsTelemetryParser
 *
 * Unified Multi-Vendor TR-069 Telemetry Normalizer for dsBilling.
 * Handles Optical Power (RX/TX), Serial Numbers, MAC Addresses, Wi-Fi SSIDs,
 * Vendor Models, and IP/PPPoE parameters across FiberHome, ZTE, Huawei, VSOL, C-Data, etc.
 */
class AcsTelemetryParser
{
    /**
     * Flatten nested GenieACS parameter data structure into flat dot-notation array.
     */
    public static function flatten(array $data, string $prefix = ''): array
    {
        $flat = [];
        foreach ($data as $k => $v) {
            if ($k === '_timestamp' || $k === '_object' || $k === '_writable') {
                continue;
            }
            $key = $prefix !== '' ? "{$prefix}.{$k}" : (string) $k;
            if (is_array($v)) {
                if (array_key_exists('_value', $v) && (is_scalar($v['_value']) || $v['_value'] === null)) {
                    $flat[$key] = $v['_value'];
                }
                $childFlat = self::flatten($v, $key);
                foreach ($childFlat as $ck => $cv) {
                    $flat[$ck] = $cv;
                }
            } elseif (is_scalar($v) || $v === null) {
                $flat[$key] = $v;
            }
        }
        return $flat;
    }

    /**
     * Helper to retrieve the first non-empty scalar from flat array given candidate keys.
     */
    public static function extractValue(array $flat, array $candidates, $default = null)
    {
        foreach ($candidates as $cand) {
            if (array_key_exists($cand, $flat)) {
                $val = $flat[$cand];
                if (is_scalar($val)) {
                    $trimmed = trim((string) $val);
                    if ($trimmed !== '' && $trimmed !== '-' && strtolower($trimmed) !== 'null' && strtolower($trimmed) !== 'undefined') {
                        return $trimmed;
                    }
                }
            }
        }
        return $default;
    }

    /**
     * Parse and normalize raw optical telemetry into standard dBm.
     * Handles milli-dBm, 0.01 dBm, micro-watts, and negative sign conventions.
     */
    public static function normalizePowerValue($raw): ?float
    {
        if ($raw === null || $raw === '' || $raw === false) {
            return null;
        }

        $clean = trim(str_ireplace(['dBm', 'dbm', ' '], '', (string)$raw));
        if (!is_numeric($clean)) {
            return null;
        }

        $val = (float) $clean;
        // 0.0 on GPON optics indicates unread, missing sensor, or loss of signal
        if ($val == 0.0) {
            return null;
        }

        $abs = abs($val);

        if ($val > 0) {
            // Positive values (typically TX laser power: range +0.5 to +5.0 dBm, or unscaled positive attenuation)
            if ($val >= 500.0) {
                // milli-dBm (e.g. 2450 -> 2.45 dBm, 19630 -> 19.63 dBm)
                return round($val / 1000.0, 2);
            } elseif ($val >= 40.0 && $val < 500.0) {
                // 0.01 dBm scale (e.g. 245 -> 2.45 dBm)
                return round($val / 100.0, 2);
            }
            return round($val, 2);
        } else {
            // Negative values (typically RX power: range -8.0 to -35.0 dBm)
            if ($abs >= 6000.0) {
                // milli-dBm (e.g. -19630 -> -19.63 dBm)
                return round($val / 1000.0, 2);
            } elseif ($abs >= 60.0 && $abs < 6000.0) {
                // 0.01 dBm scale (e.g. -1963 -> -19.63 dBm)
                return round($val / 100.0, 2);
            }
            return round($val, 2);
        }
    }

    /**
     * Extract and validate Optical Diagnostics (RX Power, TX Power, Temperature, LOS).
     * Solves vendor anomalies including ZTE inverted RX/TX parameters and missing negative signs.
     */
    public static function parseOptical(array $flat, ?Onu $onu = null): array
    {
        $rxCandidates = [
            'VirtualParameters.RXPower',
            'VirtualParameters.rxPower',
            'VirtualParameters.redaman',
            'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower',
            'InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig.RXPower',
            'InternetGatewayDevice.WANDevice.1.X_CT-COM_GponInterfaceConfig.RXPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_RxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.RxPower',
            'InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_HW_RxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_ALG-COM_RxPower',
            'Device.Optical.1.Transceiver.RxPower',
            'Device.Optical.Interface.1.RxPower',
        ];

        $txCandidates = [
            'VirtualParameters.TXPower',
            'VirtualParameters.txPower',
            'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower',
            'InternetGatewayDevice.WANDevice.1.X_GponInterfaceConfig.TXPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.TxPower',
            'InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.TxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_HW_TxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_ALG-COM_TxPower',
            'Device.Optical.1.Transceiver.TxPower',
            'Device.Optical.Interface.1.TxPower',
        ];

        $tempCandidates = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverTemperature',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_Temperature',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.TransceiverTemperature',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TransceiverTemperature',
            'Device.Optical.1.Transceiver.Temperature',
            'VirtualParameters.opticalTemperature',
        ];

        $losCandidates = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_LOSStatus',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.LOSStatus',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_LOS',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_LOS',
            'Device.Optical.1.Transceiver.Status',
            'VirtualParameters.losStatus',
        ];

        $rawRx = self::extractValue($flat, $rxCandidates);
        $rawTx = self::extractValue($flat, $txCandidates);
        $rawTemp = self::extractValue($flat, $tempCandidates);
        $rawLos = self::extractValue($flat, $losCandidates);

        // Fallback dynamic wildcard scan if direct candidate paths were omitted
        if ($rawRx === null) {
            foreach ($flat as $k => $v) {
                if (preg_match('/(RXPower|RxPower|OpticalRxPower)$/i', $k) && is_scalar($v) && trim((string)$v) !== '') {
                    $rawRx = $v;
                    break;
                }
            }
        }
        if ($rawTx === null) {
            foreach ($flat as $k => $v) {
                if (preg_match('/(TXPower|TxPower|OpticalTxPower)$/i', $k) && is_scalar($v) && trim((string)$v) !== '') {
                    $rawTx = $v;
                    break;
                }
            }
        }

        $rx = self::normalizePowerValue($rawRx);
        $tx = self::normalizePowerValue($rawTx);

        // =========================================================================
        // GPON PHYSICAL & VENDOR ANOMALY HEURISTICS
        // =========================================================================
        // Physics Rule:
        // - RX (received power from OLT): ALWAYS NEGATIVE on GPON (-8 dBm to -35 dBm).
        // - TX (modem laser transmit power): ALWAYS POSITIVE (+0.5 dBm to +5.0 dBm).
        //
        // Anomaly A (ZTE Firmware Swap):
        // ZTE F609 / F660 firmware often outputs TX in `X_ZTE-COM_RxPower` (+0.73, +1.08)
        // and true RX in `X_ZTE-COM_TxPower` (-19.xx, -21.xx).
        if ($rx !== null && $tx !== null) {
            if ($rx > 0.0 && $rx <= 6.0 && $tx <= -6.0) {
                // Swapped! Invert them to match physics
                $temp = $rx;
                $rx = $tx;
                $tx = $temp;
            }
        } elseif ($rx !== null && $rx > 0.0 && $rx <= 6.0 && $tx === null) {
            // Only one parameter was found in RX path, but it's a typical positive TX value (e.g. 0.73, 1.08)
            // It is definitely TX power!
            $tx = $rx;
            $rx = null;
        }

        // Anomaly B (Positive Attenuation reported without minus sign):
        // Some firmwares report attenuation as positive (e.g. 19.50 or 22.30 dBm)
        if ($rx !== null && $rx >= 7.0 && $rx <= 45.0) {
            $rx = -abs($rx);
        }

        // Anomaly C (OLT DDM Cross-Validation & Fallback):
        // If TR-069 RX power is missing, invalid (> -5.0 dBm or < -40.0 dBm),
        // fallback to OLT optical reading from linked ONU if available.
        if (($rx === null || $rx > -5.0 || $rx < -40.0) && $onu && !empty($onu->rx_power_dbm) && is_numeric($onu->rx_power_dbm)) {
            $oltRx = (float) $onu->rx_power_dbm;
            if ($oltRx > 0) {
                $oltRx = -abs($oltRx);
            }
            $rx = round($oltRx, 2);
        }

        if (($tx === null || $tx < 0.0) && $onu && !empty($onu->tx_power_dbm) && is_numeric($onu->tx_power_dbm)) {
            $tx = round((float) $onu->tx_power_dbm, 2);
        }

        // Format Displays
        $rxDisplay = $rx !== null ? number_format($rx, 2) . ' dBm' : 'Tidak Didukung';
        $txDisplay = $tx !== null ? number_format($tx, 2) . ' dBm' : 'Tidak Didukung';

        $status = 'unknown';
        if ($rx !== null) {
            if ($rx >= -24.0 && $rx <= -15.0) {
                $status = 'normal'; // Optimal
            } elseif (($rx >= -27.0 && $rx < -24.0) || ($rx > -15.0 && $rx <= -10.0)) {
                $status = 'warning'; // Marginal
            } else {
                $status = 'critical'; // Redaman Tinggi / Buruk
            }
        }

        // Temperature parsing
        $tempDisplay = 'Tidak Didukung';
        if ($rawTemp !== null && is_numeric($rawTemp)) {
            $tVal = (float)$rawTemp;
            if ($tVal != 0.0) {
                $tempNum = (abs($tVal) > 1000) ? round($tVal / 1000, 1) : ((abs($tVal) > 100) ? round($tVal / 10, 1) : round($tVal, 1));
                $tempDisplay = $tempNum . ' °C';
            }
        }

        // LOS Parsing
        $losDisplay = 'Normal';
        $isLos = false;
        if ($rawLos !== null && trim((string)$rawLos) !== '') {
            $low = strtolower(trim((string)$rawLos));
            if (in_array($low, ['1', 'true', 'alarm', 'los', 'down', 'loss'], true)) {
                $losDisplay = 'LOS Alarm (Kabel Putus / Tidak Terdeteksi)';
                $isLos = true;
            } elseif (in_array($low, ['0', 'false', 'normal', 'up', 'ok', 'sync'], true)) {
                $losDisplay = 'Normal (Tersambung)';
            } else {
                $losDisplay = (string) $rawLos;
            }
        }

        return [
            'rx_power' => $rx,
            'rx_power_display' => $rxDisplay,
            'rx_status' => $status,
            'tx_power' => $tx,
            'tx_power_display' => $txDisplay,
            'temperature' => $tempDisplay,
            'los_status' => $losDisplay,
            'is_los' => $isLos,
        ];
    }

    /**
     * Clean and normalize ONT Serial Numbers across vendors.
     * Converts Hex prefixes to standard ASCII (HWTC, ZTEG, FHTT, VSOL) and strips garbage bytes.
     */
    public static function parseSerialNumber(array $flat, string $fallbackSn = '', array $deviceData = []): string
    {
        $cleanSn = function ($val) {
            if (!$val || !is_scalar($val)) return null;
            $clean = preg_replace('/[^\x20-\x7E]/', '', (string)$val);
            $clean = preg_replace('/[^A-Za-z0-9_\-:]/', '', $clean);
            return trim($clean);
        };

        $candidates = [
            'VirtualParameters.getSerialNumber',
            'VirtualParameters.serialNumber',
            'VirtualParameters.SerialNumber',
            'VirtualParameters.gponSN',
            'VirtualParameters.GPONSN',
            'InternetGatewayDevice.DeviceInfo.X_HW_SerialNumber',
            'Device.DeviceInfo.X_HW_SerialNumber',
            'InternetGatewayDevice.DeviceInfo.X_HW_GponSN',
            'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.SerialNumber',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_GponSN',
            'InternetGatewayDevice.DeviceInfo.X_FH_GponSN',
            'InternetGatewayDevice.DeviceInfo.SerialNumber',
            'Device.DeviceInfo.SerialNumber',
        ];

        $detected = self::extractValue($flat, $candidates);
        $sn = $cleanSn($detected);

        if (empty($sn)) {
            $deviceSn = $deviceData['_deviceId']['_SerialNumber'] ?? null;
            $sn = $cleanSn($deviceSn) ?: $cleanSn($fallbackSn);
        }

        if (empty($sn)) {
            return '';
        }

        $snUpper = strtoupper($sn);

        // Huawei: 48575443 <-> HWTC
        if (str_starts_with($snUpper, '48575443') && strlen($snUpper) >= 12) {
            return 'HWTC' . substr($snUpper, 8);
        }

        // ZTE: 5A544547 <-> ZTEG
        if (str_starts_with($snUpper, '5A544547') && strlen($snUpper) >= 12) {
            return 'ZTEG' . substr($snUpper, 8);
        }

        // FiberHome: 46485454 <-> FHTT
        if (str_starts_with($snUpper, '46485454') && strlen($snUpper) >= 12) {
            return 'FHTT' . substr($snUpper, 8);
        }

        // VSOL: 56534F4C <-> VSOL
        if (str_starts_with($snUpper, '56534F4C') && strlen($snUpper) >= 12) {
            return 'VSOL' . substr($snUpper, 8);
        }

        // VSOL with dummy prefix 12345...
        if (str_starts_with($snUpper, '12345') && strlen($snUpper) >= 12) {
            return 'VSOL' . substr($snUpper, -8);
        }

        return $sn;
    }

    /**
     * Clean and format MAC Address into uppercase XX:XX:XX:XX:XX:XX.
     */
    public static function normalizeMac(?string $mac): ?string
    {
        if (empty($mac) || !is_string($mac)) {
            return null;
        }

        $val = trim($mac);
        if ($val === '' || $val === '00:00:00:00:00:00' || strtoupper($val) === 'FF:FF:FF:FF:FF:FF') {
            return null;
        }

        $hexOnly = preg_replace('/[^0-9A-Fa-f]/', '', $val);
        if (strlen($hexOnly) === 12) {
            return strtoupper(implode(':', str_split($hexOnly, 2)));
        }

        if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $val)) {
            return strtoupper(str_replace('-', ':', $val));
        }

        return null;
    }

    /**
     * Extract MAC Address across Multi-Vendor TR-069 trees.
     */
    public static function parseMacAddress(array $flat, ?string $fallbackMac = null): ?string
    {
        $candidates = [
            'VirtualParameters.PonMac',
            'VirtualParameters.ponMac',
            'VirtualParameters.pppoeMac',
            'VirtualParameters.MacAddress',
            'VirtualParameters.macAddress',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.PonMac',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_PonMac',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.MACAddress',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.MACAddress',
            'InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1.MACAddress',
            'Device.Ethernet.Interface.1.MACAddress',
        ];

        foreach ($candidates as $cand) {
            if (isset($flat[$cand])) {
                $norm = self::normalizeMac($flat[$cand]);
                if ($norm) return $norm;
            }
        }

        // Wildcard search in flat parameters
        foreach ($flat as $k => $v) {
            if (str_contains($k, 'MACAddress') && !str_contains($k, 'Host') && is_scalar($v)) {
                $norm = self::normalizeMac((string)$v);
                if ($norm) return $norm;
            }
        }

        return self::normalizeMac($fallbackMac);
    }

    /**
     * Extract Vendor, Model, Hardware Version, and Software Version.
     */
    public static function parseDeviceIdentity(array $flat, array $deviceData = [], string $serialNumber = ''): array
    {
        $rawManufacturer = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.Manufacturer',
            'Device.DeviceInfo.Manufacturer',
        ], $deviceData['_deviceId']['_Manufacturer'] ?? null);

        $rawModel = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.ModelName',
            'Device.DeviceInfo.ModelName',
            'InternetGatewayDevice.DeviceInfo.ProductClass',
            'Device.DeviceInfo.ProductClass',
        ], $deviceData['_deviceId']['_ProductClass'] ?? null);

        $softwareVersion = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion',
            'Device.DeviceInfo.SoftwareVersion',
        ]);

        $hardwareVersion = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.HardwareVersion',
            'Device.DeviceInfo.HardwareVersion',
        ]);

        $oui = $deviceData['_deviceId']['_OUI'] ?? self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.ManufacturerOUI',
            'Device.DeviceInfo.ManufacturerOUI',
        ]);

        // Vendor heuristic detection
        $vendor = 'Generic';
        $snUpper = strtoupper($serialNumber);
        $manUpper = strtoupper((string)$rawManufacturer);
        $modelUpper = strtoupper((string)$rawModel);

        if (str_starts_with($snUpper, 'ZTEG') || str_contains($manUpper, 'ZTE') || str_contains($modelUpper, 'F609') || str_contains($modelUpper, 'F660') || str_contains($modelUpper, 'F670')) {
            $vendor = 'ZTE';
        } elseif (str_starts_with($snUpper, 'HWTC') || str_contains($manUpper, 'HUAWEI') || str_contains($modelUpper, 'HG8') || str_contains($modelUpper, 'EG8')) {
            $vendor = 'Huawei';
        } elseif (str_starts_with($snUpper, 'FHTT') || str_contains($manUpper, 'FIBERHOME') || str_contains($modelUpper, 'AN5506') || str_contains($modelUpper, 'HG680')) {
            $vendor = 'FiberHome';
        } elseif (str_starts_with($snUpper, 'VSOL') || str_contains($manUpper, 'VSOL') || str_contains($modelUpper, 'V280')) {
            $vendor = 'VSOL';
        } elseif (str_starts_with($snUpper, 'CDAT') || str_contains($manUpper, 'C-DATA') || str_contains($manUpper, 'CDATA')) {
            $vendor = 'C-Data';
        }

        // Clean redundant vendor from model
        $modelClean = $rawModel;
        if ($modelClean) {
            $modelClean = trim(preg_replace('/^(ZTE|HUAWEI|FIBERHOME|VSOL|CDATA|EchoLife)\s+/i', '', $modelClean));
        }

        return [
            'vendor' => $vendor,
            'manufacturer' => $rawManufacturer ?: $vendor,
            'model' => $modelClean ?: ($rawModel ?: '-'),
            'raw_model' => $rawModel,
            'software_version' => $softwareVersion,
            'hardware_version' => $hardwareVersion,
            'oui' => $oui,
        ];
    }

    /**
     * Extract PPPoE Credentials and Management IP vs PPPoE IP.
     */
    public static function parsePppoeAndIp(array $flat, ?string $connReqUrl = null): array
    {
        $pppoeCandidates = [
            'VirtualParameters.pppoeUsername',
            'VirtualParameters.pppoeUsername2',
            'VirtualParameters.pppUsername',
            'VirtualParameters.pppoe_username',
            'VirtualParameters.Username',
            'VirtualParameters.getpppoeuser',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Username',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.4.WANPPPConnection.1.Username',
            'Device.PPP.Interface.1.Username',
            'Device.PPP.Interface.2.Username',
        ];

        $pppoeUser = self::extractValue($flat, $pppoeCandidates);
        if (!$pppoeUser) {
            // Wildcard search in flat parameters
            foreach ($flat as $k => $v) {
                if ((str_contains($k, 'WANPPPConnection') || str_contains($k, 'PPP.Interface')) && str_ends_with($k, 'Username') && is_scalar($v) && trim((string)$v) !== '') {
                    $pppoeUser = trim((string)$v);
                    break;
                }
            }
        }

        // Password candidates
        $passCandidates = [
            'VirtualParameters.pppoePassword',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Password',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Password',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.4.WANPPPConnection.1.Password',
            'Device.PPP.Interface.1.Password',
            'Device.PPP.Interface.2.Password',
        ];
        $pppoePass = self::extractValue($flat, $passCandidates);

        // TR-069 Management IP (from ConnectionRequestURL)
        $tr069Ip = null;
        if ($connReqUrl) {
            $parsedHost = parse_url($connReqUrl, PHP_URL_HOST);
            if ($parsedHost && $parsedHost !== '0.0.0.0' && filter_var($parsedHost, FILTER_VALIDATE_IP)) {
                $tr069Ip = $parsedHost;
            }
        }

        // PPPoE Client IP
        $rawPppIp = self::extractValue($flat, [
            'VirtualParameters.pppoeIP',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.ExternalIPAddress',
        ]);
        if (!$rawPppIp) {
            foreach ($flat as $k => $v) {
                if (str_contains($k, 'ExternalIPAddress') && is_scalar($v) && $v !== '0.0.0.0' && filter_var($v, FILTER_VALIDATE_IP)) {
                    $rawPppIp = (string)$v;
                    break;
                }
            }
        }

        $pppoeIp = ($rawPppIp && $rawPppIp !== '0.0.0.0' && $rawPppIp !== $tr069Ip) ? $rawPppIp : null;
        if (!$pppoeIp && $rawPppIp && !$tr069Ip) {
            $pppoeIp = $rawPppIp;
        }

        return [
            'pppoe_username' => $pppoeUser,
            'pppoe_password' => $pppoePass,
            'pppoe_ip' => $pppoeIp,
            'tr069_ip' => $tr069Ip,
        ];
    }

    /**
     * Extract Multi-SSID Wi-Fi settings (SSID 1 / 2.4GHz & SSID 2 / 5GHz) and Wi-Fi security.
     */
    public static function parseWifi(array $flat): array
    {
        $ssid1 = self::extractValue($flat, [
            'VirtualParameters.SSID',
            'VirtualParameters.WlanSSID',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
            'Device.WiFi.SSID.1.SSID',
        ]);
        if (!$ssid1) {
            foreach ($flat as $k => $v) {
                if (str_contains($k, 'WLANConfiguration.1.SSID') && is_scalar($v) && trim((string)$v) !== '') {
                    $ssid1 = trim((string)$v);
                    break;
                }
            }
        }

        $ssid2 = self::extractValue($flat, [
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID',
            'Device.WiFi.SSID.2.SSID',
        ]);
        if (!$ssid2) {
            foreach ($flat as $k => $v) {
                if ((str_contains($k, 'WLANConfiguration.5.SSID') || str_contains($k, 'WLANConfiguration.2.SSID')) && is_scalar($v) && trim((string)$v) !== '') {
                    $ssid2 = trim((string)$v);
                    break;
                }
            }
        }

        // Wi-Fi Passwords (KeyPassphrase / PreSharedKey / Vendor Keys)
        $wlanPass1 = self::extractValue($flat, [
            'VirtualParameters.wlanPassword',
            'VirtualParameters.wifiPassword',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.X_ZTE-COM_WPAKey',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.X_HW_KeyPassphrase',
            'Device.WiFi.AccessPoint.1.Security.KeyPassphrase',
        ]);

        return [
            'ssid_1' => $ssid1,
            'ssid_2' => $ssid2,
            'wifi_password_1' => $wlanPass1,
        ];
    }

    /**
     * Format seconds into human-readable uptime string (e.g. 2d 5h 32m).
     */
    public static function formatUptime(?int $seconds): string
    {
        if ($seconds === null || $seconds < 0) {
            return 'Tidak Didukung';
        }

        $days = intdiv($seconds, 86400);
        $rem = $seconds % 86400;
        $hours = intdiv($rem, 3600);
        $rem %= 3600;
        $minutes = intdiv($rem, 60);
        $secs = $rem % 60;

        $parts = [];
        if ($days > 0) $parts[] = "{$days}h";
        if ($hours > 0 || $days > 0) $parts[] = "{$hours}j";
        if ($minutes > 0 || $hours > 0 || $days > 0) $parts[] = "{$minutes}m";
        $parts[] = "{$secs}d";

        return implode(' ', $parts);
    }

    /**
     * Extract full Hardware Monitoring & Diagnostics telemetry across Multi-Vendor TR-069 & TR-181 trees.
     * Supports ZTE, Huawei, FiberHome, VSOL, C-Data, etc.
     */
    public static function parseHardwareMetrics(array $flat, ?ACSDevice $device = null): array
    {
        // 1. CPU Usage
        $cpuCandidates = [
            'VirtualParameters.cpuUsage',
            'VirtualParameters.CpuUsed',
            'VirtualParameters.cpuused',
            'VirtualParameters.cpu',
            'VirtualParameters.cpu_usage',
            'VirtualParameters.CPU',
            'VirtualParameters.CPUUsage',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_CpuUsed',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_CPUUsage',
            'InternetGatewayDevice.DeviceInfo.ProcessStatus.CPUUsage',
            'Device.DeviceInfo.ProcessStatus.CPUUsage',
            'InternetGatewayDevice.DeviceInfo.X_HW_CPUUsage',
            'InternetGatewayDevice.DeviceInfo.X_FH_CPUUsage',
            'InternetGatewayDevice.DeviceInfo.X_CMCC_CPUUsage',
            'InternetGatewayDevice.DeviceInfo.X_CT-COM_CPUUsage',
        ];
        $rawCpu = self::extractValue($flat, $cpuCandidates, $device?->cpu !== null ? (string)$device->cpu : null);

        $cpuValue = null;
        $cpuDisplay = 'Tidak Didukung';
        $cpuStatus = 'unsupported';

        if ($rawCpu !== null && trim((string)$rawCpu) !== '') {
            $cleanedCpu = trim(str_replace('%', '', (string)$rawCpu));
            if (is_numeric($cleanedCpu)) {
                $cpuValue = round((float)$cleanedCpu, 1);
                $cpuDisplay = $cpuValue . '%';
                $cpuStatus = 'supported';
            }
        }

        // 2. CPU / System Temperature
        $tempCandidates = [
            'VirtualParameters.cpuTemperature',
            'VirtualParameters.gettemp',
            'VirtualParameters.getTemp',
            'VirtualParameters.GetTemp',
            'VirtualParameters.temperature',
            'VirtualParameters.suhu',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_Temperature',
            'InternetGatewayDevice.DeviceInfo.X_HW_Temperature',
            'InternetGatewayDevice.DeviceInfo.X_FH_Temperature',
            'InternetGatewayDevice.DeviceInfo.Temperature',
            'Device.DeviceInfo.Temperature',
            'InternetGatewayDevice.DeviceInfo.TemperatureStatus.TemperatureSensor.1.Value',
            'Device.DeviceInfo.TemperatureStatus.TemperatureSensor.1.Value',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverTemperature',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.TransceiverTemperature',
            'Device.Optical.1.Transceiver.Temperature',
            'VirtualParameters.opticalTemperature',
        ];
        $rawTemp = self::extractValue($flat, $tempCandidates, $device?->temperature !== null ? (string)$device->temperature : null);

        $tempValue = null;
        $tempDisplay = 'Tidak Didukung';
        $tempStatus = 'unsupported';

        if ($rawTemp !== null && trim((string)$rawTemp) !== '') {
            $cleanedTemp = trim(str_ireplace(['°c', 'c'], '', (string)$rawTemp));
            if (is_numeric($cleanedTemp)) {
                $t = (float)$cleanedTemp;
                if ($t != 0.0) {
                    $normTemp = (abs($t) > 1000) ? round($t / 1000.0, 1) : ((abs($t) > 100) ? round($t / 10.0, 1) : round($t, 1));
                    $tempValue = $normTemp;
                    $tempDisplay = $normTemp . ' °C';
                    $tempStatus = 'supported';
                }
            }
        }

        // 3. RAM Total, Free, and Usage Percentage
        $ramUsageCandidates = [
            'VirtualParameters.ramUsage',
            'VirtualParameters.MemUsed',
            'VirtualParameters.memused',
            'VirtualParameters.ram',
            'VirtualParameters.ram_usage',
            'VirtualParameters.RAM',
            'VirtualParameters.MemoryUsage',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_MemUsed',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_MemoryUsage',
            'InternetGatewayDevice.DeviceInfo.X_HW_MemoryUsage',
            'InternetGatewayDevice.DeviceInfo.X_FH_MemoryUsage',
            'InternetGatewayDevice.DeviceInfo.X_CMCC_MemoryUsage',
        ];
        $rawRamUsage = self::extractValue($flat, $ramUsageCandidates, $device?->memory !== null ? (string)$device->memory : null);

        $memTotalRaw = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.MemoryStatus.Total',
            'Device.DeviceInfo.MemoryStatus.Total',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_MemTotal',
            'InternetGatewayDevice.DeviceInfo.X_HW_MemoryTotal',
            'InternetGatewayDevice.DeviceInfo.X_FH_MemoryTotal',
        ]);
        $memFreeRaw = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.MemoryStatus.Free',
            'Device.DeviceInfo.MemoryStatus.Free',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_MemFree',
            'InternetGatewayDevice.DeviceInfo.X_HW_MemoryFree',
            'InternetGatewayDevice.DeviceInfo.X_FH_MemoryFree',
        ]);

        $ramTotalBytes = null;
        $ramFreeBytes = null;
        $ramTotalDisplay = null;
        $ramFreeDisplay = null;
        $ramPct = null;
        $ramDisplay = 'Tidak Didukung';
        $ramStatus = 'unsupported';

        if ($memTotalRaw !== null && is_numeric($memTotalRaw) && (float)$memTotalRaw > 0) {
            $tot = (float)$memTotalRaw;
            // Convert to MB if reported in KB or Bytes
            if ($tot > 1048576) {
                // Bytes
                $ramTotalBytes = $tot;
                $ramTotalDisplay = round($tot / (1024 * 1024), 0) . ' MB';
            } elseif ($tot > 1024) {
                // KB
                $ramTotalBytes = $tot * 1024;
                $ramTotalDisplay = round($tot / 1024, 0) . ' MB';
            } else {
                // Direct MB
                $ramTotalBytes = $tot * 1024 * 1024;
                $ramTotalDisplay = round($tot, 0) . ' MB';
            }

            if ($memFreeRaw !== null && is_numeric($memFreeRaw)) {
                $fre = (float)$memFreeRaw;
                if ($fre > 1048576) {
                    $ramFreeBytes = $fre;
                    $ramFreeDisplay = round($fre / (1024 * 1024), 0) . ' MB';
                } elseif ($fre > 1024) {
                    $ramFreeBytes = $fre * 1024;
                    $ramFreeDisplay = round($fre / 1024, 0) . ' MB';
                } else {
                    $ramFreeBytes = $fre * 1024 * 1024;
                    $ramFreeDisplay = round($fre, 0) . ' MB';
                }

                if ($ramTotalBytes > 0 && $ramFreeBytes !== null) {
                    $used = $ramTotalBytes - $ramFreeBytes;
                    $ramPct = round(($used / $ramTotalBytes) * 100, 1);
                    $ramDisplay = $ramPct . '%';
                    $ramStatus = 'supported';
                }
            }
        }

        // Direct usage percentage fallback
        if ($ramPct === null && $rawRamUsage !== null && trim((string)$rawRamUsage) !== '') {
            $cleanRam = trim(str_replace('%', '', (string)$rawRamUsage));
            if (is_numeric($cleanRam)) {
                $ramPct = round((float)$cleanRam, 1);
                $ramDisplay = $ramPct . '%';
                $ramStatus = 'supported';
            }
        }

        // 4. Flash Storage Total / Free
        $flashTotalRaw = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_FlashTotal',
            'InternetGatewayDevice.DeviceInfo.X_HW_FlashTotal',
            'InternetGatewayDevice.DeviceInfo.X_FH_FlashTotal',
            'InternetGatewayDevice.DeviceInfo.FlashTotal',
            'Device.DeviceInfo.FlashTotal',
            'Device.DeviceInfo.VendorConfigFile.1.Size',
        ]);
        $flashFreeRaw = self::extractValue($flat, [
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_FlashFree',
            'InternetGatewayDevice.DeviceInfo.X_HW_FlashFree',
            'InternetGatewayDevice.DeviceInfo.X_FH_FlashFree',
            'InternetGatewayDevice.DeviceInfo.FlashFree',
            'Device.DeviceInfo.FlashFree',
        ]);

        $flashTotalDisplay = null;
        $flashFreeDisplay = null;
        $flashStatus = 'unsupported';
        if ($flashTotalRaw !== null && is_numeric($flashTotalRaw) && (float)$flashTotalRaw > 0) {
            $fTot = (float)$flashTotalRaw;
            $flashTotalDisplay = ($fTot > 1048576) ? round($fTot / (1024 * 1024), 0) . ' MB' : (($fTot > 1024) ? round($fTot / 1024, 0) . ' MB' : round($fTot, 0) . ' MB');
            $flashStatus = 'supported';
            if ($flashFreeRaw !== null && is_numeric($flashFreeRaw)) {
                $fFre = (float)$flashFreeRaw;
                $flashFreeDisplay = ($fFre > 1048576) ? round($fFre / (1024 * 1024), 0) . ' MB' : (($fFre > 1024) ? round($fFre / 1024, 0) . ' MB' : round($fFre, 0) . ' MB');
            }
        }

        // 5. Uptime
        $uptimeCandidates = [
            'VirtualParameters.deviceUptime',
            'VirtualParameters.uptime',
            'InternetGatewayDevice.DeviceInfo.UpTime',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_UpTime',
            'Device.DeviceInfo.UpTime',
        ];
        $rawUptime = self::extractValue($flat, $uptimeCandidates, $device?->uptime !== null ? (string)$device->uptime : null);

        $uptimeSeconds = null;
        $uptimeDisplay = 'Tidak Didukung';
        $uptimeStatus = 'unsupported';

        if ($rawUptime !== null && is_numeric($rawUptime)) {
            $uptimeSeconds = (int)$rawUptime;
            $uptimeDisplay = self::formatUptime($uptimeSeconds);
            $uptimeStatus = 'supported';
        }

        // 6. Identity & Versions
        $identity = self::parseDeviceIdentity($flat, [], $device?->serial_number ?? '');

        return [
            'cpu_usage' => $cpuValue,
            'cpu_usage_display' => $cpuDisplay,
            'cpu_status' => $cpuStatus,

            'temperature' => $tempValue,
            'temperature_display' => $tempDisplay,
            'temperature_status' => $tempStatus,

            'ram_usage' => $ramPct,
            'ram_usage_display' => $ramDisplay,
            'ram_total_display' => $ramTotalDisplay,
            'ram_free_display' => $ramFreeDisplay,
            'ram_status' => $ramStatus,

            'flash_total_display' => $flashTotalDisplay,
            'flash_free_display' => $flashFreeDisplay,
            'flash_status' => $flashStatus,

            'uptime_seconds' => $uptimeSeconds,
            'uptime_display' => $uptimeDisplay,
            'uptime_status' => $uptimeStatus,

            'hardware_version' => $identity['hardware_version'] ?? ($device?->hardware_version ?: 'Tidak Tersedia'),
            'software_version' => $identity['software_version'] ?? ($device?->software_version ?: ($device?->firmware_version ?: 'Tidak Tersedia')),
            'manufacturer' => $identity['manufacturer'] ?? ($device?->manufacturer ?: 'Generic'),
            'product_class' => $identity['model'] ?? ($device?->product_class ?: 'ONT/ONU'),
            'vendor' => $identity['vendor'] ?? 'Generic',
        ];
    }

    /**
     * Unified Dynamic WAN Discovery Parser across TR-098 and TR-181 data models.
     * Extracts PPPoE, IP Routed, Bridge, DHCP/Static, VLAN, NAT, LAN binding, and writable flags.
     */
    public static function parseWanTree(array $params, string $vendor = 'generic'): array
    {
        $wans = [];

        // 1. Discover ZTE port binding table if available
        $zteBindings = [];
        if (isset($params['InternetGatewayDevice']['X_ZTE-COM_PortBinding']) && is_array($params['InternetGatewayDevice']['X_ZTE-COM_PortBinding'])) {
            foreach ($params['InternetGatewayDevice']['X_ZTE-COM_PortBinding'] as $key => $bindingNode) {
                if ($key === '_object' || !is_array($bindingNode)) continue;
                $wanIf = $bindingNode['WANInterface']['_value'] ?? '';
                $lanIf = $bindingNode['LANInterface']['_value'] ?? '';
                if ($wanIf && $lanIf) {
                    $zteBindings[$wanIf] = $lanIf;
                }
            }
        }

        // 2. TR-098: InternetGatewayDevice.WANDevice.*
        $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? [];
        if (is_array($wanDevices)) {
            foreach ($wanDevices as $wdIndex => $wdNode) {
                if (!is_numeric($wdIndex) || !is_array($wdNode)) continue;

                $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                if (!is_array($connDevices)) continue;

                foreach ($connDevices as $cdIndex => $cdNode) {
                    if (!is_numeric($cdIndex) || !is_array($cdNode)) continue;

                    $parentVlan = $cdNode['X_FH_WANGponLinkConfig']['VLANIDMark']['_value'] ??
                                  $cdNode['X_FH_WANGponLinkConfig']['VLANID']['_value'] ??
                                  $cdNode['WANEthernetLinkConfig']['X_HW_VLAN']['_value'] ??
                                  $cdNode['WANEthernetLinkConfig']['VLANIDMark']['_value'] ??
                                  $cdNode['VLANID']['_value'] ??
                                  null;

                    // A. WANPPPConnection
                    $ppp = $cdNode['WANPPPConnection'] ?? [];
                    if (is_array($ppp)) {
                        foreach ($ppp as $pIndex => $pNode) {
                            if (!is_numeric($pIndex) || !is_array($pNode)) continue;
                            $fullPath = "InternetGatewayDevice.WANDevice.{$wdIndex}.WANConnectionDevice.{$cdIndex}.WANPPPConnection.{$pIndex}";
                            $wans[] = self::normalizeWanInstance($pNode, 'PPPoE', "{$wdIndex}.{$cdIndex}.{$pIndex}", $fullPath, $parentVlan, $zteBindings);
                        }
                    }

                    // B. WANIPConnection
                    $ip = $cdNode['WANIPConnection'] ?? [];
                    if (is_array($ip)) {
                        foreach ($ip as $iIndex => $iNode) {
                            if (!is_numeric($iIndex) || !is_array($iNode)) continue;
                            $fullPath = "InternetGatewayDevice.WANDevice.{$wdIndex}.WANConnectionDevice.{$cdIndex}.WANIPConnection.{$iIndex}";
                            $wans[] = self::normalizeWanInstance($iNode, 'IPoE', "{$wdIndex}.{$cdIndex}.{$iIndex}", $fullPath, $parentVlan, $zteBindings);
                        }
                    }
                }
            }
        }

        // 3. TR-181: Device.PPP.Interface.* & Device.IP.Interface.* (if TR-098 is empty or partial)
        $tr181Ppp = $params['Device']['PPP']['Interface'] ?? [];
        if (is_array($tr181Ppp)) {
            foreach ($tr181Ppp as $pIndex => $pNode) {
                if (!is_numeric($pIndex) || !is_array($pNode)) continue;
                $fullPath = "Device.PPP.Interface.{$pIndex}";
                $wans[] = self::normalizeWanInstance($pNode, 'PPPoE', "TR181.PPP.{$pIndex}", $fullPath, null, $zteBindings);
            }
        }

        $tr181Ip = $params['Device']['IP']['Interface'] ?? [];
        if (is_array($tr181Ip)) {
            foreach ($tr181Ip as $iIndex => $iNode) {
                if (!is_numeric($iIndex) || !is_array($iNode)) continue;
                $name = $iNode['Name']['_value'] ?? '';
                if (stripos($name, 'lan') !== false || stripos($name, 'br0') !== false || stripos($name, 'loop') !== false) {
                    continue;
                }
                $fullPath = "Device.IP.Interface.{$iIndex}";
                $wans[] = self::normalizeWanInstance($iNode, 'IPoE', "TR181.IP.{$iIndex}", $fullPath, null, $zteBindings);
            }
        }

        return $wans;
    }

    /**
     * Helper to normalize a single WAN instance node into consistent attributes.
     */
    private static function normalizeWanInstance(array $node, string $defaultType, string $pathIndex, string $fullPath, $parentVlan = null, array $zteBindings = []): array
    {
        $name = $node['Name']['_value'] ?? "WAN {$pathIndex}";
        $enable = (bool) ($node['Enable']['_value'] ?? true);
        $nat = (bool) ($node['NATEnabled']['_value'] ?? false);
        $connType = $node['ConnectionType']['_value'] ?? null;
        $addressingType = $node['AddressingType']['_value'] ?? ($defaultType === 'PPPoE' ? 'PPPoE' : 'DHCP');

        $vlan = $node['VLANID']['_value'] ??
                $node['X_BROADCOM_COM_VLANID']['_value'] ??
                $node['X_ZTE-COM_VLANID']['_value'] ??
                $node['X_HW_VLAN']['_value'] ??
                $parentVlan;

        $vlanInt = ($vlan !== null && is_numeric($vlan)) ? (int)$vlan : null;
        $vlanEnabled = ($vlanInt !== null && $vlanInt > 0);

        if (isset($node['X_ZTE-COM_VLANEnable']['_value'])) {
            $vlanEnabled = (bool) $node['X_ZTE-COM_VLANEnable']['_value'];
        }

        $serviceList = $node['X_FH_ServiceList']['_value'] ??
                       $node['X_ZTE-COM_ServiceList']['_value'] ??
                       $node['X_HW_ServiceList']['_value'] ??
                       $node['X_HW_SERVICELIST']['_value'] ??
                       '-';

        $portBind = '';
        if (isset($node['X_FH_LanInterface']['_value'])) {
            $portBind = $node['X_FH_LanInterface']['_value'];
        } elseif (isset($node['X_ZTE-COM_LanInterface']['_value'])) {
            $portBind = $node['X_ZTE-COM_LanInterface']['_value'];
        } elseif (isset($node['X_ZTE-COM_LANInterface']['_value'])) {
            $portBind = $node['X_ZTE-COM_LANInterface']['_value'];
        } elseif (isset($node['X_ZTE-COM_PortBind']['_value'])) {
            $portBind = $node['X_ZTE-COM_PortBind']['_value'];
        } elseif (isset($zteBindings[$fullPath])) {
            $portBind = $zteBindings[$fullPath];
        } elseif (isset($node['X_HW_LANBinding']['_value'])) {
            $portBind = $node['X_HW_LANBinding']['_value'];
        } elseif (isset($node['X_HW_LANBIND']) && is_array($node['X_HW_LANBIND'])) {
            $binds = [];
            foreach ($node['X_HW_LANBIND'] as $k => $v) {
                if ($k === '_object' || $k === '_writable' || $k === '_timestamp') continue;
                if (isset($v['_value']) && ($v['_value'] == 1 || strtolower((string)$v['_value']) === 'true')) {
                    $binds[] = $k;
                }
            }
            $portBind = implode(', ', $binds);
        } elseif (isset($node['X_HW_LANBIND']['_value'])) {
            $portBind = $node['X_HW_LANBIND']['_value'];
        }

        $username = $node['Username']['_value'] ?? null;
        $hasPassword = !empty($node['Password']['_value']);
        $ipAddress = $node['ExternalIPAddress']['_value'] 
            ?? ($node['IPv4Address']['1']['IPAddress']['_value'] 
            ?? ($node['IPCP']['LocalIPAddress']['_value'] ?? null));
        $subnetMask = $node['SubnetMask']['_value'] ?? ($node['IPv4Address']['1']['SubnetMask']['_value'] ?? null);
        $gateway = $node['DefaultGateway']['_value'] ?? null;
        $dns = $node['DNSServers']['_value'] ?? null;

        // Friendly label
        $modeLabel = $defaultType;
        $connUpper = strtoupper((string)$connType);
        $nameUpper = strtoupper((string)$name);

        if ($connUpper === 'PPPOE_BRIDGED' || (str_contains($nameUpper, 'BRIDGE') && str_contains($fullPath, 'WANPPPConnection') && empty($username))) {
            $modeLabel = 'Bridge (PPP)';
        } elseif ($connUpper === 'IP_BRIDGED' || str_contains($nameUpper, '_B_') || (str_contains($nameUpper, 'BRIDGE') && str_contains($fullPath, 'WANIPConnection'))) {
            $modeLabel = 'Bridge (IP)';
        } elseif ($connUpper === 'PPPOE_ROUTED' || $defaultType === 'PPPoE' || !empty($username)) {
            $modeLabel = 'PPPoE Route';
        } elseif ($connUpper === 'IP_ROUTED') {
            $modeLabel = ($addressingType === 'Static') ? 'IP Route (Static)' : 'IP Route (DHCP)';
        }

        $isTr069 = str_contains(strtoupper($serviceList), 'TR069')
            || str_contains(strtoupper($name), 'TR069')
            || ($vlanInt === 99);

        $isWritable = $node['_writable'] ?? true;

        return [
            'id' => $pathIndex,
            'name' => $name,
            'type' => $defaultType,
            'mode_label' => $modeLabel,
            'connection_type' => $connType ?: ($defaultType === 'PPPoE' ? 'IP_Routed' : 'IP_Routed'),
            'addressing_type' => $addressingType,
            'enable' => $enable,
            'nat' => $nat,
            'vlan' => $vlanInt,
            'vlan_enabled' => $vlanEnabled,
            'username' => $username,
            'has_password' => $hasPassword,
            'service_list' => $serviceList,
            'port_bind' => $portBind,
            'ip_address' => ($ipAddress && $ipAddress !== '0.0.0.0') ? $ipAddress : null,
            'subnet_mask' => ($subnetMask && $subnetMask !== '0.0.0.0') ? $subnetMask : null,
            'gateway' => ($gateway && $gateway !== '0.0.0.0') ? $gateway : null,
            'dns' => ($dns && $dns !== '0.0.0.0') ? $dns : null,
            'fullPath' => $fullPath,
            'is_tr069' => $isTr069,
            'writable' => (bool)$isWritable,
        ];
    }
}
