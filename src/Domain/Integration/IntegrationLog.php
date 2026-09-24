<?php

namespace Src\Domain\Integration;

class IntegrationLog
{
    public function __construct(
        public readonly string $driverType,
        public readonly string $driverName,
        public readonly string $action,
        public readonly bool $success,
        public readonly ?float $responseTimeMs = null,
        public readonly ?int $retryCount = 0,
        public readonly array $requestData = [],
        public readonly array $responseData = [],
        public readonly ?string $errorMessage = null,
    ) {}

    public function toArray(): array
    {
        return [
            'driver_type' => $this->driverType,
            'driver_name' => $this->driverName,
            'action' => $this->action,
            'success' => $this->success,
            'response_time_ms' => $this->responseTimeMs,
            'retry_count' => $this->retryCount,
            'request_data' => $this->requestData,
            'response_data' => $this->responseData,
            'error_message' => $this->errorMessage,
        ];
    }
}
