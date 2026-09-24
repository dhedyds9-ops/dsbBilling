<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Models\CRM\Customer;
use App\Services\Notifications\WhatsApp\WhatsAppNotificationService;
use Illuminate\Support\Facades\Log;
use Src\Domain\Billing\Events\PaymentVerifiedEvent;

/**
 * SSOT: Payment Verified Listener → Journal Post + Kirim Resi WA (audit + customer receipt).
 *
 * Urutan eksekusi:
 *   1. Audit trail ke payment_audit log (structured → bisa dimigrasikan ke GL table nanti)
 *   2. Dispatch notifikasi WA kuitansi via WhatsAppNotificationService (Async Queue + Rate Limit)
 *
 * JANGAN memanggil WhatsAppChannel di sini — itu channel untuk Alarm object, bukan Billing.
 */
final class PaymentVerifiedListener
{
    public function __construct(
        private readonly WhatsAppNotificationService $waService,
    ) {}

    public function handle(PaymentVerifiedEvent $event): void
    {
        $paymentId = (string)($event->paymentId ?? '');
        $customerId = (int)($event->customerId ?? 0);
        $amount = (float)($event->amount ?? 0.0);

        if ($paymentId === '' && $customerId === 0) {
            Log::warning('[PaymentVerifiedListener] event tanpa paymentId/customerId', (array)$event);
            return;
        }

        // TODO: Post ke Jurnal Umum (GL): Debit Kas / Bank, Kredit Piutang Usaha / Pendapatan Layanan
        Log::channel('payment_audit')->info('payment_verified_post_to_gl_pending', [
            'payment_uuid' => $paymentId,
            'customer_id' => $customerId,
            'amount' => $amount,
            'gl_entry' => [
                'debit' => ['account' => 'Kas Bank', 'amount' => $amount],
                'credit' => ['account' => 'Piutang Usaha / Pendapatan', 'amount' => $amount],
            ],
            'verified_at' => now()->toIso8601String(),
        ]);

        // Kirim receipt via WA notification service (SSOT untuk billing WA)
        try {
            $customer = Customer::query()->find($customerId);
            $receiptNo = 'PAY-' . ($paymentId ? substr($paymentId, 0, 8) : (string)time());

            // Jika ada invoice terkait, kirim notifikasi payment success per invoice via SSOT
            $payment = \App\Models\Payment\Payment::query()
                ->where('uuid', $paymentId)
                ->with('invoices')
                ->first();

            if ($payment && $payment->relationLoaded('invoices') && $payment->invoices->count() > 0) {
                foreach ($payment->invoices as $invoice) {
                    $invoiceAmount = min($amount, (float)($invoice->total_amount - $invoice->paid_amount) ?: $amount);
                    $this->waService->notifyPaymentSuccess($invoice, $invoiceAmount, $receiptNo);
                }
            } elseif ($customer && !empty($customer->phone)) {
                // Fallback: kirim notifikasi kuitansi generik via template jika invoice tidak ditemukan
                $msg = "🧾 *KUITANSI PEMBAYARAN*\n\n"
                     . "No. Resi: *{$receiptNo}*\n"
                     . "Tanggal: " . now()->format('d M Y H:i') . "\n"
                     . "Jumlah: *Rp " . number_format($amount, 0, ',', '.') . "*\n\n"
                     . "Terima kasih. Pembayaran Anda *sudah kami terima* dan diverifikasi otomatis.\n"
                     . "Simpan resi ini sebagai bukti pembayaran yang sah.";

                Log::channel('whatsapp')->info('PAYMENT_RECEIPT_WHATSAPP_FALLBACK', [
                    'to' => $customer->phone,
                    'message' => $msg,
                    'payment_uuid' => $paymentId,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[PaymentVerifiedListener] WA notification failed', [
                'err' => $e->getMessage(),
                'payment_uuid' => $paymentId,
                'customer_id' => $customerId,
            ]);
        }
    }
}
