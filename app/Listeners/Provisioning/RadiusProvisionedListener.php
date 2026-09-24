<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\ProvisionOnuJob;
use Src\Domain\Provisioning\Events\RadiusProvisionedEvent;

class RadiusProvisionedListener
{
    public function handle(RadiusProvisionedEvent $event): void
    {
        ProvisionOnuJob::dispatch((string) $event->serviceInstanceId);
    }
}
