<?php

namespace App\Services\Monitoring\Contracts;

interface CollectorInterface
{
    public function collect(): void;
    public function getName(): string;
}
