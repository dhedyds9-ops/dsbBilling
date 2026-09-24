<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\Exceptions;

final class InvalidPaymentSignatureException extends \RuntimeException
{
    public static function create(string $driver, string $reason = ''): self
    {
        return new self("Payment webhook signature invalid for driver [{$driver}]" . ($reason ? ": {$reason}" : ''));
    }
}
