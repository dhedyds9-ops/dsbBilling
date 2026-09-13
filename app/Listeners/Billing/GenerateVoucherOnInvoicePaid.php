<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Jobs\ISP\Voucher\ProvisionVoucherOrderJob;
use App\Models\Billing\Invoice;
use App\Models\VoucherOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Src\Domain\Billing\Events\InvoicePaidEvent;

/**
 * Event listener untuk mengeksekusi penyediaan voucher secara asinkron
 * ketika sebuah invoice ritel dibayar lunas.
 */
class GenerateVoucherOnInvoicePaid
{
    public function handle(InvoicePaidEvent $event): void
    {
        $invoiceUuid = $event->invoiceUuid ?? '';
        
        if (empty($invoiceUuid)) {
            return;
        }

        // Cari invoice untuk mendapatkan ID aslinya
        $invoice = Invoice::query()->where('uuid', $invoiceUuid)->first();
        if (!$invoice) {
            return;
        }

        // Cek apakah invoice ini terikat dengan pesanan voucher (Guest Checkout)
        $voucherOrder = VoucherOrder::where('invoice_id', $invoice->id)->first();
        
        if ($voucherOrder) {
            // Idempotency: Jika statusnya bukan pending atau payment_processing, abaikan.
            // Bisa jadi webhook ditarik ganda.
            if (!in_array($voucherOrder->status, [VoucherOrder::STATUS_PENDING, VoucherOrder::STATUS_PAYMENT_PROCESSING])) {
                Log::info('[GenerateVoucherOnInvoicePaid] Mengabaikan invoice paid event untuk voucher order karena status sudah diproses', [
                    'order_id' => $voucherOrder->id,
                    'status' => $voucherOrder->status
                ]);
                return;
            }

            // Update status menjadi paid
            $voucherOrder->update([
                'status' => VoucherOrder::STATUS_PAID,
                'paid_at' => now()
            ]);

            Log::info('[GenerateVoucherOnInvoicePaid] Mendelegasikan tugas generate voucher ke Queue Job', [
                'order_id' => $voucherOrder->id
            ]);

            // Dispatch background job untuk nge-hit Mikrotik dan kirim WA
            ProvisionVoucherOrderJob::dispatch($voucherOrder->id)->onQueue('default');
        }
    }
}
