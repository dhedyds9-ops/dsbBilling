<?php

namespace Src\Domain\SharedKernel\Aggregates;

use Src\Domain\SharedKernel\Events\DomainEvent;

abstract class AggregateRoot
{
    private array $events = [];

    protected function recordThat(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
