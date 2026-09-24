<?php

namespace App\Listeners\Billing;

use App\Jobs\Billing\GenerateRecurringInvoiceJob;
use App\Models\Billing\Subscription;
use Src\Domain\Billing\Events\SubscriptionCreatedEvent;

class SubscriptionCreatedListener
{
    public function handle(SubscriptionCreatedEvent $event): void
    {
        // Cari subscription berdasarkan UUID
        $subscription = Subscription::where('uuid', $event->subscriptionId)->first();
        
        if ($subscription) {
            GenerateRecurringInvoiceJob::dispatch($subscription);
        }
    }
}
