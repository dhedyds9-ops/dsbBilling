<?php

namespace App\Listeners\Customer;

use Src\Domain\Customer\Events\ServiceTerminatedEvent;

class ServiceTerminatedListener
{
    public function handle(ServiceTerminatedEvent $event): void
    {
        // TODO: Add logic for when service is terminated
    }
}
