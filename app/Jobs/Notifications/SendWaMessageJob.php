<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\Services\Notifications\WhatsApp\WaAntiSpamService;
use App\Services\Notifications\WhatsApp\WaGatewayRegistry;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SSOT: Kirim satu pesan WA via driver utama, dengan rate limit + cooldown + retry backoff.
 *
 * - Horizon queue `notifications-wa`
 * - RateLimited: 180 pesan / 60 detik (3/sec — aman di WhatsApp Web Multidevice + Official API)
 * - WithoutOverlapping per phone: 5 detik
 * - Tries 3, backoff [15, 30, 60]
 */
final class SendWaMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public array $backoff = [15, 30, 60];
    public int $maxExceptions = 5;

    public function __construct(
        public readonly WaOutgoingMessage $message,
        public readonly ?int $historyId = null,
    ) {}

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('wa-send-' . ($this->message->idempotencyKey ?? 'phone-' . $this->message->toPhone)))->releaseAfter(5)->expireAfter(300),
            new RateLimited('wa-outbound', 180, 60),
        ];
    }

    public function handle(WaGatewayRegistry $registry, WaAntiSpamService $antispam): void
    {
        $msg = $this->message;

        // 1. Preflight antispam
        [$ok, $reason] = $antispam->preflight($msg->toPhone, $msg->category, $msg->priorityHigh);
        if (!$ok) {
            Log::notice('[WA-SEND] Dibatalkan preflight antispam', [
                'reason' => $reason,
                'to' => $msg->toPhone,
                'cat' => $msg->category,
                'high' => (int)$msg->priorityHigh,
            ]);
            if (in_array($reason, ['global_quota_full', 'cooldown'], true)) {
                $this->release(30);
                return;
            }
            // invalid / daily cap: buang
            if ($this->historyId) {
                \App\Models\Integration\WaMessageHistory::find($this->historyId)?->update([
                    'status' => 'failed',
                    'error_message' => 'Dibatalkan antispam: ' . $reason
                ]);
            }
            $this->delete();
            return;
        }

        // 2. Cari primary driver (fallback chain ke driver lain jika primary gagal)
        $drivers = array_values(array_filter([$registry->primary()]));
        if (count($drivers) === 0) {
            $drivers = array_values($registry->all());
        }
        if (count($drivers) === 0) {
            Log::warning('[WA-SEND] Tidak ada driver WhatsApp tersedia');
            $this->release(60);
            return;
        }

        $lastErr = null;
        foreach ($drivers as $driver) {
            try {
                $res = $driver->sendMessage($msg);
                if ($res->success) {
                    if (!$msg->priorityHigh) {
                        $antispam->markCoolingDown($msg->toPhone, $msg->category);
                    }
                    Log::info('[WA-SEND] OK via ' . $driver->driverKey(), [
                        'to' => $msg->toPhone,
                        'gateway_msg_id' => $res->gatewayMessageId,
                    ]);
                    
                    if ($this->historyId) {
                        \App\Models\Integration\WaMessageHistory::find($this->historyId)?->update([
                            'status' => 'sent',
                            'payload' => ['gateway_msg_id' => $res->gatewayMessageId, 'driver' => $driver->driverKey()]
                        ]);
                    }
                    
                    return;
                }
                $lastErr = $res->errorCode . ' ' . $res->errorMessage;
            } catch (\Throwable $e) {
                $lastErr = get_class($e) . ': ' . $e->getMessage();
                Log::warning('[WA-SEND] driver ' . $driver->driverKey() . ' error: ' . $lastErr);
            }
        }

        Log::warning('[WA-SEND] SEMUA driver gagal. Release retry.', [
            'to' => $msg->toPhone,
            'last_err' => $lastErr,
        ]);
        
        if ($this->historyId) {
            \App\Models\Integration\WaMessageHistory::find($this->historyId)?->update([
                'error_message' => $lastErr
            ]);
        }

        $this->release(30);
    }

    public function failed(\Throwable $exception)
    {
        if ($this->historyId) {
            \App\Models\Integration\WaMessageHistory::find($this->historyId)?->update([
                'status' => 'failed',
                'error_message' => substr($exception->getMessage(), 0, 1000)
            ]);
        }
    }

    public function uniqueId(): string
    {
        return (string)($this->message->idempotencyKey ?? ('wa-' . $this->message->toPhone . '-' . substr(sha1($this->message->text ?? ''), 0, 10)));
    }

    public function tags(): array
    {
        return [
            'wa-outbound',
            'wa:cat:' . $this->message->category,
            'wa:to:' . $this->message->toPhone,
        ];
    }
}
