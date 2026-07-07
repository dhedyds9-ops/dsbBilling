<?php

namespace App\Jobs\Onboarding;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCustomerNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $customerServiceId,
    ) {}

    public function handle(): void
    {
        Log::info('Sending customer notification for service: ' . $this->customerServiceId);
        // TODO: Implement notification (WhatsApp/Email)
    }
}
