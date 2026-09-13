<?php

namespace App\Exceptions;

use Exception;

class RateLimitException extends Exception
{
    public int $retryAfter;

    public function __construct($message = "Rate Limited", $retryAfter = 60)
    {
        parent::__construct($message);
        $this->retryAfter = $retryAfter;
    }
}
