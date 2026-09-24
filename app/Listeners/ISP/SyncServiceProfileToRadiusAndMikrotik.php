<?php

namespace App\Listeners\ISP;

use App\Events\ISP\ServiceProfileSaved;
use App\Jobs\ISP\SyncServiceProfileToRadius;
use App\Jobs\ISP\SyncServiceProfileToMikrotik;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncServiceProfileToRadiusAndMikrotik
{
    use InteractsWithQueue;

    public function handle(ServiceProfileSaved $event): void
    {
        SyncServiceProfileToRadius::dispatch($event->serviceProfile);
        SyncServiceProfileToMikrotik::dispatch($event->serviceProfile);
    }
}
