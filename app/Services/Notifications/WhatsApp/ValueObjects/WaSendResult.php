<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\ValueObjects;

final readonly class WaSendResult
{
    public function __construct(
        public bool    $success,
        public string  $gatewayMessageId = '',
        public float   $latencyMs = 0.0,
        public string  $errorCode = '',
        public string  $errorMessage = '',
        public string  $rawResponse = '',
    ) {}
}
