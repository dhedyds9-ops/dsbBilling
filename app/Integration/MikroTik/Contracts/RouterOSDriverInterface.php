<?php

namespace App\Integration\MikroTik\Contracts;

interface RouterOSDriverInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function ping(): bool;
    public function getSystemInfo(): array;
    public function getInterfaceStats(): array;
    public function getTrafficStats(string $interface = ''): array;
    public function getPPPActive(): array;
    public function getHotspotActive(): array;
    public function getQueueStats(): array;

    // Pool + Profile provisioning
    public function getPools(): array;
    public function addIpPool(string $name, string $ranges, ?string $comment = null, ?string $nextPool = null): bool;
    public function updateIpPool(string $name, string $ranges, ?string $comment = null, ?string $nextPool = null): bool;
    public function removeIpPool(string $name): bool;

    public function getPppProfiles(): array;
    public function addPppProfile(string $name, array $options = []): bool;
    public function updatePppProfile(string $name, array $options = []): bool;
    public function removePppProfile(string $name): bool;

    public function getHotspotUserProfiles(): array;
    public function addHotspotUserProfile(string $name, array $options = []): bool;
    public function updateHotspotUserProfile(string $name, array $options = []): bool;
    public function removeHotspotUserProfile(string $name): bool;

    // PPP secret CRUD + state
    public function addPPPoESecret(string $username, string $password, string $profile, ?string $remoteAddress = null): bool;
    public function updatePPPoEServerUser(string $username, string $password, string $profile): bool;
    public function removePPPoESecret(string $username): bool;
    public function disablePppSecret(string $username): bool;
    public function enablePppSecret(string $username): bool;
    public function removePPPoEServerUser(string $username): bool;

    // Hotspot user CRUD + state
    public function updateHotspotUser(string $username, string $password, string $profile = 'default'): bool;
    public function addHotspotUser(string $username, string $password, string $profile = 'default', ?array $options = null): bool;
    public function disableHotspotUser(string $username): bool;
    public function enableHotspotUser(string $username): bool;
    public function removeHotspotUser(string $username): bool;

    public function addQueue(string $name, string $target, int $downloadLimit, int $uploadLimit): bool;
    public function removeQueue(string $name): bool;
    public function getStatus(): array;
    public function getHealth(): array;
    public function disconnectPppoeUser(string $username): bool;
    public function disconnectHotspotUser(string $username): bool;
}
