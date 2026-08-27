<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;

/**
 * Driver OLT C-Data (CD6000 / CD8000 / CD5508 / CD5000 series)
 * Enterprise OID: .1.3.6.1.4.1.51810
 * GPON subtree:    .1.3.6.1.4.1.51810.1.*
 *
 * CLI syntax mirip Huawei/HSGQ dengan slot/port format "0/0/PON"
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

    public function getPonPortsStatus(): array
    {
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
        $results  = [];
        $base     = $this->onuInfoOid . '.' . $ponPort;
        $serials  = $this->snmp->walk($base . '.2');
        $powers   = $this->snmp->walk($base . '.6');
        $statuses = $this->snmp->walk($base . '.3');

        foreach ($serials as $idx => $serial) {
            $raw = (int)($powers[$idx] ?? 0);
            $rx  = null;
            if ($raw < 0 && $raw > -65536) {
                $rx = round($raw / 100, 2);
            } elseif ($raw > 65536) {
                $rx = round(10 * log10($raw / 10000), 2);
            } elseif ($raw > 0 && $raw < 2000) {
                $rx = round($raw / 10 - 50, 2);
            }
            $results[] = [
                'pon_port'      => $ponPort,
                'onu_index'     => $idx,
                'serial_number' => is_string($serial) ? strtoupper(trim($serial)) : (string)$serial,
                'rx_power_dbm'  => $rx,
                'status'        => match ((int)($statuses[$idx] ?? 0)) {
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
            $output = $this->cli->execute('show gpon onu unconfigured');
            if (empty(trim($output))) {
                $output = $this->cli->execute('show onu unauthorized');
            }
            $onus = [];
            foreach (explode("\n", (string)$output) as $line) {
                if (preg_match('/(?:gpon\s+)?(?:0\/)?0\/(\d+)\s+.*?([A-F0-9]{12,16})/i', strtoupper($line), $m)) {
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
        } catch (\Exception) {
            return [];
        }
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
            $this->cli->execute("interface gpon 0/0/$ponPort");
            $cmd = match ($status) {
                'enable'  => "onu modify $onuId admin-state up",
                'disable' => "onu modify $onuId admin-state down",
                'reset'   => "onu reset $onuId",
                default   => "onu modify $onuId admin-state up"
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
            $ponPort      = $onu->pon_port ?? 0;
            $onuId        = $onu->onu_id_on_olt ?? $onu->getKey();
            $profileName  = "DSB_{$downloadMbps}_{$uploadMbps}";
            $downKbps     = $downloadMbps * 1000;
            $upKbps       = $uploadMbps * 1000;

            $this->initializeCli();
            $this->cli->execute('configure terminal');
            // Buat DBA profile (tipe 4 = max bandwidth guaranteed)
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
