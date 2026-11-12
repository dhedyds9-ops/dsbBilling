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
        $customerId = (int) $this->invoice->customer_id;
        if ($customerId <= 0) {
            return;
        }

        // 1. Pastikan status Customer dipulihkan menjadi active
        \App\Models\CRM\Customer::withoutGlobalScopes()
            ->where('id', $customerId)
            ->update(['status' => 'active']);

        // 2. Pulihkan semua CustomerService milik pelanggan
        $customerServices = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->with(['pppoeUser', 'hotspotUser'])
            ->get();

        foreach ($customerServices as $cs) {
            try {
                $reactivateResult = $provisioningService->reactivateCustomerService($cs);
                
                $isSuccess = $reactivateResult['success'] ?? false;
                
                // Update state: status selalu active setelah tagihan lunas, reactivation_status mencatat hasil sinkronisasi hardware
                $cs->update([
                    'status' => 'active',
                    'reactivation_status' => $isSuccess ? 'success' : 'pending_hardware',
                    'suspended_at' => null,
                ]);

                if ($cs->pppoeUser) {
                    $cs->pppoeUser->update([
                        'status' => 'active',
                        'suspended_at' => null,
                    ]);
                }

                if ($cs->hotspotUser) {
                    $cs->hotspotUser->update([
                        'status' => 'active',
                        'suspended_at' => null,
                    ]);
                }
                
                \Illuminate\Support\Facades\Log::info('ReactivateCustomerJob reactivate provisioning', [
                    'invoice_id' => $this->invoice->id,
                    'cs_id' => $cs->id,
                    'is_success' => $isSuccess,
                    'enabled' => $reactivateResult['enabled'] ?? 0,
                    'kicked' => $reactivateResult['kicked'] ?? 0,
                    'errors' => $reactivateResult['errors'] ?? [],
                ]);
            } catch (\Throwable $e) {
                $cs->update([
                    'status' => 'active',
                    'reactivation_status' => 'pending_hardware',
                    'suspended_at' => null,
                ]);
                
                \Illuminate\Support\Facades\Log::error('ReactivateCustomerJob reactivate provisioning warning', [
                    'cs_id' => $cs->id,
                    'invoice_id' => $this->invoice->id,
                    'err' => $e->getMessage(),
                ]);
            }
        }
    }
}
