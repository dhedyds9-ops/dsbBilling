<?php

namespace App\Listeners\Customer;

use Src\Domain\Customer\Events\ServiceActivatedEvent;

class ServiceActivatedListener
{
    public function handle(ServiceActivatedEvent $event): void
    {
        // TODO: Add logic for when service is activated (e.g., update dashboards, sync to external systems)
    }
}
