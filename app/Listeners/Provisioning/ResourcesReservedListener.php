<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\AssignDeviceJob;
use Src\Domain\Provisioning\Events\ResourcesReservedEvent;

class ResourcesReservedListener
{
    public function handle(ResourcesReservedEvent $event): void
    {
        AssignDeviceJob::dispatch((string) $event->serviceInstanceId);
    }
}
