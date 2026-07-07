<?php

namespace Src\Domain\Integration\Drivers;

use Exception;
use RouterOS\Client;
use RouterOS\Query;
use Src\Domain\Integration\Contracts\RouterDriverInterface;
use Src\Domain\Integration\HealthCheckResult;
use Src\Domain\Integration\RetryEngine;

class MikroTikRouterDriver implements RouterDriverInterface
{
    private ?Client $connection = null;

    public function __construct(
        private readonly array $config,
        private readonly RetryEngine $retryEngine,
    ) {}

    public function connect(): bool
    {
        try {
            $this->connection = $this->retryEngine->execute(function () {
                return new Client([
                    'host' => $this->config['host'],
                    'user' => $this->config['username'],
                    'pass' => $this->config['password'],
                    'port' => $this->config['port'] ?? 8728,
                ]);
            });

            return $this->connection !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }

    public function addPPPoESecret(string $username, string $password, string $profile, string $remoteAddress = null): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username, $password, $profile, $remoteAddress) {
                if (!$this->connection) {
                    $this->connect();
                }
                
                if (!$this->connection) {
                    return false;
                }

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
                if (!$this->connection) {
                    $this->connect();
                }
                
                if (!$this->connection) {
                    return false;
                }

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
                if (!$this->connection) {
                    $this->connect();
                }
                
                if (!$this->connection) {
                    return false;
                }

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
                if (!$this->connection) {
                    $this->connect();
                }
                
                if (!$this->connection) {
                    return false;
                }

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
            if (!$this->connection) {
                $this->connect();
            }
            
            if (!$this->connection) {
                return [
                    'connected' => false,
                    'uptime' => null,
                    'cpu_usage' => null,
                    'memory_usage' => null,
                ];
            }

            $query = new Query('/system/resource/print');
            $resources = $this->connection->query($query)->read();
            
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
        $healthy = $this->connect();
        $end = microtime(true);

        return (new HealthCheckResult(
            $healthy,
            $healthy ? 'MikroTik Router healthy' : 'MikroTik Router unhealthy',
            ['host' => $this->config['host']],
            ($end - $start) * 1000
        ))->toArray();
    }
}
