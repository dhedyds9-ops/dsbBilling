<?php

namespace Src\Domain\Integration\Contracts;

interface RadiusDriverInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function addUser(string $username, string $password, string $profile): bool;
    public function removeUser(string $username): bool;
    public function getUser(string $username): ?array;
    public function updateUser(string $username, array $data): bool;
    public function getAccounting(string $username = null): array;
    public function getStatus(): array;
    public function getHealth(): array;
}
