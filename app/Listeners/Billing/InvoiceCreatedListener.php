<?php

namespace App\Listeners\Billing;

use App\Jobs\Billing\GenerateRecurringInvoiceJob;
use Src\Domain\Billing\Events\InvoiceCreatedEvent;

class InvoiceCreatedListener
{
    public function __construct(
        private readonly \App\Services\Notifications\WhatsApp\WhatsAppNotificationService $waService,
    ) {}

    public function handle(InvoiceCreatedEvent $event): void
    {
        try {
            $invoice = \App\Models\Billing\Invoice::where('uuid', $event->invoiceUuid)->first();
            if ($invoice) {
                // Send WhatsApp notification
                $this->waService->notifyInvoiceCreated($invoice);
                
                // Send Email notification
                \App\Jobs\Notifications\SendInvoiceEmailJob::dispatch($invoice)->onQueue('notifications-email');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[InvoiceCreatedListener] Failed: ' . $e->getMessage());
        }
    }
}
