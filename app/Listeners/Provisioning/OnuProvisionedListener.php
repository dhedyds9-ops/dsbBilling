<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\VerifyProvisioningJob;
use Src\Domain\Provisioning\Events\OnuProvisionedEvent;

class OnuProvisionedListener
{
    public function handle(OnuProvisionedEvent $event): void
    {
        VerifyProvisioningJob::dispatch((string) $event->serviceInstanceId);
    }
}
