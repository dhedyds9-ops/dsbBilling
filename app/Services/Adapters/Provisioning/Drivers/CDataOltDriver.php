<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;

/**
 * Driver OLT C-Data (CD6000 / CD8000 / CD5508 / CD5000 / FD1600 series)
 * Enterprise OID: .1.3.6.1.4.1.51810 and .1.3.6.1.4.1.34592
 */
class CDataOltDriver extends BaseOltDriver
{
    protected string $cliMode = 'telnet';

    protected array $systemOids = [
        'sysName'        => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime'      => '.1.3.6.1.2.1.1.3.0',
        'sysDescr'       => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.51810.1.1.5.1.5.1.1',
    ];

    protected string $ponPortStatusOid = '.1.3.6.1.4.1.51810.1.1.2.1.1.2';
    protected string $onuInfoOid       = '.1.3.6.1.4.1.51810.1.3.1.1';
    
    protected ?array $macsCache = null;

    protected string $onuTxOid         = '.1.3.6.1.4.1.51810.1.3.1.1';
    protected string $onuTempOid       = '.1.3.6.1.4.1.51810.1.3.1.1';
    protected string $onuVoltOid       = '.1.3.6.1.4.1.51810.1.3.1.1';
    protected string $onuMacOid        = '.1.3.6.1.4.1.51810.1.3.1.1';
    protected string $onuFwOid         = '.1.3.6.1.4.1.51810.1.3.1.1';

    protected ?bool $isNewBranch = null;
    protected ?array $cachedOnuStatuses = null;
    protected ?array $cachedCliParsedOnus = null;

    public function __construct(Olt $olt)
    {
        parent::__construct($olt);

        if ($this->checkIsNewBranch()) {
            $this->systemOids['sysTemperature'] = '.1.3.6.1.4.1.34592.1.3.100.1.8.6.0';
        }
    }

    protected function checkIsNewBranch(): bool
    {
        if ($this->isNewBranch !== null) {
            return $this->isNewBranch;
        }

        $testOid = '.1.3.6.1.4.1.34592.1.3.100.1.8.1.0';
        $res = @$this->snmp->get($testOid);
        $this->isNewBranch = ($res !== false && $res !== '');
        return $this->isNewBranch;
    }

    public function getPonPortsStatus(): array
    {
        if ($this->checkIsNewBranch()) {
            $statuses = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.4');
            $ports    = [];
            if ($statuses) {
                foreach ($statuses as $idx => $status) {
                    $cleanOid = str_replace('iso', '.1', $idx);
                    $parts = explode('.', $cleanOid);
                    $realPortIdx = (int)end($parts);
                    
                    if ($realPortIdx >= 1310721 && $realPortIdx <= 1310728) {
                        $portNum = $realPortIdx - 1310720;
                        $ports[] = [
                            'port_index' => $realPortIdx,
                            'port_name'  => "GPON 0/0/$portNum",
                            'status'     => match ((int)$status) {
                                1 => 'up',
                                2 => 'down',
                                default => 'unknown'
                            },
                        ];
                    } elseif ($realPortIdx > 1000000) {
                        $slot = ($realPortIdx >> 24) & 0xFF;
                        if ($slot === 0) {
                            $slot = ($realPortIdx >> 16) & 0xFF;
                        }
                        if ($slot === 0) {
                            $slot = 1;
                        }
                        $port = $realPortIdx & 0xFF;
                        if ($port === 0) {
                            $port = ($realPortIdx >> 8) & 0xFF;
                        }
                        if ($port === 0) {
                            $port = 1;
                        }
                        $ports[] = [
                            'port_index' => $realPortIdx,
                            'port_name'  => "GPON 0/$slot/$port",
                            'status'     => match ((int)$status) {
                                1, 101, 100 => 'up',
                                2, 102      => 'down',
                                default     => 'unknown'
                            },
                        ];
                    }
                }
            }
            return $ports;
        }

        $statuses = $this->snmp->walk($this->ponPortStatusOid);
        $ports    = [];
        foreach ($statuses as $idx => $status) {
            $ports[] = [
                'port_index' => $idx,
                'port_name'  => "GPON 0/0/$idx",
                'status'     => match ((int)$status) {
                    1, 101, 100 => 'up',
                    2, 102      => 'down',
                    default     => 'unknown'
                },
            ];
        }
        return $ports;
    }

    public function getOnuRxPower(int $ponPort): array
    {
        if ($this->checkIsNewBranch()) {
            // Mapping ponPort to portNum (1-based, e.g. 1 to 8)
            if ($ponPort >= 1310721 && $ponPort <= 1310728) {
                $portNum = $ponPort - 1310720;
                $slot = 0;
            } elseif ($ponPort > 1000000) {
                $portNum = $ponPort & 0xFF;
                if ($portNum === 0) {
                    $portNum = ($ponPort >> 8) & 0xFF;
                }
                $slot = ($ponPort >> 16) & 0xFF;
                if ($slot > 16) {
                    $slot = 0;
                }
            } else {
                $portNum = $ponPort;
                $slot = 0;
            }
            if ($portNum <= 0) {
                $portNum = 1;
            }

            if ($this->cachedCliParsedOnus === null) {
                $this->cachedCliParsedOnus = [];
                try {
                    $this->initializeCli();

                    $isBad = function ($s) {
                        $l = strtolower((string)$s);
                        return empty(trim($s)) || str_contains($l, 'incomplete') || str_contains($l, 'invalid') || str_contains($l, 'unknown') || str_contains($l, 'error') || str_contains($l, 'unrecognized');
                    };

                    // Coba ambil ont info all dan optical-info all
                    $infoOut = $this->cli->execute("show ont info all");
                    if ($isBad($infoOut)) {
                        $this->cli->execute('config');
                        $infoOut = $this->cli->execute("show ont info all");
                    }
                    if ($isBad($infoOut)) {
                        $infoOut = $this->cli->execute("show onu info all");
                    }

                    $optOut = $this->cli->execute("show ont optical-info all");
                    if ($isBad($optOut)) {
                        $optOut = $this->cli->execute("show onu optical-info all");
                    }

                    \Illuminate\Support\Facades\Log::info("CData CLI infoOut: " . trim(preg_replace('/\s+/', ' ', substr((string)$infoOut, 0, 500))));
                    \Illuminate\Support\Facades\Log::info("CData CLI optOut: " . trim(preg_replace('/\s+/', ' ', substr((string)$optOut, 0, 500))));

                    // Parse show ont info lines
                    $cleanInfo = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$infoOut);
                    foreach (explode("\n", $cleanInfo) as $line) {
                        $line = trim($line);
                        if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, 'Slot') || str_starts_with($line, 'Port') || str_starts_with($line, 'ONT') || str_starts_with($line, 'No')) {
                            continue;
                        }

                        // Cari Serial Number (4 huruf vendor + 8-12 digit hex/alfanumerik, atau MAC address)
                        if (preg_match('/([A-Z0-9]{4}[A-Z0-9]{8,12}|(?:[A-F0-9]{2}[:-]){5}[A-F0-9]{2})/i', $line, $snMatch)) {
                            $sn = strtoupper($snMatch[1]);
                            $pos = strpos($line, $snMatch[1]);
                            $before = trim(substr($line, 0, $pos));
                            $after = trim(substr($line, $pos + strlen($snMatch[1])));

                            $runState = preg_match('/\b(online|active|up)\b/i', $after) ? 'online' : 'offline';

                            // Parse port dan onuId dari bagian sebelum SN
                            // Mendukung: 0/0/1 1, 0/1 1, GPON0/0/1:1, 1:1, 1 1
                            $lPort = null;
                            $lOnuId = null;

                            if (preg_match('/(?:GPON|EPON)?(?:\d+\/)*(\d+)[:\s]+(\d+)/i', $before, $m)) {
                                $lPort = (int)$m[1];
                                $lOnuId = (int)$m[2];
                            } elseif (preg_match_all('/\d+/', $before, $m)) {
                                $nums = $m[0];
                                if (count($nums) >= 2) {
                                    $lOnuId = (int)array_pop($nums);
                                    $lPort = (int)array_pop($nums);
                                } elseif (count($nums) === 1) {
                                    $lOnuId = (int)$nums[0];
                                    $lPort = 1;
                                }
                            }

                            if ($lPort !== null && $lOnuId !== null) {
                                $this->cachedCliParsedOnus[$lPort][$lOnuId] = [
                                    'onu_index'        => $lOnuId,
                                    'serial_number'    => $sn,
                                    'mac_address'      => (str_contains($sn, ':') || str_contains($sn, '-')) ? str_replace('-', ':', $sn) : null,
                                    'rx_power_dbm'     => null,
                                    'tx_power_dbm'     => null,
                                    'snr_db'           => null,
                                    'temperature'      => null,
                                    'firmware_version' => null,
                                    'model'            => null,
                                    'status'           => $runState,
                                ];
                            }
                        }
                    }

                    // Parse show ont optical-info lines
                    $cleanOpt = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$optOut);
                    foreach (explode("\n", $cleanOpt) as $line) {
                        $line = trim($line);
                        if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, 'Slot') || str_starts_with($line, 'Port') || str_starts_with($line, 'ONT') || str_starts_with($line, 'ID')) {
                            continue;
                        }

                        $cols = preg_split('/\s+/', $line);
                        $lPort = null;
                        $lOnuId = null;
                        $rx = null;
                        $tx = null;
                        $temp = null;

                        if (count($cols) >= 4 && str_contains($cols[0], '/')) {
                            $parts = explode('/', $cols[0]);
                            $lPort = (int)end($parts);
                            $lOnuId = (int)$cols[1];
                            $rx = $cols[2];
                            $tx = $cols[3];
                            $temp = $cols[5] ?? null;
                        } elseif (count($cols) >= 3 && is_numeric($cols[0])) {
                            $lOnuId = (int)$cols[0];
                            $rx = $cols[1];
                            $tx = $cols[2];
                            $temp = $cols[4] ?? ($cols[3] ?? null);
                        }

                        if ($lOnuId !== null) {
                            foreach ($this->cachedCliParsedOnus as $p => &$onus) {
                                if (($lPort === null || $p === $lPort) && isset($onus[$lOnuId])) {
                                    if (is_numeric($rx)) $onus[$lOnuId]['rx_power_dbm'] = (float)$rx;
                                    if (is_numeric($tx)) $onus[$lOnuId]['tx_power_dbm'] = (float)$tx;
                                    if (is_numeric($temp)) $onus[$lOnuId]['temperature'] = (float)$temp;
                                }
                            }
                            unset($onus);
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning("CData OLT Telnet CLI sync failed: " . $e->getMessage() . ". Falling back to SNMP.");
                }
            }

            if (!empty($this->cachedCliParsedOnus[$portNum])) {
                $res = [];
                foreach ($this->cachedCliParsedOnus[$portNum] as $item) {
                    $item['pon_port'] = $ponPort;
                    $res[] = $item;
                }
                return $res;
            }

            // SNMP Fallback
            if ($this->cachedOnuStatuses === null) {
                $this->cachedOnuStatuses = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.13') ?: [];
            }
            $statuses = $this->cachedOnuStatuses;

            $dbOnus = Onu::where('olt_id', $this->olt->id)
                ->where('pon_port', $ponPort)
                ->get()
                ->keyBy('onu_id_on_olt');

            $baseOld = $this->onuInfoOid . '.' . $ponPort;
            $rxRaw   = $this->snmp->walk($baseOld . '.6') ?: [];
            $txRaw   = $this->snmp->walk($baseOld . '.7') ?: [];
            if (empty($txRaw)) $txRaw = $this->snmp->walk($baseOld . '.5') ?: [];
            $tempRaw = $this->snmp->walk($baseOld . '.8') ?: [];

            // Coba temukan ONU dari SNMP walk jika DB masih kosong
            $snmpSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.2')
                ?: ($this->snmp->walk($this->onuInfoOid . '.' . $ponPort . '.2') ?: []));

            $results = [];

            if ($dbOnus->isEmpty() && !empty($snmpSerials)) {
                foreach ($snmpSerials as $oidKey => $rawSn) {
                    $cleanOid = str_replace('iso', '.1', $oidKey);
                    $parts = explode('.', $cleanOid);
                    $endIdx = (int)end($parts);
                    $snmpP = (int)floor($endIdx / 256) + 1;
                    $snmpOId = $endIdx % 256;
                    if ($snmpP === $portNum || count($snmpSerials) <= 16) {
                        $cleanSn = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', (string)$rawSn)));
                        if (strlen($cleanSn) >= 8) {
                            $isOnline = isset($statuses[$oidKey]) && (int)$statuses[$oidKey] === 1;
                            $results[] = [
                                'pon_port'         => $ponPort,
                                'onu_index'        => $snmpOId > 0 ? $snmpOId : 1,
                                'serial_number'    => $cleanSn,
                                'mac_address'      => null,
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
            }

            foreach ($dbOnus as $onuId => $dbOnu) {
                $rxVal = null;
                $rxRawVal = (int)($rxRaw[$onuId] ?? 0);
                if ($rxRawVal < 0 && $rxRawVal > -65536) {
                    $rxVal = round($rxRawVal / 100, 2);
                } elseif ($rxRawVal > 65536) {
                    $rxVal = round(10 * log10($rxRawVal / 10000), 2);
                } elseif ($rxRawVal > 0 && $rxRawVal < 2000) {
                    $rxVal = round($rxRawVal / 10 - 50, 2);
                }

                $txVal = null;
                $txRawVal = (int)($txRaw[$onuId] ?? 0);
                if ($txRawVal !== 0) {
                    if ($txRawVal < 0 && $txRawVal > -65536) {
                        $txVal = round($txRawVal / 100, 2);
                    } elseif ($txRawVal > 65536) {
                        $txVal = round(10 * log10($txRawVal / 10000), 2);
                    } elseif ($txRawVal > 0 && $txRawVal < 2000) {
                        $txVal = round($txRawVal / 10 - 50, 2);
                    }
                }

                $tempVal = null;
                if (!empty($tempRaw[$onuId]) && is_numeric($tempRaw[$onuId])) {
                    $t = (int)$tempRaw[$onuId];
                    if ($t !== 0 && $t < 65535) {
                        $tempVal = $t > 1000 ? round($t / 100, 1) : $t;
                    }
                }

                $linearIndex = (($ponPort - 1) * 256) + $onuId;
                $searchKey = "1.0." . $linearIndex;
                $statusVal = $statuses[$searchKey] ?? null;

                $isOnline = false;
                if ($statusVal !== null) {
                    $isOnline = ((int)$statusVal === 1);
                } else {
                    if ($rxVal !== null && $rxVal > -40) {
                        $isOnline = true;
                    }
                }

                $results[] = [
                    'pon_port'         => $ponPort,
                    'onu_index'        => $onuId,
                    'serial_number'    => $dbOnu->serial_number,
                    'mac_address'      => $dbOnu->mac_address,
                    'rx_power_dbm'     => $rxVal,
                    'tx_power_dbm'     => $txVal,
                    'snr_db'           => null,
                    'temperature'      => $tempVal,
                    'firmware_version' => $dbOnu->firmware_version,
                    'model'            => $dbOnu->model,
                    'status'           => $isOnline ? 'online' : 'offline',
                ];
            }

            return $results;
        }

        $results   = [];
        $base      = $this->onuInfoOid . '.' . $ponPort;
        $serials   = $this->snmp->walk($base . '.2');
        $rxRaw     = $this->snmp->walk($base . '.6');
        $txRaw     = $this->snmp->walk($base . '.7');
        $statuses  = $this->snmp->walk($base . '.3');
        $tempRaw   = $this->snmp->walk($base . '.8');
        $voltRaw   = $this->snmp->walk($base . '.9');
        $biasRaw   = $this->snmp->walk($base . '.10');
        $macs      = $this->snmp->walk($base . '.4');
        
        // Cache the entire CTC MAC table once per polling cycle
        if ($this->macsCache === null) {
            $this->macsCache = [];
            try {
                $allCtcMacs = $this->snmp->walk('.1.3.6.1.4.1.17409.2.8.4.3.1.11') ?: [];
                foreach ($allCtcMacs as $fullIndex => $val) {
                    if (preg_match('/(\d+)\.\d+$/', $fullIndex, $m)) {
                        $ifIndex = (int)$m[1];
                        $pPort1 = (($ifIndex >> 12) & 0xFF) - 128;
                        $pPort2 = ($ifIndex >> 8) & 0xFF;
                        $pPort3 = $pPort1 + 1; // Zero-indexed offset
                        $pPort4 = $pPort2 + 1; // Zero-indexed offset
                        $oId = $ifIndex & 0xFFF;
                        
                        foreach ([$pPort1, $pPort2, $pPort3, $pPort4] as $p) {
                            if ($p >= 0 && $p <= 64) {
                                $this->macsCache[$p][$oId] = $val;
                            }
                        }
                    }
                }
            } catch (\Throwable) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }
        }
        $macsCTC = $this->macsCache[$ponPort] ?? [];
        if (empty($macsCTC) && $ponPort > 1000000) {
            $actualPort = $ponPort & 0xFF;
            $macsCTC = $this->macsCache[$actualPort] ?? [];
            if (empty($macsCTC)) {
                $actualPort2 = ($ponPort >> 8) & 0xFF;
                $macsCTC = $this->macsCache[$actualPort2] ?? [];
            }
        }
        
        $firmwares = $this->snmp->walk($base . '.11');
        $models    = $this->snmp->walk($base . '.12');

        if (empty($txRaw)) {
            $txRaw = $this->snmp->walk($base . '.5');
        }
        if (empty($firmwares)) {
            $firmwares = $this->snmp->walk($base . '.13');
        }

        foreach ($serials as $idx => $serial) {
            $rxRawVal = (int)($rxRaw[$idx] ?? 0);
            $rx  = null;
            if ($rxRawVal < 0 && $rxRawVal > -65536) {
                $rx = round($rxRawVal / 100, 2);
            } elseif ($rxRawVal > 65536) {
                $rx = round(10 * log10($rxRawVal / 10000), 2);
            } elseif ($rxRawVal > 0 && $rxRawVal < 2000) {
                $rx = round($rxRawVal / 10 - 50, 2);
            }

            $txRawVal = (int)($txRaw[$idx] ?? 0);
            $tx = null;
            if ($txRawVal !== 0) {
                if ($txRawVal < 0 && $txRawVal > -65536) {
                    $tx = round($txRawVal / 100, 2);
                } elseif ($txRawVal > 65536) {
                    $tx = round(10 * log10($txRawVal / 10000), 2);
                } elseif ($txRawVal > 0 && $txRawVal < 2000) {
                    $tx = round($txRawVal / 10 - 50, 2);
                }
            }

            $snrVal = null;
            $bias = (int)($biasRaw[$idx] ?? 0);
            if ($bias > 0) {
                $snrVal = round(min(35.0, max(8.0, 10.0 + log10(max(1, $bias / 1000)) * 8)), 2);
            }

            $tempVal = null;
            if (!empty($tempRaw[$idx]) && is_numeric($tempRaw[$idx])) {
                $t = (int)$tempRaw[$idx];
                if ($t !== 0 && $t < 65535) {
                    $tempVal = $t > 1000 ? round($t / 100, 1) : $t;
                }
            }

            $mac = null;
            $rawMac = $macsCTC[$idx] ?? $macs[$idx] ?? null;
            if (!empty($rawMac)) {
                $macStr = is_string($rawMac) ? $rawMac : (string)$rawMac;
                
                $macStr = str_ireplace(['Hex-STRING:', 'STRING:', '"'], '', $macStr);
                // Handle "Hex-STRING: xx xx" from snmpwalk
                if (preg_match('/^[A-F0-9]{2}(\s[A-F0-9]{2}){5}$/i', trim($macStr)) || strpos((string)$rawMac, 'Hex-STRING') !== false) {
                    $hexClean = preg_replace('/[^A-F0-9]/i', '', $macStr);
                } else {
                    $hexClean = strtoupper(bin2hex($macStr));
                }
                
                $hexClean = substr(preg_replace('/[^A-F0-9]/i', '', $hexClean), 0, 12);
                if (strlen($hexClean) === 12) {
                    $mac = implode(':', str_split($hexClean, 2));
                }
            }

            $fw = null;
            if (!empty($firmwares[$idx]) && is_string($firmwares[$idx])) {
                $fwClean = trim($firmwares[$idx]);
                if (!empty($fwClean) && $fwClean !== 'N/A') {
                    $fw = $fwClean;
                }
            }

            $modelVal = null;
            if (!empty($models[$idx]) && is_string($models[$idx])) {
                $mClean = trim($models[$idx]);
                if (!empty($mClean) && $mClean !== 'N/A') {
                    $modelVal = $mClean;
                }
            }

            $results[] = [
                'pon_port'         => $ponPort,
                'onu_index'        => $idx,
                'serial_number'    => is_string($serial) ? strtoupper(trim($serial)) : (string)$serial,
                'mac_address'      => $mac,
                'rx_power_dbm'     => $rx,
                'tx_power_dbm'     => $tx,
                'snr_db'           => $snrVal,
                'temperature'      => $tempVal,
                'firmware_version' => $fw,
                'model'            => $modelVal,
                'status'           => match ((int)($statuses[$idx] ?? 0)) {
                    1, 2, 10       => 'online',
                    3, 0           => 'offline',
                    default        => 'unknown'
                },
            ];
        }
        return $results;
    }

    public function discoverUnregisteredOnus(): array
    {
        try {
            $this->initializeCli();
            
            $output = $this->cli->execute('show ont autofind all');
            if (str_contains($output, 'Unknown command')) {
                $output = $this->cli->execute('show gpon onu uncfg');
            }
            if (str_contains($output, 'Unknown command') || empty(trim($output))) {
                $output = $this->cli->execute('show onu uncfg');
            }
            if (str_contains($output, 'Unknown command') || empty(trim($output))) {
                $output = $this->cli->execute('show gpon onu unconfigured');
            }
            if (str_contains($output, 'Unknown command') || empty(trim($output))) {
                $output = $this->cli->execute('show onu unauthorized');
            }
            
            $onus = [];
            foreach (explode("\n", (string)$output) as $line) {
                if (preg_match('/(?:GPON-ONU_|GPON\s+|EPON-ONU_|EPON\s+)?(?:\d+\/)?\d+\/(\d+)(?::\d+)?\s+.*?([A-F0-9]{12,16})/i', strtoupper($line), $m)) {
                    $onus[] = [
                        'pon_port'      => (int)$m[1],
                        'onu_id'        => 0,
                        'serial_number' => $m[2],
                        'vendor_oui'    => substr($m[2], 0, 4),
                    ];
                } elseif (preg_match('/PON\s*(\d+)\s+.*?([A-F0-9]{12,16})/', strtoupper($line), $m)) {
                    $onus[] = [
                        'pon_port'      => (int)$m[1],
                        'onu_id'        => 0,
                        'serial_number' => $m[2],
                        'vendor_oui'    => substr($m[2], 0, 4),
                    ];
                }
            }
            return $onus;
        } catch (\Exception $e) { throw $e; }
    }

    public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool
    {
        try {
            $this->initializeCli();
            $onuId = $onu->onu_id_on_olt ?? $onu->getKey();
            $name  = $onu->name ?? ('CUST-' . $onuId);
            $this->cli->execute('configure terminal');
            $this->cli->execute("interface gpon 0/0/$ponPort");
            $this->cli->execute("onu add $onuId sn-auth $serialNumber line-profile-id 1 srv-profile-id 1 description \"$name\"");
            $this->cli->execute("onu $onuId port uni 1 native-vlan 100");
            $this->cli->execute('exit');
            $this->cli->execute('write');
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function setOnuAdminStatus(Onu $onu, string $status): bool
    {
        try {
            $ponPort = $onu->pon_port ?? 0;
            $onuId   = $onu->onu_id_on_olt ?? $onu->getKey();
            $this->initializeCli();
            $this->cli->execute('configure terminal');
            $this->cli->execute("interface gpon 0/0");
            $cmd = match ($status) {
                'enable'  => "ont modify $ponPort $onuId admin-state up",
                'disable' => "ont modify $ponPort $onuId admin-state down",
                'reset'   => "ont reset $ponPort $onuId",
                default   => "ont modify $ponPort $onuId admin-state up"
            };
            $this->cli->execute($cmd);
            $this->cli->execute('exit');
            $this->cli->execute('write');
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function pushOnuWanConfig(Onu $onu, array $config): bool
    {
        try {
            $ponPort = $onu->ponPort->port_number ?? $onu->pon_port ?? 0;
            $onuId   = $onu->onu_index ?? $onu->onu_id_on_olt ?? $onu->getKey();

            $mode = strtolower($config['mode'] ?? 'pppoe');
            $vlanId = (int)($config['vlan_id'] ?? 10);
            $priority = (int)($config['priority'] ?? 0);
            $ipIndex = (int)($config['ip_index'] ?? 1);

            $this->initializeCli();
            $this->cli->execute('configure terminal');
            $this->cli->execute('interface gpon 0/0');

            if ($mode === 'pppoe') {
                $user = $config['username'] ?? '';
                $pass = $config['password'] ?? '';
                $cmd = "ont ipconfig $ponPort $onuId ip-index $ipIndex pppoe username $user password $pass vlan $vlanId priority $priority";
            } elseif ($mode === 'dhcp' || $mode === 'ipoe') {
                $cmd = "ont ipconfig $ponPort $onuId ip-index $ipIndex dhcp vlan $vlanId priority $priority";
            } else {
                throw new \Exception("Unsupported WAN mode: $mode");
            }

            $this->cli->execute($cmd);
            $this->cli->execute('exit');
            $this->cli->execute('write');
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to push WAN config to C-Data ONU {$onu->id}: " . $e->getMessage());
            return false;
        }
    }

    public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool
    {
        try {
            $ponPort      = $onu->pon_port ?? 0;
            $onuId        = $onu->onu_id_on_olt ?? $onu->getKey();
            $profileName  = "DSB_{$downloadMbps}_{$uploadMbps}";
            $downKbps     = $downloadMbps * 1000;
            $upKbps       = $uploadMbps * 1000;

            $this->initializeCli();
            $this->cli->execute('configure terminal');
            $this->cli->execute("dba-profile add profile-name $profileName type 4 max $downKbps");
            $this->cli->execute("interface gpon 0/0/$ponPort");
            $this->cli->execute("onu $onuId tcont 1 dba-profile-name $profileName");
            $this->cli->execute("onu $onuId gemport 1 unicast tcont 1 dir both");
            $this->cli->execute("onu $onuId service-port 1 gemport 1 vlan 100 rx-cttr $downKbps tx-cttr $upKbps");
            $this->cli->execute('exit');
            $this->cli->execute('write');
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}










