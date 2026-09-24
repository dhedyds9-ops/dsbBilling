<?php

namespace App\Listeners\Customer;

use Src\Domain\Customer\Events\ServiceSuspendedEvent;

class ServiceSuspendedListener
{
    public function handle(ServiceSuspendedEvent $event): void
    {
        // TODO: Add logic for when service is suspended
    }
}
