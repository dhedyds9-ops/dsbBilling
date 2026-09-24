<?php

namespace App\Services\Adapters\Provisioning\Support;

use Exception;

class SnmpClient
{
    protected string $host;
    protected string $community;
    protected string $version;
    protected int $timeout;
    protected int $retries;

    protected static ?bool $extSnmpAvailable = null;
    protected static ?bool $cliSnmpAvailable = null;

    public function __construct(string $host, string $community = 'public', string $version = '2c', int $timeout = 5, int $retries = 2, int $port = 161)
    {
        if ($host === '' || $host === '0') {
            throw new Exception("Host SNMP tidak valid (empty).");
        }
        if (!in_array($version, ['1', '2c', '3'], true)) {
            throw new Exception("SNMP version tidak valid: {$version}. Hanya '1', '2c', atau '3' yang didukung.");
        }
        $this->host = $port === 161 ? $host : $host . ':' . $port;
        $this->community = $community;
        $this->version = $version;
        $this->timeout = max(1, $timeout) * 1000000;
        $this->retries = max(0, $retries);

        $this->ensureBackendAvailable();
    }

    protected function ensureBackendAvailable(): void
    {
        if (self::$extSnmpAvailable === null) {
            self::$extSnmpAvailable = function_exists('snmpget');
        }
        if (self::$extSnmpAvailable) {
            return;
        }

        if (self::$cliSnmpAvailable === null) {
            self::$cliSnmpAvailable = $this->detectCliSnmp();
        }

        if (!self::$extSnmpAvailable && !self::$cliSnmpAvailable) {
            throw new Exception($this->buildUnavailableMessage());
        }
    }

    protected function detectCliSnmp(): bool
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $where = @shell_exec('where snmpget 2>nul');
                if ($where !== null && trim($where) !== '' && !str_contains($where, 'Could not find files')) {
                    return true;
                }
                return false;
            }
            $which = @shell_exec('command -v snmpget 2>/dev/null');
            return $which !== null && trim($which) !== '';
        } catch (Exception) {
            return false;
        }
    }

    protected function buildUnavailableMessage(): string
    {
        $windowsFix = PHP_OS_FAMILY === 'Windows'
            ? ' [WINDOWS FIX:] Buka php.ini lalu hapus tanda ";" di baris: ;extension=snmp  =>  jadi: extension=snmp.dll  (kemudian restart Apache/Nginx/Laragon). Jika tetap gagal, install Net-SNMP Windows binary dari net-snmp.org dan tambahkan ke PATH.'
            : ' [LINUX/MAC FIX:] sudo apt-get install php-snmp net-snmp / sudo dnf install php-snmp net-snmp-utils, lalu restart web server.';

        return "TIDAK ADA BACKEND SNMP YANG TERSEDIA. PHP SNMP extension (fungsi snmpget) tidak aktif, dan binary CLI `snmpget` tidak ditemukan di PATH." . $windowsFix;
    }

    public function get(string $oid): string|bool
    {
        if ($oid === '') {
            return false;
        }
        if (self::$extSnmpAvailable) {
            // Coba dengan fungsi bawaan PHP
            // Set SNMP options agar tidak error MIB
            @snmp_set_oid_numeric_print(true);
            @snmp_set_quick_print(true);
            @snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
            
            $result = @snmpget($this->host, $this->community, $oid, $this->timeout, $this->retries);
            if ($result !== false) {
                return $this->parseValue($result);
            }
            // Jika gagal (mungkin karena MIB), JANGAN langsung false, tapi fallback ke CLI!
        }
        return $this->fallbackSnmpGet($oid);
    }

    public function walk(string $oid): array
    {
        if ($oid === '') {
            return [];
        }
        if (self::$extSnmpAvailable) {
            $result = @snmprealwalk($this->host, $this->community, $oid, $this->timeout, $this->retries);
            if ($result === false) {
                return [];
            }
            $parsed = [];
            foreach ($result as $key => $value) {
                $parsed[str_replace($oid . '.', '', $key)] = $this->parseValue($value);
            }
            return $parsed;
        }
        return $this->fallbackSnmpWalk($oid);
    }

    public function set(string $oid, string $type, mixed $value): bool
    {
        if (self::$extSnmpAvailable && function_exists('snmpset')) {
            return @snmpset($this->host, $this->community, $oid, $type, $value, $this->timeout, $this->retries);
        }
        return false;
    }

    protected function parseValue(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^(STRING|Counter\d*|Gauge\d*|Integer|OctetString|Hex-?)\s*:?\s*(.*)$/is', $raw, $m)) {
            $value = trim($m[2]);
            if ($m[1] === 'Hex-STRING' || str_starts_with(strtoupper($value), '0X')) {
                return $this->hexToAscii($value);
            }
            if ($m[1] === 'STRING' && str_starts_with($value, '"') && str_ends_with($value, '"')) {
                return trim($value, '"');
            }
            return $value;
        }
        if (str_starts_with(strtoupper($raw), '0X')) {
            return $this->hexToAscii($raw);
        }
        return $raw;
    }

    protected function hexToAscii(string $hex): string
    {
        $hex = preg_replace('/^0x/i', '', $hex);
        $hex = preg_replace('/\s+/', '', $hex);
        if ($hex === '') {
            return '';
        }
        $chars = str_split($hex, 2);
        $ascii = '';
        foreach ($chars as $char) {
            if (ctype_xdigit($char)) {
                $dec = hexdec($char);
                if ($dec >= 32 && $dec <= 126 || $dec > 160) {
                    $ascii .= chr($dec);
                }
            }
        }
        return trim($ascii);
    }

    protected function isCliErrorMessage(string $output): bool
    {
        $lower = strtolower($output);
        return str_contains($lower, 'is not recognized')
            || str_contains($lower, 'not found')
            || str_contains($lower, 'no such file or directory')
            /* || str_contains($lower, 'cannot find') */
            || str_contains($lower, 'the term \'')
            || str_contains($lower, 'is not an internal or external command')
            || str_contains($lower, 'timeout:')
            || str_contains($lower, 'error response')
            || str_contains($lower, 'permission denied')
            || str_contains($lower, 'failed');
    }

    protected function fallbackSnmpGet(string $oid): string|bool
    {
        try {
            $cmd = sprintf(
                'snmpget -O qv -v %s -c %s -t %d -r %d %s %s 2>/dev/null',
                escapeshellarg($this->version),
                escapeshellarg($this->community),
                (int)($this->timeout / 1000000),
                $this->retries,
                escapeshellarg($this->host),
                escapeshellarg($oid)
            );
            $output = @shell_exec($cmd);
            if ($output === null) {
                return false;
            }
            $output = trim($output);
            if ($output === '' || $this->isCliErrorMessage($output)) {
                return false;
            }
            if (stripos($output, 'No Such Instance') !== false || stripos($output, 'No Such Object') !== false) {
                return false;
            }
            return $this->parseValue($output);
        } catch (Exception) {
            return false;
        }
    }

    protected function fallbackSnmpWalk(string $oid): array
    {
        try {
            $cmd = sprintf(
                'snmpwalk -O q -v %s -c %s -t %d -r %d %s %s 2>/dev/null',
                escapeshellarg($this->version),
                escapeshellarg($this->community),
                (int)($this->timeout / 1000000),
                $this->retries,
                escapeshellarg($this->host),
                escapeshellarg($oid)
            );
            $output = @shell_exec($cmd);
            if ($output === null) {
                return [];
            }
            $output = trim($output);
            if ($output === '' || $this->isCliErrorMessage($output)) {
                return [];
            }
            $lines = explode("\n", $output);
            $parsed = [];
            foreach ($lines as $line) {
                $parts = explode(' = ', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    if ($this->isCliErrorMessage($value)) {
                        continue;
                    }
                    $oidSuffix = str_replace($oid . '.', '', $key);
                    $parsed[$oidSuffix] = $this->parseValue($value);
                }
            }
            return $parsed;
        } catch (Exception) {
            return [];
        }
    }
}


