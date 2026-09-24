<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;

class HsgqOltDriver extends BaseOltDriver
{
    protected string $cliMode = 'telnet';

    protected array $systemOids = [
        'sysName'        => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime'      => '.1.3.6.1.2.1.1.3.0',
        'sysDescr'       => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.5875.901.10.1.1.1.1.9.1',
    ];

    protected string $ponPortStatusOid = '.1.3.6.1.4.1.5875.901.10.1.1.1.1.5';
    protected string $onuInfoOid       = '.1.3.6.1.4.1.5875.901.10.3.1.1';

    protected ?bool $isNewBranch = null;
    protected ?array $cachedOnuSerials = null;
    protected ?array $cachedOnuStatuses = null;
    protected ?array $cachedOnuRx = null;
    protected ?array $cachedOnuTx = null;
    protected ?array $cachedOnuNames = null;
    protected ?array $cachedOnuModels = null;
    protected ?array $cachedOnuFws = null;
    protected ?array $cachedOnuTemps = null;

    public function __construct($olt)
    {
        parent::__construct($olt);

        if ($this->checkIsNewBranch()) {
            $this->systemOids['sysTemperature'] = '.1.3.6.1.4.1.50224.3.1.1.18.0';
        }
    }

    protected function checkIsNewBranch(): bool
    {
        if ($this->isNewBranch !== null) {
            return $this->isNewBranch;
        }

        $res = @$this->snmp->get('.1.3.6.1.4.1.50224.3.1.1.5.0');
        return $this->isNewBranch = ($res !== false && $res !== '');
    }

    protected function cleanSnmpString(string $str): string
    {
        $str = trim($str);
        $str = preg_replace('/^(STRING|Hex-STRING|OctetString)\s*:?\s*/i', '', $str);
        $str = trim($str, '" ');
        
        if (preg_match('/^[0-9A-Fa-f\s]+$/', $str) && str_contains($str, ' ')) {
            $hex = str_replace(' ', '', $str);
            $decoded = @hex2bin($hex);
            if ($decoded !== false) {
                return trim(str_replace("\0", '', $decoded));
            }
        }
        return trim(str_replace("\0", '', $str));
    }

    public function getPonPortsStatus(): array
    {
        if ($this->checkIsNewBranch()) {
            $names = $this->snmp->walk('.1.3.6.1.4.1.50224.3.2.1.1.2');
            $statuses = $this->snmp->walk('.1.3.6.1.4.1.50224.3.2.1.1.6');
            $ports = [];
            
            $statusMap = [];
            if ($statuses) {
                foreach ($statuses as $idx => $val) {
                    $cleanOid = str_replace('iso', '.1', $idx);
                    $parts = explode('.', $cleanOid);
                    $portIdx = (int)end($parts);
                    $statusMap[$portIdx] = (int)$val;
                }
            }

            if ($names) {
                foreach ($names as $idx => $name) {
                    $cleanOid = str_replace('iso', '.1', $idx);
                    $parts = explode('.', $cleanOid);
                    $portIdx = (int)end($parts);
                    
                    $cleanName = $this->cleanSnmpString($name);
                    if (str_contains(strtoupper($cleanName), 'PON')) {
                        $statusVal = $statusMap[$portIdx] ?? 1;
                        $ports[] = [
                            'port_index' => $portIdx,
                            'port_name'  => $cleanName,
                            'status'     => match ($statusVal) {
                                2 => 'up',
                                1 => 'down',
                                default => 'down'
                            },
                        ];
                    }
                }
            }
            return $ports;
        }

        $statuses = $this->snmp->walk($this->ponPortStatusOid);
        $ports = [];
        foreach ($statuses as $idx => $status) {
            $ports[] = [
                'port_index' => $idx,
                'port_name' => "PON $idx",
                'status' => match ((int)$status) {
                    1, 101 => 'up',
                    2, 102 => 'down',
                    default => 'unknown'
                },
            ];
        }
        return $ports;
    }

    public function getOnuRxPower(int $ponPort): array
    {
        if ($this->checkIsNewBranch()) {
            if ($this->cachedOnuSerials === null) {
                $rawSerials  = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.15') ?: [];
                $rawStatuses = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.22') ?: [];
                $rawRx       = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.3.1.4') ?: [];
                $rawTx       = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.3.1.5') ?: [];
                $rawNames    = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.2') ?: [];
                $rawModels   = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.9') ?: [];
                $rawFws      = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.2.1.11') ?: [];
                $rawTemps    = $this->snmp->walk('.1.3.6.1.4.1.50224.3.12.3.1.7') ?: [];

                $this->cachedOnuSerials = [];
                $this->cachedOnuStatuses = [];
                $this->cachedOnuRx = [];
                $this->cachedOnuTx = [];
                $this->cachedOnuNames = [];
                $this->cachedOnuModels = [];
                $this->cachedOnuFws = [];
                $this->cachedOnuTemps = [];

                $parseIdx = function($idx) {
                    // Bersihkan base OID jika gagal di-strip oleh SnmpClient (misal di localhost/php-snmp)
                    // Hapus awalan .1.3.6.1.4.1.50224.3.12.x.x.x.
                    $clean = preg_replace('/^(\.?iso|\.?1)\.3\.6\.1\.4\.1\.50224\.3\.12\.\d+\.\d+\.\d+\./i', '', $idx);
                    
                    // Setelah bersih, nilai $clean sisa "16777473" atau "16777473.1.1"
                    $parts = explode('.', $clean);
                    return (int)$parts[0];
                };

                foreach ($rawSerials as $idx => $val) {
                    $this->cachedOnuSerials[$parseIdx($idx)] = $this->cleanSnmpString($val);
                }
                foreach ($rawStatuses as $idx => $val) {
                    $this->cachedOnuStatuses[$parseIdx($idx)] = (int)$val;
                }
                foreach ($rawNames as $idx => $val) {
                    $this->cachedOnuNames[$parseIdx($idx)] = $this->cleanSnmpString($val);
                }
                foreach ($rawModels as $idx => $val) {
                    $this->cachedOnuModels[$parseIdx($idx)] = $this->cleanSnmpString($val);
                }
                foreach ($rawFws as $idx => $val) {
                    $this->cachedOnuFws[$parseIdx($idx)] = $this->cleanSnmpString($val);
                }
                foreach ($rawRx as $idx => $val) {
                    $rawVal = (int)$val;
                    if ($rawVal !== -4000) {
                        $this->cachedOnuRx[$parseIdx($idx)] = round($rawVal / 100, 2);
                    }
                }
                foreach ($rawTx as $idx => $val) {
                    $this->cachedOnuTx[$parseIdx($idx)] = round((int)$val / 100, 2);
                }
                foreach ($rawTemps as $idx => $val) {
                    $this->cachedOnuTemps[$parseIdx($idx)] = round((int)$val / 10, 1);
                }
            }

            $results = [];
            foreach ($this->cachedOnuSerials as $onuIndex => $serial) {
                $portOfOnu = $onuIndex & 0xFFFFFF00;
                if ($portOfOnu === $ponPort) {
                    $onuId = $onuIndex & 0xFF;
                    $statusVal = $this->cachedOnuStatuses[$onuIndex] ?? 0;
                    
                    $results[] = [
                        'pon_port'         => $ponPort,
                        'onu_index'        => $onuId,
                        'serial_number'    => $serial,
                        'mac_address'      => null,
                        'rx_power_dbm'     => $this->cachedOnuRx[$onuIndex] ?? null,
                        'tx_power_dbm'     => $this->cachedOnuTx[$onuIndex] ?? null,
                        'snr_db'           => null,
                        'temperature'      => $this->cachedOnuTemps[$onuIndex] ?? null,
                        'firmware_version' => $this->cachedOnuFws[$onuIndex] ?? null,
                        'model'            => $this->cachedOnuModels[$onuIndex] ?? null,
                        'status'           => match ($statusVal) {
                            1 => 'online',
                            default => 'offline'
                        },
                    ];
                }
            }
            return $results;
        }

        $results = [];
        $base = $this->onuInfoOid . '.' . $ponPort;
        $serials    = $this->snmp->walk($this->onuInfoOid . '.1.' . $ponPort);
        $statuses   = $this->snmp->walk($this->onuInfoOid . '.2.' . $ponPort);
        $rxRaw      = $this->snmp->walk($this->onuInfoOid . '.5.' . $ponPort);
        $txRaw      = $this->snmp->walk($this->onuInfoOid . '.6.' . $ponPort);
        $snrRaw     = $this->snmp->walk($this->onuInfoOid . '.9.' . $ponPort);
        $tempRaw    = $this->snmp->walk($this->onuInfoOid . '.10.' . $ponPort);
        $macs       = $this->snmp->walk($this->onuInfoOid . '.7.' . $ponPort);
        $firmwares  = $this->snmp->walk($this->onuInfoOid . '.12.' . $ponPort);
        $models     = $this->snmp->walk($this->onuInfoOid . '.13.' . $ponPort);

        if (empty($txRaw)) {
            $txRaw = $this->snmp->walk($this->onuInfoOid . '.3.' . $ponPort);
        }
        if (empty($snrRaw)) {
            $snrRaw = $this->snmp->walk($this->onuInfoOid . '.15.' . $ponPort);
        }

        foreach ($serials as $idx => $serial) {
            $rxRawVal = (int)($rxRaw[$idx] ?? 0);
            $rx = null;
            if ($rxRawVal < 0 && $rxRawVal > -65536) {
                $rx = round($rxRawVal / 100, 2);
            } elseif ($rxRawVal > 0 && $rxRawVal < 2000) {
                $rx = round(($rxRawVal / 10) - 50, 2);
            } elseif ($rxRawVal > 65536) {
                $rx = round(10 * log10($rxRawVal / 10000), 2);
            }

            $txRawVal = (int)($txRaw[$idx] ?? 0);
            $tx = null;
            if ($txRawVal !== 0) {
                if ($txRawVal < 0) {
                    $tx = round($txRawVal / 100, 2);
                } elseif ($txRawVal > 65536) {
                    $tx = round(10 * log10($txRawVal / 10000), 2);
                } elseif ($txRawVal > 0 && $txRawVal < 2000) {
                    $tx = round(($txRawVal / 10) - 50, 2);
                }
            }

            $snrVal = null;
            if (!empty($snrRaw[$idx]) && is_numeric($snrRaw[$idx])) {
                $s = (float)$snrRaw[$idx];
                if ($s > 1000) {
                    $snrVal = round($s / 100, 2);
                } elseif ($s > 0) {
                    $snrVal = round($s, 2);
                }
            }

            $tempVal = null;
            if (!empty($tempRaw[$idx]) && is_numeric($tempRaw[$idx])) {
                $t = (int)$tempRaw[$idx];
                if ($t > 1000) {
                    $tempVal = round($t / 100, 1);
                } elseif ($t !== 0) {
                    $tempVal = $t;
                }
            }

            $mac = null;
            if (!empty($macs[$idx])) {
                $hex = strtoupper(bin2hex(is_string($macs[$idx]) ? $macs[$idx] : (string)$macs[$idx]));
                if (strlen($hex) >= 12) {
                    $hexClean = substr(preg_replace('/[^A-F0-9]/i', '', $hex), 0, 12);
                    if (strlen($hexClean) === 12) {
                        $mac = implode(':', str_split($hexClean, 2));
                    }
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
                    1, 101, 100 => 'online',
                    2, 102, 0   => 'offline',
                    3           => 'dying_gasp',
                    default     => 'unknown'
                },
            ];
        }
        return $results;
    }

    public function discoverUnregisteredOnus(): array
    {
        try {
            $this->initializeCli();
            $output = $this->cli->execute('show ont unconfigured');
            if (str_contains($output, 'Unknown command') || empty(trim($output))) {
                $output = $this->cli->execute('show ont autofind');
            }
            $onus = [];
            foreach (explode("\n", $output) as $line) {
                if (preg_match('/PON(\d+)\s+.*?([A-F0-9]{12,16})/', strtoupper($line), $m)) {
                    $onus[] = [
                        'pon_port' => (int)$m[1],
                        'onu_id' => 0,
                        'serial_number' => $m[2],
                        'vendor_oui' => substr($m[2], 0, 4),
                    ];
                }
            }
            return $onus;
        } catch (\Exception) {
            return [];
        }
    }

    public function provisionOnu($onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool
    {
        try {
            $this->initializeCli();
            $onuId = $onu->getKey();
            $this->cli->execute("configure");
            $this->cli->execute("interface pon $ponPort");
            $this->cli->execute("ont add $onuId sn-auth $serialNumber omci ont-lineprofile-id 1 ont-srvprofile-id 1 desc \"" . ($onu->name ?? 'CUST-' . $onuId) . "\"");
            $this->cli->execute("exit");
            $this->cli->execute("write");
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function setOnuAdminStatus($onu, string $status): bool
    {
        try {
            $ponPort = $onu->pon_port ?? 0;
            $onuId = $onu->getKey();
            $this->initializeCli();
            $this->cli->execute("configure");
            $this->cli->execute("interface pon $ponPort");
            $cmd = match ($status) {
                'enable' => "ont modify $onuId admin-state up",
                'disable' => "ont modify $onuId admin-state down",
                'reset' => "ont reset $onuId",
                default => "ont modify $onuId admin-state up"
            };
            $this->cli->execute($cmd);
            $this->cli->execute("exit");
            $this->cli->execute("write");
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function setOnuBandwidthLimit($onu, int $downloadMbps, int $uploadMbps): bool
    {
        try {
            $ponPort = $onu->pon_port ?? 0;
            $onuId = $onu->getKey();
            $this->initializeCli();
            $this->cli->execute("configure");
            $this->cli->execute("interface pon $ponPort");
            $this->cli->execute("ont port native-vlan $onuId eth 1 vlan 100");
            $this->qosAddOrModify($downloadMbps, $uploadMbps, $onuId);
            $this->cli->execute("exit");
            $this->cli->execute("write");
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    protected function qosAddOrModify(int $downloadMbps, int $uploadMbps, int $onuId): void
    {
        $this->cli->execute("qos-profile add profile-id {$downloadMbps}-{$uploadMbps} cir downstream {$downloadMbps}000 upstream {$uploadMbps}000");
        $this->cli->execute("ont modify $onuId qos-profile " . ($downloadMbps) . '-' . $uploadMbps);
    }

    public function getSystemInfo(): array
    {
        $info = parent::getSystemInfo();
        if ($info['status'] === 'offline') {
            return $info;
        }

        $sysDescr = $this->snmp->get($this->systemOids['sysDescr']) ?: '';
        
        // Parsing HSGQ specific sysDescr output
        if (preg_match('/Product Model:([^\s]+)/i', $sysDescr, $m)) {
            $info['model'] = $m[1];
        }
        if (preg_match('/Firmware Version\s*:([^\s]+)/i', $sysDescr, $m)) {
            $info['firmware'] = $m[1];
        }
        if (preg_match('/Hardware Version:([^\s]+)/i', $sysDescr, $m)) {
            $info['hardware_version'] = $m[1];
        }
        if (preg_match('/SN:([A-Za-z0-9]+)/i', $sysDescr, $m)) {
            $info['serial_number'] = $m[1];
        }
        if (preg_match('/MAC:([A-Fa-f0-9:]+)/i', $sysDescr, $m)) {
            $info['mac_address'] = $m[1];
        }

        return $info;
    }
}

