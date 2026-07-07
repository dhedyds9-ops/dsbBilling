<?php

namespace App\Jobs\Provisioning;

use App\Models\Provisioning\ServiceInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AllocateIpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $serviceInstanceId,
    ) {}

    public function handle(): void
    {
        Log::info('Allocating IP for service instance: ' . $this->serviceInstanceId);
        // TODO: Implement IP allocation logic
    }
}
