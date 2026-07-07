<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\ReleaseResourcesJob;
use Src\Domain\Provisioning\Events\ProvisioningVerifiedEvent;

class ProvisioningVerifiedListener
{
    public function handle(ProvisioningVerifiedEvent $event): void
    {
        ReleaseResourcesJob::dispatch((string) $event->serviceInstanceId);
    }
}
