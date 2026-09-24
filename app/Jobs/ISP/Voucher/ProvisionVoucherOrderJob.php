<?php

namespace App\Jobs\ISP\Voucher;

use App\Models\VoucherOrder;
use App\Services\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Voucher\Actions\GenerateVoucherAction;

class ProvisionVoucherOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public int $voucherOrderId
    ) {}

    public function handle(GenerateVoucherAction $generateAction, WhatsAppChannel $waChannel): void
    {
        $lockKey = "provision_voucher_order_{$this->voucherOrderId}";

        $lock = Cache::lock($lockKey, 60);

        if (!$lock->get()) {
            Log::warning('[ProvisionVoucherOrderJob] Idempotency lock active. Bypassing.', ['voucher_order_id' => $this->voucherOrderId]);
            return;
        }

        try {
            /** @var VoucherOrder $order */
            $order = VoucherOrder::find($this->voucherOrderId);

            if (!$order) {
                return;
            }

            // Validasi Idempotency: Jika sudah completed, skip!
            if ($order->status === VoucherOrder::STATUS_COMPLETED || $order->voucher_id) {
                Log::info('[ProvisionVoucherOrderJob] Voucher sudah ter-generate.', ['order_id' => $order->id]);
                return;
            }

            // Ubah status ke generating
            $order->update(['status' => VoucherOrder::STATUS_VOUCHER_GENERATING]);

            // Generate Voucher!
            $attrs = [
                'type' => 'evoucher',
                'service_profile_id' => $order->service_profile_id,
                'prefix' => '',
                'notes' => "Direct Purchase INV: {$order->invoice_id}",
            ];

            // 1 system admin ID
            $vouchers = $generateAction->generateAdHocVouchers($attrs, 1, 1);

            if (empty($vouchers)) {
                throw new \Exception('Mikrotik gagal membuat voucher. Respons kosong.');
            }

            $generatedVoucher = $vouchers[0]; // Ambil yang pertama (karena quantity 1)

            // Update order
            $order->update([
                'status' => VoucherOrder::STATUS_COMPLETED,
                'voucher_id' => $generatedVoucher->id,
                'voucher_username' => $generatedVoucher->username,
                'voucher_password' => $generatedVoucher->password,
                'voucher_generated_at' => now(),
                'completed_at' => now(),
            ]);

            Log::info('[ProvisionVoucherOrderJob] Provisioning sukses.', ['order_id' => $order->id, 'voucher' => $generatedVoucher->username]);

            // Dispatch pesan WA secara aman (non-blocking ke transaksi jika error)
            try {
                $payNo = substr($order->uuid, 0, 8);
                $msg = "🎉 *Pembelian Berhasil!*\n\n"
                     . "Terima kasih telah membeli *{$order->service_profile_name}* via dsBilling.\n\n"
                     . "Gunakan kredensial berikut untuk *Login Hotspot*:\n"
                     . "Username: *{$generatedVoucher->username}*\n"
                     . "Password: *{$generatedVoucher->password}*\n\n"
                     . "Simpan pesan ini baik-baik. Selamat berselancar!";
                     
                $waChannel->send($order->wa_number, $msg);
            } catch (\Throwable $waError) {
                Log::error('[ProvisionVoucherOrderJob] Gagal mengirim WA.', [
                    'order_id' => $order->id,
                    'err' => $waError->getMessage()
                ]);
            }

        } catch (\Throwable $e) {
            DB::table('voucher_orders')->where('id', $this->voucherOrderId)->update([
                'status' => VoucherOrder::STATUS_FAILED,
                'failure_reason' => substr($e->getMessage(), 0, 500)
            ]);
            Log::error('[ProvisionVoucherOrderJob] Fatal Error', ['order_id' => $this->voucherOrderId, 'err' => $e->getMessage()]);
            throw $e;
        } finally {
            $lock->release();
        }
    }
}
