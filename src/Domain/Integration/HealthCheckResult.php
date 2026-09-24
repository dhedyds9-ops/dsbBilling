<?php

namespace Src\Domain\Integration;

class HealthCheckResult
{
    public function __construct(
        public readonly bool $healthy,
        public readonly string $message,
        public readonly array $metadata = [],
        public readonly ?float $responseTimeMs = null,
    ) {}

    public function toArray(): array
    {
        return [
            'healthy' => $this->healthy,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'response_time_ms' => $this->responseTimeMs,
        ];
    }
}
