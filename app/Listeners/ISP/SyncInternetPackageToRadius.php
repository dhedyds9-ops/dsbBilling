<?php

namespace App\Listeners\ISP;

use App\Events\ISP\InternetPackageSaved;
use App\Jobs\ISP\SyncServiceProfileToRadius;
use App\Jobs\ISP\SyncServiceProfileToMikrotik;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncInternetPackageToRadius
{
    use InteractsWithQueue;

    public function handle(InternetPackageSaved $event): void
    {
        SyncServiceProfileToRadius::dispatch($event->serviceProfile);
        SyncServiceProfileToMikrotik::dispatch($event->serviceProfile);
    }
}
