<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use Illuminate\Support\Facades\Log;

/**
 * Driver OLT C-Data (CD6000 / CD8000 / CD5508 / CD5000 / FD1600 series / FD1100 / FD1200 series)
 * Enterprise OID: .1.3.6.1.4.1.51810, .1.3.6.1.4.1.34592, and CTC .1.3.6.1.4.1.17409
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

    protected ?bool $isNewBranch = null;
    protected ?array $cachedOnuStatuses = null;
    protected ?array $cachedOnuActiveStates = null;
    protected ?array $cachedOnuSerials = null;
    protected ?array $cachedOnuRx = null;
    protected ?array $cachedOnuTx = null;
    protected ?array $cachedOnuTemps = null;
    protected ?array $cachedOnuMacs = null;
    protected ?array $cachedCliParsedOnus = null;
    protected ?array $macsCache = null;

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
        if ($res !== false && $res !== '') {
            return $this->isNewBranch = true;
        }

        // Test GPON PON Port Status OID
        $ponTest = @$this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.4');
        return $this->isNewBranch = (!empty($ponTest));
    }

    public function getPonPortsStatus(): array
    {
        if ($this->checkIsNewBranch()) {
            $statuses = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.4')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.2')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.1') ?: []));
            $ports    = [];
            if ($statuses) {
                foreach ($statuses as $idx => $status) {
                    $cleanOid = str_replace('iso', '.1', (string)$idx);
                    $parts = explode('.', $cleanOid);
                    $realPortIdx = (int)end($parts);

                    if ($realPortIdx >= 1310721 && $realPortIdx <= 1310736) {
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
            if (!empty($ports)) {
                return $ports;
            }
        }

        // EPON / Legacy OID
        $statuses = $this->snmp->walk($this->ponPortStatusOid);
        $ports    = [];
        if (!empty($statuses)) {
            foreach ($statuses as $idx => $status) {
                $portIdx = (int)preg_replace('/^.*?(\d+)$/', '$1', (string)$idx);
                if ($portIdx <= 0) {
                    $portIdx = (int)$idx;
                }
                $ports[] = [
                    'port_index' => $portIdx,
                    'port_name'  => "EPON 0/0/$portIdx",
                    'status'     => match ((int)$status) {
                        1, 101, 100 => 'up',
                        2, 102      => 'down',
                        default     => 'unknown'
                    },
                ];
            }
            return $ports;
        }

        // Fallback CTC EPON Port Table (.1.3.6.1.4.1.17409.2.3.1.2.1.1)
        $ctcPorts = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.1.2.1.1');
        if (!empty($ctcPorts)) {
            foreach ($ctcPorts as $idx => $val) {
                $portIdx = (int)preg_replace('/^.*?(\d+)$/', '$1', (string)$idx);
                $ports[] = [
                    'port_index' => $portIdx,
                    'port_name'  => "EPON 0/0/$portIdx",
                    'status'     => 'up',
                ];
            }
            return $ports;
        }

        // Default 4 PON ports fallback if OLT answered sysUpTime
        for ($p = 1; $p <= 4; $p++) {
            $ports[] = [
                'port_index' => $p,
                'port_name'  => "PON 0/0/$p",
                'status'     => 'up',
            ];
        }
        return $ports;
    }

    public function getOnuRxPower(int $ponPort): array
    {
        // 1. Calculate portNum (1-based, e.g. 1 to 16) and slot
        if ($ponPort >= 1310721 && $ponPort <= 1310736) {
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

        // 2. LAYER 1: Scrape via Telnet CLI (Fast, full signals & serial numbers)
        if ($this->cachedCliParsedOnus === null) {
            $this->cachedCliParsedOnus = [];
            $this->scrapeCliOnus($slot);
        }

        if (!empty($this->cachedCliParsedOnus[$portNum])) {
            $res = [];
            foreach ($this->cachedCliParsedOnus[$portNum] as $item) {
                $item['pon_port'] = $ponPort;
                $res[] = $item;
            }
            return $res;
        }

        // 3. LAYER 2: Pure SNMP Auto-Discovery & Signal Extraction (Works on fresh installs when DB is empty!)
        return $this->scrapeSnmpOnus($ponPort, $portNum);
    }

    protected function scrapeCliOnus(int $slot = 0): void
    {
        try {
            $this->initializeCli();

            $isBad = function ($s) {
                $l = strtolower((string)$s);
                return empty(trim($s)) || str_contains($l, 'incomplete') || str_contains($l, 'invalid') || str_contains($l, 'unknown') || str_contains($l, 'error') || str_contains($l, 'unrecognized');
            };

            // Attempt 1: Direct commands
            $infoOut = $this->cli->execute("show ont info all\r\n", false);
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show onu info all\r\n", false);
            }

            $optOut = $this->cli->execute("show ont optical-info all\r\n", false);
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show onu optical-info all\r\n", false);
            }

            // Attempt 2: Interface-level commands (standard C-Data requirement on older/modular firmware)
            if ($isBad($infoOut)) {
                $this->cli->execute("config\r\n", false);
                $inGpon = $this->cli->execute("interface gpon 0/{$slot}\r\n", false);
                if ($isBad($inGpon)) {
                    $inGpon = $this->cli->execute("interface gpon 0/0\r\n", false);
                }

                if (!$isBad($inGpon)) {
                    $infoOut = $this->cli->execute("show ont info all\r\n", false);
                    if ($isBad($infoOut)) {
                        $infoOut = $this->cli->execute("show ont info 1 all\r\n", false);
                    }
                    $optOut = $this->cli->execute("show ont optical-info all\r\n", false);
                    if ($isBad($optOut)) {
                        $optOut = $this->cli->execute("show ont optical-info 1 all\r\n", false);
                    }
                } else {
                    $inEpon = $this->cli->execute("interface epon 0/{$slot}\r\n", false);
                    if ($isBad($inEpon)) {
                        $inEpon = $this->cli->execute("interface epon 0/0\r\n", false);
                    }
                    if (!$isBad($inEpon)) {
                        $infoOut = $this->cli->execute("show onu info all\r\n", false);
                        if ($isBad($infoOut)) {
                            $infoOut = $this->cli->execute("show onu info 1 all\r\n", false);
                        }
                        $optOut = $this->cli->execute("show onu optical-info all\r\n", false);
                        if ($isBad($optOut)) {
                            $optOut = $this->cli->execute("show onu optical-info 1 all\r\n", false);
                        }
                    }
                }
                $this->cli->execute("exit\r\n", false);
            }

            // Parse show ont info lines
            $cleanInfo = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$infoOut);
            foreach (explode("\n", $cleanInfo) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, 'F/S') || str_starts_with($line, 'Slot') || str_starts_with($line, 'Port') || str_starts_with($line, 'ONT') || str_starts_with($line, 'No') || str_starts_with($line, 'Total:') || str_starts_with($line, 'show') || str_starts_with($line, 'FD1608S')) {
                    continue;
                }

                // Match SN (4 letter vendor + 8-12 hex) OR MAC address
                if (preg_match('/([A-Z0-9]{4}[A-Z0-9]{8,12}|(?:[A-F0-9]{2}[:-]){5}[A-F0-9]{2})/i', $line, $snMatch)) {
                    $sn = strtoupper($snMatch[1]);
                    $pos = strpos($line, $snMatch[1]);
                    $before = trim(substr($line, 0, $pos));
                    $after = trim(substr($line, $pos + strlen($snMatch[1])));

                    // Explicitly match 'online' vs 'offline' (ignore 'Active' control flag)
                    $runState = stripos($after, 'online') !== false ? 'online' : 'offline';

                    $tokens = preg_split('/\s+/', $before);
                    $lPort = null;
                    $lOnuId = null;

                    // Standard C-Data GPON: "0/0 1 5" -> tokens: ["0/0", "1", "5"]
                    if (count($tokens) >= 3 && str_contains($tokens[0], '/')) {
                        $lPort = (int)$tokens[1];
                        $lOnuId = (int)$tokens[2];
                    } elseif (count($tokens) === 2) {
                        if (str_contains($tokens[0], '/')) {
                            $pParts = explode('/', $tokens[0]);
                            $lPort = (int)end($pParts);
                        } else {
                            $lPort = (int)$tokens[0];
                        }
                        $lOnuId = (int)$tokens[1];
                    } elseif (preg_match('/(\d+)[:\s]+(\d+)$/', $before, $m)) {
                        $lPort = (int)$m[1];
                        $lOnuId = (int)$m[2];
                    }

                    if ($lPort !== null && $lPort > 0 && $lOnuId !== null && $lOnuId > 0) {
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
                if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, 'F/S') || str_starts_with($line, 'Slot') || str_starts_with($line, 'Port') || str_starts_with($line, 'ONT') || str_starts_with($line, 'ID') || str_starts_with($line, 'Total:') || str_starts_with($line, 'show') || str_starts_with($line, 'FD1608S')) {
                    continue;
                }

                $cols = preg_split('/\s+/', $line);
                $lPort = null;
                $lOnuId = null;
                $rx = null;
                $tx = null;
                $temp = null;

                // Format 1: "0/0 2 1 -21.40 2.35 45.0"
                if (count($cols) >= 5 && str_contains($cols[0], '/') && is_numeric($cols[1]) && is_numeric($cols[2])) {
                    $lPort = (int)$cols[1];
                    $lOnuId = (int)$cols[2];
                    $rx = $cols[3];
                    $tx = $cols[4];
                    $temp = $cols[5] ?? null;
                }
                // Format 2: "0/2 1 -21.40 2.35"
                elseif (count($cols) >= 4 && str_contains($cols[0], '/')) {
                    $parts = explode('/', $cols[0]);
                    $lPort = (int)end($parts);
                    $lOnuId = (int)$cols[1];
                    $rx = $cols[2];
                    $tx = $cols[3];
                    $temp = $cols[5] ?? null;
                }
                // Format 3: "1 5 -21.40 2.35"
                elseif (count($cols) >= 4 && is_numeric($cols[0]) && is_numeric($cols[1])) {
                    $lPort = (int)$cols[0];
                    $lOnuId = (int)$cols[1];
                    $rx = $cols[2];
                    $tx = $cols[3];
                    $temp = $cols[4] ?? null;
                }

                if ($lPort !== null && $lOnuId !== null) {
                    if (isset($this->cachedCliParsedOnus[$lPort][$lOnuId])) {
                        if (is_numeric($rx)) $this->cachedCliParsedOnus[$lPort][$lOnuId]['rx_power_dbm'] = (float)$rx;
                        if (is_numeric($tx)) $this->cachedCliParsedOnus[$lPort][$lOnuId]['tx_power_dbm'] = (float)$tx;
                        if (is_numeric($temp)) $this->cachedCliParsedOnus[$lPort][$lOnuId]['temperature'] = (float)$temp;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("CData OLT Telnet CLI sync failed: " . $e->getMessage() . ". Falling back to SNMP.");
        }
    }

    protected function scrapeSnmpOnus(int $ponPort, int $portNum): array
    {
        // Cache all SNMP walk tables once per polling cycle
        if ($this->cachedOnuSerials === null) {
            // Check C-Data GPON (FD1608S / FD1616 series 34592.1.5.1 / 34592.1.3.100)
            $gponSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.4')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.2')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.1')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.4.1.1.2')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.1.1.2') ?: [])))));
            
            // GPON Active State (1 = active/online, 2 = inactive/offline)
            $gponActive = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.7')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.7') ?: [])) : [];

            // GPON Run Status (1=init, 2=standby, 3=sn, 4=ranging, 5=operation/ONLINE, 6=popup)
            $gponRunStatus = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.8')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.8')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.13') ?: []))) : [];
            
            // Optical Rx Power (Table 19.1.1.3 or 9.4.1.3 or 9.3.1.3 or 4.1.1.8)
            $gponRx = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.20.1.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.9')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.4.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.3.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.4.1.1.8') ?: [])))))) : [];

            $gponTx = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.4')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.20.1.1.4')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.4.1.4')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.3.1.4') ?: [])))) : [];

            $gponTemps = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.6')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.4.1.6') ?: [])) : [];

            // Check C-Data EPON (34592 / 51810 / 17409 CTC)
            $eponSerials = [];
            $eponStatuses = [];
            $eponActive = [];
            $eponRx = [];
            $eponTx = [];
            $eponTemps = [];
            $eponMacs = [];

            if (empty($gponSerials)) {
                // 1. C-Data 34592 EPON ONU Table
                $eponSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.3.1.1.2') ?: [];
                if (!empty($eponSerials)) {
                    $eponStatuses = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.3.1.1.3') ?: [];
                    $eponActive = $eponStatuses;
                    $eponRx = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.3.1.1.6') ?: [];
                    $eponMacs = $eponSerials;
                }

                // 2. C-Data 51810 EPON ONU Table
                if (empty($eponSerials)) {
                    $eponSerials = $this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.2')
                        ?: ($this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.4') ?: []);
                    $eponStatuses = $this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.3') ?: [];
                    $eponActive = $eponStatuses;
                    $eponRx = $this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.6') ?: [];
                    $eponTx = $this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.7') ?: ($this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.5') ?: []);
                    $eponTemps = $this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.8') ?: [];
                    $eponMacs = $this->snmp->walk('.1.3.6.1.4.1.51810.1.3.1.1.1.4') ?: $eponSerials;
                }

                // 3. CTC 17409 Standard EPON (Universal on Chinese EPON OLTs)
                if (empty($eponSerials)) {
                    $ctcMacs = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.1.1.2')
                        ?: ($this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.1.1.7')
                        ?: ($this->snmp->walk('.1.3.6.1.4.1.17409.2.8.4.3.1.11') ?: []));
                    $ctcStatuses = $this->snmp->walk('.1.3.6.1.4.1.17409.2.3.4.1.1.3') ?: [];
                    $ctcActive = $ctcStatuses;
                    $ctcRx = $this->snmp->walk('.1.3.6.1.4.1.17409.2.8.4.3.1.8') ?: [];
                    $ctcTx = $this->snmp->walk('.1.3.6.1.4.1.17409.2.8.4.3.1.9') ?: [];
                    
                    if (!empty($ctcMacs)) {
                        $eponMacs = $ctcMacs;
                        $eponSerials = $ctcMacs;
                        $eponStatuses = $ctcStatuses;
                        $eponRx = $ctcRx;
                        $eponTx = $ctcTx;
                    }
                }
            }

            $this->cachedOnuSerials = $gponSerials ?: $eponSerials;
            $this->cachedOnuActiveStates = !empty($gponSerials) ? $gponActive : $eponActive;
            $this->cachedOnuStatuses = !empty($gponSerials) ? $gponRunStatus : $eponStatuses;
            $this->cachedOnuRx = $gponRx ?: $eponRx;
            $this->cachedOnuTx = $gponTx ?: $eponTx;
            $this->cachedOnuTemps = $gponTemps ?: $eponTemps;
            $this->cachedOnuMacs = $eponMacs;
        }

        // Helper to normalize and lookup OID suffixes across SNMP result arrays
        $lookupVal = function(?array $arr, string $key) {
            if (empty($arr)) return null;
            if (array_key_exists($key, $arr)) return $arr[$key];
            $trimmed = ltrim($key, '.');
            if (array_key_exists($trimmed, $arr)) return $arr[$trimmed];
            if (array_key_exists('.' . $trimmed, $arr)) return $arr['.' . $trimmed];
            
            // Extract suffix (e.g. 1310721.1 or 1.1)
            $parts = explode('.', $trimmed);
            if (count($parts) >= 2) {
                $lastTwo = $parts[count($parts) - 2] . '.' . end($parts);
                if (array_key_exists($lastTwo, $arr)) return $arr[$lastTwo];
                foreach ($arr as $k => $v) {
                    if (str_ends_with((string)$k, $lastTwo) || str_ends_with((string)$k, '.' . end($parts))) {
                        return $v;
                    }
                }
            }
            return null;
        };

        $cleanSnString = function($raw) {
            $raw = trim((string)$raw);
            $lower = strtolower($raw);
            if ($raw === '' 
                || str_contains($lower, 'no such')
                || str_contains($lower, 'nosuch')
                || str_contains($lower, 'error')
                || str_contains($lower, 'not found')
                || str_contains($lower, 'end of mib')
                || str_contains($lower, 'null')
                || str_contains($lower, 'undefined')) {
                return ['sn' => null, 'mac' => null];
            }

            $raw = preg_replace('/^(STRING|Hex-STRING|OctetString|Hex)\s*:?\s*/i', '', $raw);
            $raw = trim($raw, '" \r\n\t');

            // 1. Plain text string like "CDTE12345678" or "HWTC12345678"
            if (preg_match('/^[A-Z0-9]{4}[A-Z0-9]{8,12}$/i', $raw)) {
                return ['sn' => strtoupper($raw), 'mac' => null];
            }

            // 2. Colon / hyphen formatted MAC address (EPON)
            if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $raw)) {
                $mac = strtoupper(str_replace('-', ':', $raw));
                return ['sn' => $mac, 'mac' => $mac];
            }

            // Clean hex characters
            $hex = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $raw));

            // 3. 16 hex chars -> GPON Serial Number (4 bytes ASCII vendor + 4 bytes Hex)
            if (strlen($hex) === 16) {
                $vendorAscii = @hex2bin(substr($hex, 0, 8));
                $restHex = substr($hex, 8, 8);
                if ($vendorAscii !== false && preg_match('/^[A-Za-z0-9]{4}$/', $vendorAscii)) {
                    return ['sn' => strtoupper($vendorAscii) . $restHex, 'mac' => null];
                }
                return ['sn' => $hex, 'mac' => null];
            }

            // 4. 12 hex chars -> EPON MAC address (6 bytes)
            if (strlen($hex) === 12) {
                $mac = implode(':', str_split($hex, 2));
                return ['sn' => $mac, 'mac' => $mac];
            }

            // 5. Binary to ASCII printable text fallback
            if (strlen($hex) >= 8 && strlen($hex) <= 32) {
                $bin = @hex2bin($hex);
                if ($bin !== false && preg_match('/^[A-Za-z0-9\-_:]{4,16}$/', $bin)) {
                    return ['sn' => strtoupper($bin), 'mac' => null];
                }
            }

            $final = strtoupper(preg_replace('/[^A-Za-z0-9:\-]/', '', $raw));
            if (strlen($final) < 4 || strlen($final) > 20) {
                return ['sn' => null, 'mac' => null];
            }
            return ['sn' => $final, 'mac' => str_contains($final, ':') ? $final : null];
        };

        $parsePower = function($val) {
            $raw = (float)$val;
            if ($raw == 0 || $raw <= -65535 || $raw >= 65535) return null;
            if ($raw < 0 && $raw >= -4500) return round($raw / 100, 2);
            if ($raw >= 1000 && $raw <= 4500) return round(-($raw / 100), 2);
            if ($raw >= -45 && $raw <= 10) return round($raw, 2);
            if ($raw >= -450 && $raw <= -100) return round($raw / 10, 2);
            if ($raw > 0 && $raw < 100000) return round(10 * log10($raw / 10000), 2);
            return null;
        };

        $results = [];

        foreach ($this->cachedOnuSerials as $oidKey => $rawVal) {
            $parsed = $cleanSnString($rawVal);
            $sn = $parsed['sn'] ?? '';
            if (strlen($sn) < 4) continue;

            $parts = explode('.', (string)$oidKey);
            $p = null;
            $onuId = 1;

            if (count($parts) >= 2) {
                $onuId = (int)end($parts);
                $rawP = (int)$parts[count($parts) - 2];
                if ($rawP >= 1310721 && $rawP <= 1310736) {
                    $p = $rawP - 1310720;
                } elseif ($rawP > 1000000) {
                    $p = $rawP & 0xFF;
                } else {
                    $p = $rawP;
                }
            } else {
                $endIdx = (int)end($parts);
                $p = (int)floor($endIdx / 256) + 1;
                $onuId = $endIdx % 256;
            }

            // Check if this ONU belongs to the current port
            $isPortMatch = false;
            if ($p !== null && $p > 0) {
                $isPortMatch = ($p === $portNum || $p === $ponPort || (isset($rawP) && $rawP === $ponPort));
            } else {
                // If port index could not be resolved from OID, assign to port 1 only (prevent duplicating to all 8 ports)
                $isPortMatch = ($portNum === 1);
            }

            if ($isPortMatch) {
                $rawAct = trim((string)($lookupVal($this->cachedOnuActiveStates, (string)$oidKey) ?? ''));
                $rawSt  = trim((string)($lookupVal($this->cachedOnuStatuses, (string)$oidKey) ?? ''));
                $stRaw  = strtolower($rawSt);
                $actRaw = strtolower($rawAct);
                $activeVal = is_numeric($rawAct) ? (int)$rawAct : 0;
                $stVal     = is_numeric($rawSt) ? (int)$rawSt : 0;

                // In GPON: activeVal 1 = active, stVal 5 = operation (O5 state)
                // In EPON: activeVal / stVal 1 = online/authenticated, 3 = online
                $isOnline = ($activeVal === 1)
                    || in_array($stVal, [1, 2, 3, 4, 5, 6, 10, 100, 101], true)
                    || str_contains($stRaw, 'online')
                    || str_contains($stRaw, 'up')
                    || str_contains($stRaw, 'active')
                    || str_contains($stRaw, 'oper')
                    || str_contains($stRaw, 'o5')
                    || str_contains($actRaw, 'active')
                    || str_contains($actRaw, 'up')
                    || str_contains($actRaw, 'online')
                    || str_contains($actRaw, 'enable');

                $rxVal = $parsePower($lookupVal($this->cachedOnuRx, (string)$oidKey) ?? 0);
                $txVal = $parsePower($lookupVal($this->cachedOnuTx, (string)$oidKey) ?? 0);

                if ($rxVal !== null && $rxVal > -39.0 && $rxVal < 5.0) {
                    $isOnline = true;
                }

                $mac = $parsed['mac'] ?? null;
                $rawMac = $lookupVal($this->cachedOnuMacs, (string)$oidKey);
                if (!$mac && !empty($rawMac)) {
                    $mParsed = $cleanSnString($rawMac);
                    $mac = $mParsed['mac'] ?? null;
                }

                $results[] = [
                    'pon_port'         => $ponPort,
                    'onu_index'        => $onuId > 0 ? $onuId : 1,
                    'serial_number'    => $sn,
                    'mac_address'      => $mac,
                    'rx_power_dbm'     => $rxVal,
                    'tx_power_dbm'     => $txVal,
                    'snr_db'           => null,
                    'temperature'      => null,
                    'firmware_version' => null,
                    'model'            => null,
                    'status'           => $isOnline ? 'online' : 'offline',
                ];
            }
        }

        // If SNMP returned 0 ONUs but Database has existing records for this port, keep them as offline
        if (empty($results)) {
            $dbOnus = Onu::where('olt_id', $this->olt->id)
                ->where('pon_port', $ponPort)
                ->get();
            foreach ($dbOnus as $dbOnu) {
                $results[] = [
                    'pon_port'         => $ponPort,
                    'onu_index'        => $dbOnu->onu_id_on_olt ?? 1,
                    'serial_number'    => $dbOnu->serial_number,
                    'mac_address'      => $dbOnu->mac_address,
                    'rx_power_dbm'     => null,
                    'tx_power_dbm'     => null,
                    'snr_db'           => null,
                    'temperature'      => null,
                    'firmware_version' => $dbOnu->firmware_version,
                    'model'            => $dbOnu->model,
                    'status'           => 'offline',
                ];
            }
        }

        return $results;
    }

    public function discoverUnregisteredOnus(): array
    {
        try {
            $this->initializeCli();
            
            $output = $this->cli->execute("show ont autofind all\r\n", false);
            if (str_contains($output, 'Unknown command')) {
                $output = $this->cli->execute("show gpon onu uncfg\r\n", false);
            }
            if (str_contains($output, 'Unknown command') || empty(trim($output))) {
                $output = $this->cli->execute("show onu uncfg\r\n", false);
            }
            if (str_contains($output, 'Unknown command') || empty(trim($output))) {
                $output = $this->cli->execute("show gpon onu unconfigured\r\n", false);
            }
            if (str_contains($output, 'Unknown command') || empty(trim($output))) {
                $output = $this->cli->execute("show onu unauthorized\r\n", false);
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
            $this->cli->execute("configure terminal\r\n", false);
            $this->cli->execute("interface gpon 0/0/$ponPort\r\n", false);
            $this->cli->execute("onu add $onuId sn-auth $serialNumber line-profile-id 1 srv-profile-id 1 description \"$name\"\r\n", false);
            $this->cli->execute("onu $onuId port uni 1 native-vlan 100\r\n", false);
            $this->cli->execute("exit\r\n", false);
            $this->cli->execute("write\r\n", false);
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
            $this->cli->execute("configure terminal\r\n", false);
            $this->cli->execute("interface gpon 0/0\r\n", false);
            $cmd = match ($status) {
                'enable'  => "ont modify $ponPort $onuId admin-state up",
                'disable' => "ont modify $ponPort $onuId admin-state down",
                'reset'   => "ont reset $ponPort $onuId",
                default   => "ont modify $ponPort $onuId admin-state up"
            };
            $this->cli->execute($cmd . "\r\n", false);
            $this->cli->execute("exit\r\n", false);
            $this->cli->execute("write\r\n", false);
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
            $this->cli->execute("configure terminal\r\n", false);
            $this->cli->execute("interface gpon 0/0\r\n", false);

            if ($mode === 'pppoe') {
                $user = $config['username'] ?? '';
                $pass = $config['password'] ?? '';
                $cmd = "ont ipconfig $ponPort $onuId ip-index $ipIndex pppoe username $user password $pass vlan $vlanId priority $priority";
            } elseif ($mode === 'dhcp' || $mode === 'ipoe') {
                $cmd = "ont ipconfig $ponPort $onuId ip-index $ipIndex dhcp vlan $vlanId priority $priority";
            } else {
                throw new \Exception("Unsupported WAN mode: $mode");
            }

            $this->cli->execute($cmd . "\r\n", false);
            $this->cli->execute("exit\r\n", false);
            $this->cli->execute("write\r\n", false);
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
            $this->cli->execute("configure terminal\r\n", false);
            $this->cli->execute("dba-profile add profile-name $profileName type 4 max $downKbps\r\n", false);
            $this->cli->execute("interface gpon 0/0/$ponPort\r\n", false);
            $this->cli->execute("onu $onuId tcont 1 dba-profile-name $profileName\r\n", false);
            $this->cli->execute("onu $onuId gemport 1 unicast tcont 1 dir both\r\n", false);
            $this->cli->execute("onu $onuId service-port 1 gemport 1 vlan 100 rx-cttr $downKbps tx-cttr $upKbps\r\n", false);
            $this->cli->execute("exit\r\n", false);
            $this->cli->execute("write\r\n", false);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
