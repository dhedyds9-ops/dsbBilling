<?php

namespace App\Listeners\Provisioning;

use Src\Domain\Provisioning\Events\VlanAllocatedEvent;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class VlanAllocatedListener
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function handle(VlanAllocatedEvent $event): void
    {
        $this->dispatcher->dispatch(new \Src\Domain\Provisioning\Events\IpAllocatedEvent($event->serviceInstanceId));
    }
}
