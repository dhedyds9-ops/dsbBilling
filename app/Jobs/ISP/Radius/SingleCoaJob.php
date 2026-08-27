<?php

declare(strict_types=1);

namespace App\Jobs\ISP\Radius;

use App\Services\ISP\Radius\CoaDispatchService;
use App\Services\ISP\Radius\PerformanceMetricsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Single COA execution job. Dijalankan di Horizon Redis queue 'radius-coa'.
 *
 * Features:
 *   - UniqueUntilProcessing: 1 audit ID hanya dijalankan 1x sampai mulai processing (hindari double dispatch)
 *   - WithoutOverlapping: lock per coa id 60 detik
 *   - RateLimited: max 50 COA / menit per NAS (hindari throttle MikroTik)
 *   - Exponential backoff: 10s, 20s, 40s, 80s
 *   - Tries: 3x (karena CoaDispatchService punya retry internal juga, jadi job ini cukup 3x outer retry)
 */
final class SingleCoaJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public int $uniqueFor = 120;

    /**
     * @return array<int,object>
     */
    public function middleware(): array
    {
        $coaId = $this->coaAuditId;
        $rateKey = "radius-coa-rate";
        return [
            new WithoutOverlapping("radius-coa:{$coaId}", 60),
            new RateLimited($rateKey, 50, 60),
        ];
    }

    public function __construct(
        public readonly int $coaAuditId,
    ) {}

    public function uniqueId(): string
    {
        return "coa-audit-{$this->coaAuditId}";
    }

    /**
     * @return array<int,int>
     */
    public function backoff(): array
    {
        return [10, 20, 40];
    }

    public function handle(CoaDispatchService $service, PerformanceMetricsService $metrics): void
    {
        $t0 = microtime(true);
        $success = false;

        try {
            $result = $service->executeAudit($this->coaAuditId);
            $success = (bool)($result['success'] ?? false);

            if (!$success) {
                $nextState = $result['transitioned_to'];
                $err = $result['result']['error'] ?? 'unknown';
                Log::warning('SingleCoaJob: COA tidak sukses', [
                    'audit_id' => $this->coaAuditId,
                    'transitioned_to' => $nextState?->value ?? null,
                    'error' => $err,
                    'attempt' => $this->attempts(),
                ]);

                if ($this->attempts() < $this->tries) {
                    $this->release(now()->addSeconds($this->backoff()[$this->attempts()] ?? 60));
                    return;
                }
            }
        } catch (\Throwable $e) {
            Log::error('SingleCoaJob exception', [
                'audit_id' => $this->coaAuditId,
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);
            if ($this->attempts() < $this->tries) {
                $this->release(now()->addSeconds($this->backoff()[$this->attempts()] ?? 60));
                return;
            }
            throw $e;
        } finally {
            $latMs = (microtime(true) - $t0) * 1000.0;
            $metrics->recordLatency(PerformanceMetricsService::OP_COA, $latMs, $success);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::critical('SingleCoaJob FAILED permanently', [
            'audit_id' => $this->coaAuditId,
            'err' => $e->getMessage(),
        ]);
    }
}
