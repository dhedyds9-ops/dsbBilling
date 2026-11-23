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
    }

    protected function checkIsNewBranch(): bool
    {
        if ($this->isNewBranch !== null) {
            return $this->isNewBranch;
        }

        $testOid = '.1.3.6.1.4.1.34592.1.3.100.1.8.1.0';
        $res = @$this->snmp->get($testOid);
        if ($res !== false && $res !== '') {
            $this->systemOids['sysTemperature'] = '.1.3.6.1.4.1.34592.1.3.100.1.8.6.0';
            return $this->isNewBranch = true;
        }

        // Test GPON PON Port Status OID
        $ponTest = @$this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.4');
        return $this->isNewBranch = (!empty($ponTest));
    }

    protected function initializeCli(): void
    {
        if ($this->cli !== null) {
            return;
        }

        $user = !empty($this->olt->username) ? $this->olt->username : 'root';
        $pass = !empty($this->olt->password) ? $this->olt->password : 'admin';
        $port = (int)($this->olt->cli_port ?: 23);

        $this->cli = new \App\Services\Adapters\Provisioning\Support\TelnetSshClient();
        try {
            $this->cli->connect(
                $this->olt->ip_address,
                $user,
                $pass,
                $this->cliMode ?: 'telnet',
                $port,
                $this->olt->enable_secret ?? ''
            );
        } catch (\Throwable $e) {
            // Coba fallback kombinasi kredensial umum C-Data (root/admin, root/root, admin/admin, admin/stdONUpwd, dll)
            $fallbackCreds = [
                ['root', 'admin'],
                ['root', 'root'],
                ['admin', 'admin'],
                ['admin', 'stdONUpwd'],
                ['admin', '123456'],
                ['admin', 'cdata'],
            ];
            $connected = false;
            foreach ($fallbackCreds as [$altUser, $altPass]) {
                if ($altUser === $user && $altPass === $pass) {
                    continue;
                }
                try {
                    $this->cli = new \App\Services\Adapters\Provisioning\Support\TelnetSshClient();
                    $this->cli->connect(
                        $this->olt->ip_address,
                        $altUser,
                        $altPass,
                        $this->cliMode ?: 'telnet',
                        $port,
                        $this->olt->enable_secret ?? ''
                    );
                    $connected = true;
                    break;
                } catch (\Throwable) {}
            }
            if (!$connected) {
                throw $e;
            }
        }
    }

    public function getSystemInfo(): array
    {
        $info = parent::getSystemInfo();
        if (($info['status'] ?? '') === 'offline') {
            return $info;
        }

        try {
            $cliInfo = [];
            // 1. LAYER 1: Coba ambil informasi perangkat komprehensif via Telnet CLI 'show version'
            if (!empty($this->olt->username) && !empty($this->olt->password)) {
                try {
                    $this->initializeCli();
                    $verOut = (string)$this->cli->execute("show version\r\n", false);
                    if (!empty($verOut) && !str_contains(strtolower($verOut), 'invalid') && !str_contains(strtolower($verOut), 'incomplete')) {
                        // Device Model (FD1604E-C1 / FD1608S-B1-NDA0)
                        if (preg_match('/(?:Device\s*Model|Device\s*Name|Model\s*Name)\s*[:=]\s*([^\r\n]+)/i', $verOut, $m)) {
                            $cliInfo['model'] = trim($m[1]);
                        }
                        // Device Serial Number (DA60-2605000163)
                        if (preg_match('/(?:Device\s*Serial(?:Number)?|SN|Serial\s*Number)\s*[:=]\s*([^\r\n]+)/i', $verOut, $m)) {
                            $cliInfo['serial_number'] = strtoupper(trim($m[1]));
                        }
                        // Firmware Version (V3.4.28_251209)
                        if (preg_match('/(?:Firmware\s*Version|Software\s*Version|System\s*Version|FW\s*Version)\s*[:=]\s*([^\r\n]+)/i', $verOut, $m)) {
                            $cliInfo['firmware'] = trim($m[1]);
                        }
                        // Inband MAC Address (D0:5F:AF:E0:6B:EB)
                        if (preg_match('/(?:Inband\s*MAC|MAC\s*Address|Base\s*MAC)\s*[:=]\s*([0-9A-Fa-f:]{17}|[0-9A-Fa-f\-]{17})/i', $verOut, $m)) {
                            $cliInfo['mac'] = strtoupper(str_replace('-', ':', trim($m[1])));
                        }
                        // Device Type (GPON OLT / EPON OLT)
                        if (preg_match('/(?:Device\s*Type|System\s*Type|PON\s*Type)\s*[:=]\s*([^\r\n]+)/i', $verOut, $m)) {
                            $cliInfo['pon_type'] = str_contains(strtolower($m[1]), 'epon') ? 'epon' : 'gpon';
                        }
                    }
                } catch (\Throwable $cliErr) {
                    \Illuminate\Support\Facades\Log::debug("CData CLI show version error: " . $cliErr->getMessage());
                }
            }

            $cleanStr = function($v) {
                if (!$v) return null;
                $v = preg_replace('/^(STRING|Hex-STRING|OctetString|Hex)\s*:?\s*/i', '', trim((string)$v));
                $v = trim($v, '" \r\n\t');
                return (!empty($v) && !str_contains(strtolower($v), 'no such')) ? $v : null;
            };

            // 2. LAYER 2: SNMP Enterprise OID C-Data (GPON & EPON MIB)
            $cdataModel = $cliInfo['model'] ?? ($cleanStr(
                $this->snmp->get('.1.3.6.1.4.1.34592.1.3.100.1.8.1.0')
                ?: ($this->snmp->get('.1.3.6.1.4.1.34592.1.5.1.1.2.17.1.1.1.1')
                ?: ($this->snmp->get('.1.3.6.1.4.1.34592.1.3.1.1.1.0')
                ?: ($this->snmp->get('.1.3.6.1.4.1.51810.1.1.1.1.0') ?: null)))
            ));

            $cdataSn = $cliInfo['serial_number'] ?? ($cleanStr(
                $this->snmp->get('.1.3.6.1.4.1.34592.1.3.100.1.8.4.0')
                ?: ($this->snmp->get('.1.3.6.1.4.1.34592.1.5.1.1.2.17.1.1.4.1')
                ?: ($this->snmp->get('.1.3.6.1.4.1.34592.1.3.1.1.4.0')
                ?: ($this->snmp->get('.1.3.6.1.2.1.47.1.1.1.1.11.1')
                ?: ($this->snmp->get('.1.3.6.1.4.1.51810.1.1.1.4.0') ?: null))))
            ));

            $cdataFw = $cliInfo['firmware'] ?? ($cleanStr(
                $this->snmp->get('.1.3.6.1.4.1.34592.1.3.100.1.8.3.0')
                ?: ($this->snmp->get('.1.3.6.1.4.1.34592.1.5.1.1.2.17.1.1.3.1')
                ?: ($this->snmp->get('.1.3.6.1.4.1.34592.1.3.1.1.3.0')
                ?: ($this->snmp->get('.1.3.6.1.4.1.51810.1.1.1.3.0') ?: null)))
            ));

            $descr = (string)($info['description'] ?? '');
            if (!$cdataModel && preg_match('/(FD\d{4}[A-Z0-9\-_]*|CD\d{4}[A-Z0-9\-_]*)/i', $descr, $m)) {
                $cdataModel = strtoupper($m[1]);
            }

            // Jika firmware terbaca angka murni pendek (misal '680'), ambil dari sysDescr yang memuat versi lengkap
            if (empty($cdataFw) || preg_match('/^\d{2,4}$/', $cdataFw)) {
                if (preg_match('/(?:Version|Ver|V|FW|Firmware)\s*[:=]?\s*([vV\d\._-]+)/i', $descr, $m)) {
                    $cdataFw = $m[1];
                }
            }

            if ($cdataModel && ($info['model'] === 'olt' || empty($info['model']) || strtolower($info['model']) === 'c-data')) {
                $info['model'] = $cdataModel;
            }
            if (!empty($cdataSn)) {
                $info['serial_number'] = strtoupper($cdataSn);
            }
            if ($cdataFw && ($info['firmware'] === 'N/A' || empty($info['firmware']) || preg_match('/^\d{2,4}$/', (string)$info['firmware']))) {
                $info['firmware'] = $cdataFw;
            }
            if (!empty($cliInfo['pon_type'])) {
                $info['pon_type'] = $cliInfo['pon_type'];
            }
        } catch (\Throwable) {}

        return $info;
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
                        // Pada C-Data GPON pizza box (FD1608/FD1604/FD1616), byte 2 menandakan tipe PON (bukan slot 2).
                        // Standar port board adalah slot 1 (0/1/1 s.d 0/1/8).
                        if ($slot === 0 || $slot === 2) {
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

    public function getPonPortTxPower(int $ponPort): ?float
    {
        try {
            $raw = $this->snmp->get('.1.3.6.1.4.1.34592.1.3.100.2.2.1.3.' . $ponPort);
            if ($raw && is_numeric($raw)) {
                $val = (float)$raw;
                if ($val > 100) return round($val / 100, 2);
                if ($val > 0) return round($val, 2);
            }
        } catch (\Throwable) {}

        return null;
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
        } elseif ($ponPort >= 17 && $ponPort <= 32) {
            // C-Data standard: ifIndex 17-32 are PON ports 1-16 (ports 1-16 are GE/10GE uplink)
            $portNum = $ponPort - 16;
            $slot = 1;
        } else {
            $portNum = $ponPort;
            $slot = 0;
        }
        if ($portNum <= 0 || ($this->olt->pon_port_count ?? 0) === 1) {
            $portNum = 1;
        }

        // 2. LAYER 1: Scrape via Telnet CLI (Fast, full signals & serial numbers)
        if ($this->cachedCliParsedOnus === null) {
            $this->cachedCliParsedOnus = [];
            $this->scrapeCliOnus($slot);
        }

        // Check target port in CLI cache (supports both raw $ponPort, normalized $portNum, and slot-shifted)
        $targetOnus = $this->cachedCliParsedOnus[$portNum] 
            ?? ($this->cachedCliParsedOnus[$ponPort] ?? null)
            ?? ($ponPort > 16 ? ($this->cachedCliParsedOnus[$ponPort - 16] ?? null) : null);

        // Fallback: If only 1 port has parsed ONUs in CLI cache and this is the first PON port (e.g. 1 or 17)
        if (empty($targetOnus) && !empty($this->cachedCliParsedOnus) && ($portNum === 1 || $ponPort === 17 || $ponPort === 1)) {
            $firstKey = array_key_first($this->cachedCliParsedOnus);
            if ($firstKey !== null) {
                $targetOnus = $this->cachedCliParsedOnus[$firstKey];
            }
        }

        if (!empty($targetOnus)) {
            $res = [];
            $missingOpticalCount = 0;
            foreach ($targetOnus as $item) {
                $item['pon_port'] = $ponPort;
                if ($item['rx_power_dbm'] === null && ($item['status'] ?? 'offline') === 'online') {
                    $missingOpticalCount++;
                }
                $res[] = $item;
            }

            if ($missingOpticalCount > 0) {
                $this->enrichOpticalFromSnmp($res, $ponPort, $portNum);
            }

            return $res;
        }

        if (($this->olt->pon_port_count ?? 0) === 1 && !empty($this->cachedCliParsedOnus)) {
            if ($portNum !== 1) {
                return [];
            }
            $res = [];
            $missingOpticalCount = 0;
            foreach ($this->cachedCliParsedOnus as $pOnus) {
                foreach ($pOnus as $item) {
                    $item['pon_port'] = $ponPort;
                    if ($item['rx_power_dbm'] === null && ($item['status'] ?? 'offline') === 'online') {
                        $missingOpticalCount++;
                    }
                    $res[] = $item;
                }
            }
            if (!empty($res)) {
                if ($missingOpticalCount > 0) {
                    $this->enrichOpticalFromSnmp($res, $ponPort, $portNum);
                }
                return $res;
            }
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

            // Nonaktifkan pagination agar OLT mengirim seluruh data ONU tanpa jeda --More--
            try {
                $this->cli->execute("terminal length 0\r\n", false);
                $this->cli->execute("terminal page 0\r\n", false);
                $this->cli->execute("terminal page disable\r\n", false);
                $this->cli->execute("terminal pagination disable\r\n", false);
                $this->cli->execute("no terminal page\r\n", false);
                $this->cli->execute("terminal width 0\r\n", false);
            } catch (\Throwable) {}

            // Attempt 1: Direct commands (supports 0/1/1, 0/0/1, slot 1 and slot 0)
            $infoOut = $this->cli->execute("show ont info all\r\n", false);
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show ont info 0/1 all\r\n", false);
            }
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show ont info 0/1/1 all\r\n", false);
            }
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show ont info 0/1 1 all\r\n", false);
            }
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show ont info 1 all\r\n", false);
            }
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show ont info 0/0 1 all\r\n", false);
            }
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show ont info 0/0/1 all\r\n", false);
            }
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show onu info all\r\n", false);
            }
            if ($isBad($infoOut)) {
                $infoOut = $this->cli->execute("show onu info 1 all\r\n", false);
            }

            $optOut = $this->cli->execute("show ont optical-info all\r\n", false);
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show ont optical-info 0/1 all\r\n", false);
            }
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show ont optical-info 0/1/1 all\r\n", false);
            }
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show ont optical-info 0/1 1 all\r\n", false);
            }
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show ont optical-info 1 all\r\n", false);
            }
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show ont optical-info 0/0 1 all\r\n", false);
            }
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show ont optical-info 0/0/1 all\r\n", false);
            }
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show onu optical-info all\r\n", false);
            }
            if ($isBad($optOut)) {
                $optOut = $this->cli->execute("show onu optical-info 1 all\r\n", false);
            }

            // Attempt 2: Interface-level commands (standard C-Data requirement on older/modular firmware)
            if ($isBad($infoOut) || $isBad($optOut)) {
                $this->cli->execute("config\r\n", false);
                $inGpon = $this->cli->execute("interface gpon 0/{$slot}\r\n", false);
                if ($isBad($inGpon)) {
                    $inGpon = $this->cli->execute("interface gpon 0/1\r\n", false);
                }
                if ($isBad($inGpon)) {
                    $inGpon = $this->cli->execute("interface gpon 0/0\r\n", false);
                }

                if (!$isBad($inGpon)) {
                    if ($isBad($infoOut)) {
                        $infoOut = $this->cli->execute("show ont info all\r\n", false);
                        if ($isBad($infoOut)) {
                            $infoOut = $this->cli->execute("show ont info 1 all\r\n", false);
                        }
                        if ($isBad($infoOut)) {
                            $infoOut = $this->cli->execute("show ont info 0/0 1 all\r\n", false);
                        }
                    }
                    if ($isBad($optOut)) {
                        $optOut = $this->cli->execute("show ont optical-info all\r\n", false);
                        if ($isBad($optOut)) {
                            $optOut = $this->cli->execute("show ont optical-info 1 all\r\n", false);
                        }
                    }
                } else {
                    $inEpon = $this->cli->execute("interface epon 0/{$slot}\r\n", false);
                    if ($isBad($inEpon)) {
                        $inEpon = $this->cli->execute("interface epon 0/0\r\n", false);
                    }
                    if (!$isBad($inEpon)) {
                        if ($isBad($infoOut)) {
                            $infoOut = $this->cli->execute("show onu info all\r\n", false);
                            if ($isBad($infoOut)) {
                                $infoOut = $this->cli->execute("show onu info 1 all\r\n", false);
                            }
                        }
                        if ($isBad($optOut)) {
                            $optOut = $this->cli->execute("show onu optical-info all\r\n", false);
                            if ($isBad($optOut)) {
                                $optOut = $this->cli->execute("show onu optical-info 1 all\r\n", false);
                            }
                        }
                    }
                }
                $this->cli->execute("exit\r\n", false);
                $this->cli->execute("exit\r\n", false);
            }

            // Parse show ont info lines
            $cleanInfo = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$infoOut);
            foreach (explode("\n", $cleanInfo) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, 'F/S') || str_starts_with($line, 'Slot') || str_starts_with($line, 'Port') || str_starts_with($line, 'ONT') || str_starts_with($line, 'No') || str_starts_with($line, 'Total:') || str_starts_with($line, 'show') || str_contains($line, '#')) {
                    continue;
                }

                // Match SN (4 letter vendor + 8-12 hex) OR MAC address
                if (preg_match('/([A-Z0-9]{4}[A-Z0-9]{8,12}|(?:[A-F0-9]{2}[:-]){5}[A-F0-9]{2})/i', $line, $snMatch)) {
                    $sn = strtoupper($snMatch[1]);
                    $pos = strpos($line, $snMatch[1]);
                    $before = trim(substr($line, 0, $pos));
                    $after = trim(substr($line, $pos + strlen($snMatch[1])));

                    // Robust token & regex match untuk mendeteksi status Online vs Offline di CLI C-DATA:
                    // Header C-Data GPON: [F/S/P] [ONT-ID] [SN] [Desc] [Control flag] [Run state] [Config state] [Match state] [Last down-cause]
                    // Contoh line: 0/1/1  1  ZTEGC85E2D31  N/A  Active  Online  success  match  dying-gasp
                    // Atau:        0/1 1  1  ZTEGC85E2D31  Active  Online  success  match  dying-gasp
                    // PERHATIAN: Kolom "Last down-cause" bisa berisi "dying-gasp" atau "power-off" dari riwayat lama.
                    // Jangan sampai "dying-gasp" di riwayat meng-overwrite status aktif "Online"!
                    $afterLower = strtolower($after);
                    $afterTokens = preg_split('/\s+/', trim($after));

                    $isCliOnline = false;
                    // Cek token-token awal setelah SN (biasanya token ke-0, 1, 2, atau 3 berisi run state)
                    foreach (array_slice($afterTokens, 0, 4) as $tok) {
                        $tokLower = strtolower(trim($tok));
                        if (in_array($tokLower, ['online', 'up', 'working', 'operation', 'operational', 'o5', 'connected', 'ok'], true)) {
                            $isCliOnline = true;
                            break;
                        }
                    }

                    if (!$isCliOnline) {
                        // Regex match untuk memastikan ada kata "online" atau "o5" sebelum kata status offline/down-cause
                        if (preg_match('/\b(online|working|operation|operational|o5)\b/i', $afterLower)) {
                            $isCliOnline = true;
                        }
                    }

                    // Cek jika memang baris tersebut jelas-jelas Offline
                    if (!$isCliOnline) {
                        foreach (array_slice($afterTokens, 0, 4) as $tok) {
                            $tokLower = strtolower(trim($tok));
                            if (in_array($tokLower, ['offline', 'down', 'inactive', 'deactive', 'lost', 'standby', 'unregistered'], true)) {
                                $isCliOnline = false;
                                break;
                            }
                        }
                    }

                    $runState = $isCliOnline ? 'online' : 'offline';

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
                    } elseif (count($tokens) === 1 && is_numeric($tokens[0])) {
                        $lPort = (($this->olt->pon_port_count ?? 0) === 1) ? 1 : 1;
                        $lOnuId = (int)$tokens[0];
                    } elseif (preg_match('/(\d+)[:\s]+(\d+)$/', $before, $m)) {
                        $lPort = (int)$m[1];
                        $lOnuId = (int)$m[2];
                    }

                    if (($this->olt->pon_port_count ?? 0) === 1) {
                        $lPort = 1;
                        if ($lOnuId === null || $lOnuId <= 0) {
                            $lOnuId = count($this->cachedCliParsedOnus[1] ?? []) + 1;
                        }
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
                            'distance_meters'  => null,
                            'firmware_version' => null,
                            'model'            => null,
                            'status'           => $runState,
                        ];
                    }
                }
            }

            // Build fast lookup map: serial_number => [port, onu_id]
            $snMap = [];
            foreach ($this->cachedCliParsedOnus as $p => $onus) {
                foreach ($onus as $oId => $oData) {
                    if (!empty($oData['serial_number'])) {
                        $snMap[strtoupper($oData['serial_number'])] = ['port' => (int)$p, 'onu_id' => (int)$oId];
                    }
                    if (!empty($oData['mac_address'])) {
                        $cleanM = strtoupper(str_replace('-', ':', $oData['mac_address']));
                        $snMap[$cleanM] = ['port' => (int)$p, 'onu_id' => (int)$oId];
                    }
                }
            }

            // Parse show ont optical-info lines helper
            $parseOptLines = function(string $rawOpt, ?int $defaultPort = null) use (&$snMap) {
                $cleanOpt = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', $rawOpt);
                foreach (explode("\n", $cleanOpt) as $line) {
                    $line = trim($line);
                    if (empty($line) 
                        || str_starts_with($line, '-') 
                        || str_starts_with($line, '=') 
                        || str_starts_with($line, 'F/S') 
                        || str_starts_with($line, 'Slot') 
                        || str_starts_with($line, 'Port') 
                        || str_starts_with($line, 'PON') 
                        || str_starts_with($line, 'ONT') 
                        || str_starts_with($line, 'ONU') 
                        || str_starts_with($line, 'ID') 
                        || str_starts_with($line, 'Total:') 
                        || str_starts_with($line, 'show') 
                        || str_starts_with($line, 'FD16') 
                        || str_starts_with($line, 'No') 
                        || str_contains($line, '#')) {
                        continue;
                    }

                    $lPort = $defaultPort;
                    $lOnuId = null;

                    $cols = preg_split('/\s+/', $line);

                    // CASE 1: In per-port query ($defaultPort !== null):
                    // First column is the ONU ID: "1 -22.21 2.16 -28.86 55.41 3.26 14.82"
                    if ($defaultPort !== null && count($cols) >= 2 && is_numeric($cols[0]) && (int)$cols[0] > 0 && !str_contains($cols[0], '/')) {
                        $lPort = $defaultPort;
                        $lOnuId = (int)$cols[0];
                    }
                    // CASE 2: Match by known Serial Number or MAC from $snMap
                    elseif (preg_match('/([A-Z0-9]{4}[A-Z0-9]{8,12}|(?:[A-F0-9]{2}[:-]){5}[A-F0-9]{2})/i', $line, $snMatch)) {
                        $matchedSn = strtoupper(str_replace('-', ':', $snMatch[1]));
                        if (isset($snMap[$matchedSn])) {
                            $lPort = $snMap[$matchedSn]['port'];
                            $lOnuId = $snMap[$matchedSn]['onu_id'];
                        } elseif (isset($snMap[str_replace(':', '', $matchedSn)])) {
                            $mapped = $snMap[str_replace(':', '', $matchedSn)];
                            $lPort = $mapped['port'];
                            $lOnuId = $mapped['onu_id'];
                        }
                    }

                    // CASE 3: Multi-port format: "0/1/1 1 -20.71", "0/0/2 1", "0/2 1", or "0/0 2 1"
                    if ($lPort === null || $lOnuId === null) {
                        if (str_contains($cols[0], '/')) {
                            $pParts = explode('/', $cols[0]);
                            // Format 0/1/1 1 -20.71... (F/S/P lengkap di cols[0], ONU ID di cols[1])
                            if (count($pParts) >= 3 && is_numeric(end($pParts)) && isset($cols[1]) && is_numeric($cols[1]) && (int)$cols[1] > 0 && !str_contains($cols[1], '.')) {
                                $lPort = (int)end($pParts);
                                $lOnuId = (int)$cols[1];
                            }
                            // Format 0/0 2 1 -20.71... (Slot di cols[0], Port di cols[1], ONU ID di cols[2])
                            // Hanya berlaku jika cols[2] bilangan bulat positif tanpa titik desimal/minus!
                            elseif (count($pParts) === 2 && count($cols) >= 3 && is_numeric($cols[1]) && is_numeric($cols[2]) && !str_contains($cols[2], '.') && (int)$cols[2] > 0) {
                                $lPort = (int)$cols[1];
                                $lOnuId = (int)$cols[2];
                            }
                            // Format 0/2 1 -20.71... (Port di akhir cols[0], ONU ID di cols[1])
                            elseif (count($cols) >= 2 && is_numeric($cols[1]) && (int)$cols[1] > 0 && !str_contains($cols[1], '.')) {
                                $lPort = (int)end($pParts);
                                $lOnuId = (int)$cols[1];
                            }
                        } elseif (preg_match('/^(?:gpon|epon|pon)?\s*(?:0\/0\/|0\/)?(\d+)[\s:\/]+(\d+)\b/i', $line, $pm)) {
                            $lPort = (int)$pm[1];
                            $lOnuId = (int)$pm[2];
                        } elseif ($defaultPort !== null && count($cols) >= 2 && is_numeric($cols[0])) {
                            $lPort = $defaultPort;
                            $lOnuId = (int)$cols[0];
                        }
                    }

                    if (($this->olt->pon_port_count ?? 0) === 1) {
                        $lPort = 1;
                        if ($lOnuId === null && preg_match('/^(\d+)\b/', $line, $firstNum)) {
                            $lOnuId = (int)$firstNum[1];
                        }
                    }

                    if ($lPort === null || $lOnuId === null || !isset($this->cachedCliParsedOnus[$lPort][$lOnuId])) {
                        continue;
                    }

                    // Extract all numeric floats from the line (handling optional (dBm), (V), (C) units)
                    $tokens = preg_split('/\s+/', $line);
                    $floats = [];
                    foreach ($tokens as $idx => $tok) {
                        $tok = trim($tok);
                        if ($tok === '' || $tok === '--' || $tok === 'N/A' || $tok === '-' || $tok === 'null') {
                            continue;
                        }
                        if (preg_match('/^[-+]?\d+(?:\.\d+)?/', $tok, $fm)) {
                            $floats[] = [
                                'raw_idx' => $idx,
                                'val'     => (float)$fm[0],
                            ];
                        }
                    }

                    // Optical Rx power: In optical networks, Rx is ALWAYS negative dBm, typically -45.0 to -5.0
                    $detectedRx = null;
                    $detectedTx = null;
                    $detectedTemp = null;
                    $rxFloatIndex = null;

                    foreach ($floats as $fIdx => $fData) {
                        $val = $fData['val'];
                        // Skip ONU ID or Port if it happened to be in floats
                        if ($fIdx === 0 && ($val == $lPort || $val == $lOnuId)) {
                            continue;
                        }
                        if ($fIdx === 1 && $val == $lOnuId) {
                            continue;
                        }
                        // Distance can be a large integer like 1310 or 0
                        if ($val >= -45.0 && $val <= -5.0 && $rxFloatIndex === null) {
                            $detectedRx = $val;
                            $rxFloatIndex = $fIdx;
                            break;
                        }
                    }

                    // Optical Tx power: Usually the float immediately following Rx Power, typically -5.0 to +12.0 dBm
                    if ($rxFloatIndex !== null) {
                        if (isset($floats[$rxFloatIndex + 1])) {
                            $nextVal = $floats[$rxFloatIndex + 1]['val'];
                            if ($nextVal >= -5.0 && $nextVal <= 12.0) {
                                $detectedTx = $nextVal;
                            }
                        }
                    }

                    // Temperature: In C-Data optical tables, Temperature is typically between 15.0 and 85.0 °C
                    // Usually the last float in the line
                    if (!empty($floats)) {
                        $lastFloat = end($floats)['val'];
                        if ($lastFloat >= 15.0 && $lastFloat <= 90.0 && $lastFloat != $detectedRx && $lastFloat != $detectedTx) {
                            $detectedTemp = $lastFloat;
                        }
                    }

                    // Also support Key-Value formats (e.g. Rx Optical Power: -16.86 dBm)
                    if ($detectedRx === null && preg_match('/rx.*?power.*?:?\s*([-+]?\d+(?:\.\d+)?)/i', $line, $km)) {
                        $v = (float)$km[1];
                        if ($v >= -45.0 && $v <= 10.0 && $v != 0.0) $detectedRx = $v;
                    }
                    if ($detectedTx === null && preg_match('/tx.*?power.*?:?\s*([-+]?\d+(?:\.\d+)?)/i', $line, $km)) {
                        $v = (float)$km[1];
                        if ($v >= -10.0 && $v <= 25.0 && $v != 0.0) $detectedTx = $v;
                    }
                    if ($detectedTemp === null && preg_match('/temp.*?:?\s*([-+]?\d+(?:\.\d+)?)/i', $line, $km)) {
                        $v = (float)$km[1];
                        if ($v > 0.0 && $v < 120.0) $detectedTemp = $v;
                    }

                    if ($detectedRx !== null) {
                        $this->cachedCliParsedOnus[$lPort][$lOnuId]['rx_power_dbm'] = $detectedRx;
                        if ($detectedRx >= -38.0 && $detectedRx <= -7.0) {
                            $this->cachedCliParsedOnus[$lPort][$lOnuId]['status'] = 'online';
                        }
                    }
                    if ($detectedTx !== null) {
                        $this->cachedCliParsedOnus[$lPort][$lOnuId]['tx_power_dbm'] = $detectedTx;
                    }
                    if ($detectedTemp !== null) {
                        $this->cachedCliParsedOnus[$lPort][$lOnuId]['temperature'] = $detectedTemp;
                    }

                    // Extract RTT Distance in meters if exposed
                    $detectedDist = null;
                    if (preg_match('/(?:distance|dist).*?:?\s*(\d+)/i', $line, $dm)) {
                        $detectedDist = (int)$dm[1];
                    } else {
                        foreach ($floats as $fIdx => $fData) {
                            $fVal = $fData['val'];
                            if ($fVal >= 20 && $fVal <= 40000 && $fIdx !== 0 && $fIdx !== 1 && $fIdx !== $rxFloatIndex) {
                                if (floor($fVal) == $fVal && $fVal > 50) {
                                    $detectedDist = (int)$fVal;
                                    break;
                                }
                            }
                        }
                    }
                    if ($detectedDist !== null) {
                        $this->cachedCliParsedOnus[$lPort][$lOnuId]['distance_meters'] = $detectedDist;
                    }
                }
            };

            // Parse global optical output if already captured in Attempt 1 or Attempt 2
            if (!empty($optOut) && !$isBad($optOut)) {
                $parseOptLines($optOut);
            } else {
                $tryOpt = $this->cli->execute("show ont optical-info all\r\n", false);
                if (!$isBad($tryOpt)) {
                    $parseOptLines($tryOpt);
                } else {
                    $tryOpt2 = $this->cli->execute("show onu optical-info all\r\n", false);
                    if (!$isBad($tryOpt2)) {
                        $parseOptLines($tryOpt2);
                    }
                }
            }

            // 1. Parse MAC Address Dynamic globally (Supports both FD1608S and FD1602S formats)
            $macOut = $this->cli->execute("show mac-address dynamic\r\n", false);
            if (!$isBad($macOut)) {
                $cleanMac = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$macOut);
                foreach (explode("\n", $cleanMac) as $mLine) {
                    $mLine = trim($mLine);
                    // Matches both:
                    // Format A: 28:6D:DA:7C:1B:D9 99 - 113 gpon0/0/3 36 1 dynamic
                    // Format B: 6C:D7:19:75:9F:02 99 gpon0/0/1 35 1 dynamic
                    if (preg_match('/([0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}).*?(?:gpon|epon)(?:0\/0\/|\/)?(\d+)[\s:]+(\d+)/i', $mLine, $mM)) {
                        $mMac = strtoupper(str_replace('-', ':', $mM[1]));
                        $mPort = (int)$mM[2];
                        $mOnu = (int)$mM[3];
                        if ($mPort > 0 && $mOnu > 0 && isset($this->cachedCliParsedOnus[$mPort][$mOnu])) {
                            $this->cachedCliParsedOnus[$mPort][$mOnu]['mac_address'] = $mMac;
                        }
                    }
                }
            }

            // 1b. Parse ARP Dynamic globally (Extracts IP and MAC mapped to PON port and ONU ID)
            $arpOut = $this->cli->execute("show arp dynamic\r\n", false);
            if (!$isBad($arpOut)) {
                $cleanArp = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', (string)$arpOut);
                foreach (explode("\n", $cleanArp) as $aLine) {
                    $aLine = trim($aLine);
                    // Example: 192.168.155.56 84:06:FA:22:D9:42 99 gpon 0/0/1:50 Dynamic 594
                    if (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3})\s+([0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}).*?(?:gpon|epon)\s*(?:0\/0\/|\/)?(\d+)[:\s]+(\d+)/i', $aLine, $aM)) {
                        $aIp = $aM[1];
                        $aMac = strtoupper(str_replace('-', ':', $aM[2]));
                        $aPort = (int)$aM[3];
                        $aOnu = (int)$aM[4];

                        if ($aPort > 0 && $aOnu > 0 && isset($this->cachedCliParsedOnus[$aPort][$aOnu])) {
                            $this->cachedCliParsedOnus[$aPort][$aOnu]['mac_address'] = $aMac;
                            $this->cachedCliParsedOnus[$aPort][$aOnu]['ip_address'] = $aIp;
                        }
                    }
                }
            }

            // 2. Query per port optical info ONLY for ports where online ONUs actually miss optical signal
            $configuredPonType = strtolower(trim((string)($this->olt->pon_type ?? 'auto')));
            $modelStr = strtolower((string)($this->olt->model ?? '') . ' ' . (string)($this->olt->name ?? ''));
            $isExplicitEpon = ($configuredPonType === 'epon') || ($configuredPonType === 'auto' && (str_contains($modelStr, 'epon') || str_contains($modelStr, 'fd11') || str_contains($modelStr, 'fd12')));
            $isExplicitGpon = ($configuredPonType === 'gpon') || ($configuredPonType === 'auto' && (str_contains($modelStr, 'gpon') || str_contains($modelStr, 'fd16') || str_contains($modelStr, 'cd16')));

            $missingPorts = [];
            foreach ($this->cachedCliParsedOnus as $p => $onus) {
                foreach ($onus as $o) {
                    if (($o['status'] ?? 'offline') === 'online' && $o['rx_power_dbm'] === null) {
                        $missingPorts[] = (int)$p;
                        break;
                    }
                }
            }

            if (!empty($missingPorts)) {
                $this->cli->execute("config\r\n", false);
                $inInterface = false;

                if (!$isExplicitEpon) {
                    $inGpon = $this->cli->execute("interface gpon 0/{$slot}\r\n", false);
                    if ($isBad($inGpon)) {
                        $inGpon = $this->cli->execute("interface gpon 0/1\r\n", false);
                    }
                    if ($isBad($inGpon)) {
                        $inGpon = $this->cli->execute("interface gpon 0/0\r\n", false);
                    }
                    $inInterface = !$isBad($inGpon);
                }

                if (!$inInterface && !$isExplicitGpon) {
                    $inEpon = $this->cli->execute("interface epon 0/{$slot}\r\n", false);
                    if ($isBad($inEpon)) {
                        $inEpon = $this->cli->execute("interface epon 0/0\r\n", false);
                    }
                    if ($isBad($inEpon)) {
                        $inEpon = $this->cli->execute("interface epon 0/1\r\n", false);
                    }
                    $inInterface = !$isBad($inEpon);
                }

                foreach ($missingPorts as $pNum) {
                    $pOpt = null;
                    if (!$isExplicitEpon) {
                        $pOpt = $this->cli->execute("show ont optical-info {$pNum} all\r\n", false);
                        if ($isBad($pOpt)) {
                            $pOpt = $this->cli->execute("show ont optical-info 0/1 {$pNum} all\r\n", false);
                        }
                        if ($isBad($pOpt)) {
                            $pOpt = $this->cli->execute("show ont optical-info 0/0 {$pNum} all\r\n", false);
                        }
                    }

                    if (($pOpt === null || $isBad($pOpt)) && !$isExplicitGpon) {
                        $pOpt = $this->cli->execute("show onu optical-info {$pNum} all\r\n", false);
                        if ($isBad($pOpt)) {
                            $pOpt = $this->cli->execute("show onu optical-info 0/0 {$pNum} all\r\n", false);
                        }
                    }

                    if (!empty($pOpt) && !$isBad($pOpt)) {
                        $parseOptLines($pOpt, (int)$pNum);
                    }
                }

                if ($inInterface) {
                    $this->cli->execute("exit\r\n", false);
                    $this->cli->execute("exit\r\n", false);
                } else {
                    $this->cli->execute("exit\r\n", false);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("CData OLT Telnet CLI sync failed: " . $e->getMessage() . ". Falling back to SNMP.");
        }
    }

    protected function enrichOpticalFromSnmp(array &$onus, int $ponPort, int $portNum): void
    {
        try {
            $snmpOnus = $this->scrapeSnmpOnus($ponPort, $portNum);
            if (empty($snmpOnus)) {
                return;
            }

            $bySn = [];
            $byIndex = [];
            foreach ($snmpOnus as $so) {
                if (!empty($so['serial_number'])) {
                    $bySn[strtoupper($so['serial_number'])] = $so;
                }
                if (!empty($so['mac_address'])) {
                    $bySn[strtoupper(str_replace('-', ':', $so['mac_address']))] = $so;
                }
                if (!empty($so['onu_index'])) {
                    $byIndex[(int)$so['onu_index']] = $so;
                }
            }

            foreach ($onus as &$o) {
                if ($o['rx_power_dbm'] !== null) {
                    continue;
                }
                $sn = strtoupper($o['serial_number'] ?? '');
                $mac = strtoupper(str_replace('-', ':', $o['mac_address'] ?? ''));
                $idx = (int)($o['onu_index'] ?? 0);

                $match = $bySn[$sn] ?? ($bySn[$mac] ?? ($byIndex[$idx] ?? null));
                if ($match) {
                    if ($match['rx_power_dbm'] !== null) {
                        $o['rx_power_dbm'] = $match['rx_power_dbm'];
                    }
                    if ($match['tx_power_dbm'] !== null) {
                        $o['tx_power_dbm'] = $match['tx_power_dbm'];
                    }
                    if ($match['temperature'] !== null) {
                        $o['temperature'] = $match['temperature'];
                    }
                }
            }
            unset($o);
        } catch (\Throwable $e) {
            Log::debug("CData enrichOpticalFromSnmp exception: " . $e->getMessage());
        }
    }

    protected function scrapeSnmpOnus(int $ponPort, int $portNum): array
    {
        // Cache all SNMP walk tables once per polling cycle
        if ($this->cachedOnuSerials === null) {
            $configuredPonType = strtolower(trim((string)($this->olt->pon_type ?? 'auto')));
            $modelStr = strtolower((string)($this->olt->model ?? '') . ' ' . (string)($this->olt->name ?? ''));
            $isExplicitEpon = ($configuredPonType === 'epon') || ($configuredPonType === 'auto' && (str_contains($modelStr, 'epon') || str_contains($modelStr, 'fd11') || str_contains($modelStr, 'fd12')));
            $isExplicitGpon = ($configuredPonType === 'gpon') || ($configuredPonType === 'auto' && (str_contains($modelStr, 'gpon') || str_contains($modelStr, 'fd16') || str_contains($modelStr, 'cd16')));

            $gponSerials = [];
            $gponActive = [];
            $gponRunStatus = [];
            $gponRx = [];
            $gponTx = [];
            $gponTemps = [];

            // A. GPON SNMP TABLES (hanya dipindai jika bukan EPON murni)
            if (!$isExplicitEpon) {
                // 1. gponOnuSn (.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.3 - Official CDATA-GPON-MIB)
                $gponSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.3') ?: [];

                // 2. gponOnuBindSn (.18.10.1.1.2)
                if (empty($gponSerials)) {
                    $gponSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.10.1.1.2') ?: [];
                }

                // 3. FD1608/FD1616 legacy OID (.100.9.2.1.3 or .100.9.2.1.2)
                if (empty($gponSerials)) {
                    $gponSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.3')
                        ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.2') ?: []);
                }

                // 4. Autofind OID (.18.2.1.1.2) - also merge any unconfigured ONUs on the line
                $autofindSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.2.1.1.2')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.2.1.1.1') ?: []);

                if (!empty($autofindSerials)) {
                    foreach ($autofindSerials as $afKey => $afVal) {
                        if (!isset($gponSerials[$afKey])) {
                            $gponSerials[$afKey] = $afVal;
                        }
                    }
                }

                // 5. Fallbacks
                if (empty($gponSerials)) {
                    $gponSerials = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.4')
                        ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.1')
                        ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.4.1.1.2')
                        ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.1.1.2') ?: [])));
                }
            }
            
            // GPON Active State (1 = active/online, 2 = inactive/offline)
            $gponActive = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.7')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.10.1.1.3')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.7') ?: []))) : [];

            // GPON Run Status:
            // 1. gponOnuOperStatus (.18.1.1.8 - Official CDATA-GPON-MIB: 1=online, 5=operation/O5/ONLINE, 101=online)
            // 2. gponOnuBindOnlineStatus (.18.10.1.1.4: 1=online, 2=offline)
            // 3. FD1608/FD1616 legacy OID (.100.9.2.1.8 or .100.9.2.1.13)
            // 4. gponOnuAutoFindTable (.18.2.1.1 fallback)
            $gponRunStatus = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.8')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.10.1.1.4')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.8')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.13')
                ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.2.1.1') ?: []))))) : [];

            // C-Data GPON Bind Status (.18.10.1.1.4: 1=online, 2=offline)
            // Beberapa firmware FD1608/FD1616 menyimpan status online nyata pada bind table ini
            $gponBindStatus = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.10.1.1.4') ?: []) : [];
            if (!empty($gponBindStatus)) {
                foreach ($gponBindStatus as $bKey => $bVal) {
                    $cleanBVal = trim(preg_replace('/^(INTEGER|Gauge32|Counter32|Hex-STRING|STRING|OctetString|Hex)\s*:?\s*/i', '', (string)$bVal), "\" \r\n\t");
                    if ($cleanBVal === '1' || strtolower($cleanBVal) === 'online' || strtolower($cleanBVal) === 'up') {
                        $gponRunStatus[$bKey] = 5;
                    }
                }
            }
            
            // GPON Direct Optical Table:
            // gponOnuOpticalInfoTable (.18.6.1 - Official CDATA-GPON-MIB: .3=RxPower, .2=TxPower, .4=Temperature)
            // gponOnuBindRxPower (.18.10.1.1.7)
            $gponDirectRx = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.6.1.3') ?: []) : [];
            $gponBindRx   = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.10.1.1.7') ?: []) : [];
            if (!empty($gponBindRx)) {
                foreach ($gponBindRx as $bKey => $bVal) {
                    if (!isset($gponDirectRx[$bKey])) {
                        $gponDirectRx[$bKey] = $bVal;
                    }
                }
            }
            $gponDirectTx = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.6.1.2') ?: []) : [];
            $gponDirectTemp = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.6.1.4') ?: []) : [];

            // GPON Optical Collect Table (.18.19.1.2 - CDATA OctetString holding Rx, Tx, Temp)
            // Hanya walk tabel optical collect berat ini jika direct OID belum mengembalikan data
            $gponRx = $gponDirectRx;
            $gponTx = $gponDirectTx;
            $gponTemps = $gponDirectTemp;

            if (empty($gponRx)) {
                $opticalCollect = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.19.1.2')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.19.1.1.2') ?: [])) : [];

                if (!empty($opticalCollect)) {
                    foreach ($opticalCollect as $oKey => $oStr) {
                        $cleanStr = trim((string)$oStr);
                        $cleanStr = preg_replace('/^(STRING|Hex-STRING|OctetString|Hex)\s*:?\s*/i', '', $cleanStr);
                        $hexOnly = preg_replace('/[^0-9A-Fa-f]/', '', $cleanStr);
                        if (strlen($hexOnly) > 16 && strlen($hexOnly) % 2 === 0) {
                            $decoded = @hex2bin($hexOnly);
                            if ($decoded && preg_match('/[a-zA-Z]/', $decoded)) {
                                $cleanStr = $decoded;
                            }
                        }

                        if (preg_match('/rx.*?power.*?:?\s*([-+]?\d+(?:\.\d+)?)/i', $cleanStr, $rm)) {
                            $gponRx[$oKey] = (float)$rm[1];
                        } elseif (preg_match('/[-+]?\d+(?:\.\d+)?/', $cleanStr, $nm) && (float)$nm[0] < -5.0 && (float)$nm[0] > -45.0) {
                            $gponRx[$oKey] = (float)$nm[0];
                        }
                        if (preg_match('/tx.*?power.*?:?\s*([-+]?\d+(?:\.\d+)?)/i', $cleanStr, $tm)) {
                            $gponTx[$oKey] = (float)$tm[1];
                        }
                        if (preg_match('/temp.*?:?\s*([-+]?\d+(?:\.\d+)?)/i', $cleanStr, $tpm)) {
                            $gponTemps[$oKey] = (float)$tpm[1];
                        }
                    }
                }
            }

            // Numeric GPON Optical OID Fallbacks (.18.19.1.1.3 or legacy .100.9.4.1.3)
            if (empty($gponRx)) {
                $gponRx = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.19.1.1.3')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.3')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.20.1.1.3')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.9')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.4.1.3')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.3.1.3')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.4.1.1.8') ?: []))))))) : [];
            }

            if (empty($gponTx)) {
                $gponTx = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.19.1.1.4')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.4')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.20.1.1.4')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.4.1.4')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.3.1.4') ?: []))))) : [];
            }

            if (empty($gponTemps)) {
                $gponTemps = !empty($gponSerials) ? ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.19.1.1.6')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.6')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.4.1.6') ?: []))) : [];
            }

            // Check C-Data EPON (34592 / 51810 / 17409 CTC)
            $eponSerials = [];
            $eponStatuses = [];
            $eponActive = [];
            $eponRx = [];
            $eponTx = [];
            $eponTemps = [];
            $eponMacs = [];

            if (!$isExplicitGpon && empty($gponSerials)) {
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

        // Build normalized port.onu_id index maps for fast O(1) matching across any C-DATA MIB table
        $buildNormMap = function(?array $arr): array {
            $map = [];
            if (empty($arr)) return $map;
            foreach ($arr as $k => $v) {
                $clean = ltrim(preg_replace('/^.*?(\d+(?:\.\d+)+)$/', '$1', (string)$k), '.');
                $parts = explode('.', $clean);
                if (count($parts) >= 2) {
                    $onuId = (int)end($parts);
                    $rawP = (int)$parts[count($parts) - 2];
                    if ($rawP >= 1310721 && $rawP <= 1310736) {
                        $p = $rawP - 1310720;
                    } elseif ($rawP > 1000000) {
                        $p = $rawP & 0xFF;
                        if ($p === 0) $p = ($rawP >> 8) & 0xFF;
                    } elseif ($rawP > 16 && $rawP <= 32) {
                        $p = $rawP - 16;
                    } else {
                        $p = $rawP;
                    }
                    if ($p <= 0) $p = 1;
                    $map["{$p}.{$onuId}"] = $v;
                }
            }
            return $map;
        };

        $normStatuses = $buildNormMap($this->cachedOnuStatuses);
        $normActives  = $buildNormMap($this->cachedOnuActiveStates);
        $normRx       = $buildNormMap($this->cachedOnuRx);
        $normTx       = $buildNormMap($this->cachedOnuTx);
        $normTemps    = $buildNormMap($this->cachedOnuTemps);
        $normMacs     = $buildNormMap($this->cachedOnuMacs);

        // Helper to normalize and lookup OID suffixes across SNMP result arrays
        $lookupVal = function(?array $arr, string $key) {
            if (empty($arr)) return null;

            // 1. Direct match (as-is, trimmed, or with leading dot)
            if (array_key_exists($key, $arr)) return $arr[$key];
            $trimmedKey = ltrim($key, '.');
            if (array_key_exists($trimmedKey, $arr)) return $arr[$trimmedKey];
            if (array_key_exists('.' . $trimmedKey, $arr)) return $arr['.' . $trimmedKey];

            // 2. Direct normalized dot-prefix loop match
            $dotKey = '.' . $trimmedKey;
            foreach ($arr as $k => $v) {
                $dotK = '.' . ltrim((string)$k, '.');
                if ($dotK === $dotKey) return $v;
            }

            // 3. Suffix matching (e.g. comparing "0.1.5" with "1.5" or "1310721.5" with "1.5")
            $parts = explode('.', $trimmedKey);
            $cnt = count($parts);

            // Suffix 2-parts (port.onu_id) or 3-parts (slot.port.onu_id)
            for ($i = min(3, $cnt); $i >= 2; $i--) {
                $subKey = '.' . implode('.', array_slice($parts, -$i));
                foreach ($arr as $k => $v) {
                    $dotK = '.' . ltrim((string)$k, '.');
                    if (str_ends_with($dotK, $subKey) || str_ends_with($subKey, $dotK)) {
                        return $v;
                    }
                }
            }

            // Fallback for single-part ONU id if port count is 1
            if ($cnt >= 1 && ($this->olt->pon_port_count ?? 0) === 1) {
                $lastOne = '.' . end($parts);
                foreach ($arr as $k => $v) {
                    $dotK = '.' . ltrim((string)$k, '.');
                    if (str_ends_with($dotK, $lastOne)) return $v;
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

            // 1b. Text with hyphens / spaces like "HWTC-12345678"
            $noSep = preg_replace('/[\s\-_:]/', '', $raw);
            if (preg_match('/^[A-Z0-9]{4}[A-Z0-9]{8,12}$/i', $noSep) && !preg_match('/^[0-9A-Fa-f]{12}$/', $noSep)) {
                return ['sn' => strtoupper($noSep), 'mac' => null];
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
            if ($val === null || $val === '' || $val === '--' || $val === 'N/A') return null;
            if (is_string($val) && preg_match('/[-+]?[0-9]*\.?[0-9]+/', $val, $nm)) {
                $raw = (float)$nm[0];
            } else {
                $raw = (float)$val;
            }
            if ($raw == 0 || $raw <= -65535 || $raw >= 65535) return null;
            if ($raw < 0 && $raw >= -4500 && $raw <= -100) return round($raw / 100, 2);
            if ($raw >= 1000 && $raw <= 4500) return round(-($raw / 100), 2);
            if ($raw >= 100 && $raw <= 450) return round(-($raw / 10), 2);
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
            $normalizedP = ($p > 16 && $p <= 32) ? ($p - 16) : $p;
            $normalizedTarget = ($portNum > 16 && $portNum <= 32) ? ($portNum - 16) : $portNum;

            if (($this->olt->pon_port_count ?? 0) === 1) {
                $isPortMatch = ($normalizedTarget === 1);
                $p = 1;
            } elseif ($p !== null && $p > 0) {
                $isPortMatch = (
                    $p === $portNum 
                    || $p === $ponPort 
                    || $normalizedP === $normalizedTarget
                    || $p === $normalizedTarget
                    || (isset($rawP) && ($rawP === $ponPort || $rawP === $portNum))
                );
            } else {
                // If port index could not be resolved from OID, assign to port 1 only (prevent duplicating to all 8 ports)
                $isPortMatch = ($normalizedTarget === 1);
            }

            if ($isPortMatch) {
                $targetKey = "{$p}.{$onuId}";
                $targetNormKey = "{$normalizedTarget}.{$onuId}";

                $rawAct = trim((string)($normActives[$targetKey] ?? ($normActives[$targetNormKey] ?? ($lookupVal($this->cachedOnuActiveStates, (string)$oidKey) ?? ''))));
                $rawSt  = trim((string)($normStatuses[$targetKey] ?? ($normStatuses[$targetNormKey] ?? ($lookupVal($this->cachedOnuStatuses, (string)$oidKey) ?? ''))));
                $stRaw  = strtolower($rawSt);
                $actRaw = strtolower($rawAct);
                $cleanAct  = trim(preg_replace('/^(INTEGER|Gauge32|Counter32|Hex-STRING|STRING|OctetString|Hex)\s*:?\s*/i', '', $rawAct), "\" \r\n\t");
                $cleanSt   = trim(preg_replace('/^(INTEGER|Gauge32|Counter32|Hex-STRING|STRING|OctetString|Hex)\s*:?\s*/i', '', $rawSt), "\" \r\n\t");
                $activeVal = preg_match('/\b(\d+)\b/', $cleanAct, $mAct) ? (int)$mAct[1] : (is_numeric($rawAct) ? (int)$rawAct : 0);
                $stVal     = preg_match('/\b(\d+)\b/', $cleanSt, $mSt) ? (int)$mSt[1] : (is_numeric($rawSt) ? (int)$rawSt : 0);

                if ($activeVal === 0) {
                    if (str_contains($actRaw, 'active') || str_contains($actRaw, 'online') || str_contains($actRaw, 'up') || str_contains($actRaw, 'work')) {
                        $activeVal = 1;
                    } elseif (str_contains($actRaw, 'inactive') || str_contains($actRaw, 'offline') || str_contains($actRaw, 'down')) {
                        $activeVal = 2;
                    }
                }

                if ($stVal === 0) {
                    if (str_contains($stRaw, 'online') || str_contains($stRaw, 'oper') || str_contains($stRaw, 'o5') || str_contains($stRaw, 'work') || str_contains($stRaw, 'auth') || str_contains($stRaw, 'reg')) {
                        $stVal = 5;
                    }
                }

                // 1. Cek dari CLI cache terlebih dahulu jika ada
                $cliStatus = null;
                $cliRx = null;
                $cliTx = null;
                if (!empty($this->cachedCliParsedOnus)) {
                    foreach ($this->cachedCliParsedOnus as $cPort => $cOnus) {
                        foreach ($cOnus as $cData) {
                            if (!empty($cData['serial_number']) && strtoupper($cData['serial_number']) === strtoupper($sn)) {
                                $cliStatus = $cData['status'] ?? null;
                                if (!empty($cData['rx_power_dbm'])) {
                                    $cliRx = (float)$cData['rx_power_dbm'];
                                }
                                if (!empty($cData['tx_power_dbm'])) {
                                    $cliTx = (float)$cData['tx_power_dbm'];
                                }
                                break 2;
                            }
                        }
                    }
                }

                // 2. Evaluasi status SNMP
                // In GPON:
                // - gponOnuRunState (.18.2.1.1): 1=online, 2=offline
                // - gponOnuRunStatus (.18.1.1.8 / legacy): 1=init, 5=operation (O5/ONLINE), 101=online
                // In EPON (CTC standard & C-Data MIB): 1=authenticated/up/online, 2=registered/online
                $snmpOnline = false;
                if ($stVal === 5 || $stVal === 101 || $stVal === 1) {
                    $snmpOnline = true;
                } elseif ($stVal === 2) {
                    // Di GPON (.18.2.1.1), 2 adalah offline. Namun di EPON CTC, 2 adalah registered (online).
                    $snmpOnline = empty($gponSerials) ? true : ($activeVal === 1);
                } elseif (in_array($stVal, [3, 4, 6, 7, 102], true)) {
                    $snmpOnline = false;
                } elseif (str_contains($stRaw, 'online') || str_contains($stRaw, 'oper') || str_contains($stRaw, 'o5') || str_contains($stRaw, 'up') || str_contains($stRaw, 'work') || str_contains($stRaw, 'auth') || str_contains($stRaw, 'reg')) {
                    $snmpOnline = true;
                } elseif (str_contains($stRaw, 'offline') || str_contains($stRaw, 'down') || str_contains($stRaw, 'inactive') || str_contains($stRaw, 'standby') || str_contains($stRaw, 'popup') || str_contains($stRaw, 'los') || str_contains($stRaw, 'dereg')) {
                    $snmpOnline = false;
                } else {
                    $snmpOnline = ($activeVal === 1);
                }

                // Tentukan status dasar dari CLI dan SNMP
                if ($cliStatus === 'online' || $snmpOnline) {
                    $isOnline = true;
                } elseif ($cliStatus === 'offline' && !$snmpOnline) {
                    $isOnline = false;
                } else {
                    $isOnline = $snmpOnline;
                }

                // 3. Evaluasi Redaman Optik (RX / TX)
                $rxRaw = $normRx[$targetKey] ?? ($normRx[$targetNormKey] ?? ($lookupVal($this->cachedOnuRx, (string)$oidKey) ?? 0));
                $txRaw = $normTx[$targetKey] ?? ($normTx[$targetNormKey] ?? ($lookupVal($this->cachedOnuTx, (string)$oidKey) ?? 0));
                $rxVal = $parsePower($rxRaw);
                $txVal = $parsePower($txRaw);

                if ($rxVal === null && $cliRx !== null) {
                    $rxVal = $cliRx;
                }
                if ($txVal === null && $cliTx !== null) {
                    $txVal = $cliTx;
                }

                // 4. GOLDEN RULE JARINGAN FIBER OPTIK:
                // Modem yang mati listrik/kabel putus TIDAK DAPAT memancarkan laser upstream.
                // Jika OLT mendeteksi redaman optik RX valid (-38 s.d -6 dBm), modem PASTI 100% ONLINE!
                if ($rxVal !== null && $rxVal >= -38.0 && $rxVal <= -6.0) {
                    $isOnline = true;
                }

                // Jika ONU benar-benar offline, bersihkan redaman lama agar tidak menampilkan redaman semu
                if (!$isOnline) {
                    $rxVal = null;
                    $txVal = null;
                }

                $mac = $parsed['mac'] ?? null;
                $rawMac = $normMacs[$targetKey] ?? ($normMacs[$targetNormKey] ?? ($lookupVal($this->cachedOnuMacs, (string)$oidKey) ?? null));
                if (!$mac && !empty($rawMac)) {
                    $mParsed = $cleanSnString($rawMac);
                    $mac = $mParsed['mac'] ?? null;
                }

                $tempVal = $normTemps[$targetKey] ?? ($normTemps[$targetNormKey] ?? ($lookupVal($this->cachedOnuTemps, (string)$oidKey) ?? null));
                $cleanTemp = null;
                if ($tempVal !== null && $tempVal !== '') {
                    $tFloat = (float)$tempVal;
                    if ($tFloat > 256) $tFloat = round($tFloat / 256, 1);
                    if ($tFloat > 0 && $tFloat < 120) $cleanTemp = $tFloat;
                }

                $results[] = [
                    'pon_port'         => $ponPort,
                    'onu_index'        => $onuId > 0 ? $onuId : 1,
                    'serial_number'    => $sn,
                    'mac_address'      => $mac,
                    'rx_power_dbm'     => $rxVal,
                    'tx_power_dbm'     => $txVal,
                    'snr_db'           => null,
                    'temperature'      => $cleanTemp,
                    'firmware_version' => null,
                    'model'            => null,
                    'status'           => $isOnline ? 'online' : 'offline',
                ];
            }
        }

        // If SNMP returned 0 ONUs for this port, return empty array so OltPollingService
        // can invoke safety protection (skipping destructive mass offline sweep).
        return $results;
    }

    public function discoverUnregisteredOnus(): array
    {
        $onus = [];
        try {
            $this->initializeCli();

            $commands = [
                "show ont autofind all\r\n",
                "show ont autofind\r\n",
                "show gpon onu uncfg\r\n",
                "show onu uncfg\r\n",
                "show gpon onu unconfigured\r\n",
                "show onu unauthorized\r\n",
                "show epon onu unregister\r\n",
                "show autofind all\r\n",
                "show autofind\r\n",
            ];

            foreach ($commands as $cmd) {
                $output = $this->cli->execute($cmd, false);
                if (empty(trim((string)$output)) || str_contains($output, 'Unknown command')) {
                    continue;
                }

                foreach (explode("\n", (string)$output) as $line) {
                    $lineStr = trim($line);
                    if (empty($lineStr)) continue;

                    // Format 1: Slot/Port/ONU SN (e.g. 0/1:1 ZTEGC408F02 atau GPON-ONU_0/2:1 HWTC12345678)
                    if (preg_match('/(?:GPON-ONU_|GPON\s+|EPON-ONU_|EPON\s+)?(?:\d+\/)?(\d+)(?::\d+)?\s+.*?([A-Za-z0-9:.-]{11,20})/i', $lineStr, $m)) {
                        $candidateSn = strtoupper(trim($m[2]));
                        $candidatePort = (int)$m[1];
                        if (self::isValidOnuSerial($candidateSn)) {
                            $onus[$candidateSn] = [
                                'pon_port'      => $candidatePort ?: 1,
                                'onu_id'        => 0,
                                'serial_number' => $candidateSn,
                                'vendor_oui'    => substr($candidateSn, 0, 4),
                            ];
                        }
                    }

                    // Format 2: PON/Port X SN (e.g. PON 2 ZTEGC408F02)
                    if (preg_match('/(?:PON|PORT)\s*(\d+)\s+.*?([A-Za-z0-9:.-]{11,20})/i', $lineStr, $m)) {
                        $candidateSn = strtoupper(trim($m[2]));
                        $candidatePort = (int)$m[1];
                        if (self::isValidOnuSerial($candidateSn)) {
                            $onus[$candidateSn] = [
                                'pon_port'      => $candidatePort ?: 1,
                                'onu_id'        => 0,
                                'serial_number' => $candidateSn,
                                'vendor_oui'    => substr($candidateSn, 0, 4),
                            ];
                        }
                    }

                    // Format 3: Standalone ITU-T GPON SN dalam baris (e.g. ZTEGC408F02, HWTC12345678)
                    if (preg_match('/\b([A-Za-z]{4}[0-9A-Fa-f]{7,8})\b/', $lineStr, $m)) {
                        $candidateSn = strtoupper($m[1]);
                        if (self::isValidOnuSerial($candidateSn) && !isset($onus[$candidateSn])) {
                            $candidatePort = 1;
                            if (preg_match('/(?:\/|port\s*|pon\s*)(\d+)/i', $lineStr, $pm)) {
                                $candidatePort = (int)$pm[1];
                            }
                            $onus[$candidateSn] = [
                                'pon_port'      => $candidatePort ?: 1,
                                'onu_id'        => 0,
                                'serial_number' => $candidateSn,
                                'vendor_oui'    => substr($candidateSn, 0, 4),
                            ];
                        }
                    }
                }

                // Jika sudah menemukan perangkat valid di perintah ini, tidak perlu teruskan ke perintah fallback lain
                if (!empty($onus)) {
                    break;
                }
            }
        } catch (\Throwable) {}

        // Fallback to SNMP discovery for Autofind/Unregistered ONUs jika CLI belum menemukan
        if (empty($onus)) {
            try {
                $autofindSn = $this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.2.1.1.2')
                    ?: ($this->snmp->walk('.1.3.6.1.4.1.34592.1.5.1.1.2.18.2.1.1.1') ?: []);

                foreach ($autofindSn as $oidKey => $rawSn) {
                    $raw = trim(preg_replace('/^(STRING|Hex-STRING|OctetString|Hex)\s*:?\s*/i', '', (string)$rawSn), '" \r\n\t');
                    if (empty($raw) || str_contains(strtolower($raw), 'no such')) continue;

                    $hex = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $raw));
                    $sn = null;
                    if (preg_match('/^[A-Z0-9]{4}[A-Z0-9]{7,12}$/i', $raw)) {
                        $sn = strtoupper($raw);
                    } elseif (strlen($hex) === 16) {
                        $vendorAscii = @hex2bin(substr($hex, 0, 8));
                        if ($vendorAscii !== false && preg_match('/^[A-Za-z0-9]{4}$/', $vendorAscii)) {
                            $sn = strtoupper($vendorAscii) . substr($hex, 8, 8);
                        } else {
                            $sn = $hex;
                        }
                    }

                    if ($sn && self::isValidOnuSerial($sn)) {
                        $parts = explode('.', (string)$oidKey);
                        $port = 1;
                        if (count($parts) >= 2) {
                            $portCandidate = (int)$parts[count($parts) - 2];
                            if ($portCandidate > 0 && $portCandidate <= 16) {
                                $port = $portCandidate;
                            }
                        }
                        if (($this->olt->pon_port_count ?? 0) === 1) {
                            $port = 1;
                        }

                        $onus[$sn] = [
                            'pon_port'      => $port,
                            'onu_id'        => 0,
                            'serial_number' => $sn,
                            'vendor_oui'    => substr($sn, 0, 4),
                        ];
                    }
                }
            } catch (\Throwable) {}
        }

        return array_values($onus);
    }

    protected function resolveVlanId(Onu $onu, int $default = 200): int
    {
        try {
            $cs = $onu->customerService ?? \App\Models\Customer\CustomerService::where('onu_id', $onu->id)->latest('id')->first();
            if ($cs) {
                if (!empty($cs->networkProfile?->vlan_id)) {
                    return (int) $cs->networkProfile->vlan_id;
                }
                if (!empty($cs->network_profile_id)) {
                    $npVlan = \App\Models\Provisioning\NetworkProfile::whereKey($cs->network_profile_id)->value('vlan_id');
                    if (!empty($npVlan)) {
                        return (int) $npVlan;
                    }
                }
                $sp = $cs->serviceProfile ?? ($cs->service_profile_id ? \App\Models\ISP\ServiceProfile::find($cs->service_profile_id) : null);
                if ($sp) {
                    if (!empty($sp->vlan?->vlan_id)) {
                        return (int) $sp->vlan->vlan_id;
                    }
                    if (!empty($sp->vlan_id)) {
                        $vlanTag = \App\Models\ISP\Vlan::whereKey($sp->vlan_id)->value('vlan_id');
                        return (int) ($vlanTag ?: $sp->vlan_id);
                    }
                }
            }
        } catch (\Throwable) {}

        return $default;
    }

    public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool
    {
        try {
            $this->initializeCli();
            $onuId = $onu->onu_id_on_olt ?? $onu->getKey();
            $name  = $onu->name ?? ('CUST-' . $onuId);
            $vlanId = $this->resolveVlanId($onu, 200);

            $this->cli->execute("configure terminal\r\n", false);
            $this->cli->execute("interface gpon 0/0/$ponPort\r\n", false);
            $this->cli->execute("onu add $onuId sn-auth $serialNumber line-profile-id 1 srv-profile-id 1 description \"$name\"\r\n", false);
            $this->cli->execute("onu $onuId port uni 1 native-vlan $vlanId\r\n", false);
            $this->cli->execute("exit\r\n", false);
            $this->cli->execute("write\r\n", false);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to provision C-Data ONU {$onu->id}: " . $e->getMessage());
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
        } catch (\Exception $e) {
            Log::error("Failed to set admin status on C-Data ONU {$onu->id}: " . $e->getMessage());
            return false;
        }
    }

    public function deleteOnu(Onu $onu): bool
    {
        try {
            $rawPort = (int)($onu->pon_port ?? ($onu->ponPort->port_number ?? 0));
            $onuId   = (int)($onu->onu_id_on_olt ?? $onu->onu_index ?? 0);

            // Normalize port index (handles SNMP indexes like 1310721 -> 1)
            if ($rawPort >= 1310721 && $rawPort <= 1310736) {
                $ponPort = $rawPort - 1310720;
            } elseif ($rawPort > 1000000) {
                $ponPort = $rawPort & 0xFF;
                if ($ponPort === 0) {
                    $ponPort = ($rawPort >> 8) & 0xFF;
                }
            } else {
                $ponPort = $rawPort;
            }
            if ($ponPort <= 0 && ($this->olt->pon_port_count ?? 0) === 1) {
                $ponPort = 1;
            }

            // Fallback: cari onuId dari CLI cache jika belum tersimpan di DB
            if ($onuId <= 0 && !empty($onu->serial_number)) {
                $sn = strtoupper(trim($onu->serial_number));
                if (!empty($this->cachedCliParsedOnus)) {
                    foreach ($this->cachedCliParsedOnus as $p => $onus) {
                        foreach ($onus as $id => $data) {
                            if (!empty($data['serial_number']) && strtoupper($data['serial_number']) === $sn) {
                                $ponPort = (int)$p;
                                $onuId = (int)$id;
                                break 2;
                            }
                        }
                    }
                }
            }

            if ($ponPort <= 0 || $onuId <= 0) {
                Log::warning("CDataOltDriver::deleteOnu: Invalid pon_port ({$ponPort}) or onu_id ({$onuId}) for ONU #{$onu->id}");
                return false;
            }

            $this->initializeCli();

            // Masuk mode config
            $this->cli->execute("config\r\n", false);

            $isEpon = str_contains(strtolower($this->olt->model ?? ''), 'epon') || str_contains(strtolower($onu->pon_port ?? ''), 'epon');
            if ($isEpon) {
                // Mode EPON
                $this->cli->execute("interface epon 0/0\r\n", false);
                $this->cli->execute("onu delete $ponPort onu $onuId\r\n", false);
                $this->cli->execute("exit\r\n", false);
            } else {
                // Standar C-Data GPON interface mode (FD1600 / FD1200 / CD5000): interface gpon 0/0
                $this->cli->execute("interface gpon 0/0\r\n", false);
                $this->cli->execute("ont delete $ponPort $onuId\r\n", false);
                $this->cli->execute("exit\r\n", false);
            }

            // Eksekusi penghapusan ONU di memori operasional OLT selesai secara instan.
            // Tidak menjalankan blocking 'write' di sini agar interaksi pengguna tetap responsif (< 500ms).

            Log::info("CDataOltDriver::deleteOnu successfully issued delete command for ONU #{$onu->id} (Port: {$ponPort}, Index: {$onuId})");
            return true;
        } catch (\Throwable $e) {
            Log::error("CDataOltDriver::deleteOnu failed for ONU #{$onu->id}: " . $e->getMessage());
            return false;
        }
    }

    public function pushOnuWanConfig(Onu $onu, array $config): bool
    {
        try {
            $ponPort = $onu->pon_port ?? ($onu->ponPort->port_number ?? ($onu->pon_port_id ?? 1));
            $onuId   = $onu->onu_index ?? $onu->onu_id_on_olt ?? $onu->getKey();

            $mode = strtolower($config['mode'] ?? 'pppoe');
            $vlanId = (int)($config['vlan_id'] ?? $this->resolveVlanId($onu, 200));
            $priority = (int)($config['priority'] ?? 0);
            $ipIndex = (int)($config['ip_index'] ?? 1);

            $this->initializeCli();
            $this->cli->execute("configure terminal\r\n", false);
            $this->cli->execute("interface gpon 0/0\r\n", false);

            if ($mode === 'pppoe') {
                $user = $config['username'] ?? '';
                $pass = $config['password'] ?? '';
                $cmd = "ont ipconfig $ponPort $onuId ip-index $ipIndex pppoe username $user password $pass vlan $vlanId priority $priority";
                $this->cli->execute($cmd . "\r\n", false);
            } elseif ($mode === 'dhcp' || $mode === 'ipoe') {
                $cmd = "ont ipconfig $ponPort $onuId ip-index $ipIndex dhcp vlan $vlanId priority $priority";
                $this->cli->execute($cmd . "\r\n", false);
            }

            // Injeksi otomatis WAN IP Management TR-069 (IP Host OMCI ME 134)
            $tr069Mode = strtolower($config['tr069_mode'] ?? 'dhcp');
            $tr069Vlan = (int)($config['tr069_vlan'] ?? 901);
            if ($tr069Mode === 'static' && !empty($config['tr069_static_ip'])) {
                $stIp = trim($config['tr069_static_ip']);
                $stMask = trim($config['tr069_netmask'] ?? '255.255.255.0');
                $stGw = trim($config['tr069_gateway'] ?? '');
                $mgmtCmd = "ont ipconfig $ponPort $onuId ip-index 2 static ip $stIp mask $stMask gateway $stGw vlan $tr069Vlan priority 0";
            } else {
                $mgmtCmd = "ont ipconfig $ponPort $onuId ip-index 2 dhcp vlan $tr069Vlan priority 0";
            }
            $this->cli->execute($mgmtCmd . "\r\n", false);

            // Injeksi OMCI TR-069 ACS Server URL jika tersedia di konfigurasi / pengaturan
            $acsSettings = \App\Models\Setting::getValue('connection.acs', []);
            $acsUrl = $config['acs_url'] ?? ($acsSettings['acs_url'] ?? config('genieacs.cpe_defaults.acs_url', ''));
            if (!empty($acsUrl)) {
                $acsUser = $config['acs_username'] ?? ($acsSettings['connection_request_username'] ?? config('genieacs.cpe_defaults.connection_request_username', 'dsbilling_cwmp'));
                $acsPass = $config['acs_password'] ?? ($acsSettings['connection_request_password'] ?? config('genieacs.cpe_defaults.connection_request_password', 'ChangeMePlease!123'));
                $tr069Cmd = "ont tr069-server $ponPort $onuId url $acsUrl user $acsUser password $acsPass";
                $this->cli->execute($tr069Cmd . "\r\n", false);
            }

            $this->cli->execute("exit\r\n", false);
            $this->cli->execute("write\r\n", false);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to push WAN & TR-069 config to C-Data ONU {$onu->id}: " . $e->getMessage());
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
            $vlanId       = $this->resolveVlanId($onu, 200);
            $tr069Vlan    = 901;

            $this->initializeCli();
            $this->cli->execute("configure terminal\r\n", false);
            $this->cli->execute("dba-profile add profile-name $profileName type 4 max $downKbps\r\n", false);
            $this->cli->execute("interface gpon 0/0/$ponPort\r\n", false);
            $this->cli->execute("onu $onuId tcont 1 dba-profile-name $profileName\r\n", false);
            
            // GEM 1 & Service-Port 1: Internet Data (VLAN 200 / VLAN Hotspot 300)
            $this->cli->execute("onu $onuId gemport 1 unicast tcont 1 dir both\r\n", false);
            $this->cli->execute("onu $onuId service-port 1 gemport 1 vlan $vlanId rx-cttr $downKbps tx-cttr $upKbps\r\n", false);

            // GEM 2 & Service-Port 2: Management TR-069 GenieACS (VLAN 901)
            $this->cli->execute("onu $onuId gemport 2 unicast tcont 1 dir both\r\n", false);
            $this->cli->execute("onu $onuId service-port 2 gemport 2 vlan $tr069Vlan\r\n", false);

            $this->cli->execute("exit\r\n", false);
            $this->cli->execute("write\r\n", false);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to set bandwidth & service ports on C-Data ONU {$onu->id}: " . $e->getMessage());
            return false;
        }
    }
}
