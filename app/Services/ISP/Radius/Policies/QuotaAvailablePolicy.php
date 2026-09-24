<?php

namespace App\Services\ISP\Radius\Policies;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * Policy #6: Kuota (Fair Usage Policy / FUP) masih tersedia.
 *
 * Config:
 *   quota_gb: jika di service_profile ada kolom monthly_quota_gb. Atau ambil dari sini.
 *   after_exhaustion_action: throttle_rate | reject | redirect
 */
class QuotaAvailablePolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        if ($ctx->voucher !== null) {
            $v = $ctx->voucher;
            $quotaBytes = (int)($v->quota_bytes ?? $config['voucher_default_quota_bytes'] ?? 0);
            if ($quotaBytes <= 0) return PolicyResult::pass();
            $used = (int)($v->used_bytes ?? 0);
            if ($used >= $quotaBytes) {
                return PolicyResult::reject('Kuota voucher sudah habis, silakan isi ulang kuota', self::ruleClass());
            }
            return PolicyResult::pass();
        }
        $sp = $ctx->serviceProfile;
        $cs = $ctx->customerService;
        if (!$sp || !$cs) return PolicyResult::pass();

        $quotaGb = (float)($sp->monthly_quota_gb ?? $config['quota_gb'] ?? 0);
        if ($quotaGb <= 0) return PolicyResult::pass(); // Unlimited
        $quotaBytes = (int)ceil($quotaGb * 1073741824); // GB -> B

        // Cek pemakaian bulan ini dari radius_accounting
        $startOfMonth = now()->startOfMonth();
        $usedBytes = (int)\App\Models\ISP\RadiusAccounting::query()
            ->where('customer_service_id', (int)$cs->id)
            ->where('acct_start_time', '>=', $startOfMonth)
            ->selectRaw('
                (CAST(COALESCE(SUM(acct_input_octets),0) AS SIGNED) +
                 CAST(COALESCE(SUM(acct_output_octets),0) AS SIGNED) +
                 CAST(COALESCE(SUM(acct_input_gigawords),0) AS SIGNED) * 4294967296 +
                 CAST(COALESCE(SUM(acct_output_gigawords),0) AS SIGNED) * 4294967296) AS total_bytes
            ')->value('total_bytes');

        $afterAction = (string)($config['after_exhaustion_action'] ?? 'throttle_rate');
        if ($usedBytes >= $quotaBytes) {
            return match ($afterAction) {
                'reject' => PolicyResult::reject('Kuota FUP bulanan Anda sudah habis, silakan upgrade paket', self::ruleClass()),
                default => PolicyResult::warn(
                    reason: 'Kuota habis! Kecepatan diturunkan menjadi 128k/128k sampai akhir bulan',
                    ruleClass: self::ruleClass(),
                    reply: ['MikroTik-Rate-Limit' => '128k/128k']
                ),
            };
        }
        return PolicyResult::pass();
    }

    public static function ruleClass(): string { return self::class; }
}
