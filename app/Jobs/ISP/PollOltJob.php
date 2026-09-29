<?php

namespace App\Jobs\ISP;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ISP\Olt;
use App\Services\ISP\OltPollingService;

class PollOltJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function __construct(public int $oltId) {}

    public function handle(OltPollingService $service)
    {
        $olt = Olt::find($this->oltId);
        if ($olt) {
            $service->pollOlt($olt);
        }
    }
}
