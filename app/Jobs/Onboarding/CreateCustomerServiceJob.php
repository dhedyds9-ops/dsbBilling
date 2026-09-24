<?php

namespace App\Jobs\Onboarding;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateCustomerServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $installationId,
    ) {}

    public function handle(): void
    {
        Log::info('Creating customer service for installation: ' . $this->installationId);
        // TODO: Implement customer service creation
    }
}
