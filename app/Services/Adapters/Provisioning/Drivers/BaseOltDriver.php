<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Services\Adapters\Provisioning\Contracts\OltDriverInterface;
use App\Services\Adapters\Provisioning\Support\SnmpClient;
use App\Services\Adapters\Provisioning\Support\TelnetSshClient;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseOltDriver implements OltDriverInterface
{
    protected Olt $olt;
    protected ?SnmpClient $snmp = null;
    protected ?TelnetSshClient $cli = null;

    protected string $snmpCommunityRead = 'public';
    protected string $snmpCommunityWrite = 'private';
    protected string $snmpVersion = '2c';

    protected array $systemOids = [
        'sysName' => '.1.3.6.1.2.1.1.5.0',
        'sysUpTime' => '.1.3.6.1.2.1.1.3.0',
        'sysDescr' => '.1.3.6.1.2.1.1.1.0',
        'sysTemperature' => '.1.3.6.1.4.1.xxxx.x.x.x',
    ];

    protected string $cliMode = 'telnet';
    protected string $enableSecret = '';

    public function __construct(Olt $olt)
    {
        $this->olt = $olt;
        $this->initializeSnmp();
    }

    protected function initializeSnmp(): void
    {
        $read = $this->olt->snmp_community_read ?? $this->snmpCommunityRead;
        $write = $this->olt->snmp_community_write ?? $this->snmpCommunityWrite;
        $version = $this->olt->snmp_version ?? $this->snmpVersion;
        $this->snmpCommunityRead = $read;
        $this->snmpCommunityWrite = $write;
        $this->snmpVersion = $version;
        $this->snmp = new SnmpClient($this->olt->ip_address, $this->snmpCommunityRead, $this->snmpVersion);
    }

    protected function initializeCli(): void
    {
        if ($this->cli !== null) {
            return;
        }
        $this->cli = new TelnetSshClient();
        $this->cli->connect(
            $this->olt->ip_address,
            $this->olt->username,
            $this->olt->password,
            $this->cliMode,
            (int)($this->olt->cli_port ?? 0),
            $this->enableSecret
        );
    }

    public function getSystemInfo(): array
    {
        try {
            return [
                'name' => $this->snmp->get($this->systemOids['sysName']) ?: $this->olt->name,
                'uptime' => $this->formatUptime($this->snmp->get($this->systemOids['sysUpTime']) ?: '0'),
                'description' => $this->snmp->get($this->systemOids['sysDescr']) ?: 'N/A',
                'temperature' => $this->getTemperature(),
                'ip_address' => $this->olt->ip_address,
                'model' => $this->olt->model,
                'status' => 'online',
            ];
        } catch (Exception $e) {
            Log::warning('OLT SNMP failed: ' . $e->getMessage(), ['olt_id' => $this->olt->id]);
            return [
                'name' => $this->olt->name,
                'uptime' => 'N/A',
                'description' => 'N/A',
                'temperature' => 0,
                'ip_address' => $this->olt->ip_address,
                'model' => $this->olt->model,
                'status' => 'offline',
            ];
        }
    }

    public function getTemperature(): float
    {
        try {
            $raw = $this->snmp->get($this->systemOids['sysTemperature']);
            if ($raw === false || $raw === '') {
                return 0.0;
            }
            $val = (float)$raw;
            if ($val > 1000) {
                $val = $val / 1000;
            }
            return round($val, 1);
        } catch (Exception) {
            return 0.0;
        }
    }

    public function executeCommand(string $command, string $mode = 'telnet'): string|bool
    {
        try {
            $this->cliMode = in_array($mode, ['ssh', 'telnet']) ? $mode : $this->cliMode;
            $this->initializeCli();
            return $this->cli->execute($command);
        } catch (Exception $e) {
            Log::error('OLT CLI execute error: ' . $e->getMessage(), [
                'olt_id' => $this->olt->id,
                'command' => $command
            ]);
            return false;
        }
    }

    public function rebootOlt(): bool
    {
        try {
            $this->initializeCli();
            $this->cli->execute('reboot');
            return true;
        } catch (Exception $e) {
            Log::error('OLT reboot failed: ' . $e->getMessage(), ['olt_id' => $this->olt->id]);
            return false;
        }
    }

    public function saveConfig(): bool
    {
        try {
            $this->initializeCli();
            $this->cli->execute('write memory');
            return true;
        } catch (Exception $e) {
            Log::error('OLT save config failed: ' . $e->getMessage(), ['olt_id' => $this->olt->id]);
            return false;
        }
    }

    public function rebootOnu(Onu $onu): bool
    {
        return $this->setOnuAdminStatus($onu, 'reset');
    }

    public function getOnuSignal(Onu $onu): array
    {
        try {
            $ponPort = $onu->pon_port ?? $onu->ponPort?->port_number ?? 0;
            $allSignals = $this->getOnuRxPower($ponPort);
            foreach ($allSignals as $signal) {
                if (
                    (isset($signal['serial_number']) && $signal['serial_number'] === $onu->serial_number)
                    || (isset($signal['mac_address']) && $signal['mac_address'] === $onu->mac_address)
                ) {
                    return $signal;
                }
            }
            return [
                'onu_id' => $onu->id,
                'status' => 'not_found',
                'rx_power_dbm' => null,
                'tx_power_dbm' => null,
                'snr_db' => null,
            ];
        } catch (Exception $e) {
            Log::error('ONU signal failed: ' . $e->getMessage(), ['onu_id' => $onu->id]);
            return [
                'onu_id' => $onu->id,
                'status' => 'error',
                'rx_power_dbm' => null,
                'tx_power_dbm' => null,
                'snr_db' => null,
            ];
        }
    }

    protected function formatUptime(string $rawTicks): string
    {
        $ticks = (int)$rawTicks;
        if ($ticks <= 0) {
            return '0 days';
        }
        $seconds = (int)($ticks / 100);
        $days = (int)($seconds / 86400);
        $hours = (int)(($seconds % 86400) / 3600);
        $minutes = (int)(($seconds % 3600) / 60);
        if ($days > 0) {
            return sprintf('%d hari, %d jam %d menit', $days, $hours, $minutes);
        }
        return sprintf('%d jam %d menit', $hours, $minutes);
    }

    abstract public function getPonPortsStatus(): array;
    abstract public function getOnuRxPower(int $ponPort): array;
    abstract public function discoverUnregisteredOnus(): array;
    abstract public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool;
    abstract public function setOnuAdminStatus(Onu $onu, string $status): bool;
    abstract public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool;
}
