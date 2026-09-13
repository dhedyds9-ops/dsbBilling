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

            // Baca settlement price: coba kolom owner_settlement_price (lama),
            // fallback ke owner_price (baru) jika nol
            $ownerSettlementPrice    = (float) ($customerService?->serviceProfile?->owner_settlement_price
                ?: $customerService?->serviceProfile?->owner_price ?? 0);
            $branchSettlementPrice   = (float) ($customerService?->serviceProfile?->branch_settlement_price ?? 0);
            $resellerSettlementPrice = (float) ($customerService?->serviceProfile?->reseller_settlement_price
                ?: $customerService?->serviceProfile?->reseller_price ?? 0);

            $invoiceNumber = 'INV/' . date('Y/m/d') . '/' . strtoupper(Str::random(6));

            $invoice = Invoice::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $this->subscription->customer_id,
                'contract_id' => $this->subscription->contract_id,
                'invoice_number' => $invoiceNumber,
                'issue_date' => now(),
                'due_date' => now()->addDays((int) \App\Models\Setting::getValue('billing.due_days', 7)),
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
                'owner_settlement_price' => $ownerSettlementPrice,
                'branch_settlement_price' => $branchSettlementPrice,
                'reseller_settlement_price' => $resellerSettlementPrice,
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
                return $current->copy()->addWeek();
            case 'biweekly':
                return $current->copy()->addWeeks(2);
            case 'monthly':
                return $current->copy()->addMonthNoOverflow();
            case 'quarterly':
                return $current->copy()->addMonthsNoOverflow(3);
            case 'yearly':
                return $current->copy()->addYearNoOverflow();
            default:
                return $current->copy()->addMonthNoOverflow();
        }
    }
}
