<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Billing\Events\ReminderSentEvent;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string $reminderType,
    ) {}

    public function handle(): void
    {
        // TODO: Integrasi dengan notification engine
        event(new ReminderSentEvent(
            $this->invoice->uuid,
            $this->invoice->customer_id,
            $this->reminderType
        ));
    }
}
