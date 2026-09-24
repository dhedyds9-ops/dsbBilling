<?php

namespace App\Listeners\Provisioning;

use App\Jobs\Provisioning\ProvisionRouterJob;
use Src\Domain\Provisioning\Events\QueueAllocatedEvent;

class QueueAllocatedListener
{
    public function handle(QueueAllocatedEvent $event): void
    {
        ProvisionRouterJob::dispatch((string) $event->serviceInstanceId);
    }
}
