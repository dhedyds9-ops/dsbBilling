<?php

namespace Src\Domain\SharedKernel\Events;

use DateTimeImmutable;

abstract class DomainEvent
{
    public readonly DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new DateTimeImmutable();
    }

    abstract public function getName(): string;
}
