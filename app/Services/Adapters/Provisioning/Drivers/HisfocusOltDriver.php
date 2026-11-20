<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;
use App\Services\Adapters\Provisioning\Support\TelnetSshClient;
use Illuminate\Support\Facades\Log;

class HisfocusOltDriver extends BaseOltDriver
{
    protected string $cliMode = 'telnet';

    protected array $systemOids = [
        'sysName'        => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime'      => '.1.3.6.1.2.1.1.3.0',
        'sysDescr'       => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.8888.1.2.1.1.2.26.0', // GBN / GP210 temperature (52 C)
    ];

    /**
     * Override getSystemInfo untuk membaca spesifikasi asli dari OLT GBN / GP210 (.1.3.6.1.4.1.8888)
     */
    public function getSystemInfo(): array
    {
        $info = parent::getSystemInfo();

        try {
            // Model OLT (GP210)
            $model = $this->snmp->get('.1.3.6.1.4.1.8888.1.2.1.1.2.15.0');
            if (!empty($model)) {
                $cleanModel = trim((string)$model, '" ');
                $cleanModel = preg_replace('/^(STRING|Hex-STRING)\s*:?\s*/i', '', $cleanModel);
                $info['model'] = trim($cleanModel);
            }

            // Serial Number OLT
            $serial = $this->snmp->get('.1.3.6.1.4.1.8888.1.2.1.1.2.19.0');
            if (!empty($serial)) {
                $cleanSerial = trim((string)$serial, '" ');
                $cleanSerial = preg_replace('/^(STRING|Hex-STRING)\s*:?\s*/i', '', $cleanSerial);
                $info['serial_number'] = trim($cleanSerial);
            }

            // Firmware Version
            $fw = $this->snmp->get('.1.3.6.1.4.1.8888.1.2.1.1.2.2.0');
            if (!empty($fw)) {
                $cleanFw = trim((string)$fw, '" ');
                $cleanFw = preg_replace('/^(STRING|Hex-STRING)\s*:?\s*/i', '', $cleanFw);
                $info['firmware'] = trim($cleanFw);
            }

            // Suhu (°C)
            $temp = $this->snmp->get('.1.3.6.1.4.1.8888.1.2.1.1.2.26.0');
            if ($temp !== null && is_numeric($temp) && (int)$temp > 0 && (int)$temp < 120) {
                $info['temperature'] = (int)$temp;
            }
        } catch (\Throwable) {}

        return $info;
    }

    /**
     * Cache hasil walk SNMP agar tidak melakukan walk berulang untuk setiap port.
     */
    protected ?array $cachedSnmpOnus = null;

    /**
     * Cache hasil CLI scraping agar tidak membuka koneksi Telnet berulang kali.
     */
    protected ?array $cachedCliOnus = null;

    /**
     * Mendeteksi daftar port PON pada OLT HISFOCUS.
     */
    public function getPonPortsStatus(): array
    {
        $ports = [];

        // 1. Coba OID V-SOL GPON Modern (65000) - standar V-SOL V1600G series (HF-G02, HF-G04)
        try {
            $vsolPorts = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.2.1.2') ?: [];
            if (!empty($vsolPorts)) {
                $statusMap = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.2.1.4') ?: [];
                foreach ($vsolPorts as $idx => $name) {
                    $cleanOid = str_replace(['iso', 'SNMPv2-SMI::enterprises.'], ['.1', '.1.3.6.1.4.1.'], (string)$idx);
                    $parts = explode('.', trim($cleanOid, '. '));
                    $pNum = (int)end($parts);
                    $cleanName = trim((string)$name);
                    $cleanName = preg_replace('/^(STRING|Hex-STRING|OctetString)\s*:?\s*/i', '', $cleanName);
                    $cleanName = trim($cleanName, '" ');
                    $st = (int)($statusMap[$idx] ?? 1);
                    $ports[] = [
                        'port_index' => $pNum > 0 ? $pNum : (count($ports) + 1),
                        'port_name'  => !empty($cleanName) && str_contains(strtoupper($cleanName), 'PON') ? $cleanName : "GPON 0/0/{$pNum}",
                        'status'     => match ($st) {
                            1 => 'up',
                            2 => 'down',
                            default => 'up'
                        },
                    ];
                }
                if (!empty($ports)) {
                    return $this->capPonPortsToOltConfig($ports);
                }
            }
        } catch (\Throwable) {}

        // 2. Coba OID RicherLink / V-SOL OEM (37950)
        try {
            $rlPorts = $this->snmp->walk('.1.3.6.1.4.1.37950.1.1.5.10.1.1.2') ?: [];
            if (!empty($rlPorts)) {
                foreach ($rlPorts as $idx => $name) {
                    $cleanOid = str_replace(['iso', 'SNMPv2-SMI::enterprises.'], ['.1', '.1.3.6.1.4.1.'], (string)$idx);
                    $parts = explode('.', trim($cleanOid, '. '));
                    $pNum = (int)end($parts);
                    $cleanName = trim((string)$name, '" ');
                    $ports[] = [
                        'port_index' => $pNum > 0 ? $pNum : (count($ports) + 1),
                        'port_name'  => !empty($cleanName) && str_contains(strtoupper($cleanName), 'PON') ? $cleanName : "GPON 0/0/{$pNum}",
                        'status'     => 'up',
                    ];
                }
                if (!empty($ports)) {
                    return $this->capPonPortsToOltConfig($ports);
                }
            }
        } catch (\Throwable) {}

        // 3. Coba OID C-Data GPON (34592)
        try {
            $cdPorts = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.4') ?: [];
            if (!empty($cdPorts)) {
                foreach ($cdPorts as $idx => $st) {
                    $cleanOid = str_replace(['iso', 'SNMPv2-SMI::enterprises.'], ['.1', '.1.3.6.1.4.1.'], (string)$idx);
                    $parts = explode('.', trim($cleanOid, '. '));
                    $pNum = (int)end($parts);
                    $ports[] = [
                        'port_index' => $pNum > 0 ? $pNum : (count($ports) + 1),
                        'port_name'  => "GPON 0/0/{$pNum}",
                        'status'     => (int)$st === 1 ? 'up' : 'down',
                    ];
                }
                if (!empty($ports)) {
                    return $this->capPonPortsToOltConfig($ports);
                }
            }
        } catch (\Throwable) {}

        // 4. Coba OID HSGQ Modern (50224) - sering dipakai di HISFOCUS GPON (HF-G04/G08)
        try {
            $names = $this->snmp->walk('.1.3.6.1.4.1.50224.3.2.1.1.2') ?: ($this->snmp->walk('.1.3.6.1.4.1.50224.3.3.1.1.2') ?: []);
            $statuses = $this->snmp->walk('.1.3.6.1.4.1.50224.3.2.1.1.6') ?: ($this->snmp->walk('.1.3.6.1.4.1.50224.3.3.1.1.6') ?: []);
            if (!empty($names) || !empty($statuses)) {
                $statusMap = [];
                foreach ($statuses as $idx => $val) {
                    $cleanOid = str_replace(['iso', 'SNMPv2-SMI::enterprises.'], ['.1', '.1.3.6.1.4.1.'], (string)$idx);
                    $parts = explode('.', trim($cleanOid, '. '));
                    $portIdx = (int)end($parts);
                    $statusMap[$portIdx] = (int)$val;
                }
                foreach ($names as $idx => $name) {
                    $cleanOid = str_replace(['iso', 'SNMPv2-SMI::enterprises.'], ['.1', '.1.3.6.1.4.1.'], (string)$idx);
                    $parts = explode('.', trim($cleanOid, '. '));
                    $portIdx = (int)end($parts);
                    $cleanName = trim((string)$name);
                    $cleanName = preg_replace('/^(STRING|Hex-STRING|OctetString)\s*:?\s*/i', '', $cleanName);
                    $cleanName = trim($cleanName, '" ');
                    if (str_contains(strtoupper($cleanName), 'PON') || str_contains(strtoupper($cleanName), 'EPON') || str_contains(strtoupper($cleanName), 'GPON')) {
                        $st = $statusMap[$portIdx] ?? 1;
                        $ports[] = [
                            'port_index' => $portIdx,
                            'port_name'  => $cleanName,
                            'status'     => ($st === 2 || $st === 1) ? 'up' : 'down',
                        ];
                    }
                }
                if (!empty($ports)) {
                    return $this->capPonPortsToOltConfig($ports);
                }
            }
        } catch (\Throwable) {}

        // 5. Coba CTC EPON Standard MIB (.1.3.6.1.4.1.17409.2.3.1.2.1.1) - standar HISFOCUS EPON (HF-E04/E08)
        try {
            $ctcPorts = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.1.2.1.1');
            if (!empty($ctcPorts)) {
                foreach ($ctcPorts as $idx => $val) {
                    $portIdx = (int)preg_replace('/^.*?(\d+)$/', '$1', (string)$idx);
                    $pNum = $portIdx > 0 ? $portIdx : (count($ports) + 1);
                    $ports[] = [
                        'port_index' => $pNum,
                        'port_name'  => "EPON 0/0/{$pNum}",
                        'status'     => 'up',
                    ];
                }
                if (!empty($ports)) {
                    return $this->capPonPortsToOltConfig($ports);
                }
            }
        } catch (\Throwable) {}

        // 6. Coba OID HSGQ Legacy (5875)
        try {
            $statuses = $this->snmp->walk('.1.3.6.1.4.1.5875.901.10.1.1.1.1.5');
            if (!empty($statuses)) {
                foreach ($statuses as $idx => $status) {
                    $portIdx = (int)preg_replace('/^.*?(\d+)$/', '$1', (string)$idx);
                    $ports[] = [
                        'port_index' => $portIdx > 0 ? $portIdx : (int)$idx,
                        'port_name'  => "PON 0/0/" . ($portIdx > 0 ? $portIdx : $idx),
                        'status'     => match ((int)$status) {
                            1, 101 => 'up',
                            2, 102 => 'down',
                            default => 'unknown'
                        },
                    ];
                }
                if (!empty($ports)) {
                    return $this->capPonPortsToOltConfig($ports);
                }
            }
        } catch (\Throwable) {}

        // 7. Fallback Universal: Standard IF-MIB RFC 2863 & Database Port Count
        return $this->capPonPortsToOltConfig($this->getStandardIfMibPonPorts());
    }

    /**
     * Membatasi jumlah port PON agar sesuai dengan spesifikasi perangkat fisik (misal 2 Port OLT).
     */
    protected function capPonPortsToOltConfig(array $ports): array
    {
        $configured = (int)($this->olt->pon_port_count ?? 0);

        // Deteksi cerdas dari Nama atau Model OLT jika database belum diset
        // Misal: HF-G02, 2 PORT, 2PON, 2P, V1600G1, V1600G-02
        if ($configured <= 0) {
            $ident = strtolower(($this->olt->model ?? '') . ' ' . ($this->olt->name ?? ''));
            if (preg_match('/\b(g0?2|e0?2|2\s*p(?:ort)?|v1600g1|v1600g-02)\b/i', $ident)) {
                $configured = 2;
            } elseif (preg_match('/\b(g0?4|e0?4|4\s*p(?:ort)?|v1600g2|v1600g-04)\b/i', $ident)) {
                $configured = 4;
            } elseif (preg_match('/\b(g0?8|e0?8|8\s*p(?:ort)?|v1600g-08)\b/i', $ident)) {
                $configured = 8;
            }
        }

        if ($configured > 0 && count($ports) > $configured) {
            $ports = array_slice($ports, 0, $configured);
        }

        return $ports;
    }

    /**
     * Membaca seluruh data ONU (SN, MAC, Status, Rx/Tx Power) untuk port PON tertentu.
     */
    public function getOnuRxPower(int $ponPort): array
    {
        // 1. Ambil atau bangun cache ONU dari SNMP jika belum tersedia
        if ($this->cachedSnmpOnus === null) {
            $this->cachedSnmpOnus = $this->collectAllOnusFromSnmp();
        }

        // 2. Filter ONU berdasarkan target ponPort
        $matched = $this->filterOnusByPort($this->cachedSnmpOnus, $ponPort);

        if (!empty($matched)) {
            return $matched;
        }

        // 3. Jika SNMP tidak mengembalikan ONU untuk port ini, coba CLI Scraping (Telnet/SSH)
        if ($this->cachedCliOnus === null) {
            $this->cachedCliOnus = [];
            try {
                $this->cachedCliOnus = $this->collectAllOnusFromCli();
            } catch (\Throwable $e) {
                Log::warning("Hisfocus CLI scraping error: " . $e->getMessage());
            }
        }

        $cliMatched = $this->filterOnusByPort($this->cachedCliOnus, $ponPort);
        if (!empty($cliMatched)) {
            return $cliMatched;
        }

        // 4. Fallback: Jika target port adalah port pertama (1 atau 5) atau port count OLT <= 2 dan semua ONU ada di port 1/flat
        if (($ponPort === 1 || $ponPort === 5) && !empty($this->cachedSnmpOnus)) {
            return $this->cachedSnmpOnus;
        }
        if (($ponPort === 1 || $ponPort === 5) && !empty($this->cachedCliOnus)) {
            return $this->cachedCliOnus;
        }

        return [];
    }

    /**
     * Mengumpulkan seluruh data ONU dari berbagai OID SNMP:
     * 1. V-SOL GPON Modern (65000) - OEM utama HISFOCUS GPON (HF-G02/G04)
     * 2. C-Data GPON (34592)
     * 3. RicherLink GPON (37950)
     * 4. HSGQ Modern (50224)
     * 5. CTC EPON (17409)
     * 6. HSGQ Legacy (5875)
     * 7. Bridge FDB (17.4.3.1.1)
     */
    protected function collectAllOnusFromSnmp(): array
    {
        $onus = [];

        // --- Sumber 0: GBN / GP210 Enterprise MIB (.1.3.6.1.4.1.8888) ---
        try {
            foreach (['.1.3.6.1.4.1.8888.3', '.1.3.6.1.4.1.8888.2', '.1.3.6.1.4.1.8888.1.3', '.1.3.6.1.4.1.8888.4'] as $baseBranch) {
                $rawWalk = $this->snmp->walk($baseBranch);
                if (empty($rawWalk)) continue;

                foreach ($rawWalk as $idx => $val) {
                    $sn = $this->cleanSnmpSerialOrMac($val);
                    if (!empty($sn) && (preg_match('/^[A-Z]{4}[0-9A-F]{8,12}$/i', $sn) || str_contains($sn, ':'))) {
                        $parsed = $this->parsePortAndOnuIndex((string)$idx);
                        $onus[] = [
                            'pon_port'         => $parsed['port'],
                            'raw_port_idx'     => $parsed['raw_port'],
                            'onu_index'        => $parsed['onu'],
                            'serial_number'    => str_replace(':', '', $sn),
                            'mac_address'      => str_contains($sn, ':') ? $sn : null,
                            'rx_power_dbm'     => null,
                            'tx_power_dbm'     => null,
                            'snr_db'           => null,
                            'temperature'      => null,
                            'firmware_version' => null,
                            'model'            => null,
                            'name'             => null,
                            'status'           => 'online',
                        ];
                    }
                }
                if (!empty($onus)) {
                    return $onus;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus GBN 8888 SNMP error: " . $e->getMessage());
        }

        $isGbn = str_contains(strtolower($this->olt->model ?? ''), 'gp210') ||
                 str_contains(strtolower($this->olt->name ?? ''), 'gp210') ||
                 $this->systemOids['sysTemperature'] === '.1.3.6.1.4.1.8888.1.2.1.1.2.26.0';

        if ($isGbn) {
            // Pada GBN GP210, SNMP walk tidak menyediakan tabel ONU.
            // Langsung lewati vendor lain agar tidak membuang waktu 25 detik SNMP timeout!
            return $onus;
        }

        // --- Sumber 1: V-SOL GPON Modern (.1.3.6.1.4.1.65000.1.3.4) ---
        // Sering dipakai pada OLT HISFOCUS GPON (HF-G02 / HF-G04)
        try {
            $vsolSerials = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.5') ?: [];
            if (!empty($vsolSerials)) {
                $vsolStatuses = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.3') ?: [];
                $vsolRx       = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.14') ?: [];
                $vsolTx       = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.15') ?: [];
                $vsolTemps    = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.16') ?: [];
                $vsolMacs     = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.6') ?: [];
                $vsolFws      = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.11') ?: [];
                $vsolModels   = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.10') ?: [];
                $vsolNames    = $this->snmp->walk('.1.3.6.1.4.1.65000.1.3.4.1.2') ?: [];

                foreach ($vsolSerials as $idx => $serialRaw) {
                    $parsed = $this->parsePortAndOnuIndex((string)$idx);
                    $sn = $this->cleanSnmpSerialOrMac($serialRaw);
                    if (empty($sn)) continue;

                    $rxRawVal = (float)($vsolRx[$idx] ?? 0);
                    $rxVal = null;
                    if ($rxRawVal != 0) {
                        if ($rxRawVal > 1000 && $rxRawVal < 65000) {
                            $rxVal = round(($rxRawVal / 100) - 50, 2);
                        } elseif ($rxRawVal > 100 || $rxRawVal < -100) {
                            $rxVal = round($rxRawVal / 100, 2);
                        } else {
                            $rxVal = round($rxRawVal, 2);
                        }
                        if ($rxVal > 0 && $rxVal <= 45) {
                            $rxVal = -abs($rxVal);
                        }
                    }

                    $txRawVal = (float)($vsolTx[$idx] ?? 0);
                    $txVal = null;
                    if ($txRawVal != 0) {
                        if ($txRawVal > 1000 && $txRawVal < 65000) {
                            $txVal = round(($txRawVal / 100) - 50, 2);
                        } elseif ($txRawVal > 100 || $txRawVal < -100) {
                            $txVal = round($txRawVal / 100, 2);
                        } else {
                            $txVal = round($txRawVal, 2);
                        }
                    }

                    $tempVal = null;
                    if (!empty($vsolTemps[$idx])) {
                        $t = (float)$vsolTemps[$idx];
                        $tempVal = $t > 1000 ? round($t / 100, 1) : $t;
                    }

                    $st = (int)($vsolStatuses[$idx] ?? 0);
                    $isOnline = in_array($st, [1, 3, 10], true) || ($rxVal !== null && $rxVal >= -38.0 && $rxVal <= -6.0);

                    $mac = !empty($vsolMacs[$idx]) ? $this->cleanSnmpSerialOrMac($vsolMacs[$idx]) : (str_contains($sn, ':') ? $sn : null);

                    $onus[] = [
                        'pon_port'         => $parsed['port'],
                        'raw_port_idx'     => $parsed['raw_port'],
                        'onu_index'        => $parsed['onu'],
                        'serial_number'    => str_replace(':', '', $sn),
                        'mac_address'      => $mac,
                        'rx_power_dbm'     => ($rxVal !== null && $rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                        'tx_power_dbm'     => ($txVal !== null && $txVal >= -10.0 && $txVal <= 15.0) ? $txVal : null,
                        'snr_db'           => null,
                        'temperature'      => $tempVal,
                        'firmware_version' => isset($vsolFws[$idx]) ? trim((string)$vsolFws[$idx], '" ') : null,
                        'model'            => isset($vsolModels[$idx]) ? trim((string)$vsolModels[$idx], '" ') : null,
                        'name'             => isset($vsolNames[$idx]) ? trim((string)$vsolNames[$idx], '" ') : null,
                        'status'           => $isOnline ? 'online' : 'offline',
                    ];
                }

                if (!empty($onus)) {
                    return $onus;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus V-SOL 65000 SNMP error: " . $e->getMessage());
        }

        // --- Sumber 2: C-Data GPON MIB (.1.3.6.1.4.1.34592.1.5.1 / 34592.1.3.100) ---
        try {
            $cdSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.3') ?: []);
            if (!empty($cdSerials)) {
                $cdStatuses = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.8')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.8') ?: []);
                $cdRx = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.19.1.1.3')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.6.1.3') ?: []);
                $cdTx = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.19.1.1.4') ?: [];

                foreach ($cdSerials as $idx => $serialRaw) {
                    $parsed = $this->parsePortAndOnuIndex((string)$idx);
                    $sn = $this->cleanSnmpSerialOrMac($serialRaw);
                    if (empty($sn)) continue;

                    $rxVal = (float)($cdRx[$idx] ?? 0);
                    if ($rxVal > 100 || $rxVal < -100) $rxVal = round($rxVal / 100, 2);
                    if ($rxVal > 0) $rxVal = -abs($rxVal);

                    $txVal = (float)($cdTx[$idx] ?? 0);
                    if ($txVal > 100 || $txVal < -100) $txVal = round($txVal / 100, 2);

                    $st = (int)($cdStatuses[$idx] ?? 0);
                    $isOnline = in_array($st, [1, 2, 8], true) || ($rxVal >= -38.0 && $rxVal <= -6.0);

                    $onus[] = [
                        'pon_port'         => $parsed['port'],
                        'raw_port_idx'     => $parsed['raw_port'],
                        'onu_index'        => $parsed['onu'],
                        'serial_number'    => str_replace(':', '', $sn),
                        'mac_address'      => str_contains($sn, ':') ? $sn : null,
                        'rx_power_dbm'     => ($rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                        'tx_power_dbm'     => ($txVal >= -10.0 && $txVal <= 15.0) ? $txVal : null,
                        'snr_db'           => null,
                        'temperature'      => null,
                        'firmware_version' => null,
                        'model'            => null,
                        'name'             => null,
                        'status'           => $isOnline ? 'online' : 'offline',
                    ];
                }

                if (!empty($onus)) {
                    return $onus;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus C-Data 34592 SNMP error: " . $e->getMessage());
        }

        // --- Sumber 3: RicherLink GPON MIB (.1.3.6.1.4.1.37950) ---
        try {
            $rlSerials = $this->snmp->walk('.1.3.6.1.4.1.37950.1.1.5.12.1.15')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.37950.1.1.5.12.1.25') ?: []);
            if (!empty($rlSerials)) {
                $rlStatuses = $this->snmp->walk('.1.3.6.1.4.1.37950.1.1.5.12.1.2') ?: [];
                $rlRx       = $this->snmp->walk('.1.3.6.1.4.1.37950.1.1.5.12.2.1.4') ?: [];
                $rlTx       = $this->snmp->walk('.1.3.6.1.4.1.37950.1.1.5.12.2.1.3') ?: [];

                foreach ($rlSerials as $idx => $serialRaw) {
                    $parsed = $this->parsePortAndOnuIndex((string)$idx);
                    $sn = $this->cleanSnmpSerialOrMac($serialRaw);
                    if (empty($sn)) continue;

                    $rxVal = (float)($rlRx[$idx] ?? 0);
                    if ($rxVal > 100 || $rxVal < -100) $rxVal = round($rxVal / 100, 2);
                    if ($rxVal > 0) $rxVal = -abs($rxVal);

                    $txVal = (float)($rlTx[$idx] ?? 0);
                    if ($txVal > 100 || $txVal < -100) $txVal = round($txVal / 100, 2);

                    $st = (int)($rlStatuses[$idx] ?? 0);
                    $isOnline = ($st === 1 || ($rxVal >= -38.0 && $rxVal <= -6.0));

                    $onus[] = [
                        'pon_port'         => $parsed['port'],
                        'raw_port_idx'     => $parsed['raw_port'],
                        'onu_index'        => $parsed['onu'],
                        'serial_number'    => str_replace(':', '', $sn),
                        'mac_address'      => str_contains($sn, ':') ? $sn : null,
                        'rx_power_dbm'     => ($rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                        'tx_power_dbm'     => ($txVal >= -10.0 && $txVal <= 15.0) ? $txVal : null,
                        'snr_db'           => null,
                        'temperature'      => null,
                        'firmware_version' => null,
                        'model'            => null,
                        'name'             => null,
                        'status'           => $isOnline ? 'online' : 'offline',
                    ];
                }

                if (!empty($onus)) {
                    return $onus;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus RicherLink 37950 SNMP error: " . $e->getMessage());
        }

        // --- Sumber 4: HSGQ Modern OID (.1.3.6.1.4.1.50224.3.12) ---
        try {
            $rawSerials = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.15') ?: [];
            if (!empty($rawSerials)) {
                $rawStatuses = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.22') ?: [];
                $rawRx       = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.3.1.4') ?: [];
                $rawTx       = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.3.1.5') ?: [];
                $rawNames    = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.2') ?: [];
                $rawModels   = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.9') ?: [];
                $rawFws      = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.11') ?: [];
                $rawTemps    = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.3.1.7') ?: [];

                foreach ($rawSerials as $idx => $serialRaw) {
                    $parsed = $this->parsePortAndOnuIndex((string)$idx);
                    $sn = $this->cleanSnmpSerialOrMac($serialRaw);
                    if (empty($sn)) continue;

                    $rxVal = (float)($rawRx[$idx] ?? 0);
                    if ($rxVal > 100 || $rxVal < -100) $rxVal = round($rxVal / 100, 2);
                    if ($rxVal > 0) $rxVal = -abs($rxVal);

                    $txVal = (float)($rawTx[$idx] ?? 0);
                    if ($txVal > 100 || $txVal < -100) $txVal = round($txVal / 100, 2);

                    $st = (int)($rawStatuses[$idx] ?? 0);
                    $isOnline = ($st === 1 || $st === 2 || $st === 3 || ($rxVal >= -38.0 && $rxVal <= -6.0));

                    $onus[] = [
                        'pon_port'         => $parsed['port'],
                        'raw_port_idx'     => $parsed['raw_port'],
                        'onu_index'        => $parsed['onu'],
                        'serial_number'    => $sn,
                        'mac_address'      => str_contains($sn, ':') ? $sn : null,
                        'rx_power_dbm'     => ($rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                        'tx_power_dbm'     => ($txVal >= -10.0 && $txVal <= 15.0) ? $txVal : null,
                        'snr_db'           => null,
                        'temperature'      => isset($rawTemps[$idx]) ? (float)$rawTemps[$idx] : null,
                        'firmware_version' => isset($rawFws[$idx]) ? trim((string)$rawFws[$idx], '" ') : null,
                        'model'            => isset($rawModels[$idx]) ? trim((string)$rawModels[$idx], '" ') : null,
                        'name'             => isset($rawNames[$idx]) ? trim((string)$rawNames[$idx], '" ') : null,
                        'status'           => $isOnline ? 'online' : 'offline',
                    ];
                }

                if (!empty($onus)) {
                    return $onus;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus HSGQ 50224 SNMP error: " . $e->getMessage());
        }

        // --- Sumber 5: CTC EPON Standard MIB (.1.3.6.1.4.1.17409.2.3.4) untuk OLT HISFOCUS EPON ---
        try {
            $ctcMacs = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.1.1.2') ?: [];
            if (!empty($ctcMacs)) {
                $ctcStatuses = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.1.1.7') ?: [];
                $ctcRx       = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.4.1.4') ?: ($this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.4.1.3') ?: []);
                $ctcTx       = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.4.1.3') ?: ($this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.4.1.2') ?: []);

                foreach ($ctcMacs as $idx => $rawMac) {
                    $parsed = $this->parsePortAndOnuIndex((string)$idx);
                    $mac = $this->cleanSnmpSerialOrMac($rawMac);
                    if (empty($mac)) continue;

                    $st = (int)($ctcStatuses[$idx] ?? 0);
                    $rxVal = (float)($ctcRx[$idx] ?? 0);
                    if ($rxVal > 100 || $rxVal < -100) $rxVal = round($rxVal / 100, 2);
                    if ($rxVal > 0) $rxVal = -abs($rxVal);

                    $txVal = (float)($ctcTx[$idx] ?? 0);
                    if ($txVal > 100 || $txVal < -100) $txVal = round($txVal / 100, 2);

                    $isOnline = ($st === 1 || ($rxVal >= -38.0 && $rxVal <= -6.0));

                    $onus[] = [
                        'pon_port'         => $parsed['port'],
                        'raw_port_idx'     => $parsed['raw_port'],
                        'onu_index'        => $parsed['onu'],
                        'serial_number'    => str_replace(':', '', $mac),
                        'mac_address'      => $mac,
                        'rx_power_dbm'     => ($rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                        'tx_power_dbm'     => ($txVal >= -10.0 && $txVal <= 15.0) ? $txVal : null,
                        'snr_db'           => null,
                        'temperature'      => null,
                        'firmware_version' => null,
                        'model'            => null,
                        'name'             => null,
                        'status'           => $isOnline ? 'online' : 'offline',
                    ];
                }

                if (!empty($onus)) {
                    return $onus;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus CTC EPON SNMP error: " . $e->getMessage());
        }

        // --- Sumber 6: HSGQ Legacy MIB (.1.3.6.1.4.1.5875.901.10.3) ---
        try {
            $legSerials = $this->snmp->walk('.1.3.6.1.4.1.5875.901.10.3.1.1.1.1') ?: [];
            if (!empty($legSerials)) {
                $legStatuses = $this->snmp->walk('.1.3.6.1.4.1.5875.901.10.3.1.1.1.2') ?: [];
                $legRx       = $this->snmp->walk('.1.3.6.1.4.1.5875.901.10.3.1.1.1.7') ?: [];

                foreach ($legSerials as $idx => $raw) {
                    $parsed = $this->parsePortAndOnuIndex((string)$idx);
                    $sn = $this->cleanSnmpSerialOrMac($raw);
                    if (empty($sn)) continue;

                    $rxVal = (float)($legRx[$idx] ?? 0);
                    if ($rxVal > 100 || $rxVal < -100) $rxVal = round($rxVal / 100, 2);
                    if ($rxVal > 0) $rxVal = -abs($rxVal);

                    $st = (int)($legStatuses[$idx] ?? 0);
                    $isOnline = ($st === 1 || ($rxVal >= -38.0 && $rxVal <= -6.0));

                    $onus[] = [
                        'pon_port'         => $parsed['port'],
                        'raw_port_idx'     => $parsed['raw_port'],
                        'onu_index'        => $parsed['onu'],
                        'serial_number'    => $sn,
                        'mac_address'      => str_contains($sn, ':') ? $sn : null,
                        'rx_power_dbm'     => ($rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                        'tx_power_dbm'     => null,
                        'snr_db'           => null,
                        'temperature'      => null,
                        'firmware_version' => null,
                        'model'            => null,
                        'name'             => null,
                        'status'           => $isOnline ? 'online' : 'offline',
                    ];
                }

                if (!empty($onus)) {
                    return $onus;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus HSGQ 5875 SNMP error: " . $e->getMessage());
        }

        // --- Sumber 7: Standard Bridge FDB / MAC Table (.1.3.6.1.2.1.17.4.3.1.1) Fallback ---
        try {
            $fdbMacs = $this->snmp->walk('.1.3.6.1.2.1.17.4.3.1.1') ?: [];
            $fdbPorts = $this->snmp->walk('.1.3.6.1.2.1.17.4.3.1.2') ?: [];
            if (!empty($fdbMacs)) {
                $counter = 1;
                foreach ($fdbMacs as $idx => $rawMac) {
                    $mac = $this->cleanSnmpSerialOrMac($rawMac);
                    if (empty($mac)) continue;

                    $pIdx = (int)($fdbPorts[$idx] ?? 1);
                    $onus[] = [
                        'pon_port'         => $pIdx > 0 ? $pIdx : 1,
                        'raw_port_idx'     => $pIdx,
                        'onu_index'        => $counter++,
                        'serial_number'    => str_replace(':', '', $mac),
                        'mac_address'      => $mac,
                        'rx_power_dbm'     => null,
                        'tx_power_dbm'     => null,
                        'snr_db'           => null,
                        'temperature'      => null,
                        'firmware_version' => null,
                        'model'            => null,
                        'name'             => null,
                        'status'           => 'online',
                    ];
                }
            }
        } catch (\Throwable) {}

        return $onus;
    }

    /**
     * Inisialisasi koneksi CLI dengan auto-fallback kredensial umum admin/admin atau root/admin.
     */
    protected function initializeCli(): void
    {
        if ($this->cli !== null) {
            return;
        }

        $username = !empty($this->olt->username) ? $this->olt->username : 'admin';
        $password = !empty($this->olt->password) ? $this->olt->password : 'admin';
        $port = (int)($this->olt->cli_port ?? 0);
        if ($port <= 0) {
            $port = $this->cliMode === 'ssh' ? 22 : 23;
        }

        $this->cli = new TelnetSshClient();
        try {
            $this->cli->connect(
                $this->olt->ip_address,
                $username,
                $password,
                $this->cliMode,
                $port,
                $this->olt->enable_secret ?? '',
                4
            );
        } catch (\Throwable $e) {
            // Coba alternatif default root/admin jika kredensial admin gagal
            if ($username === 'admin') {
                try {
                    $this->cli = new TelnetSshClient();
                    $this->cli->connect(
                        $this->olt->ip_address,
                        'root',
                        'admin',
                        $this->cliMode,
                        $port,
                        $this->olt->enable_secret ?? '',
                        4
                    );
                    return;
                } catch (\Throwable) {}
            }
            throw $e;
        }
    }

    /**
     * Mengumpulkan data ONU via CLI Scraping Telnet/SSH.
     */
    protected function collectAllOnusFromCli(): array
    {
        $onus = [];
        try {
            $this->initializeCli();

            $isGbn = str_contains(strtolower($this->olt->model ?? ''), 'gp210') ||
                     str_contains(strtolower($this->olt->name ?? ''), 'gp210') ||
                     $this->systemOids['sysTemperature'] === '.1.3.6.1.4.1.8888.1.2.1.1.2.26.0';

            if ($isGbn) {
                // Untuk GP210 / GBN VRP OLT, jalankan perintah langsung yang cepat dan terarah
                $commands = [
                    'display rule-profile',
                    'display ont info 2/1 all',
                    'display ont info 2/2 all',
                    'display ont optical-info 2/1 all',
                    'display ont optical-info 2/2 all',
                ];
            } else {
                $commands = [
                    'show ont info all',
                    'show onu info all',
                    'show gpon ont info all',
                    'show ont optical-info all',
                    'show onu optical-info all',
                    'show epon onu-info',
                    'display ont info all',
                    'display ont optical-info all',
                ];
            }

            $infoOut = '';
            foreach ($commands as $cmd) {
                try {
                    $res = $this->cli->execute("{$cmd}\r\n", false);
                    $lower = strtolower((string)$res);
                    if (!empty($res) && !str_contains($lower, 'incomplete') && !str_contains($lower, 'invalid') && !str_contains($lower, 'unrecognized') && !str_contains($lower, 'error')) {
                        $infoOut .= "\n" . $res;
                    }
                } catch (\Throwable $e) {
                    Log::debug("CLI cmd {$cmd} error: " . $e->getMessage());
                }
            }

            // Fallback: Jika display rule-profile kosong atau belum berisi rule-profile sn-auth, jalankan display current-config
            if ($isGbn && (empty($infoOut) || !str_contains($infoOut, 'sn-auth'))) {
                try {
                    $res = $this->cli->execute("display current-config\r\n", false);
                    $lower = strtolower((string)$res);
                    if (!empty($res) && !str_contains($lower, 'unrecognized') && !str_contains($lower, 'error')) {
                        $infoOut .= "\n" . $res;
                    }
                } catch (\Throwable $e) {
                    Log::debug("CLI display current-config error: " . $e->getMessage());
                }
            }

            if (!empty($infoOut)) {
                $clean = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$infoOut);
                $snCounter = 1;

                // 1. Parsing format asli OLT GP210 / GBN dari rule-profile / current-config:
                // rule-profile 2/1/1 name AUTO_ONT_2/1/1
                // sn-auth string-hex HWUD-0F456BB3 line-profile 10
                if (preg_match_all('/rule-profile\s+(?:\d+\/)?(\d+)\/(\d+)(?:[^\r\n]*name\s+([^\r\n]+))?[\r\n\s]+sn-auth\s+string-hex\s+([A-Za-z0-9-]+)/i', $clean, $rules, PREG_SET_ORDER)) {
                    $seenSns = [];
                    foreach ($rules as $r) {
                        $p = (int)$r[1]; // 1 = Port 1 (2/1), 2 = Port 2 (2/2)
                        $onuId = (int)$r[2];
                        $name = !empty($r[3]) ? trim($r[3]) : ("ONT-{$p}-{$onuId}");
                        $rawSn = str_replace(['-', ':', ' '], '', trim($r[4]));
                        $sn = strtoupper($rawSn);
                        if (empty($sn) || isset($seenSns[$sn])) continue;
                        $seenSns[$sn] = true;

                        $onus[] = [
                            'pon_port'         => $p > 0 ? $p : 1,
                            'raw_port_idx'     => $p,
                            'onu_index'        => $onuId,
                            'serial_number'    => $sn,
                            'mac_address'      => null,
                            'rx_power_dbm'     => null,
                            'tx_power_dbm'     => null,
                            'snr_db'           => null,
                            'temperature'      => null,
                            'firmware_version' => null,
                            'model'            => null,
                            'name'             => $name,
                            'status'           => 'online',
                        ];
                    }
                }

                // 2. Parsing baris-per-baris untuk format tabel info / optical-info
                foreach (explode("\n", $clean) as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, '=') || str_starts_with($line, 'F/S') || str_starts_with($line, 'Slot') || str_starts_with($line, 'Port') || str_starts_with($line, 'show') || str_starts_with($line, 'display') || str_starts_with($line, 'Total:') || str_contains($line, '#')) {
                        continue;
                    }

                    // Cocokkan Serial Number GPON atau MAC Address EPON
                    if (preg_match('/([A-Z]{4}[0-9A-F]{8,12}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}|(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|\b[0-9A-Fa-f]{12}\b)/i', $line, $snMatch)) {
                        $rawSn = $snMatch[1];
                        $sn = $this->cleanSnmpSerialOrMac($rawSn);
                        if (empty($sn)) continue;

                        $cleanSn = str_replace([':', '-', ' '], '', $sn);
                        $isOnline = preg_match('/\b(online|working|operation|operational|up|ok|enable|active)\b/i', $line);

                        // Ekstrak redaman Rx jika ada (misal -21.50)
                        $rxVal = null;
                        if (preg_match('/(-[1-4]\d(?:\.\d+)?)\s*(?:dBm)?/i', $line, $rxMatch)) {
                            $rxVal = (float)$rxMatch[1];
                        }

                        // Ekstrak port number dari baris CLI
                        $detectedPort = 1;
                        if (preg_match('/(?:gpon|epon|pon|port)?\s*(?:0\/0\/|0\/|2\/)?(\d+)[\s:\/]+(\d+)/i', $line, $pm)) {
                            $detectedPort = (int)$pm[1];
                        } elseif (preg_match('/\/(\d+)\//', $line, $pm)) {
                            $detectedPort = (int)$pm[1];
                        }

                        // Jika ONU sudah ada dari rule-profile, update rx_power atau statusnya
                        $existingIdx = null;
                        foreach ($onus as $idx => $existing) {
                            if ($existing['serial_number'] === $cleanSn) {
                                $existingIdx = $idx;
                                break;
                            }
                        }

                        if ($existingIdx !== null) {
                            if ($rxVal !== null) {
                                $onus[$existingIdx]['rx_power_dbm'] = $rxVal;
                            }
                            if ($isOnline) {
                                $onus[$existingIdx]['status'] = 'online';
                            }
                        } else {
                            $onus[] = [
                                'pon_port'         => $detectedPort > 0 ? $detectedPort : 1,
                                'raw_port_idx'     => $detectedPort,
                                'onu_index'        => $snCounter++,
                                'serial_number'    => $cleanSn,
                                'mac_address'      => str_contains($sn, ':') ? $sn : null,
                                'rx_power_dbm'     => ($rxVal !== null && $rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                                'tx_power_dbm'     => null,
                                'snr_db'           => null,
                                'temperature'      => null,
                                'firmware_version' => null,
                                'model'            => null,
                                'name'             => null,
                                'status'           => $isOnline ? 'online' : 'offline',
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus CLI scraping error: " . $e->getMessage());
        }

        return $onus;
    }

    /**
     * Memfilter daftar ONU untuk port PON yang diminta.
     */
    protected function filterOnusByPort(array $onus, int $targetPort): array
    {
        // Normalisasi untuk GPON pizza box OLT (misal gpon0/2/1 dengan IfIndex 5 / port 17 -> Port 1, gpon0/2/2 dengan IfIndex 6 / port 18 -> Port 2)
        $altTarget = match ($targetPort) {
            5, 17 => 1,
            6, 18 => 2,
            1 => 5,
            2 => 6,
            default => $targetPort,
        };

        $filtered = [];
        foreach ($onus as $item) {
            $p = (int)($item['pon_port'] ?? 1);
            $rawP = (int)($item['raw_port_idx'] ?? $p);

            // Pencocokan fleksibel:
            // 1. Port sama persis atau cocok dengan target alternatif (IfIndex 5 <-> Port 1, IfIndex 6 <-> Port 2)
            if ($p === $targetPort || $rawP === $targetPort || $p === $altTarget || $rawP === $altTarget) {
                $filtered[] = $item;
                continue;
            }

            // 2. Jika targetPort bernilai besar (> 1000000), ekstrak port normalisasinya
            if ($targetPort > 1000000) {
                $normTarget = ($targetPort >> 8) & 0xFF ?: (($targetPort >> 16) & 0xFF);
                if ($normTarget === $p) {
                    $filtered[] = $item;
                    continue;
                }
            }

            // 3. Jika raw_port_idx bernilai besar (> 1000000) dan targetPort 1..16
            if ($rawP > 1000000) {
                $normRaw = ($rawP >> 8) & 0xFF ?: (($rawP >> 16) & 0xFF);
                if ($normRaw === $targetPort) {
                    $filtered[] = $item;
                    continue;
                }
            }
        }

        return $filtered;
    }

    /**
     * Mem-parsing port number dan ONU ID dari index OID SNMP.
     * Mendukung dotted OID (V-SOL / C-Data: .1.5) dan 32-bit integer (HSGQ: 16777473).
     */
    protected function parsePortAndOnuIndex(string $rawOid): array
    {
        $cleanOid = str_replace(['iso', 'SNMPv2-SMI::enterprises.'], ['.1', '.1.3.6.1.4.1.'], trim($rawOid));
        $parts = explode('.', trim($cleanOid, '. '));

        if (count($parts) >= 2) {
            $onu = (int)array_pop($parts);
            $port = (int)array_pop($parts);
            return [
                'port'     => $port > 0 ? $port : 1,
                'raw_port' => $port,
                'onu'      => $onu > 0 ? $onu : 1,
            ];
        }

        $num = (int)$parts[0];
        if ($num > 1000000) {
            // HSGQ 32-bit format: Byte 1 = Port, Byte 0 = ONU ID
            $p1 = ($num >> 8) & 0xFF;
            $p2 = ($num >> 16) & 0xFF;
            $port = $p1 > 0 ? $p1 : ($p2 > 0 ? $p2 : 1);
            $onu = $num & 0xFF;
            return ['port' => $port, 'raw_port' => $num, 'onu' => $onu > 0 ? $onu : 1];
        }

        return ['port' => 1, 'raw_port' => 1, 'onu' => $num > 0 ? $num : 1];
    }

    /**
     * Membersihkan dan menormalisasi string Serial Number atau MAC Address dari respon SNMP.
     */
    protected function cleanSnmpSerialOrMac(mixed $val): ?string
    {
        if ($val === null || $val === false) return null;
        $str = trim((string)$val);
        $str = preg_replace('/^(STRING|Hex-STRING|OctetString|Hex)\s*:?\s*/i', '', $str);
        $str = trim($str, '" ');

        // Kasus 1: Spaced hex bytes: "5A 54 45 47 C8 5E 2D 31"
        if (preg_match('/^[0-9A-Fa-f]{2}(?:\s+[0-9A-Fa-f]{2})+$/', $str)) {
            $hexClean = str_replace(' ', '', $str);

            // Decode ITU-T GPON: 4 bytes pertama adalah Vendor ID (ZTEG, HWTC, HSGQ, VSOL, dsb)
            if (strlen($hexClean) === 16) {
                $vendorPrefix = @hex2bin(substr($hexClean, 0, 8));
                if ($vendorPrefix && preg_match('/^[A-Za-z0-9]{4}$/', $vendorPrefix)) {
                    return strtoupper($vendorPrefix) . strtoupper(substr($hexClean, 8));
                }
            }

            // Decode MAC address 6 bytes (12 hex digits)
            if (strlen($hexClean) === 12) {
                return strtoupper(implode(':', str_split($hexClean, 2)));
            }

            $bin = @hex2bin($hexClean);
            if ($bin && preg_match('/^[A-Za-z0-9]{4,16}$/', $bin)) {
                return strtoupper($bin);
            }

            return strtoupper($hexClean);
        }

        // Kasus 2: 12-karakter MAC address
        $cleanMac = str_replace([':', '-', '.'], '', $str);
        if (strlen($cleanMac) === 12 && ctype_xdigit($cleanMac)) {
            return strtoupper(implode(':', str_split($cleanMac, 2)));
        }

        // Kasus 3: Standar string serial ITU-T
        if (preg_match('/^[A-Za-z0-9_-]{4,24}$/', $str)) {
            return strtoupper($str);
        }

        return null;
    }

    public function discoverUnregisteredOnus(): array
    {
        try {
            $this->initializeCli();
            $output = $this->cli->execute("display ont autofind all\r\n", false);
            if (empty($output) || str_contains(strtolower($output), 'incomplete') || str_contains(strtolower($output), 'invalid') || str_contains(strtolower($output), 'unrecognized')) {
                $output = $this->cli->execute("show ont autofind all\r\n", false);
            }
            if (empty($output) || str_contains(strtolower($output), 'incomplete') || str_contains(strtolower($output), 'invalid')) {
                $output = $this->cli->execute("show onu autofind all\r\n", false);
            }
            if (empty($output) || str_contains(strtolower($output), 'invalid')) {
                $output = $this->cli->execute("show gpon auth-fail\r\n", false);
            }
            $onus = [];
            foreach (explode("\n", (string)$output) as $line) {
                if (preg_match('/(\d+)[\/:\s]+(\d+)\s+([A-Z0-9]{12,})/i', $line, $m)) {
                    $onus[] = [
                        'pon_port'      => (int)$m[2],
                        'onu_id'        => 0,
                        'serial_number' => strtoupper($m[3]),
                        'vendor_oui'    => substr($m[3], 0, 4),
                    ];
                }
            }
            return $onus;
        } catch (\Throwable) {
            return [];
        }
    }

    public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool
    {
        try {
            $this->initializeCli();
            $onuId = $onu->getKey();
            $this->cli->execute("configure terminal");
            $this->cli->execute("interface pon $ponPort");
            $this->cli->execute("ont add $onuId sn-auth $serialNumber omci desc \"" . ($onu->name ?? 'CUST-' . $onuId) . "\"");
            $this->cli->execute("exit");
            $this->cli->execute("write");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function setOnuAdminStatus(Onu $onu, string $status): bool
    {
        try {
            $ponPort = $onu->pon_port ?? 0;
            $onuId = $onu->getKey();
            $this->initializeCli();
            $this->cli->execute("configure terminal");
            $this->cli->execute("interface pon $ponPort");
            $cmd = match ($status) {
                'enable'  => "ont modify $onuId admin-state up",
                'disable' => "ont modify $onuId admin-state down",
                'reset'   => "ont reset $onuId",
                default   => "ont modify $onuId admin-state up"
            };
            $this->cli->execute($cmd);
            $this->cli->execute("exit");
            $this->cli->execute("write");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool
    {
        return true;
    }
}
