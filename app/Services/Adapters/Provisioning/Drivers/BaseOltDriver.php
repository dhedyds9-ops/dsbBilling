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

        if (!function_exists('snmpget')) {
            Log::warning('PHP SNMP extension tidak terpasang. Fallback ke shell_exec `snmpget` — pastikan net-snmp-utils / snmp binary tersedia di PATH.', [
                'olt_id' => $olt->id,
                'ip' => $olt->ip_address,
            ]);
        }

        $this->initializeSnmp();
    }

    protected function initializeSnmp(): void
    {
        $read = $this->olt->snmp_community_read ?? $this->snmpCommunityRead;
        $write = $this->olt->snmp_community_write ?? $this->snmpCommunityWrite;

        $rawVersion = $this->olt->snmp_version ?? $this->snmpVersion;
        if (is_string($rawVersion)) {
            $rawVersion = preg_replace('/^v/i', '', trim($rawVersion));
        }
        $version = in_array($rawVersion, ['1', '2c', '3'], true) ? $rawVersion : '2c';

        $this->snmpCommunityRead = $read;
        $this->snmpCommunityWrite = $write;
        $this->snmpVersion = $version;

        $port = (int)($this->olt->snmp_port ?? 161);
        $timeout = (int)config('olt-drivers.defaults.timeout_seconds', 3);
        $timeout = max(1, $timeout);
        $retries = 1;

        $this->snmp = new SnmpClient((string)$this->olt->ip_address, $read, $version, $timeout, $retries, $port);
    }

    protected function initializeCli(): void
    {
        if ($this->cli !== null) {
            return;
        }
        $this->cli = new TelnetSshClient();
        $this->cli->connect(
            $this->olt->ip_address,
            $this->olt->username ?? '',
            $this->olt->password ?? '',
            $this->cliMode,
            (int)($this->olt->cli_port ?? 0),
            $this->olt->enable_secret ?? ''
        );
    }

    /**
     * Membersihkan nilai dinamis (nama ONU, username, password, VLAN, dsb) sebelum
     * disisipkan ke command CLI OLT. Karakter kontrol (CR/LF/TAB) dan
     * separator/quote dihapus agar nilai tidak dapat memutus perintah atau
     * keluar dari konteks argumen (mencegah command injection ke perangkat).
     */
    protected function sanitizeCliValue(?string $value): string
    {
        return trim(str_replace(
            ["\r", "\n", "\t", "\0", ';', '"', "'", '`', '|'],
            '',
            (string) $value
        ));
    }

    public function getSystemInfo(): array
    {
        try {
            // Lakukan PING (sysUpTime) pertama kali. Jika gagal, langsung abort agar tidak menunggu timeout berlipat.
            $sysUpTime = $this->snmp->get($this->systemOids['sysUpTime']);

            if ($sysUpTime === false || $sysUpTime === '' || $sysUpTime === null) {
                Log::warning('OLT SNMP sysUpTime kosong (connection refused/timeout)', [
                    'olt_id' => $this->olt->id,
                    'ip' => $this->olt->ip_address,
                    'community' => $this->snmpCommunityRead,
                    'version' => $this->snmpVersion,
                ]);
                return [
                    'name' => $this->olt->name,
                    'uptime' => 'N/A',
                    'description' => 'N/A',
                    'firmware' => null,
                    'temperature' => 0,
                    'ip_address' => $this->olt->ip_address,
                    'model' => $this->olt->model,
                    'status' => 'offline',
                    'error' => 'SNMP Timeout / Unreachable',
                ];
            }

            // Jika sysUpTime berhasil, OLT dipastikan online. Lanjut ambil metrik lainnya.
            $sysName = $this->snmp->get($this->systemOids['sysName']);
            $sysDescr = $this->snmp->get($this->systemOids['sysDescr']);

            $firmware = $this->extractFirmwareVersion(
                is_string($sysDescr) ? $sysDescr : '',
                is_string($this->olt->firmware_version) ? $this->olt->firmware_version : null
            );

            return [
                'name' => $sysName !== false ? $sysName : $this->olt->name,
                'uptime' => $this->formatUptime((string)$sysUpTime),
                'description' => $sysDescr !== false ? $sysDescr : 'N/A',
                'firmware' => $firmware,
                'temperature' => $this->getTemperature(),
                'ip_address' => $this->olt->ip_address,
                'model' => $this->olt->model,
                'status' => 'online',
            ];
        } catch (Exception $e) {
            Log::warning('OLT SNMP failed: ' . $e->getMessage(), [
                'olt_id' => $this->olt->id,
                'ip' => $this->olt->ip_address,
            ]);
            return [
                'name' => $this->olt->name,
                'uptime' => 'N/A',
                'description' => 'N/A',
                'temperature' => 0,
                'ip_address' => $this->olt->ip_address,
                'model' => $this->olt->model,
                'status' => 'offline',
                'error' => $e->getMessage(),
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

    public function deleteOnu(Onu $onu): bool
    {
        Log::warning("deleteOnu is not implemented for OLT driver: " . static::class, ['onu_id' => $onu->id]);
        return false;
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
        $trimmed = trim($rawTicks);
        if ($trimmed === '' || $trimmed === 'N/A' || $trimmed === '0') {
            return $trimmed === '0' ? '0 menit' : 'N/A';
        }

        if (!is_numeric($trimmed)) {
            if (preg_match('/\((\d+)\)/', $trimmed, $m)) {
                $ticks = (int)$m[1];
            } elseif (preg_match('/^(\d+):(\d+):(\d+):(\d+)\.\d+$/', $trimmed, $m)) {
                // Parse format D:H:M:S.ms dari snmpget -O qv
                $days = (int)$m[1];
                $hours = (int)$m[2];
                $mins = (int)$m[3];
                $result = [];
                if ($days > 0) $result[] = $days . ' hari';
                if ($hours > 0) $result[] = $hours . ' jam';
                if ($mins > 0) $result[] = $mins . ' menit';
                return empty($result) ? '< 1 menit' : implode(' ', $result);
            } else {
                return $trimmed; // Jangan return N/A jika format tidak dikenali, return string aslinya agar tidak dianggap offline
            }
        } else {
            $ticks = (int)$trimmed;
        }

        if ($ticks <= 0) {
            return 'N/A';
        }
        $seconds = (int)($ticks / 100);
        if ($seconds < 60) {
            return '< 1 menit';
        }
        $days = (int)($seconds / 86400);
        $hours = (int)(($seconds % 86400) / 3600);
        $minutes = (int)(($seconds % 3600) / 60);
        if ($days > 0) {
            return sprintf('%d hari, %d jam %d menit', $days, $hours, $minutes);
        }
        if ($hours > 0) {
            return sprintf('%d jam %d menit', $hours, $minutes);
        }
        return sprintf('%d menit', $minutes);
    }

    protected function extractFirmwareVersion(string $sysDescr, ?string $default = null): ?string
    {
        if (preg_match('/(?:Version|Ver|V|FW|Firmware)\s*[:=]?\s*([vV\d\.]+)/i', $sysDescr, $matches)) {
            return $matches[1];
        }
        return $default ?? 'N/A';
    }

    public static function isValidOnuSerial(string $sn): bool
    {
        $sn = strtoupper(trim($sn));
        if (strlen($sn) < 6 || strlen($sn) > 24) {
            return false;
        }

        $blacklist = [
            'AUTOMATICALLY', 'AUTOMATIC', 'UNCONFIGURED', 'UNREGISTERED', 'UNREGISTER',
            'UNAUTHORIZED', 'AUTHENTICATION', 'AUTHENTICATED', 'DESCRIPTION', 'INFORMATION',
            'SUCCESSFULLY', 'CONFIGURATION', 'SERIALNUMBER', 'DISCONNECTED', 'ADMINISTRATION',
            'MANUFACTURER', 'MACADDRESS', 'EQUIPMENTID', 'SOFTWAREVER', 'HARDWAREVER',
            'COMMAND', 'UNKNOWN', 'INTERFACE', 'PASSWORD', 'PORTNAME', 'AUTOFIND',
            'DISABLED', 'ENABLED', 'CONNECTED', 'OFFLINE', 'ONLINE', 'PROFILE',
            'BLANK', 'NONE', 'NULL', 'DEFAULT',
        ];

        if (in_array($sn, $blacklist, true)) {
            return false;
        }

        // Teks murni alfabet tanpa angka bukan serial number perangkat
        if (preg_match('/^[A-Z]+$/', $sn)) {
            return false;
        }

        // Format standar ITU-T GPON: 4 huruf kapital (Vendor ID) diikuti 7-8 karakter heksadesimal (e.g. ZTEGC408F02, HWTC12345678)
        if (preg_match('/^[A-Z]{4}[0-9A-F]{7,8}$/i', $sn)) {
            return true;
        }

        // Format 16-heksadesimal yang memiliki setidaknya satu angka
        if (preg_match('/^[0-9A-F]{16}$/i', $sn) && preg_match('/[0-9]/', $sn)) {
            return true;
        }

        // Format MAC Address EPON (12 hex chars atau dipisah :/-)
        $cleanMac = str_replace([':', '-', '.'], '', $sn);
        if (strlen($cleanMac) === 12 && ctype_xdigit($cleanMac) && preg_match('/[0-9]/', $cleanMac)) {
            return true;
        }

        // Vendor prefix GPON terdaftar (ZTEG, HWTC, CDAT, CIOT, FHTT, ALCL, VSOL, HSGQ, dll)
        if (preg_match('/^(ZTEG|HWTC|CDAT|CIOT|FHTT|ALCL|VSOL|HSGQ|BDCM|MSTC|ISKT)[0-9A-F]{6,12}$/i', $sn)) {
            return true;
        }

        return false;
    }

    /**
     * Fallback Universal untuk mendeteksi PON Ports menggunakan Standard IF-MIB (RFC 1213 / RFC 2863)
     * ataupun dari konfigurasi OLT pon_port_count di database.
     */
    public function getStandardIfMibPonPorts(): array
    {
        $ports = [];
        try {
            // 1. Coba ifName (.1.3.6.1.2.1.31.1.1.1.1) atau ifDescr (.1.3.6.1.2.1.2.2.1.2)
            $ifNames = $this->snmp->walk('.1.3.6.1.2.1.31.1.1.1.1') ?: [];
            if (empty($ifNames)) {
                $ifNames = $this->snmp->walk('.1.3.6.1.2.1.2.2.1.2') ?: [];
            }
            $ifStatuses = $this->snmp->walk('.1.3.6.1.2.1.2.2.1.8') ?: [];

            $statusMap = [];
            foreach ($ifStatuses as $k => $st) {
                $idx = (int)preg_replace('/^.*?(\d+)$/', '$1', (string)$k);
                $statusMap[$idx] = (int)$st;
            }

            foreach ($ifNames as $k => $name) {
                $idx = (int)preg_replace('/^.*?(\d+)$/', '$1', (string)$k);
                $cleanName = trim((string)$name);
                $cleanName = preg_replace('/^(STRING|Hex-STRING|OctetString)\s*:?\s*/i', '', $cleanName);
                $cleanName = trim($cleanName, '" ');

                $lower = strtolower($cleanName);
                if (
                    str_contains($lower, 'pon') ||
                    str_contains($lower, 'epon') ||
                    str_contains($lower, 'gpon') ||
                    str_contains($lower, 'olt')
                ) {
                    $st = $statusMap[$idx] ?? 1;
                    $ports[] = [
                        'port_index' => $idx > 0 ? $idx : (count($ports) + 1),
                        'port_name'  => $cleanName,
                        'status'     => match ($st) {
                            1 => 'up',
                            2 => 'down',
                            default => 'unknown'
                        },
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::debug("getStandardIfMibPonPorts exception: " . $e->getMessage());
        }

        // 2. Jika user mengonfigurasi pon_port_count secara spesifik di database (misal 2 port)
        $userConfiguredCount = (int)($this->olt->pon_port_count ?? 0);
        if ($userConfiguredCount > 0 && count($ports) > $userConfiguredCount) {
            $ports = array_slice($ports, 0, $userConfiguredCount);
        }

        // 3. Jika Standard IF-MIB tidak menghasilkan port PON atau enterprise MIB kosong,
        // namun OLT online (menjawab sysUpTime), gunakan konfigurasi pon_port_count dari database!
        if (empty($ports)) {
            $count = $userConfiguredCount > 0 ? $userConfiguredCount : 2; // Default 2 PON untuk pizza box OLT jika belum diset
            $oltType = str_contains(strtolower(($this->olt->model ?? '') . ' ' . ($this->olt->name ?? '')), 'epon') ? 'EPON' : 'GPON';
            for ($p = 1; $p <= $count; $p++) {
                $ports[] = [
                    'port_index' => $p,
                    'port_name'  => "{$oltType} 0/0/{$p}",
                    'status'     => 'up',
                ];
            }
        }

        return $ports;
    }

    abstract public function getPonPortsStatus(): array;
    abstract public function getOnuRxPower(int $ponPort): array;
    abstract public function discoverUnregisteredOnus(): array;
    abstract public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool;
    abstract public function setOnuAdminStatus(Onu $onu, string $status): bool;
    abstract public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool;

    /**
     * Get Optical Tx Power of the OLT SFP Transceiver for this PON port (dBm)
     */
    public function getPonPortTxPower(int $ponPort): ?float
    {
        return null;
    }

    /**
     * Get RTT Ranging Distance for ONUs under this PON port (in meters)
     * Returns array [onu_index => distance_in_meters]
     */
    public function getOnuDistances(int $ponPort): array
    {
        return [];
    }

    public function pushOnuWanConfig(Onu $onu, array $config): bool
    {
        Log::warning("pushOnuWanConfig is not implemented for this OLT driver: " . static::class);
        return false;
    }
}
