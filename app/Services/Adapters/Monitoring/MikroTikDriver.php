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
            // Attempt Fallback to VPN IP if available
            if (!empty($device->vpn_ip)) {
                try {
                    $fallbackConfig = [
                        'host' => $device->vpn_ip,
                        'user' => $device->username,
                        'pass' => $device->password,
                        'port' => $device->api_port ?? 8728,
                        'ssl' => $device->use_ssl ?? false,
                        'timeout' => $device->timeout ?? 30,
                    ];
                    $this->clients[$key] = new Client($fallbackConfig);
                    return $this->clients[$key];
                } catch (Exception $e2) {
                    throw new MonitoringException('Failed to connect to router (Primary & Fallback VPN): ' . $e2->getMessage(), 0, $e2);
                }
            }
            
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
                        'board_name' => $resources[0]['board-name'] ?? '-',
                        'architecture_name' => $resources[0]['architecture-name'] ?? '-',
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
                
                $names = [];
                foreach ($interfaces as $interface) {
                    if (!in_array($interface['type'] ?? '', ['pppoe-in', 'hotspot'])) {
                        $names[] = $interface['name'];
                    }
                }
                
                $trafficMap = [];
                if (!empty($names)) {
                    $q = new Query('/interface/monitor-traffic');
                    $q->equal('interface', implode(',', $names));
                    $q->equal('once', '');
                    $traffic = $client->query($q)->read();
                    
                    if (is_array($traffic)) {
                        foreach ($traffic as $t) {
                            if (isset($t['name'])) {
                                $trafficMap[$t['name']] = $t;
                            }
                        }
                    }
                }
                
                $stats = [];
                foreach ($interfaces as $interface) {
                    $name = $interface['name'] ?? 'unknown';
                    $txBps = $trafficMap[$name]['tx-bits-per-second'] ?? 0;
                    $rxBps = $trafficMap[$name]['rx-bits-per-second'] ?? 0;
                    
                    $stats[] = [
                        'name' => $name,
                        'type' => $interface['type'] ?? 'unknown',
                        'status' => ($interface['running'] ?? 'false') === 'true' ? 'link-up' : 'link-down',
                        'tx-byte' => $interface['tx-byte'] ?? 0,
                        'rx-byte' => $interface['rx-byte'] ?? 0,
                        'tx-bps' => $txBps,
                        'rx-bps' => $rxBps,
                    ];
                }
                
                return $stats;
            }
            
            return [];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('getInterfaceStats failed', [
                'device' => is_object($device) ? ($device->name ?? 'unknown') : 'unknown',
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);
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
    
    public function checkAndRepairRadius($device, string $radiusIp, string $radiusSecret): bool
    {
        try {
            $client = $this->getClient($device);
            if (!$client) return false;
            
            // 1. Check if our radius server is configured
            $query = new Query('/radius/print');
            $query->where('address', $radiusIp);
            $radiuses = $client->query($query)->read();
            
            if (empty($radiuses)) {
                // Add missing radius
                $addQuery = new Query('/radius/add');
                $addQuery->equal('address', $radiusIp);
                $addQuery->equal('secret', $radiusSecret);
                $addQuery->equal('service', 'ppp,hotspot');
                $client->query($addQuery)->read();
            } else {
                // Ensure it is enabled
                $id = $radiuses[0]['.id'];
                if (isset($radiuses[0]['disabled']) && $radiuses[0]['disabled'] === 'true') {
                    $enableQuery = new Query('/radius/enable');
                    $enableQuery->equal('.id', $id);
                    $client->query($enableQuery)->read();
                }
            }
            
            // 2. Check radius incoming
            $incomingQuery = new Query('/radius/incoming/print');
            $incoming = $client->query($incomingQuery)->read();
            
            if (empty($incoming) || (isset($incoming[0]['accept']) && $incoming[0]['accept'] === 'false')) {
                $setIncoming = new Query('/radius/incoming/set');
                $setIncoming->equal('accept', 'yes');
                $setIncoming->equal('port', '3799');
                $client->query($setIncoming)->read();
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function checkConfigDrift($device): bool
    {
        try {
            $client = $this->getClient($device);
            if (!$client) return false;
            
            // 1. Check for local PPPoE secrets
            $pppQuery = new Query('/ppp/secret/print');
            $pppSecrets = $client->query($pppQuery)->read();
            
            // 2. Check for local Hotspot users (ignoring default admin)
            $hotspotQuery = new Query('/ip/hotspot/user/print');
            $hotspotUsers = $client->query($hotspotQuery)->read();
            
            $localHotspotCount = 0;
            foreach ($hotspotUsers as $user) {
                if (($user['name'] ?? '') !== 'admin') {
                    $localHotspotCount++;
                }
            }
            
            // If there are more than 1 PPP secrets (maybe one is for testing) or any non-admin hotspot users
            if (count($pppSecrets) > 0 || $localHotspotCount > 0) {
                return true; // Drift detected!
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // --- Web Winbox Phase 1 Methods ---

    public function getLogs($device, int $limit = 50): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            $query = new Query('/log/print');
            $logs = $client->query($query)->read();
            return array_slice(array_reverse($logs), 0, $limit);
        } catch (Exception $e) { return []; }
    }

    public function getPppServers($device): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            return $client->query(new Query('/interface/pppoe-server/server/print'))->read();
        } catch (Exception $e) { return []; }
    }

    public function getPppProfiles($device): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            return $client->query(new Query('/ppp/profile/print'))->read();
        } catch (Exception $e) { return []; }
    }

    public function getPppSecrets($device): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            return $client->query(new Query('/ppp/secret/print'))->read();
        } catch (Exception $e) { return []; }
    }

    public function getVpnServers($device): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            return [
                'l2tp' => $client->query(new Query('/interface/l2tp-server/server/print'))->read()[0] ?? [],
                'pptp' => $client->query(new Query('/interface/pptp-server/server/print'))->read()[0] ?? [],
                'sstp' => $client->query(new Query('/interface/sstp-server/server/print'))->read()[0] ?? [],
                'ovpn' => $client->query(new Query('/interface/ovpn-server/server/print'))->read()[0] ?? [],
            ];
        } catch (Exception $e) { return []; }
    }

    public function getHotspotServers($device): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            return $client->query(new Query('/ip/hotspot/print'))->read();
        } catch (Exception $e) { return []; }
    }

    public function getHotspotProfiles($device): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            return $client->query(new Query('/ip/hotspot/user/profile/print'))->read();
        } catch (Exception $e) { return []; }
    }

    public function getWalledGarden($device): array
    {
        try {
            if (!($client = $this->getClient($device))) return [];
            return $client->query(new Query('/ip/hotspot/walled-garden/print'))->read();
        } catch (Exception $e) { return []; }
    }
    
    // --- Web Winbox Phase 2 Methods (Terminal & Actions) ---
    
    public function runTerminalCommand($device, string $command): array
    {
        try {
            if (!($client = $this->getClient($device))) return ['error' => 'Not connected'];
            
            $command = trim($command);
            if (empty($command)) return [];
            
            $parts = explode(' ', $command);
            $baseCommand = array_shift($parts);
            
            $query = new Query($baseCommand);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $query->add($part);
                }
            }
            
            $response = $client->query($query)->read();
            return empty($response) ? [['status' => 'Success (Empty Response / No Data)']] : $response;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function rebootRouter($device): bool
    {
        try {
            if (!($client = $this->getClient($device))) return false;
            $client->query(new Query('/system/reboot'))->read();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function backupRouter($device): ?array
    {
        try {
            if (!($client = $this->getClient($device))) return null;
            
            $filename = 'backup_' . date('Ymd_His');
            
            // Generate export
            $exportQuery = new Query('/export');
            $exportQuery->equal('file', $filename);
            $client->query($exportQuery)->read();
            
            // Also generate .backup
            $backupQuery = new Query('/system/backup/save');
            $backupQuery->equal('name', $filename);
            $client->query($backupQuery)->read();
            
            return [
                'filename' => $filename,
                'rsc_file' => $filename . '.rsc',
                'backup_file' => $filename . '.backup'
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
