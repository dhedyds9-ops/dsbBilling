<?php

declare(strict_types=1);

namespace App\Jobs\ISP\Radius;

use App\Enums\ISP\CoaType;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\SuspendPolicy;
use App\Services\ISP\Radius\CoaDispatchService;
use App\Services\ISP\Radius\PerformanceMetricsService;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Dispatch Batch COA: chunking PPPoE Users → create audit → dispatch SingleCoaJob dalam Batch.
 *
 * Digunakan oleh:
 *   - CheckOverdueInvoicesJob (suspend massal untuk overdue)
 *   - ReactivateCustomerJob (reactivate massal setelah payment)
 *   - Rebalance profile massal
 *   - Admin force-disconnect massal
 *
 * Rate limit batch: 1000 coa/batch, chunk 100, jeda 2s antar chunk.
 */
final class DispatchBatchCoaJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 900;

    /**
     * @return array<int,object>
     */
    public function middleware(): array
    {
        return [
            new RateLimited("radius-coa-batch", 5, 300), // max 5 batch / 5 menit
        ];
    }

    /**
     * @param  Collection<int,PPPoEUser>|array<int,PPPoEUser>  $users
     */
    public function __construct(
        public readonly CoaType $coaType,
        public Collection|array $users,
        public readonly int $operatorId = 1,
        public readonly ?SuspendPolicy $overrideSuspendPolicy = null,
        public readonly ?int $maxAttemptsPerCoa = null,
        public readonly int $chunkSize = 100,
        public readonly string $batchName = '',
        public readonly array $extraAttrs = [],
    ) {
        if (is_array($this->users)) {
            $this->users = collect($this->users);
        }
    }

    /**
     * @throws Throwable
     */
    public function handle(CoaDispatchService $service, PerformanceMetricsService $metrics): void
    {
        $t0 = microtime(true);
        $allJobs = [];
        $totalQueued = 0;
        $totalFailed = 0;

        /** @var Collection<int,PPPoEUser> $users */
        $users = $this->users;
        $totalUsers = $users->count();

        Log::info('DispatchBatchCoaJob starting', [
            'type' => $this->coaType->value,
            'total_users' => $totalUsers,
            'chunk_size' => $this->chunkSize,
            'batch_name' => $this->batchName,
        ]);

        if ($totalUsers === 0) {
            Log::warning('DispatchBatchCoaJob: 0 users, nothing to do');
            return;
        }

        $batches = $users->chunk($this->chunkSize);
        $batchCount = $batches->count();

        foreach ($batches as $chunkIdx => $chunk) {
            $auditIds = [];
            foreach ($chunk as $user) {
                try {
                    $r = $service->enqueue(
                        type: $this->coaType,
                        pppoeUser: $user,
                        operatorId: $this->operatorId,
                        overrideSuspendPolicy: $this->overrideSuspendPolicy,
                        maxAttempts: $this->maxAttemptsPerCoa,
                        extraAttrs: $this->extraAttrs,
                    );
                    if ($r['ok'] && !empty($r['audit_id'])) {
                        $auditIds[] = (int)$r['audit_id'];
                        $totalQueued++;
                    } else {
                        $totalFailed++;
                    }
                } catch (\Throwable $e) {
                    $totalFailed++;
                    Log::warning('DispatchBatchCoaJob enqueue user failed', [
                        'pppoe_user_id' => $user->id ?? null,
                        'err' => $e->getMessage(),
                    ]);
                }
            }

            foreach ($auditIds as $aid) {
                $allJobs[] = new SingleCoaJob($aid);
            }

            // Chunk throttling: kasitirahan sedikit antar chunk supaya DB/MikroTik tidak kebanjiran
            if ($chunkIdx < $batchCount - 1 && count($auditIds) > 0) {
                usleep(200_000); // 200ms
            }
        }

        $jobsCount = count($allJobs);

        if ($jobsCount > 0) {
            $batchName = $this->batchName ?: "COA-{$this->coaType->value}-{$totalUsers}u";
            Bus::batch($allJobs)
                ->name($batchName)
                ->allowFailures()
                ->onConnection(config('queue.default', 'redis'))
                ->onQueue('radius-coa')
                ->then(function (Batch $batch) use ($metrics, $totalQueued, $t0) {
                    $latMs = (microtime(true) - $t0) * 1000.0;
                    $metrics->recordLatency(PerformanceMetricsService::OP_COA, $latMs, true);
                    Log::info('DispatchBatchCoaJob batch queued OK', [
                        'batch_id' => $batch->id,
                        'name' => $batch->name,
                        'total_jobs' => $batch->totalJobs,
                        'total_queued' => $totalQueued,
                    ]);
                })
                ->catch(function (Batch $batch, Throwable $e) use ($metrics, $t0) {
                    $latMs = (microtime(true) - $t0) * 1000.0;
                    $metrics->recordLatency(PerformanceMetricsService::OP_COA, $latMs, false);
                    Log::error('DispatchBatchCoaJob batch catch', ['err' => $e->getMessage()]);
                })
                ->finally(function (Batch $batch) {
                    Log::info('DispatchBatchCoaJob batch finished', [
                        'batch_id' => $batch->id,
                        'processed' => $batch->processedJobs(),
                        'failed' => $batch->failedJobs,
                        'progress' => $batch->progress(),
                    ]);
                })
                ->dispatch();
        } else {
            Log::warning('DispatchBatchCoaJob: 0 jobs queued (all enqueue failed?)', [
                'total_users' => $totalUsers,
                'total_failed' => $totalFailed,
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::critical('DispatchBatchCoaJob FAILED', [
            'coa_type' => $this->coaType->value,
            'total_users' => $this->users->count(),
            'err' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
