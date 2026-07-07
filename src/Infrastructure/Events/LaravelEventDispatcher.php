<?php

namespace Src\Infrastructure\Events;

use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void
    {
        Event::dispatch($event);
    }

    public function dispatchAll(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }

    public function listen(string $eventName, callable $listener): void
    {
        Event::listen($eventName, $listener);
    }
}
