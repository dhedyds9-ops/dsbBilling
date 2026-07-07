<?php

namespace Src\Domain\Integration\Contracts;

interface NotificationDriverInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function send(string $to, string $message, array $options = []): bool;
    public function getStatus(): array;
    public function getHealth(): array;
}
