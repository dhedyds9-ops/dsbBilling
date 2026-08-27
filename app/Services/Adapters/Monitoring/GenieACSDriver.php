<?php

namespace App\Services\Adapters\Monitoring;

use App\Models\ISP\Onu;
use App\Models\ISP\Vendor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Exception;

class GenieACSDriver
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
        $this->username = config('genieacs.username', 'admin');
        $this->password = config('genieacs.password', 'admin');
        $this->timeout = (int)config('genieacs.timeout', 30);
    }

    private function getWifiParameterPath(string $vendor = 'default', string $parameter = 'wpa_passphrase'): string
    {
        $vendors = config('onu-vendors.vendors', []);
        $vendorKey = strtolower(trim($vendor));
        if (!isset($vendors[$vendorKey])) {
            $fuzzy = collect(array_keys($vendors))->first(fn ($k) => str_contains($vendorKey, $k) || str_contains($k, $vendorKey));
            $vendorKey = $fuzzy ?? 'default';
        }
        return $vendors[$vendorKey]['wifi_parameters'][$parameter] ?? $vendors['default']['wifi_parameters'][$parameter] ?? '';
    }

    public function listDevices(array $query = [], int $limit = 100): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->get("{$this->baseUrl}/devices", array_merge(['limit' => $limit], $query));
            if ($response->successful()) {
                return $response->json();
            }
            throw new Exception("GenieACS listDevices failed: HTTP " . $response->status());
        } catch (ConnectionException|RequestException $e) {
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function getDeviceParameters(string $deviceId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/devices/{$deviceId}");
            if ($response->successful()) {
                return $response->json() ?: [];
            }
            throw new Exception("Failed to get device parameters: " . $response->status());
        } catch (ConnectionException|RequestException $e) {
            if ($e->response?->status() === 404) {
                throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function getDeviceSignal(string $deviceId): array
    {
        $params = $this->getDeviceParameters($deviceId);
        $rx = $tx = $snr = $temp = null;

        $extract = function (string $path) use ($params) {
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) {
                    return null;
                }
                $node = $node[$p];
            }
            if (is_array($node) && isset($node['_value'])) {
                return $node['_value'];
            }
            if (is_scalar($node)) {
                return $node;
            }
            return null;
        };

        $candidates = [
            ['rx' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower',
             'tx' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower',
             'snr'=> 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.*',
             'temp'=>'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_Temperature'],
            ['rx' => 'Device.Optical.1.Transceiver.RxPower',
             'tx' => 'Device.Optical.1.Transceiver.TxPower',
             'snr'=> 'Device.Optical.1.XPON.SNRMarginDownstream',
             'temp'=>'Device.Optical.1.Transceiver.Temperature'],
            ['rx' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_ALG-COM_RxPower',
             'tx' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_ALG-COM_TxPower',
             'snr'=> null, 'temp' => null],
        ];
        foreach ($candidates as $set) {
            $candidateRx = $extract($set['rx']);
            if ($candidateRx !== null && $candidateRx !== '' && $candidateRx !== 0) {
                $rx = is_numeric($candidateRx) ? (float)$candidateRx / (($candidateRx > 1000 || $candidateRx < -1000) ? 1000 : 1) : null;
                $tx = $set['tx'] ? ($extract($set['tx']) ?: null) : null;
                $snr = $set['snr'] ? ($extract($set['snr']) ?: null) : null;
                $temp = $set['temp'] ? ($extract($set['temp']) ?: null) : null;
                break;
            }
        }

        $lastInform = $params['_lastInform'] ?? null;
        $online = $lastInform && now()->diffInMinutes(Carbon::parse($lastInform)) < 5;

        return [
            'device_id' => $deviceId,
            'online' => $online,
            'last_inform' => $lastInform,
            'rx_power_dbm' => $rx,
            'tx_power_dbm' => $tx,
            'snr_db' => $snr,
            'temperature' => $temp,
            'firmware_version' => $params['InternetGatewayDevice.DeviceInfo.SoftwareVersion._value']
                ?? $params['Device.DeviceInfo.SoftwareVersion._value'] ?? null,
            'hardware_version' => $params['InternetGatewayDevice.DeviceInfo.HardwareVersion._value']
                ?? $params['Device.DeviceInfo.HardwareVersion._value'] ?? null,
            'manufacturer' => $params['InternetGatewayDevice.DeviceInfo.Manufacturer._value']
                ?? $params['Device.DeviceInfo.Manufacturer._value'] ?? null,
            'raw' => $params,
        ];
    }

    public function updateWifiPassword(string $deviceId, string $newPassword, string $vendor = 'default'): bool
    {
        return $this->setParameterValues($deviceId, [
            $this->getWifiParameterPath($vendor, 'wpa_passphrase') => $newPassword,
            $this->getWifiParameterPath($vendor, 'wpa_pre_shared_key') => $newPassword,
            $this->getWifiParameterPath($vendor, 'security_mode') => 'WPA2PSK',
        ]);
    }

    public function updateWifiSsid(string $deviceId, string $ssid, string $vendor = 'default'): bool
    {
        return $this->setParameterValues($deviceId, [
            $this->getWifiParameterPath($vendor, 'ssid') => $ssid,
        ]);
    }

    public function setParameterValues(string $deviceId, array $keyValuePairs): bool
    {
        try {
            $parameterValues = [];
            foreach ($keyValuePairs as $path => $value) {
                if ($path === '' || $path === null) {
                    continue;
                }
                $type = is_bool($value) ? 'xsd:boolean' : (is_int($value) ? 'xsd:int' : (is_numeric($value) ? 'xsd:unsignedInt' : 'xsd:string'));
                if ($type === 'xsd:unsignedInt' && str_contains($path, 'SSID')) {
                    $type = 'xsd:string';
                }
                $parameterValues[] = ['name' => $path, 'value' => $value, 'type' => $type];
            }
            if (count($parameterValues) === 0) {
                return false;
            }
            $payload = ['name' => 'setParameterValues', 'parameterValues' => $parameterValues];
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/{$deviceId}/tasks", $payload);
            if ($response->successful()) {
                return true;
            }
            $body = (string)$response->body();
            throw new Exception("setParameterValues HTTP {$response->status()}: {$body}");
        } catch (ConnectionException|RequestException $e) {
            if ($e->response?->status() === 404) {
                throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function rebootDevice(string $deviceId): bool
    {
        try {
            $payload = ['name' => 'reboot'];
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/{$deviceId}/tasks", $payload);
            return $response->successful();
        } catch (ConnectionException|RequestException $e) {
            if ($e->response?->status() === 404) {
                throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function factoryResetDevice(string $deviceId): bool
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/{$deviceId}/tasks", ['name' => 'factoryReset']);
            return $response->successful();
        } catch (ConnectionException|RequestException $e) {
            throw new Exception("GenieACS factoryReset error: " . $e->getMessage(), 0, $e);
        }
    }

    public function isDeviceOnline(string $deviceId): bool
    {
        try {
            $params = $this->getDeviceParameters($deviceId);
            return isset($params['_lastInform'])
                && now()->diffInMinutes(Carbon::parse($params['_lastInform'])) < 5;
        } catch (Exception) {
            return false;
        }
    }

    public function getTasks(string $deviceId, ?string $status = null): array
    {
        $url = "{$this->baseUrl}/devices/{$deviceId}/tasks";
        if ($status !== null) {
            $url .= "?status=" . urlencode($status);
        }
        try {
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get($url);
            return $r->successful() ? $r->json() : [];
        } catch (Exception) {
            return [];
        }
    }

    public function listProvisions(): array
    {
        try {
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get("{$this->baseUrl}/provisions");
            return $r->successful() ? $r->json() : [];
        } catch (Exception) {
            return [];
        }
    }

    public function upsertProvision(string $provisionName, string $javascriptCode, int $weight = 0): bool
    {
        try {
            $existing = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/provisions/{$provisionName}");
            $method = $existing->successful() ? 'put' : 'post';
            $url = $existing->successful()
                ? "{$this->baseUrl}/provisions/{$provisionName}"
                : "{$this->baseUrl}/provisions";
            $payload = array_merge(
                $existing->successful() ? [] : ['_id' => $provisionName],
                [
                    'weight' => $weight,
                    'script' => $javascriptCode,
                ]
            );
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->{$method}($url, $payload);
            return $response->successful();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function addDevice(string $deviceId, array $metadata = []): bool
    {
        try {
            $payload = array_merge(['_id' => $deviceId], $metadata);
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->put("{$this->baseUrl}/devices/{$deviceId}", $payload);
            return $response->successful() || $response->status() === 409;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }
}
