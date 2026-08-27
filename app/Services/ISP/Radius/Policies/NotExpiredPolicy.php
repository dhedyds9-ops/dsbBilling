<?php

namespace App\Services\ISP\Radius\Policies;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * Policy #3: Cek expired_at. Jika customer_services.expired_at > today = lulus.
 * (Biasanya untuk paket prabayar / voucher based yang punya masa aktif)
 */
class NotExpiredPolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        if ($ctx->voucher !== null) {
            $v = $ctx->voucher;
            if (!empty($v->expires_at) && $v->expires_at->isPast()) {
                return PolicyResult::reject('Voucher sudah kadaluarsa, silakan beli voucher baru', self::ruleClass());
            }
            return PolicyResult::pass();
        }
        $cs = $ctx->customerService;
        if ($cs && !empty($cs->expires_at)) {
            $expires = is_string($cs->expires_at) ? \Illuminate\Support\Carbon::parse($cs->expires_at) : $cs->expires_at;
            if ($expires->isPast()) {
                return PolicyResult::reject('Masa aktif paket Anda sudah berakhir. Silakan perpanjang di Customer Portal', self::ruleClass());
            }
        }
        if ($ctx->pppoeUser && !empty($ctx->pppoeUser->expires_at)) {
            $expires = is_string($ctx->pppoeUser->expires_at) ? \Illuminate\Support\Carbon::parse($ctx->pppoeUser->expires_at) : $ctx->pppoeUser->expires_at;
            if ($expires->isPast()) {
                return PolicyResult::reject('Masa aktif akun PPPoE Anda berakhir', self::ruleClass());
            }
        }
        return PolicyResult::pass();
    }

    public static function ruleClass(): string { return self::class; }
}
