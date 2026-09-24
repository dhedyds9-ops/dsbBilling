<?php

namespace App\Services\ISP\Radius\Policies;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * Policy #2: Paket Internet aktif
 */
class PackageActivePolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        if ($ctx->voucher !== null) return PolicyResult::pass();
        if ($ctx->serviceProfile === null) return PolicyResult::reject('Paket layanan tidak terkonfigurasi', self::ruleClass());
        $active = (string)($ctx->serviceProfile->status ?? 'inactive');
        if ($active !== 'active') {
            return PolicyResult::reject('Paket internet Anda saat ini sudah dinonaktifkan', self::ruleClass());
        }
        return PolicyResult::pass();
    }

    public static function ruleClass(): string { return self::class; }
}
