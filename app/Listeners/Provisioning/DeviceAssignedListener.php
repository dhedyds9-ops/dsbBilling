<?php

namespace App\Listeners\Provisioning;

use Src\Domain\Provisioning\Events\DeviceAssignedEvent;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class DeviceAssignedListener
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function handle(DeviceAssignedEvent $event): void
    {
        $this->dispatcher->dispatch(new \Src\Domain\Provisioning\Events\VlanAllocatedEvent($event->serviceInstanceId));
    }
}
