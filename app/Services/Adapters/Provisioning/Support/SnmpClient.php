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

    public function __construct(string $host, string $community = 'public', string $version = '2c', int $timeout = 2, int $retries = 2)
    {
        $this->host = $host;
        $this->community = $community;
        $this->version = $version;
        $this->timeout = $timeout * 1000000;
        $this->retries = $retries;
    }

    public function get(string $oid): string|bool
    {
        if (!function_exists('snmpget')) {
            return $this->fallbackSnmpGet($oid);
        }
        $result = @snmpget($this->host, $this->community, $oid, $this->timeout, $this->retries);
        if ($result === false) {
            return false;
        }
        return $this->parseValue($result);
    }

    public function walk(string $oid): array
    {
        if (!function_exists('snmprealwalk')) {
            return $this->fallbackSnmpWalk($oid);
        }
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

    public function set(string $oid, string $type, mixed $value): bool
    {
        if (!function_exists('snmpset')) {
            return false;
        }
        return @snmpset($this->host, $this->community, $oid, $type, $value, $this->timeout, $this->retries);
    }

    protected function parseValue(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/^(STRING|Counter\d*|Gauge\d*|Integer|OctetString|Hex-?)\s*:?\s*(.*)$/i', $raw, $m)) {
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

    protected function fallbackSnmpGet(string $oid): string|bool
    {
        try {
            $cmd = sprintf(
                'snmpget -O qv -v %s -c %s -t %d -r %d %s %s 2>&1',
                escapeshellarg($this->version),
                escapeshellarg($this->community),
                (int)($this->timeout / 1000000),
                $this->retries,
                escapeshellarg($this->host),
                escapeshellarg($oid)
            );
            $output = @shell_exec($cmd);
            if ($output === null || trim($output) === '' || str_contains($output, 'No Such Instance')) {
                return false;
            }
            return $this->parseValue(trim($output));
        } catch (Exception) {
            return false;
        }
    }

    protected function fallbackSnmpWalk(string $oid): array
    {
        try {
            $cmd = sprintf(
                'snmpwalk -O q -v %s -c %s -t %d -r %d %s %s 2>&1',
                escapeshellarg($this->version),
                escapeshellarg($this->community),
                (int)($this->timeout / 1000000),
                $this->retries,
                escapeshellarg($this->host),
                escapeshellarg($oid)
            );
            $output = @shell_exec($cmd);
            if ($output === null || trim($output) === '') {
                return [];
            }
            $lines = explode("\n", $output);
            $parsed = [];
            foreach ($lines as $line) {
                $parts = explode(' = ', $line, 2);
            if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
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
