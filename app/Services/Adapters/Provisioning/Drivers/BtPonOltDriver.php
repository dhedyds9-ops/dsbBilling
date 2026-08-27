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

    protected string $ponStatusOid = '.1.3.6.1.4.1.5200.2.1.1.1.2';
    protected string $onuRxPowerOid = '.1.3.6.1.4.1.5200.2.3.1.1.15';
    protected string $onuSerialOid = '.1.3.6.1.4.1.5200.2.3.1.1.5';
    protected string $onuStatusOid = '.1.3.6.1.4.1.5200.2.3.1.1.2';

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
        $serials = $this->snmp->walk($this->onuSerialOid . '.' . $ponPort);
        $powers = $this->snmp->walk($this->onuRxPowerOid . '.' . $ponPort);
        $statuses = $this->snmp->walk($this->onuStatusOid . '.' . $ponPort);
        foreach ($serials as $idx => $serial) {
            $raw = (int)($powers[$idx] ?? 0);
            $rx = $raw !== 0 ? round($raw / 100, 2) : null;
            $results[] = [
                'pon_port' => $ponPort,
                'onu_index' => $idx,
                'serial_number' => $serial,
                'rx_power_dbm' => $rx,
                'status' => match ((int)($statuses[$idx] ?? 0)) {
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
