<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\Exceptions;

final class WaGatewayException extends \RuntimeException
{
    public static function create(string $driver, string $message, string $code = ''): self
    {
        return new self("[WA-{$driver}] {$message}" . ($code ? " [code:{$code}]" : ''));
    }
}
