<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Services\Notifications\Channels\WhatsAppChannel;
use Illuminate\Support\Facades\Log;
use Src\Domain\Billing\Events\PaymentVerifiedEvent;

/**
 * SSOT: Payment Verified Listener → Journal Post + Kirim Resi WA (audit + customer receipt).
 */
final class PaymentVerifiedListener
{
    public function __construct(
        private readonly WhatsAppChannel $wa,
    ) {}

    public function handle(PaymentVerifiedEvent $event): void
    {
        $paymentUuid = (string)($event->paymentUuid ?? '');
        $customerId = (int)($event->customerId ?? 0);
        $amount = (float)($event->amount ?? 0.0);

        // TODO: Post ke Jurnal Umum (GL): Debit Kas / Bank, Kredit Piutang Usaha / Pendapatan Layanan
        Log::channel('payment_audit')->info('payment_verified_post_to_gl_pending', [
            'payment_uuid' => $paymentUuid,
            'customer_id' => $customerId,
            'amount' => $amount,
            'gl_entry' => [
                'debit' => ['account' => 'Kas Bank', 'amount' => $amount],
                'credit' => ['account' => 'Piutang Usaha / Pendapatan', 'amount' => $amount],
            ],
        ]);

        // Kirim receipt via WA jika no-telp ditemukan
        try {
            $customer = \App\Models\CRM\Customer::query()->find($customerId);
            if ($customer && !empty($customer->phone)) {
                $payNo = 'PAY-' . ($paymentUuid ? substr($paymentUuid, 0, 8) : time());
                $msg = "🧾 *KUITANSI PEMBAYARAN - dsBilling*\n\n"
                     . "No. Resi: *{$payNo}*\n"
                     . "Tanggal: " . now()->format('d M Y H:i') . "\n"
                     . "Jumlah: *Rp " . number_format($amount, 0, ',', '.') . "*\n\n"
                     . "Terima kasih. Pembayaran Anda *sudah kami terima* dan diverifikasi otomatis.\n"
                     . "Simpan resi ini sebagai bukti pembayaran yang sah.";

                Log::channel('whatsapp')->info('PAYMENT_RECEIPT_WHATSAPP', [
                    'to' => $customer->phone,
                    'message' => $msg,
                    'payment_uuid' => $paymentUuid,
                ]);
            }
        } catch (\Throwable) {
        }
    }
}
