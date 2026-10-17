<?php

namespace App\Livewire\Guest;

use App\Jobs\ISP\Voucher\ProvisionVoucherOrderJob;
use App\Models\Payment\Payment;
use App\Models\VoucherOrder;
use App\Services\Adapters\Payment\PaymentGatewayRegistry;
use App\Services\Adapters\Payment\PaymentOrchestrationService;
use App\Services\Adapters\Payment\ValueObjects\WebhookEvent;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BuyVoucherSuccess extends Component
{
    public string $uuid;
    public ?VoucherOrder $voucherOrder = null;

    public function mount(string $uuid)
    {
        $this->uuid = $uuid;
        $this->loadOrder();
    }

    public function loadOrder(): void
    {
        $this->voucherOrder = VoucherOrder::withoutGlobalScopes()
            ->with(['serviceProfile', 'voucher', 'invoice'])
            ->where('uuid', $this->uuid)
            ->first();

        if ($this->voucherOrder && ($this->voucherOrder->status !== VoucherOrder::STATUS_COMPLETED || empty($this->voucherOrder->voucher_username))) {
            $this->syncPaymentStatus();
        }
    }

    public function checkStatus(): void
    {
        $this->loadOrder();
    }

    protected function syncPaymentStatus(): void
    {
        if (!$this->voucherOrder) {
            return;
        }

        // Ambil orderId dari query redirect (Midtrans mengirimkan ?order_id=...&transaction_status=settlement)
        $queryOrderId = trim((string) request()->query('order_id', ''));
        $queryStatus = strtolower(trim((string) request()->query('transaction_status', '')));
        $queryStatusCode = (string) request()->query('status_code', '');

        $orderId = $queryOrderId ?: $this->voucherOrder->payment_reference;

        // Cari record Payment terkait
        $payment = null;
        if ($orderId) {
            $payment = Payment::withoutGlobalScopes()->where('reference_number', $orderId)->first();
        }
        if (!$payment && $this->voucherOrder->invoice_id) {
            $payment = Payment::withoutGlobalScopes()->whereHas('invoices', function ($q) {
                $q->where('invoices.id', $this->voucherOrder->invoice_id);
            })->latest()->first();
        }

        $isPaid = false;

        // 1. Cek apakah Payment di DB sudah status success / paid
        if ($payment && in_array($payment->status, ['success', 'paid'])) {
            $isPaid = true;
        }

        // 2. Jika belum paid di DB, cek langsung status ke Gateway (Midtrans API)
        if (!$isPaid && $payment) {
            try {
                $gatewayKey = $payment->gateway ?: 'midtrans';
                $registry = app(PaymentGatewayRegistry::class);
                $driver = $registry->get($gatewayKey);

                if ($driver && method_exists($driver, 'checkStatus')) {
                    $checkRef = $payment->reference_number ?: $orderId;
                    $statusResp = $driver->checkStatus($checkRef);

                    if (in_array($statusResp->status, ['paid', 'success', 'settlement', 'capture'])) {
                        $isPaid = true;

                        // Panggil orchestration untuk menyelesaikan invoice & payment secara resmi
                        $syntheticEvent = new WebhookEvent(
                            eventType: 'payment.paid',
                            gatewayOrderId: $statusResp->gatewayReferenceId ?: $checkRef,
                            merchantOrderId: $checkRef,
                            amountIdr: $statusResp->paidAmountIdr ?: (int)$payment->amount,
                            paidAmountIdr: $statusResp->paidAmountIdr ?: (int)$payment->amount,
                            paymentMethod: $statusResp->paymentMethod ?: $payment->method,
                            paidAtIso: $statusResp->paidAtIso ?: now()->toISOString(),
                            signatureVerifiedBy: 'browser_sync_success_page',
                            rawBodyHash: hash('sha256', 'success_sync_' . $checkRef . '_' . time()),
                            rawPayload: ['synthetic' => true, 'source' => 'success_page', 'status' => $statusResp->status],
                        );

                        app(PaymentOrchestrationService::class)->handleWebhookPaid($syntheticEvent, $gatewayKey);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[BuyVoucherSuccess] gateway checkStatus exception: ' . $e->getMessage());
            }
        }

        // 3. Fallback: Verifikasi redirect parameter dari Midtrans (status_code 200 & transaction_status settlement)
        if (!$isPaid && in_array($queryStatus, ['settlement', 'capture']) && in_array($queryStatusCode, ['200', '201'])) {
            $isPaid = true;
            if ($payment && !in_array($payment->status, ['success', 'paid'])) {
                $payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
            }
            if ($this->voucherOrder->invoice && $this->voucherOrder->invoice->status !== 'paid') {
                $this->voucherOrder->invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        }

        // 4. Jika pembayaran sudah valid/paid, tapi voucher belum ter-generate di database:
        if ($isPaid && (empty($this->voucherOrder->voucher_username) || $this->voucherOrder->status !== VoucherOrder::STATUS_COMPLETED)) {
            try {
                if ($this->voucherOrder->status !== VoucherOrder::STATUS_PAID) {
                    $this->voucherOrder->update([
                        'status' => VoucherOrder::STATUS_PAID,
                        'paid_at' => $this->voucherOrder->paid_at ?: now(),
                    ]);
                }

                // Eksekusi provisioning langsung agar user langsung melihat kode voucher di layar
                ProvisionVoucherOrderJob::dispatchSync($this->voucherOrder->id);

                // Reload data order terbaru
                $this->voucherOrder = VoucherOrder::withoutGlobalScopes()
                    ->with(['serviceProfile', 'voucher'])
                    ->where('uuid', $this->uuid)
                    ->first();
            } catch (\Throwable $e) {
                Log::error('[BuyVoucherSuccess] Direct Provisioning Failed: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $title = 'Status Pembelian Voucher Internet';
        if ($this->voucherOrder && $this->voucherOrder->serviceProfile) {
            $title .= ' - ' . $this->voucherOrder->serviceProfile->name;
        }

        return view('livewire.guest.buy-voucher-success')
            ->layout('layouts.guest', ['title' => $title]);
    }
}
