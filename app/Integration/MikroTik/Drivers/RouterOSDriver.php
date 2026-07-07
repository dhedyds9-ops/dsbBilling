<?php

namespace App\Integration\MikroTik\Drivers;

use App\Integration\MikroTik\Contracts\RouterOSDriverInterface;
use App\Integration\MikroTik\Exceptions\RouterOSConnectionException;
use RouterOS\Client;
use RouterOS\Query;
use Src\Domain\Integration\HealthCheckResult;
use Src\Domain\Integration\RetryEngine;
use Exception;

class RouterOSDriver implements RouterOSDriverInterface
{
    private ?Client $connection = null;
    private array $config;
    private RetryEngine $retryEngine;
    private array $clients = [];

    public function __construct(array $config, RetryEngine $retryEngine)
    {
        $this->config = $config;
        $this->retryEngine = $retryEngine;
    }

    public function connect(): bool
    {
        try {
            $this->connection = $this->retryEngine->execute(function () {
                return new Client([
                    'host' => $this->config['host'],
                    'user' => $this->config['username'],
                    'pass' => $this->config['password'],
                    'port' => $this->config['port'] ?? 8728,
                    'ssl' => $this->config['ssl'] ?? false,
                    'timeout' => $this->config['timeout'] ?? 30,
                ]);
            });

            return $this->connection !== null;
        } catch (Exception $e) {
            throw new RouterOSConnectionException('Failed to connect to router: ' . $e->getMessage(), 0, $e);
        }
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }

    private function ensureConnected(): void
    {
        if (!$this->connection || !$this->isConnected()) {
            $this->connect();
        }
    }

    private function isConnected(): bool
    {
        if (!$this->connection) {
            return false;
        }

        try {
            $this->connection->query(new Query('/system/resource/print'))->read();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function ping(): bool
    {
        try {
            $this->ensureConnected();
            $this->connection->query(new Query('/system/resource/print'))->read();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSystemInfo(): array
    {
        try {
            $this->ensureConnected();
            $resources = $this->connection->query(new Query('/system/resource/print'))->read();
            $identity = $this->connection->query(new Query('/system/identity/print'))->read();

            if (!empty($resources[0])) {
                return [
                    'identity' => $identity[0]['name'] ?? 'unknown',
                    'version' => $resources[0]['version'] ?? 'unknown',
                    'cpu' => $resources[0]['cpu'] ?? 'unknown',
                    'cpu_load' => $resources[0]['cpu-load'] ?? 0,
                    'free_memory' => $resources[0]['free-memory'] ?? 0,
                    'total_memory' => $resources[0]['total-memory'] ?? 0,
                    'uptime' => $resources[0]['uptime'] ?? 'unknown',
                ];
            }

            return [
                'identity' => 'unknown',
                'version' => 'unknown',
                'cpu' => 'unknown',
                'cpu_load' => 0,
                'free_memory' => 0,
                'total_memory' => 0,
                'uptime' => 'unknown',
            ];
        } catch (Exception $e) {
            return [
                'identity' => 'unknown',
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

    public function getInterfaceStats(): array
    {
        try {
            $this->ensureConnected();
            $interfaces = $this->connection->query(new Query('/interface/print'))->read();

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
        } catch (Exception $e) {
            return [];
        }
    }

    public function getTrafficStats(): array
    {
        return [];
    }

    public function getPPPActive(): array
    {
        try {
            $this->ensureConnected();
            $active = $this->connection->query(new Query('/ppp/active/print'))->read();

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
        } catch (Exception $e) {
            return [];
        }
    }

    public function getHotspotActive(): array
    {
        try {
            $this->ensureConnected();
            $active = $this->connection->query(new Query('/ip/hotspot/active/print'))->read();

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
        } catch (Exception $e) {
            return [];
        }
    }

    public function getQueueStats(): array
    {
        try {
            $this->ensureConnected();
            $queues = $this->connection->query(new Query('/queue/simple/print'))->read();

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
        } catch (Exception $e) {
            return [];
        }
    }

    public function addPPPoESecret(string $username, string $password, string $profile, string $remoteAddress = null): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username, $password, $profile, $remoteAddress) {
                $this->ensureConnected();

                $query = (new Query('/ppp/secret/add'))
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('profile', $profile);

                if ($remoteAddress) {
                    $query->equal('remote-address', $remoteAddress);
                }

                $this->connection->query($query)->read();

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function removePPPoESecret(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();

                $query = new Query('/ppp/secret/print');
                $query->where('name', $username);
                $secrets = $this->connection->query($query)->read();

                if (!empty($secrets[0]['.id'])) {
                    $removeQuery = (new Query('/ppp/secret/remove'))
                        ->equal('.id', $secrets[0]['.id']);
                    $this->connection->query($removeQuery)->read();
                }

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function addQueue(string $name, string $target, int $downloadLimit, int $uploadLimit): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name, $target, $downloadLimit, $uploadLimit) {
                $this->ensureConnected();

                $query = (new Query('/queue/simple/add'))
                    ->equal('name', $name)
                    ->equal('target', $target)
                    ->equal('max-limit', $uploadLimit . 'k/' . $downloadLimit . 'k');

                $this->connection->query($query)->read();

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function removeQueue(string $name): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name) {
                $this->ensureConnected();

                $query = new Query('/queue/simple/print');
                $query->where('name', $name);
                $queues = $this->connection->query($query)->read();

                if (!empty($queues[0]['.id'])) {
                    $removeQuery = (new Query('/queue/simple/remove'))
                        ->equal('.id', $queues[0]['.id']);
                    $this->connection->query($removeQuery)->read();
                }

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function getStatus(): array
    {
        try {
            $this->ensureConnected();
            $resources = $this->connection->query(new Query('/system/resource/print'))->read();

            if (!empty($resources[0])) {
                return [
                    'connected' => true,
                    'uptime' => $resources[0]['uptime'] ?? null,
                    'cpu_usage' => $resources[0]['cpu-load'] ?? null,
                    'memory_usage' => $resources[0]['free-memory'] && $resources[0]['total-memory']
                        ? round((($resources[0]['total-memory'] - $resources[0]['free-memory']) / $resources[0]['total-memory']) * 100)
                        : null,
                ];
            }

            return [
                'connected' => true,
                'uptime' => null,
                'cpu_usage' => null,
                'memory_usage' => null,
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'uptime' => null,
                'cpu_usage' => null,
                'memory_usage' => null,
            ];
        }
    }

    public function getHealth(): array
    {
        $start = microtime(true);
        $healthy = $this->ping();
        $end = microtime(true);

        return (new HealthCheckResult(
            $healthy,
            $healthy ? 'MikroTik Router healthy' : 'MikroTik Router unhealthy',
            ['host' => $this->config['host']],
            ($end - $start) * 1000
        ))->toArray();
    }
}
