<?php

namespace App\Jobs\ISP;

use App\Models\ISP\ServiceProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncServiceProfileToMikrotik implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $serviceProfile;

    public function __construct(ServiceProfile $serviceProfile)
    {
        $this->serviceProfile = $serviceProfile;
    }

    public function handle(): void
    {
        // TODO: Implement actual MikroTik sync logic here
        Log::info('Syncing ServiceProfile to MikroTik: ' . $this->serviceProfile->name);
    }
}
