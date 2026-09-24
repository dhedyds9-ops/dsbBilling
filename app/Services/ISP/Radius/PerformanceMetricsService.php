<?php

declare(strict_types=1);

namespace App\Services\ISP\Radius;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * SSOT: Performance Metrics Counter untuk Radius Subsystem.
 *
 * Menghitung:
 *   - Latency percentile (p50, p95, p99) per operation
 *   - Success rate: auth / authorize / coa / accounting
 *   - Throughput (requests/second) rolling window 5m
 *
 * Backend: Redis Hash + Sorted Set untuk time-series murah.
 */
final class PerformanceMetricsService
{
    public const KEY_PREFIX = 'radius:metrics';

    public const OP_AUTH = 'auth';
    public const OP_AUTHORIZE = 'authorize';
    public const OP_PREAUTH = 'preauth';
    public const OP_COA = 'coa';
    public const OP_DISCONNECT = 'disconnect';
    public const OP_ACCOUNTING = 'accounting';

    public const WINDOW_SECONDS = 300;

    public function recordLatency(string $operation, float $latencyMs, bool $success): void
    {
        $now = time();
        $bucket = (int)floor($now / 60);

        try {
            Redis::pipeline(function ($pipe) use ($operation, $latencyMs, $success, $bucket) {
                $base = self::KEY_PREFIX . ":{$operation}:{$bucket}";

                $pipe->hIncrBy($base, 'total', 1);
                $pipe->hIncrByFloat($base, 'latency_sum', $latencyMs);
                $pipe->hIncrBy($base, $success ? 'success' : 'fail', 1);
                $pipe->zAdd("{$base}:latencies", [$latencyMs => uniqid('', true)]);

                $pipe->expire($base, self::WINDOW_SECONDS + 60);
                $pipe->expire("{$base}:latencies", self::WINDOW_SECONDS + 60);

                $pipe->hIncrBy(self::KEY_PREFIX . ":all:total", $operation, 1);
                $pipe->hIncrBy(self::KEY_PREFIX . ":all:success", $operation, (int)$success);
            });
        } catch (\Throwable $e) {
            Cache::put(self::KEY_PREFIX . ":fallback:{$operation}:{$now}", [
                'l' => $latencyMs,
                's' => $success,
            ], 300);
        }
    }

    /**
     * @return array{total: int, success: int, fail: int, success_rate_pct: float, avg_latency_ms: float, p50_ms: float, p95_ms: float, p99_ms: float}
     */
    public function snapshot(string $operation): array
    {
        $now = time();
        $startBucket = (int)floor(($now - self::WINDOW_SECONDS) / 60);
        $endBucket = (int)floor($now / 60);

        $total = 0;
        $success = 0;
        $fail = 0;
        $latencySum = 0.0;
        $allLatencies = [];

        try {
            for ($b = $startBucket; $b <= $endBucket; $b++) {
                $base = self::KEY_PREFIX . ":{$operation}:{$b}";
                $h = Redis::hGetAll($base);
                if (empty($h)) continue;

                $total += (int)($h['total'] ?? 0);
                $success += (int)($h['success'] ?? 0);
                $fail += (int)($h['fail'] ?? 0);
                $latencySum += (float)($h['latency_sum'] ?? 0.0);

                $scores = Redis::zRange("{$base}:latencies", 0, -1, true);
                if (is_array($scores)) {
                    foreach ($scores as $score) {
                        $allLatencies[] = (float)$score;
                    }
                }
            }
        } catch (\Throwable) {
        }

        sort($allLatencies, SORT_NUMERIC);
        $count = count($allLatencies);

        return [
            'total' => $total,
            'success' => $success,
            'fail' => $fail,
            'success_rate_pct' => $total > 0 ? round(($success / $total) * 100, 2) : 100.0,
            'avg_latency_ms' => $total > 0 ? round($latencySum / $total, 2) : 0.0,
            'p50_ms' => $count > 0 ? round($this->percentile($allLatencies, 0.50), 2) : 0.0,
            'p95_ms' => $count > 0 ? round($this->percentile($allLatencies, 0.95), 2) : 0.0,
            'p99_ms' => $count > 0 ? round($this->percentile($allLatencies, 0.99), 2) : 0.0,
        ];
    }

    /**
     * @return array<string, array{total: int, success: int, fail: int, success_rate_pct: float, avg_latency_ms: float}>
     */
    public function all(): array
    {
        $ops = [
            self::OP_AUTH, self::OP_AUTHORIZE, self::OP_PREAUTH,
            self::OP_COA, self::OP_DISCONNECT, self::OP_ACCOUNTING,
        ];
        $out = [];
        foreach ($ops as $op) {
            $out[$op] = $this->snapshot($op);
        }
        return $out;
    }

    /**
     * @param array<float> $sorted
     */
    private function percentile(array $sorted, float $p): float
    {
        $n = count($sorted);
        if ($n === 0) return 0.0;
        $idx = (int)ceil($p * $n) - 1;
        if ($idx < 0) $idx = 0;
        if ($idx >= $n) $idx = $n - 1;
        return (float)$sorted[$idx];
    }

    public static function measure(string $operation, callable $fn): mixed
    {
        $svc = app(self::class);
        $t0 = microtime(true);
        $success = false;
        try {
            $result = $fn();
            $success = true;
            return $result;
        } finally {
            $latMs = (microtime(true) - $t0) * 1000.0;
            $svc->recordLatency($operation, $latMs, $success);
        }
    }
}
