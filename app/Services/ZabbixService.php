<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class ZabbixService
{
    protected string $url;
    protected string $username;
    protected string $password;
    protected ?string $authToken = null;
    
    // Zabbix API requires a unique ID for each JSON-RPC request. We use a counter or random number.
    protected int $requestId = 1;

    public function __construct()
    {
        $this->url = Setting::getValue('zabbix.url', 'http://127.0.0.1/zabbix/api_jsonrpc.php');
        $this->username = Setting::getValue('zabbix.username', 'Admin');
        $this->password = Setting::getValue('zabbix.password', 'zabbix');
    }

    /**
     * Send a JSON-RPC request to Zabbix API
     */
    protected function request(string $method, array $params = [], bool $requiresAuth = true): array
    {
        if ($requiresAuth && !$this->authToken) {
            $this->authenticate();
        }

        $payload = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->requestId++,
        ];

        if ($requiresAuth) {
            // Zabbix API < 6.4 uses 'auth', >= 6.4 uses Authorization header or 'auth' in payload. 
            // We'll pass it in the payload for compatibility.
            $payload['auth'] = $this->authToken;
        }

        try {
            $response = Http::timeout(10)->post($this->url, $payload);
            
            if ($response->failed()) {
                throw new Exception("Zabbix API Error: HTTP " . $response->status());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error('Zabbix API Request Error', [
                    'method' => $method,
                    'error' => $data['error']
                ]);
                throw new Exception("Zabbix API Error: " . ($data['error']['data'] ?? $data['error']['message']));
            }

            return $data['result'] ?? [];
            
        } catch (Exception $e) {
            Log::error('Zabbix Connection Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Authenticate and get token, cached for 10 minutes
     */
    public function authenticate(): string
    {
        // Check cache first to avoid authenticating on every request
        $cacheKey = 'zabbix_auth_token_' . md5($this->url . $this->username);
        
        $token = Cache::remember($cacheKey, 600, function () {
            $result = $this->request('user.login', [
                'user' => $this->username,
                'password' => $this->password
            ], false);
            
            return $result;
        });

        $this->authToken = $token;
        return $token;
    }

    /**
     * Test Connection
     */
    public function testConnection(): bool
    {
        try {
            $this->authenticate();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all Hosts from Zabbix
     */
    public function getHosts(array $hostIds = []): array
    {
        $params = [
            'output' => ['hostid', 'host', 'name', 'status', 'error'],
            'selectInterfaces' => ['ip', 'port', 'type']
        ];
        
        if (!empty($hostIds)) {
            $params['hostids'] = $hostIds;
        }

        return $this->request('host.get', $params);
    }

    /**
     * Get Items (Metrics) for a specific host
     */
    public function getHostItems(string $hostId, string $searchKey = ''): array
    {
        $params = [
            'output' => ['itemid', 'name', 'key_', 'lastvalue', 'units', 'lastclock'],
            'hostids' => $hostId,
        ];
        
        if ($searchKey) {
            $params['search'] = ['key_' => $searchKey];
        }

        return $this->request('item.get', $params);
    }
}

