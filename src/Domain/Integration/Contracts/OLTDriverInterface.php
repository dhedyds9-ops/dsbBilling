<?php

namespace Src\Domain\Integration\Contracts;

interface OLTDriverInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function getONUs(): array;
    public function getONUStatus(string $ponPort, string $onuId): array;
    public function rebootONU(string $ponPort, string $onuId): bool;
    public function getPONStatus(string $ponPort): array;
    public function getStatus(): array;
    public function getHealth(): array;
}
