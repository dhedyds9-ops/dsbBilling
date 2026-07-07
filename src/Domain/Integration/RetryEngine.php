<?php

namespace Src\Domain\Integration;

use Closure;
use Exception;

class RetryEngine
{
    public function __construct(
        private readonly int $maxRetries = 3,
        private readonly int $delayMs = 1000,
        private readonly array $retryableExceptions = [],
    ) {}

    public function execute(Closure $callback): mixed
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < $this->maxRetries) {
            try {
                return $callback();
            } catch (Exception $e) {
                $lastException = $e;
                $attempts++;

                if ($attempts >= $this->maxRetries) {
                    break;
                }

                if (!empty($this->retryableExceptions) && !in_array(get_class($e), $this->retryableExceptions)) {
                    break;
                }

                usleep($this->delayMs * 1000 * $attempts);
            }
        }

        throw $lastException;
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    public function getDelayMs(): int
    {
        return $this->delayMs;
    }
}
