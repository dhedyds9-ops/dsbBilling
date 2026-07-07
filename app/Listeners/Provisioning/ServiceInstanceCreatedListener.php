<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\ReserveResourcesJob;
use Src\Domain\Provisioning\Events\ServiceInstanceCreatedEvent;

class ServiceInstanceCreatedListener
{
    public function handle(ServiceInstanceCreatedEvent $event): void
    {
        ReserveResourcesJob::dispatch((string) $event->serviceInstanceId);
    }
}
