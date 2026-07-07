<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\ProvisionRadiusJob;
use Src\Domain\Provisioning\Events\RouterProvisionedEvent;

class RouterProvisionedListener
{
    public function handle(RouterProvisionedEvent $event): void
    {
        ProvisionRadiusJob::dispatch((string) $event->serviceInstanceId);
    }
}
