<?php

namespace App\Services\Adapters\Monitoring;

use App\Models\ISP\Router;
use App\Services\ISP\DeviceMonitorInterface;
use App\Exceptions\Monitoring\MonitoringException;
use RouterOS\Client;
use RouterOS\Query;
use Exception;

class MikroTikDriver implements DeviceMonitorInterface
{
    private array $clients = [];

    private function getClientKey($device): string
    {
        return $device->id;
    }

    private function getClient($device): ?Client
    {
        $key = $this->getClientKey($device);
        
        if (isset($this->clients[$key])) {
            return $this->clients[$key];
        }

        try {
            $config = [
                'host' => $device->ip_address,
                'user' => $device->username,
                'pass' => $device->password,
                'port' => $device->api_port ?? 8728,
                'ssl' => $device->use_ssl ?? false,
                'timeout' => $device->timeout ?? 30,
            ];
            
            $this->clients[$key] = new Client($config);
            return $this->clients[$key];
        } catch (Exception $e) {
            throw new MonitoringException('Failed to connect to router: ' . $e->getMessage(), 0, $e);
        }
    }

    public function ping($device): bool
    {
        try {
            $client = $this->getClient($device);
            if ($client) {
                $query = new Query('/system/resource/print');
                $client->query($query)->read();
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSystemInfo($device): array
    {
        try {
            $client = $this->getClient($device);
            if ($client) {
                $query = new Query('/system/resource/print');
                $resources = $client->query($query)->read();
                
                $query = new Query('/system/identity/print');
                $identity = $client->query($query)->read();

                if (!empty($resources[0])) {
                    return [
                        'identity' => $identity[0]['name'] ?? 'mikrotik-' . $device->code,
                        'version' => $resources[0]['version'] ?? 'unknown',
                        'cpu' => $resources[0]['cpu'] ?? 'unknown',
                        'cpu_load' => $resources[0]['cpu-load'] ?? 0,
                        'free_memory' => $resources[0]['free-memory'] ?? 0,
                        'total_memory' => $resources[0]['total-memory'] ?? 0,
                        'uptime' => $resources[0]['uptime'] ?? 'unknown',
                    ];
                }
            }
            
            return [
                'identity' => 'mikrotik-' . $device->code,
                'version' => 'unknown',
                'cpu' => 'unknown',
                'cpu_load' => 0,
                'free_memory' => 0,
                'total_memory' => 0,
                'uptime' => 'unknown',
            ];
        } catch (Exception $e) {
            return [
                'identity' => 'mikrotik-' . $device->code,
                'version' => 'unknown',
                'cpu' => 'unknown',
                'cpu_load' => 0,
                'free_memory' => 0,
                'total_memory' => 0,
                'uptime' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getInterfaceStats($device): array
    {
        try {
            $client = $this->getClient($device);
            if ($client) {
                $query = new Query('/interface/print');
                $interfaces = $client->query($query)->read();
                
                $stats = [];
                foreach ($interfaces as $interface) {
                    $stats[] = [
                        'name' => $interface['name'] ?? 'unknown',
                        'type' => $interface['type'] ?? 'unknown',
                        'status' => $interface['running'] === 'true' ? 'link-up' : 'link-down',
                        'tx-byte' => $interface['tx-byte'] ?? 0,
                        'rx-byte' => $interface['rx-byte'] ?? 0,
                    ];
                }
                
                return $stats;
            }
            
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getTrafficStats($device): array
    {
        return [];
    }

    public function getPPPActive($device): array
    {
        try {
            $client = $this->getClient($device);
            if ($client) {
                $query = new Query('/ppp/active/print');
                $active = $client->query($query)->read();
                
                $stats = [];
                foreach ($active as $session) {
                    $stats[] = [
                        'name' => $session['name'] ?? 'unknown',
                        'service' => $session['service'] ?? 'unknown',
                        'caller_id' => $session['caller-id'] ?? null,
                        'address' => $session['address'] ?? 'unknown',
                        'uptime' => $session['uptime'] ?? 'unknown',
                        'bytes_in' => $session['bytes-in'] ?? 0,
                        'bytes_out' => $session['bytes-out'] ?? 0,
                        'packets_in' => $session['packets-in'] ?? 0,
                        'packets_out' => $session['packets-out'] ?? 0,
                        'rate_up' => $session['rate-up'] ?? null,
                        'rate_down' => $session['rate-down'] ?? null,
                    ];
                }
                
                return $stats;
            }
            
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getHotspotActive($device): array
    {
        try {
            $client = $this->getClient($device);
            if ($client) {
                $query = new Query('/ip/hotspot/active/print');
                $active = $client->query($query)->read();
                
                $stats = [];
                foreach ($active as $session) {
                    $stats[] = [
                        'user' => $session['user'] ?? 'unknown',
                        'mac_address' => $session['mac-address'] ?? 'unknown',
                        'address' => $session['address'] ?? 'unknown',
                        'server' => $session['server'] ?? 'unknown',
                        'login_by' => $session['login-by'] ?? 'unknown',
                        'uptime' => $session['uptime'] ?? 'unknown',
                        'bytes_in' => $session['bytes-in'] ?? 0,
                        'bytes_out' => $session['bytes-out'] ?? 0,
                    ];
                }
                
                return $stats;
            }
            
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getQueueStats($device): array
    {
        try {
            $client = $this->getClient($device);
            if ($client) {
                $query = new Query('/queue/simple/print');
                $queues = $client->query($query)->read();
                
                $stats = [];
                foreach ($queues as $queue) {
                    $stats[] = [
                        'queue_name' => $queue['name'] ?? 'unknown',
                        'target' => $queue['target'] ?? 'unknown',
                        'max_limit' => $queue['max-limit'] ?? null,
                        'burst_limit' => $queue['burst-limit'] ?? null,
                        'limit_at' => $queue['limit-at'] ?? null,
                        'bytes_in' => $queue['bytes-in'] ?? 0,
                        'bytes_out' => $queue['bytes-out'] ?? 0,
                        'packets_in' => $queue['packets-in'] ?? 0,
                        'packets_out' => $queue['packets-out'] ?? 0,
                        'rate_up' => $queue['rate-up'] ?? null,
                        'rate_down' => $queue['rate-down'] ?? null,
                    ];
                }
                
                return $stats;
            }
            
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
}
