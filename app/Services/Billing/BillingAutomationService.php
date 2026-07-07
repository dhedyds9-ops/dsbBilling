<?php

namespace App\Services\Billing;

use App\Jobs\Billing\GenerateInvoiceJob;
use App\Jobs\Billing\ReactivateCustomerJob;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use Illuminate\Support\Facades\DB;

class BillingAutomationService
{
    public function generateInvoicesForDueSubscriptions(): void
    {
        $subscriptions = Subscription::where('status', 'active')
            ->where('next_billing_date', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            GenerateInvoiceJob::dispatch($subscription);
        }
    }

    public function processOverdueInvoices(): void
    {
        $overdueInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<=', now())
            ->get();

        foreach ($overdueInvoices as $invoice) {
            if ($invoice->status === 'pending') {
                $invoice->update(['status' => 'overdue']);
            }
        }
    }

    public function processGracePeriodExpiry(): void
    {
        $expiredInvoices = Invoice::where('status', 'overdue')
            ->where('due_date', '<=', now()->subDays(7))
            ->get();

        foreach ($expiredInvoices as $invoice) {
            // Suspend customer service
            $subscription = Subscription::where('customer_id', $invoice->customer_id)->first();
            if ($subscription && $subscription->customer_service_id) {
                $customerService = \App\Models\Customer\CustomerService::find($subscription->customer_service_id);
                if ($customerService) {
                    $customerService->update(['status' => 'suspended']);
                }
            }
        }
    }

    public function verifyPayment(Invoice $invoice, float $amount): void
    {
        DB::transaction(function () use ($invoice, $amount) {
            $newPaidAmount = $invoice->paid_amount + $amount;
            $invoice->update(['paid_amount' => $newPaidAmount]);

            if ($newPaidAmount >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
                ReactivateCustomerJob::dispatch($invoice);
            }
        });
    }
}
