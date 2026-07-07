<?php

namespace Src\Domain\SharedKernel\Events;

interface EventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void;
    public function dispatchAll(array $events): void;
    public function listen(string $eventName, callable $listener): void;
}
