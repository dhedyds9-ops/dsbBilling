<?php

namespace App\Integration\MikroTik\Contracts;

interface RouterOSDriverInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function ping(): bool;
    public function getSystemInfo(): array;
    public function getInterfaceStats(): array;
    public function getTrafficStats(): array;
    public function getPPPActive(): array;
    public function getHotspotActive(): array;
    public function getQueueStats(): array;
    public function addPPPoESecret(string $username, string $password, string $profile, ?string $remoteAddress = null): bool;
    public function removePPPoESecret(string $username): bool;
    public function addQueue(string $name, string $target, int $downloadLimit, int $uploadLimit): bool;
    public function removeQueue(string $name): bool;
    public function getStatus(): array;
    public function getHealth(): array;
}
