<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;

class ZteOltDriver extends BaseOltDriver
{
    protected array $systemOids = [
        'sysName' => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime' => '.1.3.6.1.2.1.1.3.0',
        'sysDescr' => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.3902.1082.500.10.2.46.1.5.1.0.0',
    ];

    protected string $ponPortStatusOid = '.1.3.6.1.4.1.3902.1082.500.10.2.4.1.1';
    protected string $ponPortNameOid = '.1.3.6.1.4.1.3902.1082.500.10.2.4.1.2';
    protected string $onuSerialOid = '.1.3.6.1.4.1.3902.1082.500.10.2.46.1.2';
    protected string $onuRxPowerOid = '.1.3.6.1.4.1.3902.1082.500.10.2.46.1.4';
    protected string $onuStatusOid = '.1.3.6.1.4.1.3902.1082.500.10.2.46.1.3';

    public function getPonPortsStatus(): array
    {
        $ports = [];
        $statuses = $this->snmp->walk($this->ponPortStatusOid);
        $names = $this->snmp->walk($this->ponPortNameOid);
        foreach (array_keys($names) as $idx) {
            $ports[] = [
                'port_index' => $idx,
                'port_name' => $names[$idx] ?? "pon-$idx",
                'status' => match ((int)($statuses[$idx] ?? 0)) {
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
            $rawPower = (int)($powers[$idx] ?? 0);
            $rxDbm = $rawPower !== 0 ? round($rawPower / 1000 - 30, 2) : null;
            $results[] = [
                'pon_port' => $ponPort,
                'onu_index' => $idx,
                'serial_number' => $serial,
                'rx_power_dbm' => $rxDbm,
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
            $output = $this->cli->execute('show gpon onu uncfg');
            $onus = [];
            foreach (explode("\n", $output) as $line) {
                if (preg_match('/gpon-onu_(\d+)\/(\d+)\/(\d+):(\d+)\s+(\w+)/', $line, $m)) {
                    $onus[] = [
                        'pon_port' => (int)$m[3],
                        'onu_id' => (int)$m[4],
                        'serial_number' => $m[5],
                        'vendor_oui' => strtoupper(substr($m[5], 0, 4)),
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
            $cmds = [
                "configure terminal",
                "interface gpon-olt_1/1/$ponPort",
                "onu " . $onu->getKey() . " type ZTE-F609 sn $serialNumber",
                "exit",
                "interface gpon-onu_1/1/$ponPort:" . $onu->getKey(),
                "name " . ($onu->name ?? 'ONU-' . $onu->getKey()),
                "profile line $profile",
                "profile internet default",
                "exit",
                "exit",
                "write memory"
            ];
            foreach ($cmds as $cmd) {
                $this->cli->execute($cmd);
            }
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ZTE provision failed: ' . $e->getMessage());
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
            $this->cli->execute("interface gpon-onu_1/1/$ponPort:$onuId");
            $cmd = match ($status) {
                'enable' => 'admin-state enable',
                'disable' => 'admin-state disable',
                'reset' => 'reset-onu',
                default => 'admin-state enable'
            };
            $this->cli->execute($cmd);
            $this->cli->execute("exit");
            $this->cli->execute("exit");
            $this->cli->execute("write memory");
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
            $this->cli->execute("configure terminal");
            $this->cli->execute("interface gpon-onu_1/1/$ponPort:$onuId");
            $this->cli->execute("traffic-profile upstream bandwidth {$uploadMbps}mbps");
            $this->cli->execute("traffic-profile downstream bandwidth {$downloadMbps}mbps");
            $this->cli->execute("exit");
            $this->cli->execute("exit");
            $this->cli->execute("write memory");
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
