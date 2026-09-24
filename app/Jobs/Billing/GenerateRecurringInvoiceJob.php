<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Subscription;
use App\Services\Billing\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRecurringInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Subscription $subscription,
    ) {}

    public function handle(InvoiceService $invoiceService): void
    {
        $contract = $this->subscription->contract;
        $customerService = $this->subscription->customerService;
        $userId = 1; // TODO: gunakan user system

        $invoiceService->createInvoice(
            $contract,
            $userId,
            [
                [
                    'description' => 'Langganan ' . $customerService->service->name,
                    'quantity' => 1,
                    'unit_price' => $this->subscription->recurring_price,
                ],
            ],
        );

        $this->subscription->update([
            'last_billing_date' => now(),
            'next_billing_date' => $this->calculateNextBillingDate(),
        ]);
    }

    protected function calculateNextBillingDate(): \DateTimeInterface
    {
        $nextDate = clone $this->subscription->next_billing_date;

        switch ($this->subscription->billing_cycle) {
            case 'monthly':
                $nextDate->add(new \DateInterval('P1M'));
                break;
            case 'quarterly':
                $nextDate->add(new \DateInterval('P3M'));
                break;
            case 'yearly':
                $nextDate->add(new \DateInterval('P1Y'));
                break;
        }

        return $nextDate;
    }
}
