<?php

namespace Src\Domain\Integration;

use Exception;
use Throwable;

class RetryEngine
{
    private int $maxRetries;
    private int $delayMs;

    public function __construct(int $maxRetries = 3, int $delayMs = 1000)
    {
        $this->maxRetries = $maxRetries;
        $this->delayMs = $delayMs;
    }

    /**
     * @template T
     * @param callable(): T $action
     * @return T
     * @throws Throwable
     */
    public function execute(callable $action)
    {
        $attempts = 0;
        
        while (true) {
            try {
                return $action();
            } catch (Throwable $e) {
                $attempts++;
                
                if ($attempts >= $this->maxRetries) {
                    throw $e;
                }
                
                if ($this->delayMs > 0) {
                    usleep($this->delayMs * 1000);
                }
            }
        }
    }
}

