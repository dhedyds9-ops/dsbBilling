<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;

class BtPonOltDriver extends BaseOltDriver
{
    protected string $cliMode = 'telnet';

    protected array $systemOids = [
        'sysName' => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime' => '.1.3.6.1.2.1.1.3.0',
        'sysDescr' => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.5200.1.1.1.1.5.1.0.0.0',
    ];

    protected string $ponStatusOid   = '.1.3.6.1.4.1.5200.2.1.1.1.2';
    protected string $onuRxPowerOid  = '.1.3.6.1.4.1.5200.2.3.1.1.15';
    protected string $onuTxPowerOid  = '.1.3.6.1.4.1.5200.2.3.1.1.16';
    protected string $onuSerialOid   = '.1.3.6.1.4.1.5200.2.3.1.1.5';
    protected string $onuStatusOid   = '.1.3.6.1.4.1.5200.2.3.1.1.2';
    protected string $onuTempOid     = '.1.3.6.1.4.1.5200.2.3.1.1.17';
    protected string $onuMacOid      = '.1.3.6.1.4.1.5200.2.3.1.1.6';
    protected string $onuFwOid       = '.1.3.6.1.4.1.5200.2.3.1.1.12';
    protected string $onuModelOid    = '.1.3.6.1.4.1.5200.2.3.1.1.11';

    public function getPonPortsStatus(): array
    {
        $statuses = $this->snmp->walk($this->ponStatusOid);
        $ports = [];
        foreach ($statuses as $idx => $status) {
            $ports[] = [
                'port_index' => $idx,
                'port_name' => "XG-PON $idx",
                'status' => match ((int)$status) {
                    1 => 'up',
                    2 => 'down',
                    default => 'unknown'
                },
            ];
        }
        return $ports;
    }

    public function getOnuRxPower(int $ponPort): array
    {
        $results = [];
        $serials   = $this->snmp->walk($this->onuSerialOid . '.' . $ponPort);
        $rxRaw     = $this->snmp->walk($this->onuRxPowerOid . '.' . $ponPort);
        $txRaw     = $this->snmp->walk($this->onuTxPowerOid . '.' . $ponPort);
        $statuses  = $this->snmp->walk($this->onuStatusOid . '.' . $ponPort);
        $tempRaw   = $this->snmp->walk($this->onuTempOid . '.' . $ponPort);
        $macs      = $this->snmp->walk($this->onuMacOid . '.' . $ponPort);
        $firmwares = $this->snmp->walk($this->onuFwOid . '.' . $ponPort);
        $models    = $this->snmp->walk($this->onuModelOid . '.' . $ponPort);

        foreach ($serials as $idx => $serial) {
            $rxRawVal = (int)($rxRaw[$idx] ?? 0);
            $rx = $rxRawVal !== 0 ? round($rxRawVal / 100, 2) : null;

            $txRawVal = (int)($txRaw[$idx] ?? 0);
            $tx = null;
            if ($txRawVal !== 0) {
                if ($txRawVal < 0) {
                    $tx = round($txRawVal / 100, 2);
                } elseif ($txRawVal > 0 && $txRawVal < 65536) {
                    $tx = round(($txRawVal / 100) - 50, 2);
                }
            }

            $tempVal = null;
            if (!empty($tempRaw[$idx]) && is_numeric($tempRaw[$idx])) {
                $t = (int)$tempRaw[$idx];
                if ($t !== 0 && $t < 65535) {
                    $tempVal = $t > 1000 ? round($t / 100, 1) : $t;
                }
            }

            $mac = null;
            if (!empty($macs[$idx])) {
                $macStr = is_string($macs[$idx]) ? $macs[$idx] : (string)$macs[$idx];
                $hex = strtoupper(bin2hex($macStr) ?: $macStr);
                $hexClean = substr(preg_replace('/[^A-F0-9]/i', '', $hex), 0, 12);
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
                'snr_db'           => null,
                'temperature'      => $tempVal,
                'firmware_version' => $fw,
                'model'            => $modelVal,
                'status'           => match ((int)($statuses[$idx] ?? 0)) {
                    1 => 'online',
                    2 => 'offline',
                    3 => 'dying_gasp',
                    default => 'unknown'
                },
            ];
        }
        return $results;
    }

    public function discoverUnregisteredOnus(): array
    {
        try {
            $this->initializeCli();
            $output = $this->cli->execute('show onu unprovisioned');
            $onus = [];
            foreach (explode("\n", $output) as $line) {
                if (preg_match('/port (\d+)\s+.*?([A-F0-9]{12,})/', strtoupper($line), $m)) {
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

    public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool
    {
        try {
            $this->initializeCli();
            $onuId = $onu->getKey();
            $this->cli->execute('enable');
            $this->cli->execute('configure');
            $this->cli->execute("interface xgpon $ponPort");
            $this->cli->execute("onu add $onuId serial-number $serialNumber");
            $this->cli->execute("onu description \"" . ($onu->name ?? 'CUST-' . $onuId) . "\"");
            $this->cli->execute("onu profile line $profile");
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
            $onuId = $onu->getKey();
            $this->initializeCli();
            $this->cli->execute('enable');
            $this->cli->execute('configure');
            $this->cli->execute("interface xgpon $ponPort");
            $cmd = match ($status) {
                'enable' => "onu $onuId state up",
                'disable' => "onu $onuId state down",
                'reset' => "onu $onuId reset",
                default => "onu $onuId state up"
            };
            $this->cli->execute($cmd);
            $this->cli->execute('exit');
            $this->cli->execute('write');
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool
    {
        try {
            $ponPort = $onu->pon_port ?? 0;
            $onuId = $onu->getKey();
            $this->initializeCli();
            $this->cli->execute('enable');
            $this->cli->execute('configure');
            $this->cli->execute("interface xgpon $ponPort");
            $this->cli->execute("onu $onuId bandwidth downstream {$downloadMbps}m upstream {$uploadMbps}m");
            $this->cli->execute('exit');
            $this->cli->execute('write');
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
