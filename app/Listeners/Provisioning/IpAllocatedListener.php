<?php

namespace App\Listeners\Provisioning;

use Src\Domain\Provisioning\Events\IpAllocatedEvent;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class IpAllocatedListener
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function handle(IpAllocatedEvent $event): void
    {
        $this->dispatcher->dispatch(new \Src\Domain\Provisioning\Events\QueueAllocatedEvent($event->serviceInstanceId));
    }
}
