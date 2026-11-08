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
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }
        $this->baseUrl = rtrim($settings['base_url'] ?? config('genieacs.base_url', 'http://localhost:7557'), '/');
        $this->username = $settings['connection_request_username'] ?? $settings['username'] ?? config('genieacs.username', 'admin');
        $this->password = $settings['connection_request_password'] ?? $settings['password'] ?? config('genieacs.password', 'admin');
        $this->timeout = (int)($settings['timeout'] ?? config('genieacs.timeout', 10));
    }

    private function getWifiParameterPath(string $vendor = 'default', string $parameter = 'wpa_passphrase'): string
    {
        $vendors = config('acs_vendors', []);
        $vendorKey = strtolower(trim($vendor));
        if (!isset($vendors[$vendorKey])) {
            $fuzzy = collect(array_keys($vendors))->first(fn ($k) => str_contains($vendorKey, $k) || str_contains($k, $vendorKey));
            $vendorKey = $fuzzy ?? 'default';
        }
        return $vendors[$vendorKey][$parameter] ?? $vendors['default'][$parameter] ?? '';
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
        $cleanId = trim($deviceId);
        if (empty($cleanId)) {
            return [];
        }

        try {
            $queryObj = [
                '$or' => [
                    ['_id' => $cleanId],
                    ['_deviceId._SerialNumber' => $cleanId],
                    ['_deviceId._MACAddress' => $cleanId],
                    ['_id' => ['$regex' => preg_quote($cleanId, '/')]],
                ]
            ];
            $query = urlencode(json_encode($queryObj));
            $projection = urlencode('_id,_deviceId,_lastInform,VirtualParameters,InternetGatewayDevice,Device');

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/devices/?query={$query}&projection={$projection}");
                
            if ($response->successful()) {
                $data = $response->json();
                return $data[0] ?? [];
            }
            if ($response->status() === 404) {
                return [];
            }
            throw new Exception("Failed to get device parameters: HTTP " . $response->status());
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
                return [];
            }
            throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
        }
    }

    public function resolveDeviceId(string $deviceId): string
    {
        $cleanId = trim($deviceId);
        if (empty($cleanId)) {
            return $deviceId;
        }

        if (substr_count($cleanId, '-') >= 2) {
            return $cleanId;
        }

        try {
            $params = $this->getDeviceParameters($cleanId);
            if (!empty($params['_id'])) {
                return (string)$params['_id'];
            }
        } catch (\Throwable) {}

        return $cleanId;
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
        if (empty($params)) {
            return [
                'ssid' => null,
                'password' => null,
                'real_device_id' => null,
            ];
        }
        
        $extract = function (string $path) use ($params) {
            // 1. Try Laravel data_get (supports both dot notation and arrays)
            $val = data_get($params, $path . '._value') ?? data_get($params, $path);
            if ($val !== null && is_scalar($val) && $val !== '') {
                return (string)$val;
            }

            // 2. Try nested array traversal
            $parts = explode('.', $path);
            $node = $params;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) {
                    $node = null;
                    break;
                }
                $node = $node[$p];
            }
            if ($node !== null) {
                if (is_array($node) && isset($node['_value']) && is_scalar($node['_value'])) {
                    return (string)$node['_value'];
                }
                if (is_scalar($node)) {
                    return (string)$node;
                }
            }

            // 3. Try direct key in flat params map
            if (isset($params[$path])) {
                $pNode = $params[$path];
                if (is_array($pNode) && isset($pNode['_value']) && is_scalar($pNode['_value'])) {
                    return (string)$pNode['_value'];
                }
                if (is_scalar($pNode)) {
                    return (string)$pNode;
                }
            }

            return null;
        };

        // Vendor-configured paths
        $vendorSsidPath = $this->getWifiParameterPath($vendor, 'ssid');
        $vendorPassPath = $this->getWifiParameterPath($vendor, 'wpa_passphrase');

        $ssidCandidates = array_unique(array_filter([
            $vendorSsidPath,
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID',
            'Device.WiFi.SSID.1.SSID',
            'Device.WiFi.AccessPoint.1.SSID',
            'VirtualParameters.wifiSsid',
        ]));

        $passCandidates = array_unique(array_filter([
            $vendorPassPath,
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.KeyPassphrase',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.X_ZTE-COM_WPAKey',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.X_HW_KeyPassphrase',
            'Device.WiFi.AccessPoint.1.Security.KeyPassphrase',
            'Device.WiFi.AccessPoint.1.Security.PreSharedKey',
            'VirtualParameters.wifiPassword',
            'VirtualParameters.wifiPassphrase',
        ]));

        $ssid = null;
        foreach ($ssidCandidates as $path) {
            $val = $extract($path);
            if (!empty($val)) {
                $ssid = $val;
                break;
            }
        }

        $password = null;
        foreach ($passCandidates as $path) {
            $val = $extract($path);
            if (!empty($val) && $val !== '******') {
                $password = $val;
                break;
            }
        }

        // Dynamic WLAN traversal if SSID or Password is still missing
        if (empty($ssid) || empty($password)) {
            $wlanNode = $params['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration'] 
                ?? $params['Device']['WiFi']['SSID'] 
                ?? [];
            if (is_array($wlanNode)) {
                foreach ($wlanNode as $key => $node) {
                    if (is_numeric($key) && is_array($node)) {
                        if (empty($ssid)) {
                            $s = $node['SSID']['_value'] ?? $node['SSID'] ?? null;
                            if (!empty($s) && is_scalar($s)) {
                                $ssid = (string)$s;
                            }
                        }
                        if (empty($password)) {
                            $p = $node['PreSharedKey']['1']['KeyPassphrase']['_value']
                                ?? $node['PreSharedKey']['1']['PreSharedKey']['_value']
                                ?? $node['KeyPassphrase']['_value']
                                ?? $node['PreSharedKey']['1']['KeyPassphrase']
                                ?? $node['KeyPassphrase']
                                ?? null;
                            if (!empty($p) && is_scalar($p) && $p !== '******') {
                                $password = (string)$p;
                            }
                        }
                        if (!empty($ssid) && !empty($password)) {
                            break;
                        }
                    }
                }
            }
        }

        return [
            'ssid' => $ssid,
            'password' => $password,
            'real_device_id' => $params['_id'] ?? null,
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

    public function postTaskWithFallback(string $deviceId, array $payload, bool $tryConnectionRequest = true): \Illuminate\Http\Client\Response
    {
        $realDeviceId = $this->resolveDeviceId($deviceId);
        $encodedId = urlencode($realDeviceId);

        if ($tryConnectionRequest) {
            try {
                return Http::withBasicAuth($this->username, $this->password)
                    ->timeout(10)
                    ->asJson()
                    ->post("{$this->baseUrl}/devices/{$encodedId}/tasks?connection_request", $payload);
            } catch (ConnectionException $ce) {
                // Jika connection_request timeout (modem di balik NAT/firewall tidak merespons pemicu port 7547),
                // antrikan task TANPA ?connection_request ke GenieACS agar tetap tersimpan dan dieksekusi saat modem Inform.
                if (str_contains(strtolower($ce->getMessage()), 'timeout') || str_contains(strtolower($ce->getMessage()), 'timed out')) {
                    \Illuminate\Support\Facades\Log::warning("GenieACS connection_request timed out for device {$realDeviceId}. Fallbacking to queue task without connection_request.");
                    return Http::withBasicAuth($this->username, $this->password)
                        ->timeout(10)
                        ->asJson()
                        ->post("{$this->baseUrl}/devices/{$encodedId}/tasks", $payload);
                }
                throw $ce;
            }
        }

        return Http::withBasicAuth($this->username, $this->password)
            ->timeout(10)
            ->asJson()
            ->post("{$this->baseUrl}/devices/{$encodedId}/tasks", $payload);
    }

    public function setParameterValues(string $deviceId, array $keyValuePairs): bool
    {
        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $parameterValues = [];
            foreach ($keyValuePairs as $path => $value) {
                if ($path === '' || $path === null) {
                    continue;
                }
                $type = is_bool($value) ? 'xsd:boolean' : (is_int($value) ? 'xsd:int' : (is_numeric($value) ? 'xsd:unsignedInt' : 'xsd:string'));
                if ($type === 'xsd:unsignedInt' && str_contains($path, 'SSID')) {
                    $type = 'xsd:string';
                }
                if (str_contains(strtoupper($path), 'VLAN') && !is_bool($value)) {
                    $type = 'xsd:unsignedInt';
                }
                if (str_ends_with($path, '.Mode')) {
                    $type = 'xsd:unsignedInt';
                }
                $parameterValues[] = [$path, $value, $type];
            }
            if (count($parameterValues) === 0) {
                return false;
            }
            $payload = ['name' => 'setParameterValues', 'parameterValues' => $parameterValues];
            $response = $this->postTaskWithFallback($realDeviceId, $payload, true);
                
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
            $response = $this->postTaskWithFallback($deviceId, $payload, true);
                
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
            $response = $this->postTaskWithFallback($deviceId, $payload, true);
                
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

    public static function calculateStatus(array $params): string
    {
        $lastInform = $params['_lastInform'] ?? null;
        if (!$lastInform) return 'offline';

        $informInterval = 300;
        if (isset($params['InternetGatewayDevice']['ManagementServer']['PeriodicInformInterval']['_value'])) {
            $informInterval = (int)$params['InternetGatewayDevice']['ManagementServer']['PeriodicInformInterval']['_value'];
        } elseif (isset($params['Device']['ManagementServer']['PeriodicInformInterval']['_value'])) {
            $informInterval = (int)$params['Device']['ManagementServer']['PeriodicInformInterval']['_value'];
        }

        $diffInSeconds = abs(now()->diffInSeconds(\Carbon\Carbon::parse($lastInform)));
        $offlineThreshold = ($informInterval * 2) + 120;

        if ($diffInSeconds <= $informInterval + 60) return 'online';
        if ($diffInSeconds <= $offlineThreshold) return 'stale';
        return 'offline';
    }

    public function isDeviceOnline(string $deviceId): bool
    {
        $params = $this->getDeviceParameters($deviceId);
        if (empty($params)) {
            return false;
        }
        return self::calculateStatus($params) !== 'offline';
    }

    public function summonDeviceResult(string $deviceId): array
    {
        $result = [
            'task_queued' => false,
            'connection_request_success' => false,
            'error' => null,
        ];

        try {
            $realDeviceId = $this->resolveDeviceId($deviceId);
            $encodedId = urlencode($realDeviceId);

            // 1. Pemicu utama: Kirim Connection Request dengan single root refreshObject (persis seperti dsbilling-mobile / genieacs-mobile)
            // objectName "" (root) memicu seluruh parameter tree modem diperbarui tanpa membebani antrian task
            try {
                $crUrl = "{$this->baseUrl}/devices/{$encodedId}/tasks?connection_request";
                $crResp = Http::timeout(10)
                    ->withBasicAuth($this->username, $this->password)
                    ->asJson()
                    ->post($crUrl, ['name' => 'refreshObject', 'objectName' => '']);

                if ($crResp->successful() || in_array($crResp->status(), [200, 201, 202])) {
                    $result['connection_request_success'] = true;
                    $result['task_queued'] = true;
                    return $result;
                }
            } catch (\Throwable $ce) {
                // Connection request timeout jika modem berada di balik IP Privat/NAT
            }

            // 2. Fallback jika modem berada di balik NAT: antrikan task refreshObject tanpa connection_request
            $url = "{$this->baseUrl}/devices/{$encodedId}/tasks";
            $resp = Http::timeout(8)
                ->withBasicAuth($this->username, $this->password)
                ->asJson()
                ->post($url, ['name' => 'refreshObject', 'objectName' => '']);

            if ($resp->successful() || in_array($resp->status(), [200, 201, 202])) {
                $result['task_queued'] = true;
            }

            return $result;
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
            return $result;
        }
    }

    public function summonDevice(string $deviceId): bool
    {
        $res = $this->summonDeviceResult($deviceId);
        return $res['connection_request_success'] || $res['task_queued'];
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

    public function listPresets(): array
    {
        try {
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get("{$this->baseUrl}/presets");
            return $r->successful() ? $r->json() : [];
        } catch (Exception) {
            return [];
        }
    }

    public function getPreset(string $presetName): ?array
    {
        try {
            $url = "{$this->baseUrl}/presets/" . urlencode($presetName);
            $r = Http::withBasicAuth($this->username, $this->password)->timeout($this->timeout)->get($url);
            return $r->successful() ? $r->json() : null;
        } catch (Exception) {
            return null;
        }
    }

    public function upsertPreset(string $presetName, array $presetData): bool
    {
        try {
            $url = "{$this->baseUrl}/presets/" . urlencode($presetName);
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->put($url, $presetData);
            
            return $response->successful();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function deletePreset(string $presetName): bool
    {
        try {
            $url = "{$this->baseUrl}/presets/" . urlencode($presetName);
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->delete($url);
            
            return $response->successful();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function getEventBridgeProvisionScript(): string
    {
        return <<<'JS'
// dsBilling_Event_Ingestion Provision Script (v1.2.16)
// Safe, read-only event ingestion without modifying CPE configuration
const now = Date.now();

// 1. Identify Device
let idDecl = declare("DeviceID.ID", {value: 1});
let deviceId = (idDecl && idDecl.value && idDecl.value.length) ? idDecl.value[0] : "";
if (!deviceId) {
  let snDecl = declare("DeviceID.SerialNumber", {value: 1});
  deviceId = (snDecl && snDecl.value && snDecl.value.length) ? snDecl.value[0] : "";
}

let snDecl = declare("DeviceID.SerialNumber", {value: 1});
let serialNumber = (snDecl && snDecl.value && snDecl.value.length) ? snDecl.value[0] : "";

let mfgDecl = declare("DeviceID.Manufacturer", {value: 1});
let manufacturer = (mfgDecl && mfgDecl.value && mfgDecl.value.length) ? mfgDecl.value[0] : "";

let pcDecl = declare("DeviceID.ProductClass", {value: 1});
let productClass = (pcDecl && pcDecl.value && pcDecl.value.length) ? pcDecl.value[0] : "";

// 2. Collect Inform Events
let eventCodes = [];
let eventsDecl = declare("Events.*", {value: 1});
if (eventsDecl && eventsDecl.path) {
  for (let p of eventsDecl) {
    if (p.value && p.value[0]) {
      eventCodes.push(p.value[0]);
    }
  }
}

// 3. Optional metadata: SoftwareVersion & IP
let swDecl = declare("InternetGatewayDevice.DeviceInfo.SoftwareVersion", {value: 1});
if (!swDecl || !swDecl.value || !swDecl.value.length) {
  swDecl = declare("Device.DeviceInfo.SoftwareVersion", {value: 1});
}
let softwareVersion = (swDecl && swDecl.value && swDecl.value.length) ? swDecl.value[0] : "";

let ipDecl = declare("InternetGatewayDevice.ManagementServer.ConnectionRequestURL", {value: 1});
if (!ipDecl || !ipDecl.value || !ipDecl.value.length) {
  ipDecl = declare("Device.ManagementServer.ConnectionRequestURL", {value: 1});
}
let connUrl = (ipDecl && ipDecl.value && ipDecl.value.length) ? ipDecl.value[0] : "";
let ipAddress = "";
if (connUrl) {
  let match = connUrl.match(/:\/\/([^:\/]+)/);
  if (match) {
    ipAddress = match[1];
  }
}

// 4. Send Event via Extension
if (deviceId) {
  ext("dsbilling-webhook", "inform", JSON.stringify({
    device_id: deviceId,
    serial_number: serialNumber,
    manufacturer: manufacturer,
    product_class: productClass,
    software_version: softwareVersion,
    ip_address: ipAddress,
    last_inform: new Date(now).toISOString(),
    event_codes: eventCodes
  }));
}
JS;
    }

    public function getEventBridgePresetData(): array
    {
        return [
            'weight' => 0,
            'events' => [
                '0 BOOTSTRAP' => true,
                '1 BOOT' => true,
                '2 PERIODIC' => true,
                '4 VALUE CHANGE' => true,
                '6 CONNECTION REQUEST' => true,
                'M Reboot' => true,
            ],
            'precondition' => '',
            'configurations' => [
                [
                    'type' => 'provision',
                    'name' => 'dsBilling_Event_Ingestion',
                ],
            ],
        ];
    }

    public function deployEventBridge(): array
    {
        $provisionName = 'dsBilling_Event_Ingestion';
        $presetName = 'dsBilling_Event_Ingestion';

        $provSuccess = $this->upsertProvision($provisionName, $this->getEventBridgeProvisionScript(), 0);
        $presetSuccess = $this->upsertPreset($presetName, $this->getEventBridgePresetData());

        return [
            'provision_deployed' => $provSuccess,
            'preset_deployed' => $presetSuccess,
            'success' => $provSuccess && $presetSuccess,
        ];
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

    public function upsertVirtualParameter(string $vpName, string $javascriptCode): bool
    {
        try {
            $url = "{$this->baseUrl}/virtual_parameters/{$vpName}";
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->withBody($javascriptCode, 'text/plain')
                ->put($url);
            
            return $response->successful();
        } catch (\Exception $e) {
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
        
        // 1. Flatten all parameters to key-value map for robust multi-vendor lookups (similar to genieacs-mobile)
        $flat = [];
        $flatten = function ($data, $prefix = '') use (&$flatten, &$flat) {
            if (!is_array($data)) return;
            foreach ($data as $k => $v) {
                if ($k === '_timestamp' || $k === '_object' || $k === '_writable') continue;
                $key = $prefix ? "$prefix.$k" : (string) $k;
                if (is_array($v)) {
                    if (isset($v['_value']) && is_scalar($v['_value'])) {
                        $flat[$key] = (string) $v['_value'];
                    }
                    $flatten($v, $key);
                } elseif (is_scalar($v)) {
                    $flat[$key] = (string) $v;
                }
            }
        };
        $flatten($params);

        // 2. Extract AssociatedDevice RSSI and Band mapping by MAC
        $assocRssiByMac = [];
        $assocBandByMac = [];
        foreach ($flat as $k => $v) {
            if (str_contains($k, 'AssociatedDevice.') && (str_contains($k, 'MACAddress') || str_contains($k, 'AssociatedDeviceMACAddress'))) {
                if (preg_match('/WLANConfiguration\.(\d+)\.AssociatedDevice\.(\d+)/i', $k, $m)) {
                    $wlanIdx = (int) $m[1];
                    $assocIdx = $m[2];
                    $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIdx}.AssociatedDevice.{$assocIdx}";
                    
                    $macVal = strtoupper(trim($v));
                    if (!empty($macVal) && $macVal !== '00:00:00:00:00:00') {
                        $rssiStr = $flat["{$prefix}.X_CMCC_RSSI"] 
                                ?? $flat["{$prefix}.X_HW_RSSI"] 
                                ?? $flat["{$prefix}.X_ZTE-COM_RSSI"] 
                                ?? $flat["{$prefix}.X_CT-COM_RSSI"] 
                                ?? $flat["{$prefix}.SignalStrength"] 
                                ?? $flat["{$prefix}.RSSI"] 
                                ?? null;

                        if ($rssiStr !== null) {
                            $cleanRssi = (int) trim(str_ireplace('dBm', '', $rssiStr));
                            if ($cleanRssi >= -110 && $cleanRssi <= -1) {
                                $assocRssiByMac[$macVal] = $cleanRssi;
                            }
                        }
                        $assocBandByMac[$macVal] = $wlanIdx <= 4 ? "WiFi 2.4G (SSID {$wlanIdx})" : "WiFi 5G (SSID {$wlanIdx})";
                    }
                }
            }
        }

        // 3. Extract Hosts and merge with AssociatedDevices
        $hosts = [];
        $seenMacs = [];

        // Check standard Hosts.Host
        $landevices = $params['InternetGatewayDevice']['LANDevice'] ?? [];
        foreach ($landevices as $lanIdx => $lanDev) {
            if ($lanIdx === '_object' || $lanIdx === '_timestamp' || $lanIdx === '_writable' || !is_array($lanDev)) continue;
            
            if (isset($lanDev['Hosts']['Host']) && is_array($lanDev['Hosts']['Host'])) {
                foreach ($lanDev['Hosts']['Host'] as $idx => $host) {
                    if ($idx === '_object' || $idx === '_timestamp' || $idx === '_writable' || !is_array($host)) continue;
                    
                    $mac = isset($host['MACAddress']['_value']) ? $host['MACAddress']['_value'] : (is_scalar($host['MACAddress'] ?? null) ? $host['MACAddress'] : 'Unknown');
                    $normMac = strtoupper(trim((string)$mac));
                    if ($normMac !== 'UNKNOWN' && in_array($normMac, $seenMacs, true)) continue;
                    if ($normMac !== 'UNKNOWN') $seenMacs[] = $normMac;

                    $rssi = $assocRssiByMac[$normMac] ?? null;
                    $ifType = isset($host['InterfaceType']['_value']) ? $host['InterfaceType']['_value'] : (is_scalar($host['InterfaceType'] ?? null) ? $host['InterfaceType'] : 'Unknown');
                    $band = $assocBandByMac[$normMac] ?? ($ifType === 'Ethernet' ? 'LAN (Kabel)' : ($rssi !== null ? 'WiFi' : $ifType));

                    $hosts[] = [
                        'MACAddress' => $normMac,
                        'IPAddress' => isset($host['IPAddress']['_value']) ? $host['IPAddress']['_value'] : (is_scalar($host['IPAddress'] ?? null) ? $host['IPAddress'] : '-'),
                        'HostName' => isset($host['HostName']['_value']) ? $host['HostName']['_value'] : (is_scalar($host['HostName'] ?? null) ? $host['HostName'] : 'Perangkat #' . $idx),
                        'Active' => (($host['Active']['_value'] ?? (is_scalar($host['Active'] ?? null) ? $host['Active'] : '0')) == '1' || ($host['Active']['_value'] ?? (is_scalar($host['Active'] ?? null) ? $host['Active'] : '0')) === true),
                        'InterfaceType' => $band,
                        'Layer1Interface' => isset($host['Layer1Interface']['_value']) ? $host['Layer1Interface']['_value'] : (is_scalar($host['Layer1Interface'] ?? null) ? $host['Layer1Interface'] : 'WLAN'),
                        'RSSI' => $rssi,
                        'SignalStrength' => $rssi !== null ? "{$rssi} dBm" : null,
                    ];
                }
            }
        }

        // 4. Also add any AssociatedDevice that wasn't listed in Hosts.Host (e.g. static IP clients or ZTE/Huawei without DHCP lease)
        foreach ($assocBandByMac as $mac => $band) {
            if (!in_array($mac, $seenMacs, true)) {
                $seenMacs[] = $mac;
                $rssi = $assocRssiByMac[$mac] ?? null;
                $hosts[] = [
                    'MACAddress' => $mac,
                    'IPAddress' => '-',
                    'HostName' => 'Klien Wi-Fi',
                    'Active' => true,
                    'InterfaceType' => $band,
                    'Layer1Interface' => 'WLAN',
                    'RSSI' => $rssi,
                    'SignalStrength' => $rssi !== null ? "{$rssi} dBm" : null,
                ];
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



    public function updateWifiSsidAndPassword(
        string $deviceId, 
        string $ssid, 
        ?string $password = null, 
        string $vendor = 'default',
        int $wlanIndex = 1,
        string $securityMode = 'WPA_WPA2',
        ?bool $isHidden = null,
        ?bool $isEnabled = null
    ): bool {
        $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.";
        $params = [];

        // 1. Radio status (Enable)
        if ($isEnabled !== null) {
            $params[$prefix . 'Enable'] = (bool) $isEnabled;
        }

        // 2. SSID Broadcast (Hidden / Visible)
        if ($isHidden !== null) {
            $params[$prefix . 'SSIDAdvertisementEnabled'] = !$isHidden;
        }

        // 3. SSID Name (Vendor + Standard + VirtualParameter sync)
        $params[$prefix . 'SSID'] = trim($ssid);
        $vendorSsidPath = $this->getWifiParameterPath($vendor, 'ssid');
        if (!empty($vendorSsidPath)) {
            $params[$vendorSsidPath] = trim($ssid);
        }
        if ($wlanIndex === 1) {
            $params['VirtualParameters.WlanSSID'] = trim($ssid);
        }

        // 4. Security Mode & Password
        if ($password !== null) {
            $trimmedPass = (string) $password;

            if ($securityMode === 'OPEN') {
                $params[$prefix . 'BeaconType'] = 'None';
            } elseif ($securityMode === 'WPA2') {
                $params[$prefix . 'BeaconType'] = '11i';
                $params[$prefix . 'WPAEncryptionModes'] = 'AESEncryption';
                $params[$prefix . 'PreSharedKey.1.KeyPassphrase'] = $trimmedPass;
                $params[$prefix . 'KeyPassphrase'] = $trimmedPass;
                $params[$prefix . 'PreSharedKey.1.PreSharedKey'] = $trimmedPass;
            } elseif ($securityMode === 'WPA') {
                $params[$prefix . 'BeaconType'] = 'WPA';
                $params[$prefix . 'WPAEncryptionModes'] = 'TKIPEncryption';
                $params[$prefix . 'PreSharedKey.1.KeyPassphrase'] = $trimmedPass;
                $params[$prefix . 'KeyPassphrase'] = $trimmedPass;
                $params[$prefix . 'PreSharedKey.1.PreSharedKey'] = $trimmedPass;
            } else { // WPA_WPA2 (Default Rekomendasi)
                $params[$prefix . 'BeaconType'] = '11i';
                $params[$prefix . 'WPAEncryptionModes'] = 'TKIPandAESEncryption';
                $params[$prefix . 'PreSharedKey.1.KeyPassphrase'] = $trimmedPass;
                $params[$prefix . 'KeyPassphrase'] = $trimmedPass;
                $params[$prefix . 'PreSharedKey.1.PreSharedKey'] = $trimmedPass;
            }

            // Vendor-specific paths fallback
            $secPath = $this->getWifiParameterPath($vendor, 'security_mode');
            if (!empty($secPath)) {
                $params[$secPath] = str_contains($secPath, 'BeaconType') ? ($securityMode === 'OPEN' ? 'None' : '11i') : 'WPA2-Personal';
            }
            $vPass1 = $this->getWifiParameterPath($vendor, 'wpa_passphrase');
            if (!empty($vPass1)) {
                $params[$vPass1] = $trimmedPass;
            }
            $vPass2 = $this->getWifiParameterPath($vendor, 'wpa_pre_shared_key');
            if (!empty($vPass2)) {
                $params[$vPass2] = $trimmedPass;
            }

            if ($wlanIndex === 1 && $securityMode !== 'OPEN') {
                $params['VirtualParameters.WlanPassword'] = $trimmedPass;
            }
        }

        return $this->setParameterValues($deviceId, $params);
    }

    public function addProvisionTask(string $deviceId, string $provisionName): bool
    {
        try {
            $payload = ['name' => 'provision', 'args' => ['provision' => $provisionName]];
            $response = $this->postTaskWithFallback($deviceId, $payload, true);
            return $response->successful();
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}


