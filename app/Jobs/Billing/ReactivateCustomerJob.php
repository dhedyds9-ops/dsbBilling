<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Invoice;
use App\Models\Customer\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReactivateCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
    ) {}

    public function handle(\App\Services\ISP\ISPProvisioningService $provisioningService): void
    {
        // Cari layanan pelanggan yang dibayar dan menunggu aktivasi (pending)
        $customerServices = CustomerService::where('customer_id', $this->invoice->customer_id)
            ->where('status', 'suspended')
            ->where('reactivation_status', 'pending')
            ->get();

        foreach ($customerServices as $cs) {
            try {
                $reactivateResult = $provisioningService->reactivateCustomerService($cs);
                
                $isSuccess = $reactivateResult['success'] ?? false;
                
                // Update state sesuai konsep: REACTIVATION_PENDING -> COA SUCCESS/FAILED
                $cs->update([
                    'status' => $isSuccess ? 'active' : 'suspended',
                    'reactivation_status' => $isSuccess ? 'success' : 'failed',
                    'suspended_at' => $isSuccess ? null : $cs->suspended_at,
                ]);
                
                \Illuminate\Support\Facades\Log::info('ReactivateCustomerJob reactivate provisioning', [
                    'invoice_id' => $this->invoice->id,
                    'cs_id' => $cs->id,
                    'is_success' => $isSuccess,
                    'enabled' => $reactivateResult['enabled'] ?? 0,
                    'kicked' => $reactivateResult['kicked'] ?? 0,
                    'errors' => $reactivateResult['errors'] ?? [],
                ]);
            } catch (\Throwable $e) {
                // Hard failure -> REACTIVATION_FAILED
                $cs->update([
                    'reactivation_status' => 'failed'
                ]);
                
                \Illuminate\Support\Facades\Log::error('ReactivateCustomerJob reactivate provisioning FATAL', [
                    'cs_id' => $cs->id,
                    'invoice_id' => $this->invoice->id,
                    'err' => $e->getMessage(),
                ]);
            }
        }
    }
}
