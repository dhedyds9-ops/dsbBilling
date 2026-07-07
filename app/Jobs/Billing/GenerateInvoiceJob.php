<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Subscription;
use App\Models\Customer\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $customerService = CustomerService::find($this->subscription->customer_service_id);
            $serviceName = $customerService?->service?->name ?? 'Langganan';

            $invoiceNumber = 'INV/' . date('Y/m/d') . '/' . strtoupper(Str::random(6));

            $invoice = Invoice::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $this->subscription->customer_id,
                'contract_id' => $this->subscription->contract_id,
                'invoice_number' => $invoiceNumber,
                'issue_date' => now(),
                'due_date' => now()->addDays(7),
                'total_amount' => $this->subscription->recurring_price,
                'currency' => 'IDR',
                'status' => 'unpaid',
                'paid_amount' => 0,
                'created_by' => auth()->id() ?? 1,
                'updated_by' => auth()->id() ?? 1,
            ]);

            InvoiceItem::create([
                'uuid' => (string) Str::uuid(),
                'invoice_id' => $invoice->id,
                'description' => $serviceName,
                'quantity' => 1,
                'unit_price' => $this->subscription->recurring_price,
                'subtotal' => $this->subscription->recurring_price,
                'created_by' => auth()->id() ?? 1,
                'updated_by' => auth()->id() ?? 1,
            ]);

            $this->subscription->update([
                'last_billing_date' => now(),
                'next_billing_date' => $this->calculateNextBillingDate(),
            ]);
        });
    }

    private function calculateNextBillingDate()
    {
        $current = $this->subscription->next_billing_date ?? now();
        
        switch ($this->subscription->billing_cycle) {
            case 'weekly':
                return $current->addWeek();
            case 'biweekly':
                return $current->addWeeks(2);
            case 'monthly':
                return $current->addMonth();
            case 'quarterly':
                return $current->addQuarter();
            case 'yearly':
                return $current->addYear();
            default:
                return $current->addMonth();
        }
    }
}
