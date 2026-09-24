<?php

namespace App\Services\Billing;

use App\Jobs\Billing\GenerateInvoiceJob;
use App\Jobs\Billing\ReactivateCustomerJob;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Services\ISP\ISPProvisioningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingAutomationService
{
    public function __construct(
        private ISPProvisioningService $provisioningService
    ) {}

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
            $subscription = Subscription::where('customer_id', $invoice->customer_id)->first();
            if (!$subscription || !$subscription->customer_service_id) {
                continue;
            }

            $customerService = \App\Models\Customer\CustomerService::find($subscription->customer_service_id);
            if (!$customerService) {
                continue;
            }

            if ($customerService->status === 'suspended' || $customerService->status === 'terminated') {
                continue;
            }

            try {
                $customerService->update([
                    'status' => 'suspended',
                    'suspended_at' => $customerService->suspended_at ?? now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Update DB status suspend gagal', [
                    'cs_id' => $customerService->id,
                    'invoice_id' => $invoice->id,
                    'err' => $e->getMessage(),
                ]);
            }

            try {
                $provisionResult = $this->provisioningService->suspendCustomerService($customerService);
                Log::info('BillingAutomation suspend provisioning', [
                    'invoice_id' => $invoice->id,
                    'cs_id' => $customerService->id,
                    'disabled' => $provisionResult['disabled'] ?? 0,
                    'kicked' => $provisionResult['kicked'] ?? 0,
                    'errors' => $provisionResult['errors'] ?? [],
                ]);
            } catch (\Throwable $e) {
                Log::error('BillingAutomation suspend provisioning FATAL', [
                    'cs_id' => $customerService->id,
                    'invoice_id' => $invoice->id,
                    'err' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    public function verifyPayment(Invoice $invoice, float $amount): void
    {
        DB::transaction(function () use ($invoice, $amount) {
            $newPaidAmount = $invoice->paid_amount + $amount;
            $invoice->update(['paid_amount' => $newPaidAmount]);

            if ($newPaidAmount >= $invoice->total_amount && $invoice->status !== 'paid') {
                $invoice->update(['status' => 'paid']);

                $subscription = Subscription::where('customer_id', $invoice->customer_id)->first();
                if ($subscription && $subscription->customer_service_id) {
                    $customerService = \App\Models\Customer\CustomerService::find($subscription->customer_service_id);
                    if ($customerService) {
                        try {
                            $reactivateResult = $this->provisioningService->reactivateCustomerService($customerService);
                            Log::info('BillingAutomation reactivate provisioning', [
                                'invoice_id' => $invoice->id,
                                'cs_id' => $customerService->id,
                                'enabled' => $reactivateResult['enabled'] ?? 0,
                                'kicked' => $reactivateResult['kicked'] ?? 0,
                                'errors' => $reactivateResult['errors'] ?? [],
                            ]);
                        } catch (\Throwable $e) {
                            Log::error('BillingAutomation reactivate provisioning FATAL', [
                                'cs_id' => $customerService->id,
                                'invoice_id' => $invoice->id,
                                'err' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                }

                ReactivateCustomerJob::dispatch($invoice);
            }
        });
    }
}
