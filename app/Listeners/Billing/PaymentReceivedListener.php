<?php

namespace App\Listeners\Billing;

use Src\Domain\Billing\Events\PaymentReceivedEvent;

class PaymentReceivedListener
{
    public function handle(PaymentReceivedEvent $event): void
    {
        // TODO: Kirim notifikasi payment diterima via email/WhatsApp
        // TODO: Log ke audit trail dengan reference: paymentId, customerId, amount
    }
}
