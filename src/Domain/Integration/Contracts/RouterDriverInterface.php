<?php

namespace Src\Domain\Integration\Contracts;

interface RouterDriverInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function addPPPoESecret(string $username, string $password, string $profile, string $remoteAddress = null): bool;
    public function removePPPoESecret(string $username): bool;
    public function addQueue(string $name, string $target, int $downloadLimit, int $uploadLimit): bool;
    public function removeQueue(string $name): bool;
    public function getStatus(): array;
    public function getHealth(): array;
}
