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

    public function updatePPPoEServerUser(string $username, string $password, string $profile): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username, $password, $profile) {
                $this->ensureConnected();

                // Check if secret exists
                $query = new Query('/ppp/secret/print');
                $query->where('name', $username);
                $secrets = $this->connection->query($query)->read();

                if (!empty($secrets[0]['.id'])) {
                    // Update existing secret
                    $updateQuery = (new Query('/ppp/secret/set'))
                        ->equal('.id', $secrets[0]['.id'])
                        ->equal('password', $password)
                        ->equal('profile', $profile);
                    $this->connection->query($updateQuery)->read();
                } else {
                    // Add new secret if not exists
                    $this->addPPPoESecret($username, $password, $profile);
                }

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function removePPPoEServerUser(string $username): bool
    {
        return $this->removePPPoESecret($username);
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

    public function updateHotspotUser(string $username, string $password, string $profile = 'default'): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username, $password, $profile) {
                $this->ensureConnected();

                // Check if hotspot user exists
                $query = new Query('/ip/hotspot/user/print');
                $query->where('name', $username);
                $users = $this->connection->query($query)->read();

                if (!empty($users[0]['.id'])) {
                    // Update existing user
                    $updateQuery = (new Query('/ip/hotspot/user/set'))
                        ->equal('.id', $users[0]['.id'])
                        ->equal('password', $password);
                    if ($profile) {
                        $updateQuery->equal('profile', $profile);
                    }
                    $this->connection->query($updateQuery)->read();
                } else {
                    // Add new user if not exists (though this shouldn't happen for existing customers)
                    $addQuery = (new Query('/ip/hotspot/user/add'))
                        ->equal('name', $username)
                        ->equal('password', $password);
                    if ($profile) {
                        $addQuery->equal('profile', $profile);
                    }
                    $this->connection->query($addQuery)->read();
                }

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function disconnectHotspotUser(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();

                // Find all active sessions for this user
                $query = new Query('/ip/hotspot/active/print');
                $query->where('user', $username);
                $sessions = $this->connection->query($query)->read();

                // Disconnect each active session
                foreach ($sessions as $session) {
                    if (!empty($session['.id'])) {
                        $removeQuery = (new Query('/ip/hotspot/active/remove'))
                            ->equal('.id', $session['.id']);
                        $this->connection->query($removeQuery)->read();
                    }
                }

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function disconnectPppoeUser(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();

                $query = new Query('/ppp/active/print');
                $query->where('name', $username);
                $sessions = $this->connection->query($query)->read();

                foreach ($sessions as $session) {
                    if (!empty($session['.id'])) {
                        $removeQuery = (new Query('/ppp/active/remove'))
                            ->equal('.id', $session['.id']);
                        $this->connection->query($removeQuery)->read();
                    }
                }

                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    // ============================================================
    // IP POOL (/ip/pool)
    // ============================================================
    public function getPools(): array
    {
        try {
            $this->ensureConnected();
            return $this->connection->query(new Query('/ip/pool/print'))->read() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function addIpPool(string $name, string $ranges, ?string $comment = null, ?string $nextPool = null): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name, $ranges, $comment, $nextPool) {
                $this->ensureConnected();
                $q = (new Query('/ip/pool/add'))
                    ->equal('name', $name)
                    ->equal('ranges', $ranges);
                if ($comment) $q->equal('comment', $comment);
                if ($nextPool) $q->equal('next-pool', $nextPool);
                $this->connection->query($q)->read();
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateIpPool(string $name, string $ranges, ?string $comment = null, ?string $nextPool = null): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name, $ranges, $comment, $nextPool) {
                $this->ensureConnected();
                $find = (new Query('/ip/pool/print'));
                $find->where('name', $name);
                $rows = $this->connection->query($find)->read();
                if (empty($rows[0]['.id'])) {
                    return $this->addIpPool($name, $ranges, $comment, $nextPool);
                }
                $q = (new Query('/ip/pool/set'))
                    ->equal('.id', $rows[0]['.id'])
                    ->equal('ranges', $ranges);
                if ($comment !== null) $q->equal('comment', $comment);
                if ($nextPool !== null) $q->equal('next-pool', $nextPool);
                $this->connection->query($q)->read();
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function removeIpPool(string $name): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name) {
                $this->ensureConnected();
                $find = new Query('/ip/pool/print');
                $find->where('name', $name);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $this->connection->query((new Query('/ip/pool/remove'))->equal('.id', $rows[0]['.id']))->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    // ============================================================
    // PPP PROFILE (/ppp/profile)
    // ============================================================
    public function getPppProfiles(): array
    {
        try {
            $this->ensureConnected();
            return $this->connection->query(new Query('/ppp/profile/print'))->read() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function addPppProfile(string $name, array $options = []): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name, $options) {
                $this->ensureConnected();
                $q = (new Query('/ppp/profile/add'))->equal('name', $name);
                foreach ($options as $k => $v) {
                    if ($v !== null && $v !== '') $q->equal($k, $v);
                }
                $this->connection->query($q)->read();
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function updatePppProfile(string $name, array $options = []): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name, $options) {
                $this->ensureConnected();
                $find = new Query('/ppp/profile/print');
                $find->where('name', $name);
                $rows = $this->connection->query($find)->read();
                if (empty($rows[0]['.id'])) {
                    return $this->addPppProfile($name, $options);
                }
                $q = (new Query('/ppp/profile/set'))->equal('.id', $rows[0]['.id']);
                foreach ($options as $k => $v) {
                    if ($v !== null && $v !== '') $q->equal($k, $v);
                }
                $this->connection->query($q)->read();
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function removePppProfile(string $name): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name) {
                $this->ensureConnected();
                $find = new Query('/ppp/profile/print');
                $find->where('name', $name);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $this->connection->query((new Query('/ppp/profile/remove'))->equal('.id', $rows[0]['.id']))->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function disablePppSecret(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();
                $find = new Query('/ppp/secret/print');
                $find->where('name', $username);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $q = (new Query('/ppp/secret/set'))
                        ->equal('.id', $rows[0]['.id'])
                        ->equal('disabled', 'yes');
                    $this->connection->query($q)->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function enablePppSecret(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();
                $find = new Query('/ppp/secret/print');
                $find->where('name', $username);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $q = (new Query('/ppp/secret/set'))
                        ->equal('.id', $rows[0]['.id'])
                        ->equal('disabled', 'no');
                    $this->connection->query($q)->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    // ============================================================
    // HOTSPOT USER PROFILE (/ip/hotspot/user/profile)
    // ============================================================
    public function getHotspotUserProfiles(): array
    {
        try {
            $this->ensureConnected();
            return $this->connection->query(new Query('/ip/hotspot/user/profile/print'))->read() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function addHotspotUserProfile(string $name, array $options = []): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name, $options) {
                $this->ensureConnected();
                $q = (new Query('/ip/hotspot/user/profile/add'))->equal('name', $name);
                foreach ($options as $k => $v) {
                    if ($v !== null && $v !== '') $q->equal($k, $v);
                }
                $this->connection->query($q)->read();
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateHotspotUserProfile(string $name, array $options = []): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name, $options) {
                $this->ensureConnected();
                $find = new Query('/ip/hotspot/user/profile/print');
                $find->where('name', $name);
                $rows = $this->connection->query($find)->read();
                if (empty($rows[0]['.id'])) {
                    return $this->addHotspotUserProfile($name, $options);
                }
                $q = (new Query('/ip/hotspot/user/profile/set'))->equal('.id', $rows[0]['.id']);
                foreach ($options as $k => $v) {
                    if ($v !== null && $v !== '') $q->equal($k, $v);
                }
                $this->connection->query($q)->read();
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function removeHotspotUserProfile(string $name): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($name) {
                $this->ensureConnected();
                $find = new Query('/ip/hotspot/user/profile/print');
                $find->where('name', $name);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $this->connection->query((new Query('/ip/hotspot/user/profile/remove'))->equal('.id', $rows[0]['.id']))->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function addHotspotUser(string $username, string $password, string $profile = 'default', ?array $options = null): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username, $password, $profile, $options) {
                $this->ensureConnected();
                $q = (new Query('/ip/hotspot/user/add'))
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('profile', $profile);
                if (is_array($options)) {
                    foreach ($options as $k => $v) {
                        if ($v !== null && $v !== '') $q->equal($k, $v);
                    }
                }
                $this->connection->query($q)->read();
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function disableHotspotUser(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();
                $find = new Query('/ip/hotspot/user/print');
                $find->where('name', $username);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $q = (new Query('/ip/hotspot/user/set'))
                        ->equal('.id', $rows[0]['.id'])
                        ->equal('disabled', 'yes');
                    $this->connection->query($q)->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function enableHotspotUser(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();
                $find = new Query('/ip/hotspot/user/print');
                $find->where('name', $username);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $q = (new Query('/ip/hotspot/user/set'))
                        ->equal('.id', $rows[0]['.id'])
                        ->equal('disabled', 'no');
                    $this->connection->query($q)->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function removeHotspotUser(string $username): bool
    {
        try {
            return $this->retryEngine->execute(function () use ($username) {
                $this->ensureConnected();
                $find = new Query('/ip/hotspot/user/print');
                $find->where('name', $username);
                $rows = $this->connection->query($find)->read();
                if (!empty($rows[0]['.id'])) {
                    $this->connection->query((new Query('/ip/hotspot/user/remove'))->equal('.id', $rows[0]['.id']))->read();
                }
                return true;
            });
        } catch (Exception $e) {
            return false;
        }
    }
}
