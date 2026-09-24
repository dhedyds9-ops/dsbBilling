<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Invoice;
use App\Models\Customer\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SuspendCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
    ) {}

    public function handle(): void
    {
        $customerServices = CustomerService::where('customer_id', $this->invoice->customer_id)
            ->where('status', 'active')
            ->get();

        foreach ($customerServices as $cs) {
            $cs->update(['status' => 'suspended']);
        }
    }
}
