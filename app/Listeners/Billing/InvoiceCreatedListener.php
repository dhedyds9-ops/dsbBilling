<?php

namespace App\Listeners\Billing;

use App\Jobs\Billing\GenerateRecurringInvoiceJob;
use Src\Domain\Billing\Events\InvoiceCreatedEvent;

class InvoiceCreatedListener
{
    public function handle(InvoiceCreatedEvent $event): void
    {
        // TODO: Kirim notifikasi invoice ke customer via email/WhatsApp
        // TODO: Log ke audit trail
    }
}
