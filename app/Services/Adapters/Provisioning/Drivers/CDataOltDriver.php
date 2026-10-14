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
            try {
                $slot = ($ponPort >> 24) & 0xFF;
                if ($slot === 0) {
                    $slot = ($ponPort >> 16) & 0xFF;
                }
                if ($slot === 0) {
                    $slot = 1;
                }
                $portNum = $ponPort & 0xFF;
                if ($portNum === 0) {
                    $portNum = ($ponPort >> 8) & 0xFF;
                }
                if ($portNum === 0) {
                    $portNum = 1;
                }

                $this->initializeCli();
                $this->cli->execute('config');
                
                // Coba masuk interface gpon 0/$slot (misal interface gpon 0/1 atau 0/2)
                $out = $this->cli->execute("interface gpon 0/{$slot}");
                $inInterface = !str_contains(strtolower($out), 'unknown') && !str_contains(strtolower($out), 'error') && !str_contains(strtolower($out), 'invalid') && !str_contains(strtolower($out), 'incomplete');
                $isEpon = false;

                if (!$inInterface) {
                    $outEpon = $this->cli->execute("interface epon 0/{$slot}"); 
                    if (!str_contains(strtolower($outEpon), 'unknown') && !str_contains(strtolower($outEpon), 'error') && !str_contains(strtolower($outEpon), 'invalid')) {
                        $isEpon = true;
                        $inInterface = true;
                    }
                }

                $isBad = function($s) {
                    $l = strtolower((string)$s);
                    return empty(trim($s)) || str_contains($l, 'incomplete') || str_contains($l, 'invalid') || str_contains($l, 'unknown') || str_contains($l, 'error') || str_contains($l, 'unrecognized');
                };

                // Jalankan perintah show ont/onu info & optical info
                $infoOut = '';
                $optOut = '';

                if ($inInterface) {
                    $infoOut = $this->cli->execute($isEpon ? "show onu info {$portNum} all" : "show ont info {$portNum} all");
                    if ($isBad($infoOut)) {
                        $infoOut = $this->cli->execute($isEpon ? "show onu info all" : "show ont info all");
                    }

                    $optOut = $this->cli->execute($isEpon ? "show onu optical-info {$portNum} all" : "show ont optical-info {$portNum} all");
                    if ($isBad($optOut)) {
                        $optOut = $this->cli->execute($isEpon ? "show onu optical-info all" : "show ont optical-info all");
                    }

                    // Kembali ke config mode
                    $this->cli->execute('exit');
                }

                // Jika gagal atau tidak dalam interface, coba dari config mode dengan parameter slot/port
                if ($isBad($infoOut)) {
                    $infoOut = $this->cli->execute($isEpon ? "show onu info 0/{$slot} {$portNum} all" : "show ont info 0/{$slot} {$portNum} all");
                }
                if ($isBad($infoOut)) {
                    $infoOut = $this->cli->execute($isEpon ? "show onu info 0/{$slot} all" : "show ont info 0/{$slot} all");
                }
                if ($isBad($infoOut)) {
                    $infoOut = $this->cli->execute($isEpon ? "show onu info all" : "show ont info all");
                }

                if ($isBad($optOut)) {
                    $optOut = $this->cli->execute($isEpon ? "show onu optical-info 0/{$slot} {$portNum} all" : "show ont optical-info 0/{$slot} {$portNum} all");
                }
                if ($isBad($optOut)) {
                    $optOut = $this->cli->execute($isEpon ? "show onu optical-info 0/{$slot} all" : "show ont optical-info 0/{$slot} all");
                }
                if ($isBad($optOut)) {
                    $optOut = $this->cli->execute($isEpon ? "show onu optical-info all" : "show ont optical-info all");
                }

                \Illuminate\Support\Facades\Log::info("CData CLI infoOut Slot {$slot} Port {$portNum}: " . trim(preg_replace('/\s+/', ' ', substr($infoOut, 0, 500))));
                \Illuminate\Support\Facades\Log::info("CData CLI optOut Slot {$slot} Port {$portNum}: " . trim(preg_replace('/\s+/', ' ', substr($optOut, 0, 500))));

                // Fetch MACs via CTC OID (cached per instance)
                if ($this->macsCache === null) {
                    $this->macsCache = [];
                    try {
                        $allCtcMacs = $this->snmp->walk('.1.3.6.1.4.1.17409.2.8.4.3.1.11') ?: [];
                        foreach ($allCtcMacs as $fullIndex => $val) {
                            if (preg_match('/(\d+)\.\d+$/', $fullIndex, $m)) {
                                $ifIndex = (int)$m[1];
                                $pPort1 = (($ifIndex >> 12) & 0xFF) - 128;
                                $pPort2 = ($ifIndex >> 8) & 0xFF;
                                $pPort3 = $pPort1 + 1;
                                $pPort4 = $pPort2 + 1;
                                $oId = $ifIndex & 0xFFF;
                                
                                foreach ([$pPort1, $pPort2, $pPort3, $pPort4] as $p) {
                                    if ($p >= 0 && $p <= 64) {
                                        $this->macsCache[$p][$oId] = $val;
                                    }
                                }
                            }
                        }
                    } catch (\Throwable) {
                        // ignore cache error
                    }
                }
                $macsCTC = $this->macsCache[$portNum] ?? ($this->macsCache[$ponPort] ?? []);
                $macsRaw = [];

                $onuList = [];
                // Parse show ont info
                // Format GPON: "  0/1 1  1      ZTEGC85E2D31     Active   Online  success  ..."
                // Format EPON: "  EPON0/1:1    00:0a:c2:xx:xx:xx   Online ..."
                $infoOut = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', $infoOut);
                foreach (explode("\n", $infoOut) as $line) {
                    $line = trim($line);
                    $onuId = null;
                    $sn = null;
                    $runState = null;

                    // Match 1: Format GPON "0/1 1 1 ZTEGC85E2D31 Active Online ..."
                    if (preg_match('/^\s*(\d+\/(\d+))\s+(\d+)\s+(\d+)\s+([A-Z0-9:-]{12,17})\s+.*?(\bOnline\b|\bOffline\b)/i', $line, $m)) {
                        $lineSlot = (int)$m[2];
                        $linePort = (int)$m[3];
                        if ($lineSlot === $slot && $linePort === $portNum) {
                            $onuId = (int)$m[4];
                            $sn = strtoupper($m[5]);
                            $runState = strtolower($m[6]);
                        }
                    } 
                    // Match 2: Format EPON / GPON port:id "0/1:1 00:0a:c2:... Online"
                    elseif (preg_match('/^(?:EPON|GPON)?(?:\d+\/)?(\d+)[:\s]+(\d+)\s+([A-Z0-9.:-]{12,17})\s+.*?(\bOnline\b|\bOffline\b)/i', $line, $m)) {
                        $linePort = (int)$m[1];
                        if ($linePort === $portNum) {
                            $onuId = (int)$m[2];
                            $sn = strtoupper($m[3]);
                            $runState = strtolower($m[4]);
                        }
                    }
                    // Match 3: Format ID SN Status "1 FHTT91C67218 ... Online" (ketika di dalam interface gpon 0/x)
                    elseif (preg_match('/^\s*(\d+)\s+([A-Z0-9:-]{12,17})\s+.*?(\bOnline\b|\bOffline\b)/i', $line, $m)) {
                        $onuId = (int)$m[1];
                        $sn = strtoupper($m[2]);
                        $runState = strtolower($m[3]);
                    }

                    if ($onuId !== null && $sn !== null) {

                        $mac = null;
                        if (str_contains($sn, ':') || str_contains($sn, '-')) { $mac = str_replace('-', ':', $sn); $sn = str_replace(':', '', $mac); }
                        $rawMac = $macsCTC[$onuId] ?? $macsRaw[$onuId] ?? null;
                        
                        // If completely missing, try searching globally in macsCache
                        if (empty($rawMac)) {
                            foreach ($this->macsCache as $portMacs) {
                                if (!empty($portMacs[$onuId])) {
                                    $rawMac = $portMacs[$onuId];
                                    break;
                                }
                            }
                        }

                        if (!empty($rawMac)) {
                            $macStr = is_string($rawMac) ? $rawMac : (string)$rawMac;
                            $macStr = str_ireplace(['Hex-STRING:', 'STRING:', '"'], '', $macStr);
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

                        $onuList[$onuId] = [
                            'pon_port'         => $ponPort,
                            'onu_index'        => $onuId,
                            'serial_number'    => $sn,
                            'mac_address'      => $mac,
                            'rx_power_dbm'     => null,
                            'tx_power_dbm'     => null,
                            'snr_db'           => null,
                            'temperature'      => null,
                            'firmware_version' => null,
                            'model'            => null,
                            'status'           => ($runState === 'online') ? 'online' : 'offline',
                        ];
                    }
                }
                
                // Parse show ont optical-info
                $optOut = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', $optOut);
                foreach (explode("\n", $optOut) as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, 'ONT') || str_starts_with($line, 'ID')) continue;
                    
                    $optOnuId = null;
                    $rx = null;
                    $tx = null;
                    $temp = null;

                    $cols = preg_split('/\s+/', $line);
                    // Format dengan slot/port di depan: "0/1 1 1 -19.50 2.30 -20.10 45 3.3 12.5"
                    if (count($cols) >= 5 && str_contains($cols[0], '/')) {
                        $lineSlot = (int)(explode('/', $cols[0])[1] ?? 1);
                        $linePort = (int)$cols[1];
                        if ($lineSlot === $slot && $linePort === $portNum) {
                            $optOnuId = (int)$cols[2];
                            $rx = $cols[3];
                            $tx = $cols[4];
                            $temp = $cols[6] ?? null;
                        }
                    }
                    // Format standar: "1 -19.50 2.30 -20.10 45 3.3 12.5"
                    elseif (count($cols) >= 3 && is_numeric($cols[0])) {
                        $optOnuId = (int)$cols[0];
                        $rx = $cols[1];
                        $tx = $cols[2];
                        $temp = $cols[4] ?? ($cols[3] ?? null);
                    }

                    if ($optOnuId !== null && isset($onuList[$optOnuId])) {
                        if (is_numeric($rx)) {
                            $onuList[$optOnuId]['rx_power_dbm'] = (float)$rx;
                        }
                        if (is_numeric($tx)) {
                            $onuList[$optOnuId]['tx_power_dbm'] = (float)$tx;
                        }
                        if (is_numeric($temp)) {
                            $onuList[$optOnuId]['temperature'] = (float)$temp;
                        }
                    }
                }

                \Illuminate\Support\Facades\Log::info("CData Slot {$slot} Port {$portNum} parsed " . count($onuList) . " ONUs: " . json_encode(array_values($onuList)));
                
                if (!empty($onuList)) {
                    return array_values($onuList);
                }
            } catch (\Exception $e) {
                \Log::warning("CData OLT Telnet CLI sync failed: " . $e->getMessage() . ". Falling back to SNMP.");
            }

            $results = [];
            $snmpPort = ($ponPort & 0xFF) - 1;
            if ($ponPort > 1000000 && ($ponPort & 0xFF) === 0) {
                $snmpPort = (($ponPort >> 8) & 0xFF) - 1;
            }
            


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
            if (empty($statuses) && empty($rxRaw)) { throw new \Exception('SNMP fallback completely failed: No OID 13 and No RxPower'); }
            $tempRaw = $this->snmp->walk($baseOld . '.8') ?: [];

            // Fallback robust: Loop melalui ONU yang ada di Database untuk PON ini.
            // Karena SNMP C-Data .13 mengembalikan array linear yang kacau (semua port = 0),
            // kita gunakan DB sebagai acuan, lalu mapping RX Power dan Statusnya.
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

                // Coba cari status di .13 menggunakan index linear.
                // Index linear CData biasanya: (snmpPort * 256) + onuId.
                // snmpPort = $ponPort - 1.
                $linearIndex = (($ponPort - 1) * 256) + $onuId;
                $searchKey = "1.0." . $linearIndex;
                $statusVal = $statuses[$searchKey] ?? null;

                // Jika tidak ketemu dengan multiplier 256, coba cari di output mentah 
                // jika OLT hanya menumpuknya (seperti EPON 64/128).
                // Sebagai fallback final: jika RX Power valid dan > -40, anggap online!
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










