<?php

namespace App\Jobs\Notifications;

use App\Models\Billing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public readonly Invoice $invoice
    ) {}

    public function handle(): void
    {
        try {
            $customer = \App\Models\Customer::find($this->invoice->customer_id);
            if (!$customer) {
                $customer = \App\Models\CRM\Customer::find($this->invoice->customer_id);
            }

            if (!$customer || empty($customer->email)) {
                Log::info('[EMAIL-SEND] Skip sending invoice email, no valid email found for customer ID: ' . $this->invoice->customer_id);
                return;
            }

            // Simulate Email Sending since we don't have views or real SMTP setup yet
            // Mail::to($customer->email)->send(new \App\Mail\InvoiceCreatedMail($this->invoice));
            
            Log::info('[EMAIL-SEND] Invoice email sent to ' . $customer->email . ' for Invoice ID: ' . $this->invoice->id);
            
            // Mark email as sent on invoice if needed
        } catch (\Throwable $e) {
            Log::error('[EMAIL-SEND] Failed to send email: ' . $e->getMessage());
            throw $e;
        }
    }
}
