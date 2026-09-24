<?php

namespace App\Jobs\Onboarding;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ActivatePppoeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $installationId,
    ) {}

    public function handle(): void
    {
        Log::info('Activating PPPoE for installation: ' . $this->installationId);
        // TODO: Implement PPPoE activation
    }
}
