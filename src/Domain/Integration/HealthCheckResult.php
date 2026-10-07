<?php

namespace Src\Domain\Integration;

class HealthCheckResult
{
    private bool $healthy;
    private string $message;
    private array $meta;
    private float $latency;

    public function __construct(bool $healthy, string $message = "", array $meta = [], float $latency = 0.0)
    {
        $this->healthy = $healthy;
        $this->message = $message;
        $this->meta = $meta;
        $this->latency = $latency;
    }

    public function isHealthy(): bool
    {
        return $this->healthy;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getLatency(): float
    {
        return $this->latency;
    }
}

