<?php

namespace App\Listeners\Billing;

use Src\Domain\Billing\Events\InvoiceOverdueEvent;

class InvoiceOverdueListener
{
    public function handle(InvoiceOverdueEvent $event): void
    {
        // TODO: Kirim reminder overdue pertama ke customer
        // TODO: Tambah flag ke customer untuk follow-up kolektif
        // TODO: Trigger BillingAutomation grace period countdown
    }
}
