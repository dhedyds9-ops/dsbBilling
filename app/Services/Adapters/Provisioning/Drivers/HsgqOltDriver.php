<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;

class HsgqOltDriver extends BaseOltDriver
{
    protected string $cliMode = 'telnet';

    protected array $systemOids = [
        'sysName' => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime' => '.1.3.6.1.2.1.1.3.0',
        'sysDescr' => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.5875.901.10.1.1.1.1.9.1',
    ];

    protected string $ponPortStatusOid = '.1.3.6.1.4.1.5875.901.10.1.1.1.1.5';
    protected string $onuInfoOid = '.1.3.6.1.4.1.5875.901.10.3.1.1';

    public function getPonPortsStatus(): array
    {
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
        $results = [];
        $serials = $this->snmp->walk($this->onuInfoOid . '.1.' . $ponPort);
        $powers = $this->snmp->walk($this->onuInfoOid . '.5.' . $ponPort);
        $statuses = $this->snmp->walk($this->onuInfoOid . '.2.' . $ponPort);
        foreach ($serials as $idx => $serial) {
            $raw = (int)($powers[$idx] ?? 0);
            $rx = null;
            if ($raw < 0 && $raw > -65536) {
                $rx = round($raw / 100, 2);
            } elseif ($raw > 0 && $raw < 100000) {
                $rx = round(10 * log10($raw / 10000), 2);
            }
            $results[] = [
                'pon_port' => $ponPort,
                'onu_index' => $idx,
                'serial_number' => $serial,
                'rx_power_dbm' => $rx,
                'status' => match ((int)($statuses[$idx] ?? 0)) {
                    1 => 'online',
                    2 => 'offline',
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
            $output = $this->cli->execute('show ont unconfigured');
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

    public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool
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

    public function setOnuAdminStatus(Onu $onu, string $status): bool
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

    public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool
    {
        try {
            $ponPort = $onu->pon_port ?? 0;
            $onuId = $onu->getKey();
            $this->initializeCli();
            $this->cli->execute("configure");
            $this->cli->execute("interface pon $ponPort");
            $this->cli->execute("ont port native-vlan $onuId eth 1 vlan 100");
            $this->cli->execute("qos-profile add profile-id {$downloadMbps}-{$uploadMbps} cir downstream {$downloadMbps}000 upstream {$uploadMbps}000");
            $this->cli->execute("ont modify $onuId qos-profile " . ($downloadMbps) . '-' . $uploadMbps);
            $this->cli->execute("exit");
            $this->cli->execute("write");
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
