<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

/**
 * SSOT: Batch Broadcast WA (chunk 50 pesan per batch child) — Horizon Bus::batch.
 *
 * - Dipakai untuk: Broadcast maintenance / outage / promosi.
 * - Flow: iterasi daftar toPhone + texts → chunk 50 → tiap chunk = array SendWaMessageJob
 *   → Bus::batch($jobs) → then/finally callback.
 * - throttle per chunk: 10 detik delay di tiap awal job chunk
 */
final class SendWaBroadcastBatchJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;
    public array $backoff = [30, 60];

    /**
     * @param list<array{0:string,1:string,2?:string,3?:bool}> $items  [ [toPhone, text, category?, priorityHigh?], ... ]
     */
    public function __construct(
        public readonly array $items,
        public readonly string $broadcastName = 'wa-broadcast',
    ) {}

    public function handle(): void
    {
        $chunks = array_chunk($this->items, 50);
        $batches = [];
        $totalEnqueued = 0;
        foreach ($chunks as $i => $chunk) {
            $jobs = [];
            foreach ($chunk as $row) {
                $to = (string)($row[0] ?? '');
                $txt = (string)($row[1] ?? '');
                if ($to === '' || $txt === '') continue;
                $cat = (string)($row[2] ?? 'marketing');
                $hi = (bool)($row[3] ?? false);
                $msg = new \App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage(
                    toPhone: $to,
                    type: 'text',
                    text: $txt,
                    category: $cat,
                    priorityHigh: $hi,
                );
                $jobs[] = new SendWaMessageJob($msg);
                $totalEnqueued++;
            }
            if (count($jobs) === 0) continue;
            $delaySec = $i * 10; // chunk rate limiter: 10 detik antar chunk = 6/min maksimal top-up burst
            $batches[] = Bus::batch($jobs)
                ->allowFailures()
                ->name($this->broadcastName . '-chunk-' . $i)
                ->delay(now()->addSeconds($delaySec))
                ->then(function (Batch $batch) {
                    Log::info('[WA-BROADCAST] chunk selesai sukses.', ['batch_id' => $batch->id, 'ok' => $batch->processedJobs()]);
                })
                ->catch(function (Batch $batch, \Throwable $e) {
                    Log::warning('[WA-BROADCAST] chunk error', ['batch_id' => $batch->id, 'err' => $e->getMessage()]);
                })
                ->dispatch();
        }
        Log::info('[WA-BROADCAST] Main batch disubmit', [
            'name' => $this->broadcastName,
            'chunks' => count($batches),
            'total_msgs' => $totalEnqueued,
        ]);
    }
}
