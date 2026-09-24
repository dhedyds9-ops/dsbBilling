<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Enums\ISP\CoaType;
use App\Jobs\ISP\Radius\DispatchBatchCoaJob;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\ISP\PPPoEUser;
use Illuminate\Support\Facades\Log;
use Src\Domain\Billing\Events\InvoicePaidEvent;

/**
 * SSOT: InvoicePaid Listener → Auto-Activation Flow.
 *
 * Urutan eksekusi (non-blocking):
 *   1. Kumpulkan PPPoEUser dari semua invoice subscription
 *   2. DISPATCH BATCH COA Reactivate via Horizon (TIDAK sync loop!)
 *   3. KIRIM WA notifikasi via WhatsAppChannel
 *   4. LOG Audit trail ke payment_paid_audits (via Log facade → audit structured logging → bisa dimigrasikan ke table nanti)
 *
 * JANGAN PERNAH memanggil COA sync di sini. Selalu async via queue!
 */
final class InvoicePaidListener
{
    public function __construct(
        private readonly \App\Services\Notifications\WhatsApp\WhatsAppNotificationService $waService,
    ) {}

    public function handle(InvoicePaidEvent $event): void
    {
        $invoiceUuid = $event->invoiceId ?? '';
        $customerId = (int)($event->customerId ?? 0);
        $amount = (float)($event->amountPaid ?? 0.0);

        if ($invoiceUuid === '' && $customerId === 0) {
            Log::warning('[InvoicePaidListener] event tanpa uuid/customer_id', (array)$event);
            return;
        }

        /** @var Invoice|null $invoice */
        $invoice = Invoice::query()->where('uuid', $invoiceUuid)->first();
        if (!$invoice && $customerId > 0) {
            $invoice = Invoice::query()
                ->where('customer_id', $customerId)
                ->orderBy('due_date', 'desc')
                ->first();
        }
        if (!$invoice) {
            Log::warning('[InvoicePaidListener] Invoice tidak ditemukan', compact('invoiceUuid', 'customerId'));
            return;
        }

        $customer = Customer::query()->find((int)$invoice->customer_id);
        if (!$customer) {
            Log::warning('[InvoicePaidListener] Customer tidak ditemukan', ['customer_id' => $invoice->customer_id]);
        }

        // 1. Temukan PPPoEUsers aktif / suspended dari subscription milik customer
        $pppoeUsers = PPPoEUser::query()
            ->whereHas('customerService', function ($q) use ($customer) {
                $q->where('customer_id', (int)($customer?->id ?? $invoice->customer_id));
            })
            ->whereIn('status', ['suspended', 'active'])
            ->get();

        if ($pppoeUsers->count() > 0) {
            try {
                DispatchBatchCoaJob::dispatch(
                    coaType: CoaType::Reactivate,
                    users: $pppoeUsers,
                    operatorId: 1,
                    batchName: "Auto-Reactivate-{$invoice->invoice_number}-" . now()->format('H-i') . "-{$pppoeUsers->count()}u",
                )->onQueue('radius-coa');

                Log::info('[InvoicePaidListener] Dispatched batch COA Reactivate', [
                    'invoice_uuid' => $invoiceUuid,
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'pppoe_count' => $pppoeUsers->count(),
                    'amount' => $amount,
                ]);
            } catch (\Throwable $e) {
                Log::critical('[InvoicePaidListener] FATAL dispatch COA Reactivate', [
                    'err' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'invoice_id' => $invoice->id,
                ]);
                // Jangan throw, listener jangan gagal cuma karena COA. Kita kirim WA dulu.
            }
        } else {
            Log::info('[InvoicePaidListener] Tidak ada PPPoE user untuk reactivate (tidak berlangganan fiber)', [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
            ]);
        }

        // 2. Kirim WhatsApp notifikasi
        try {
            $this->waService->notifyPaymentSuccess($invoice, $amount, $invoiceUuid);
        } catch (\Throwable $e) {
            Log::warning('[InvoicePaidListener] WA notification failed', [
                'err' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);
        }

        // 3. Audit trail (structured log — bisa digabung ke DB audit table nanti)
        Log::channel('payment_audit')->info('invoice_paid_processed', [
            'invoice_uuid' => $invoiceUuid,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number ?? null,
            'customer_id' => $invoice->customer_id,
            'customer_name' => $customer?->name ?? null,
            'amount_paid' => $amount,
            'pppoe_users_reactivated' => $pppoeUsers->count(),
            'processed_at' => now()->toIso8601String(),
        ]);
    }
}
