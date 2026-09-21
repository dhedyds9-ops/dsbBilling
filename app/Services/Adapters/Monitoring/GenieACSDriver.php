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
        $settings = [];
        try {
            $settings = \App\Models\Setting::getValue('connection.acs', []);
        } catch (\Exception $e) {
        }
        $this->baseUrl = rtrim($settings['base_url'] ?? config('genieacs.base_url', 'http://localhost:7557'), '/');
        $this->username = $settings['connection_request_username'] ?? $settings['username'] ?? config('genieacs.username', 'admin');
        $this->password = $settings['connection_request_password'] ?? $settings['password'] ?? config('genieacs.password', 'admin');
        $this->timeout = (int)($settings['timeout'] ?? config('genieacs.timeout', 10));
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
                ->timeout(120) // Increased from $this->timeout for large payloads
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
            $query = urlencode(json_encode(['_id' => $deviceId]));
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/devices/?query={$query}");
                
            if ($response->successful()) {
                $data = $response->json();
                return $data[0] ?? [];
            }
            throw new Exception("Failed to get device parameters: " . $response->status());
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
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
        $online = $lastInform && abs(now()->diffInMinutes(Carbon::parse($lastInform))) < 5;

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

    public function getWifiCredentials(string $deviceId, string $vendor = 'default'): array
    {
        $params = $this->getDeviceParameters($deviceId);
        
        $ssidPath = $this->getWifiParameterPath($vendor, 'ssid');
        $passPath = $this->getWifiParameterPath($vendor, 'wpa_passphrase');
        
        $extract = function (string $path) use ($params) {
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) return null;
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

        return [
            'ssid' => $extract($ssidPath),
            'password' => $extract($passPath),
        ];
    }

    public function updateWifiPassword(string $deviceId, string $newPassword, string $vendor = 'default'): bool
    {
        // Try to guess correct security value based on path
        $secPath = $this->getWifiParameterPath($vendor, 'security_mode');
        $secValue = str_contains($secPath, 'BeaconType') ? '11i' : 'WPA2-Personal';

        return $this->setParameterValues($deviceId, [
            $this->getWifiParameterPath($vendor, 'wpa_passphrase') => $newPassword,
            $this->getWifiParameterPath($vendor, 'wpa_pre_shared_key') => $newPassword,
            $secPath => $secValue,
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
                $parameterValues[] = [$path, $value, $type];
            }
            if (count($parameterValues) === 0) {
                return false;
            }
            $payload = ['name' => 'setParameterValues', 'parameterValues' => $parameterValues];
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", $payload);
                
            // Trigger connection request asynchronously without blocking
            try {
                Http::withBasicAuth($this->username, $this->password)
                    ->timeout(1)
                    ->asJson()
                    ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks?connection_request", ['name' => 'refreshObject', 'objectName' => '']);
            } catch (\Exception $e) {}
            if ($response->successful()) {
                return true;
            }
            $body = (string)$response->body();
            throw new Exception("setParameterValues HTTP {$response->status()}: {$body}");
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
                throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function provisionPppoe(string $deviceId, string $username, string $password, ?string $vlanId = null): bool
    {
        try {
            $payload = [
                'name' => 'run_provision',
                'provision' => 'dsBilling_Setup_WAN',
                'args' => [$username, $password, $vlanId ?? ""]
            ];
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", $payload);
                
            // Trigger connection request asynchronously without blocking
            try {
                Http::withBasicAuth($this->username, $this->password)
                    ->timeout(1)
                    ->asJson()
                    ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks?connection_request", ['name' => 'refreshObject', 'objectName' => '']);
            } catch (\Exception $e) {}
            if ($response->successful()) {
                return true;
            }
            $body = (string)$response->body();
            throw new Exception("provisionPppoe HTTP {$response->status()}: {$body}");
        } catch (Exception $e) {
            throw new Exception("GenieACS PPPoE Provisioning failed: " . $e->getMessage(), 0, $e);
        }
    }

    public function rebootDevice(string $deviceId): bool
    {
        try {
            $payload = ['name' => 'reboot'];
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", $payload);
                
            // Trigger connection request asynchronously without blocking
            try {
                Http::withBasicAuth($this->username, $this->password)
                    ->timeout(1)
                    ->asJson()
                    ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks?connection_request", ['name' => 'refreshObject', 'objectName' => '']);
            } catch (\Exception $e) {}
            return $response->successful();
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
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
                ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", ['name' => 'factoryReset']);
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
                && abs(now()->diffInMinutes(\Carbon\Carbon::parse($params['_lastInform']))) < 15;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function summonDevice(string $deviceId): bool
    {
        try {
            $response = Http::timeout(5)
                ->withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", [
                    'name' => 'refreshObject',
                    'objectName' => ''
                ]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getTasks(string $deviceId, ?string $status = null): array
    {
        $url = "{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks";
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
            // In GenieACS v1.2, you simply PUT the raw javascript string to /provisions/{name}
            $url = "{$this->baseUrl}/provisions/{$provisionName}";
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->withBody($javascriptCode, 'text/plain')
                ->put($url);
            
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
                ->put("{$this->baseUrl}/devices/" . urlencode($deviceId), $payload);
            return $response->successful() || $response->status() === 409;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function getConnectedDevices(string $deviceId): array
    {
        $params = $this->getDeviceParameters($deviceId);
        
        $hosts = [];
        $landevices = $params['InternetGatewayDevice']['LANDevice'] ?? [];
        foreach ($landevices as $lanIdx => $lanDev) {
            if ($lanIdx === '_object' || $lanIdx === '_timestamp' || $lanIdx === '_writable') continue;
            
            if (isset($lanDev['Hosts']['Host'])) {
                foreach ($lanDev['Hosts']['Host'] as $idx => $host) {
                    if ($idx === '_object' || $idx === '_timestamp' || $idx === '_writable') continue;
                    $hosts[] = [
                        'MACAddress' => $host['MACAddress']['_value'] ?? $host['MACAddress'] ?? 'Unknown',
                        'IPAddress' => $host['IPAddress']['_value'] ?? $host['IPAddress'] ?? 'Unknown',
                        'HostName' => $host['HostName']['_value'] ?? $host['HostName'] ?? 'Unknown',
                        'Active' => (($host['Active']['_value'] ?? $host['Active'] ?? '0') == '1' || ($host['Active']['_value'] ?? $host['Active'] ?? '0') === true),
                        'InterfaceType' => $host['InterfaceType']['_value'] ?? $host['InterfaceType'] ?? 'Unknown',
                    ];
                }
            }
        }
        
        return $hosts;
    }

    public function getWlanStats(string $deviceId, int $wlanIndex = 1): array
    {
        $params = $this->getDeviceParameters($deviceId);
        $wlan = $params['InternetGatewayDevice']['LANDevice'][1]['WLANConfiguration'][$wlanIndex] ?? null;
        if (!$wlan) return [];
        
        return [
            'TotalBytesSent' => $wlan['TotalBytesSent']['_value'] ?? $wlan['TotalBytesSent'] ?? 0,
            'TotalBytesReceived' => $wlan['TotalBytesReceived']['_value'] ?? $wlan['TotalBytesReceived'] ?? 0,
            'TotalPacketsSent' => $wlan['TotalPacketsSent']['_value'] ?? $wlan['TotalPacketsSent'] ?? 0,
            'TotalPacketsReceived' => $wlan['TotalPacketsReceived']['_value'] ?? $wlan['TotalPacketsReceived'] ?? 0,
        ];
    }

    public function blockMacAddress(string $deviceId, string $macToBlock, int $wlanIndex = 1): bool
    {
        $params = $this->getDeviceParameters($deviceId);
        $enabledPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlEnabled";
        $listPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlList";

        $extractPath = function($path) use ($params) {
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) return null;
                $node = $node[$p];
            }
            return $node;
        };

        if ($extractPath($enabledPath) === null) {
            throw new Exception("Fitur MAC Filtering tidak didukung atau path tidak ditemukan.");
        }

        $currentList = $extractPath($listPath)['_value'] ?? '';
        $macs = array_filter(array_map('trim', explode(',', $currentList)));

        if (!in_array($macToBlock, $macs)) {
            $macs[] = $macToBlock;
        }

        return $this->setParameterValues($deviceId, [
            $enabledPath => true,
            $listPath => implode(',', $macs)
        ]);
    }

    public function unblockMacAddress(string $deviceId, string $macToUnblock, int $wlanIndex = 1): bool
    {
        $params = $this->getDeviceParameters($deviceId);
        $enabledPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlEnabled";
        $listPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.MACAddressControlList";

        $extractPath = function($path) use ($params) {
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) return null;
                $node = $node[$p];
            }
            return $node;
        };

        if ($extractPath($enabledPath) === null) {
            throw new Exception("Fitur MAC Filtering tidak didukung atau path tidak ditemukan.");
        }

        $currentList = $extractPath($listPath)['_value'] ?? '';
        $macs = array_filter(array_map('trim', explode(',', $currentList)));

        $macs = array_values(array_filter($macs, fn($m) => $m !== $macToUnblock));

        return $this->setParameterValues($deviceId, [
            $listPath => implode(',', $macs)
        ]);
    }



    public function updateWifiSsidAndPassword(string $deviceId, string $ssid, ?string $password = null, string $vendor = 'default'): bool
    {
        $params = [
            $this->getWifiParameterPath($vendor, 'ssid') => $ssid,
        ];
        
        if ($password !== null) {
            $secPath = $this->getWifiParameterPath($vendor, 'security_mode');
            $secValue = str_contains($secPath, 'BeaconType') ? '11i' : 'WPA2-Personal';
            
            $params[$this->getWifiParameterPath($vendor, 'wpa_passphrase')] = $password;
            $params[$this->getWifiParameterPath($vendor, 'wpa_pre_shared_key')] = $password;
            $params[$secPath] = $secValue;
        }

        return $this->setParameterValues($deviceId, $params);
    }
}