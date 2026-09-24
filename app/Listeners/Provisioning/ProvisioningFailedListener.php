<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\RollbackProvisioningJob;
use Src\Domain\Provisioning\Events\ProvisioningFailedEvent;

class ProvisioningFailedListener
{
    public function handle(ProvisioningFailedEvent $event): void
    {
        RollbackProvisioningJob::dispatch((string) $event->serviceInstanceId);
    }
}
