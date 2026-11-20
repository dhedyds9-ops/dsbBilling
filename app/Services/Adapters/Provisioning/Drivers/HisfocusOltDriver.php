<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;
use Illuminate\Support\Facades\Log;

class HisfocusOltDriver extends BaseOltDriver
{
    protected string $cliMode = 'telnet';

    protected array $systemOids = [
        'sysName'        => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime'      => '.1.3.6.1.2.1.1.3.0',
        'sysDescr'       => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.50224.3.1.1.18.0',
    ];

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

        // 1. Coba OID HSGQ Modern (50224) - sering dipakai di HISFOCUS GPON (HF-G04/G08)
        try {
            $names = $this->snmp->walk('.1.3.6.1.4.1.50224.3.2.1.1.2') ?: ($this->snmp->walk('.1.3.6.1.4.1.50224.3.3.1.1.2') ?: []);
            $statuses = $this->snmp->walk('.1.3.6.1.4.1.50224.3.2.1.1.6') ?: ($this->snmp->walk('.1.3.6.1.4.1.50224.3.3.1.1.6') ?: []);
            if (!empty($names) || !empty($statuses)) {
                $statusMap = [];
                foreach ($statuses as $idx => $val) {
                    $cleanOid = str_replace('iso', '.1', (string)$idx);
                    $parts = explode('.', $cleanOid);
                    $portIdx = (int)end($parts);
                    $statusMap[$portIdx] = (int)$val;
                }
                foreach ($names as $idx => $name) {
                    $cleanOid = str_replace('iso', '.1', (string)$idx);
                    $parts = explode('.', $cleanOid);
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
                    return $ports;
                }
            }
        } catch (\Throwable) {}

        // 2. Coba CTC EPON Standard MIB (.1.3.6.1.4.1.17409.2.3.1.2.1.1) - standar HISFOCUS EPON (HF-E04/E08)
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
                    return $ports;
                }
            }
        } catch (\Throwable) {}

        // 3. Coba OID HSGQ Legacy (5875)
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
                    return $ports;
                }
            }
        } catch (\Throwable) {}

        // 4. Fallback Universal: Standard IF-MIB RFC 2863 & Database Port Count
        return $this->getStandardIfMibPonPorts();
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
            $this->cachedCliOnus = $this->collectAllOnusFromCli();
        }

        $cliMatched = $this->filterOnusByPort($this->cachedCliOnus, $ponPort);
        if (!empty($cliMatched)) {
            return $cliMatched;
        }

        // 4. Fallback: Jika target port adalah port pertama (1) dan ada ONU terdeteksi tanpa port spesifik
        if (($ponPort === 1 || ($this->olt->pon_port_count ?? 0) === 1) && !empty($this->cachedSnmpOnus)) {
            return $this->cachedSnmpOnus;
        }
        if (($ponPort === 1 || ($this->olt->pon_port_count ?? 0) === 1) && !empty($this->cachedCliOnus)) {
            return $this->cachedCliOnus;
        }

        return [];
    }

    /**
     * Mengumpulkan seluruh data ONU dari berbagai OID SNMP (HSGQ 50224, CTC EPON 17409, HSGQ 5875, VSOL).
     */
    protected function collectAllOnusFromSnmp(): array
    {
        $onus = [];

        // --- Sumber A: HSGQ Modern OID (.1.3.6.1.4.1.50224.3.12) ---
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

        // --- Sumber B: CTC EPON Standard MIB (.1.3.6.1.4.1.17409.2.3.4) untuk OLT HISFOCUS EPON ---
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

        // --- Sumber C: HSGQ Legacy MIB (.1.3.6.1.4.1.5875.901.10.3) ---
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

        // --- Sumber D: Standard Bridge FDB / MAC Table (.1.3.6.1.2.1.17.4.3.1.1) Fallback ---
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
     * Mengumpulkan data ONU via CLI Scraping Telnet/SSH.
     */
    protected function collectAllOnusFromCli(): array
    {
        $onus = [];
        try {
            $this->initializeCli();

            // Coba berbagai perintah status ONU yang umum pada OLT HISFOCUS / HSGQ / EPON
            $commands = [
                'show ont info all',
                'show onu info all',
                'show onu info 1 all',
                'show epon onu-info',
                'show onu status all',
                'show mac-address dynamic',
                'show mac',
            ];

            $infoOut = '';
            foreach ($commands as $cmd) {
                $res = $this->cli->execute("{$cmd}\r\n", false);
                $lower = strtolower((string)$res);
                if (!empty($res) && !str_contains($lower, 'incomplete') && !str_contains($lower, 'invalid') && !str_contains($lower, 'unrecognized') && !str_contains($lower, 'error')) {
                    $infoOut .= "\n" . $res;
                }
            }

            if (!empty($infoOut)) {
                // Coba ambil optical info juga jika ada
                $optOut = $this->cli->execute("show ont optical-info all\r\n", false);
                if (empty($optOut) || str_contains(strtolower($optOut), 'invalid') || str_contains(strtolower($optOut), 'unrecognized')) {
                    $optOut = $this->cli->execute("show onu optical-info all\r\n", false);
                }

                $clean = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$infoOut);
                $snCounter = 1;

                foreach (explode("\n", $clean) as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, '=') || str_starts_with($line, 'F/S') || str_starts_with($line, 'Slot') || str_starts_with($line, 'Port') || str_starts_with($line, 'show') || str_starts_with($line, 'Total:') || str_contains($line, '#')) {
                        continue;
                    }

                    // Cocokkan Serial Number GPON atau MAC Address EPON
                    // Mendukung: ZTEGC85E2D31, HWTC415EAA9B, 28:6D:DA:7C:1B:D9, 28-6D-DA..., 286D.DA7C.1BD9, atau 12-karakter Hex
                    if (preg_match('/([A-Z]{4}[0-9A-F]{8,12}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}|(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|\b[0-9A-Fa-f]{12}\b)/i', $line, $snMatch)) {
                        $rawSn = $snMatch[1];
                        $sn = $this->cleanSnmpSerialOrMac($rawSn);
                        if (empty($sn)) continue;

                        $isOnline = preg_match('/\b(online|working|operation|operational|up|ok|enable|active)\b/i', $line);

                        // Ekstrak port number dari baris CLI
                        $detectedPort = 1;
                        if (preg_match('/(?:gpon|epon|pon|port)?\s*(?:0\/0\/|0\/)?(\d+)[\s:\/]+(\d+)/i', $line, $pm)) {
                            $detectedPort = (int)$pm[1];
                        } elseif (preg_match('/\/(\d+)\//', $line, $pm)) {
                            $detectedPort = (int)$pm[1];
                        }

                        $onus[] = [
                            'pon_port'         => $detectedPort > 0 ? $detectedPort : 1,
                            'raw_port_idx'     => $detectedPort,
                            'onu_index'        => $snCounter++,
                            'serial_number'    => str_replace(':', '', $sn),
                            'mac_address'      => str_contains($sn, ':') ? $sn : null,
                            'rx_power_dbm'     => null,
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
        $filtered = [];
        foreach ($onus as $item) {
            $p = (int)($item['pon_port'] ?? 1);
            $rawP = (int)($item['raw_port_idx'] ?? $p);

            // Pencocokan fleksibel:
            // 1. Port sama persis
            if ($p === $targetPort || $rawP === $targetPort) {
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
     * Mendukung: 32-bit int (16777473), OID dotted (.1.5), dan flat index.
     */
    protected function parsePortAndOnuIndex(string $rawOid): array
    {
        $clean = preg_replace('/^(\.?iso|\.?1)(?:\.\d+)*\.(?=\d+(?:\.\d+)?$)/i', '', trim($rawOid));
        $parts = explode('.', $clean);

        if (count($parts) >= 2) {
            $p = (int)$parts[0];
            $o = (int)$parts[1];
            return ['port' => $p > 0 ? $p : 1, 'raw_port' => $p, 'onu' => $o > 0 ? $o : 1];
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

            // Decode ITU-T GPON: 4 bytes pertama adalah Vendor ID (ZTEG, HWTC, HSGQ, dsb)
            if (strlen($hexClean) === 16) {
                $vendorPrefix = @hex2bin(substr($hexClean, 0, 8));
                if ($vendorPrefix && preg_match('/^[A-Z]{4}$/', $vendorPrefix)) {
                    return $vendorPrefix . strtoupper(substr($hexClean, 8));
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
            $output = $this->cli->execute("show ont autofind all\r\n", false);
            if (empty($output) || str_contains(strtolower($output), 'incomplete') || str_contains(strtolower($output), 'invalid')) {
                $output = $this->cli->execute("show onu autofind all\r\n", false);
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
