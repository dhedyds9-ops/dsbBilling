<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;

/**
 * SSOT: WhatsApp Anti Spam & Rate Limit Service.
 *
 * 3-Layer defense:
 * 1. Global quota per jam (total WA outgoing / hour) - default 600/jam
 * 2. Cooldown per nomor (misal 60 detik antar pesan kategori sama, 300s broadcast)
 * 3. Per-nomor frequency cap (maks N pesan per 24 jam ke nomor sama = anti spam ke customer)
 *
 * Sumber: Redis (laravel default) — kompatibel dengan semua driver.
 */
final class WaAntiSpamService
{
    private const DEFAULT_GLOBAL_HOURLY_LIMIT = 600;
    private const DEFAULT_PER_DAY_PER_NUMBER = 20;
    private const DEFAULT_CATEGORY_COOLDOWN = [
        'billing'    => 60,   // tagihan (invoice baru) — user sama tidak boleh 2x dalam 60s
        'reminder'   => 3600, // H-3 / H-1 reminder — maks 1x/jam
        'marketing'  => 900,  // broadcast gangguan/maintenance — 15 menit
        'info'       => 30,   // payment sukses / suspended / reactivated — 30s
        'support'    => 5,    // bot reply user — hampir no delay (5s)
        'general'    => 30,   // general — 30s
    ];

    public function __construct(
        private readonly int $globalHourlyLimit = self::DEFAULT_GLOBAL_HOURLY_LIMIT,
        private readonly int $perDayPerNumber = self::DEFAULT_PER_DAY_PER_NUMBER,
    ) {}

    public function acquireGlobalQuota(int $n = 1): bool
    {
        $key = 'wa:global:hour:' . now()->format('YmdH');
        try {
            return RateLimiter::attempt($key, $this->globalHourlyLimit, fn() => true, 3600, $n);
        } catch (\Throwable) {
            // fallback kalau redis down — allow via hit counter array
            static $fallback = 0;
            $fallback++;
            return $fallback < ($this->globalHourlyLimit * 10);
        }
    }

    public function remainingGlobalQuota(): int
    {
        $key = 'wa:global:hour:' . now()->format('YmdH');
        try {
            return max(0, $this->globalHourlyLimit - RateLimiter::remaining($key, $this->globalHourlyLimit));
        } catch (\Throwable) {
            return $this->globalHourlyLimit;
        }
    }

    public function isCoolingDown(string $phoneNorm, string $category = 'general'): bool
    {
        $cd = (int)self::DEFAULT_CATEGORY_COOLDOWN[$category] ?? 30;
        $key = 'wa:cd:' . $category . ':' . $phoneNorm;
        try {
            $v = Redis::connection()->get($key);
            return $v !== null;
        } catch (\Throwable) {
            static $store = [];
            return isset($store[$key]) && ($store[$key] >= time());
        }
    }

    public function markCoolingDown(string $phoneNorm, string $category = 'general', ?int $seconds = null): void
    {
        $cd = (int)($seconds ?? (self::DEFAULT_CATEGORY_COOLDOWN[$category] ?? 30));
        if ($cd <= 0) return;
        $key = 'wa:cd:' . $category . ':' . $phoneNorm;
        try {
            Redis::connection()->setex($key, $cd, (string)now()->timestamp);
        } catch (\Throwable) {
            static $store = [];
            $store[$key] = time() + $cd;
        }
        // Juga increment per-nomor daily cap
        try {
            $dayKey = 'wa:daycap:' . now()->format('Ymd') . ':' . $phoneNorm;
            Redis::connection()->incr($dayKey);
            Redis::connection()->expire($dayKey, 86400);
        } catch (\Throwable) {
        }
    }

    public function dailyReached(string $phoneNorm): bool
    {
        $dayKey = 'wa:daycap:' . now()->format('Ymd') . ':' . $phoneNorm;
        try {
            $c = (int)Redis::connection()->get($dayKey);
            return $c >= $this->perDayPerNumber;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Pre-flight check untuk satu outgoing message.
     * Return [true, ''] jika OK, [false, reason] jika ditolak.
     */
    public function preflight(string $phoneNorm, string $category, bool $priorityHigh = false): array
    {
        if (!preg_match('/^628[0-9]{8,14}$/', $phoneNorm)) return [false, 'invalid_phone'];
        if ($priorityHigh) return [true, '']; // OTP/support/payment bypass semua
        if ($this->dailyReached($phoneNorm)) return [false, 'daily_cap_reached'];
        if ($this->isCoolingDown($phoneNorm, $category)) return [false, 'cooldown'];
        if (!$this->acquireGlobalQuota(1)) return [false, 'global_quota_full'];
        return [true, ''];
    }
}
