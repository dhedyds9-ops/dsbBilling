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

    protected ?array $cachedOnuData = null;

    public function getPonPortsStatus(): array
    {
        $ports = [];

        // 1. Coba OID HSGQ Modern (50224) yang paling sering digunakan pada OLT HISFOCUS HF-G04/G08
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

        // 2. Coba CTC EPON Standard MIB (.1.3.6.1.4.1.17409.2.3.1.2.1.1) untuk HISFOCUS seri EPON (HF-E04/E08)
        try {
            $ctcPorts = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.1.2.1.1');
            if (!empty($ctcPorts)) {
                foreach ($ctcPorts as $idx => $val) {
                    $portIdx = (int)preg_replace('/^.*?(\d+)$/', '$1', (string)$idx);
                    $ports[] = [
                        'port_index' => $portIdx > 0 ? $portIdx : (count($ports) + 1),
                        'port_name'  => "EPON 0/0/" . ($portIdx > 0 ? $portIdx : (count($ports) + 1)),
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

    public function getOnuRxPower(int $ponPort): array
    {
        $results = [];

        // 1. Coba SNMP HSGQ 50224
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
                    $cleanOid = str_replace('iso', '.1', (string)$idx);
                    $parts = explode('.', $cleanOid);
                    $oIdx = (int)end($parts);
                    $pNum = count($parts) >= 2 ? (int)$parts[count($parts) - 2] : $ponPort;

                    if ($ponPort > 0 && $pNum > 0 && $pNum !== $ponPort && count($rawSerials) > 1) {
                        // Port spesifik
                        continue;
                    }

                    $sn = trim((string)$serialRaw);
                    $sn = preg_replace('/^(STRING|Hex-STRING|OctetString)\s*:?\s*/i', '', $sn);
                    $sn = trim($sn, '" ');

                    $rxVal = (float)($rawRx[$idx] ?? 0);
                    if ($rxVal > 100 || $rxVal < -100) {
                        $rxVal = round($rxVal / 100, 2);
                    }
                    if ($rxVal > 0) {
                        $rxVal = -abs($rxVal);
                    }

                    $txVal = (float)($rawTx[$idx] ?? 0);
                    if ($txVal > 100 || $txVal < -100) {
                        $txVal = round($txVal / 100, 2);
                    }

                    $st = (int)($rawStatuses[$idx] ?? 0);

                    $results[] = [
                        'pon_port'         => $ponPort,
                        'onu_index'        => $oIdx,
                        'serial_number'    => strtoupper($sn),
                        'mac_address'      => null,
                        'rx_power_dbm'     => ($rxVal >= -45.0 && $rxVal <= -5.0) ? $rxVal : null,
                        'tx_power_dbm'     => ($txVal >= -10.0 && $txVal <= 15.0) ? $txVal : null,
                        'snr_db'           => null,
                        'temperature'      => isset($rawTemps[$idx]) ? (float)$rawTemps[$idx] : null,
                        'firmware_version' => isset($rawFws[$idx]) ? trim((string)$rawFws[$idx], '" ') : null,
                        'model'            => isset($rawModels[$idx]) ? trim((string)$rawModels[$idx], '" ') : null,
                        'status'           => ($st === 1 || $st === 2 || ($rxVal >= -38.0 && $rxVal <= -6.0)) ? 'online' : 'offline',
                    ];
                }

                if (!empty($results)) {
                    return $results;
                }
            }
        } catch (\Throwable) {}

        // 2. Coba CLI Scraping (Fast & Universal untuk HISFOCUS)
        try {
            $this->initializeCli();
            $infoOut = $this->cli->execute("show ont info all\r\n", false);
            if (empty($infoOut) || str_contains(strtolower($infoOut), 'incomplete') || str_contains(strtolower($infoOut), 'invalid') || str_contains(strtolower($infoOut), 'unrecognized')) {
                $infoOut = $this->cli->execute("show onu info all\r\n", false);
            }
            if (empty($infoOut) || str_contains(strtolower($infoOut), 'incomplete') || str_contains(strtolower($infoOut), 'invalid') || str_contains(strtolower($infoOut), 'unrecognized')) {
                $infoOut = $this->cli->execute("show ont info $ponPort all\r\n", false);
            }

            if (!empty($infoOut)) {
                $clean = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$infoOut);
                $optOut = $this->cli->execute("show ont optical-info all\r\n", false);
                if (empty($optOut) || str_contains(strtolower($optOut), 'incomplete') || str_contains(strtolower($optOut), 'invalid')) {
                    $optOut = $this->cli->execute("show onu optical-info all\r\n", false);
                }

                foreach (explode("\n", $clean) as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, 'F/S') || str_starts_with($line, 'Slot') || str_starts_with($line, 'show')) {
                        continue;
                    }
                    if (preg_match('/([A-Z0-9]{4}[A-Z0-9]{8,12}|(?:[A-F0-9]{2}[:-]){5}[A-F0-9]{2})/i', $line, $snMatch)) {
                        $sn = strtoupper($snMatch[1]);
                        $isOnline = preg_match('/\b(online|working|operation|operational|up|ok)\b/i', $line);
                        $results[] = [
                            'pon_port'         => $ponPort,
                            'onu_index'        => count($results) + 1,
                            'serial_number'    => $sn,
                            'mac_address'      => (str_contains($sn, ':') || str_contains($sn, '-')) ? str_replace('-', ':', $sn) : null,
                            'rx_power_dbm'     => null,
                            'tx_power_dbm'     => null,
                            'snr_db'           => null,
                            'temperature'      => null,
                            'firmware_version' => null,
                            'model'            => null,
                            'status'           => $isOnline ? 'online' : 'offline',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Hisfocus CLI scraping exception: " . $e->getMessage());
        }

        return $results;
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
