<?php

namespace App\Listeners\Customer;

use Src\Domain\Customer\Events\ServiceReactivatedEvent;

class ServiceReactivatedListener
{
    public function handle(ServiceReactivatedEvent $event): void
    {
        // TODO: Add logic for when service is reactivated
    }
}
