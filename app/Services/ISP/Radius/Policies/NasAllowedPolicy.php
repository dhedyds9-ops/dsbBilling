<?php

namespace App\Services\ISP\Radius\Policies;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * Policy #5: User allowed to login FROM NAS ini? (geo-locked per POP)
 *
 * Config:
 *   whitelist_pop_ids: [1, 3, 5] = hanya bisa login di POP tertentu
 *   blacklist_nas_ips: ['10.100.5.22'] = block NAS ini
 */
class NasAllowedPolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        if ($ctx->voucher !== null) return PolicyResult::pass();
        $nas = $ctx->nas;
        $nasIp = $ctx->nasIp;
        $blacklist = array_map('strval', (array)($config['blacklist_nas_ips'] ?? []));
        if (in_array((string)$nasIp, $blacklist, true)) {
            return PolicyResult::reject('Login dari lokasi POP ini diblokir untuk sementara', self::ruleClass());
        }
        $whitelistPops = array_map('intval', (array)($config['whitelist_pop_ids'] ?? []));
        if (count($whitelistPops) > 0) {
            $cs = $ctx->customerService;
            if (!$cs) return PolicyResult::pass();
            $popId = (int)($cs->pop_id ?? 0);
            if ($popId > 0 && !in_array($popId, $whitelistPops, true)) {
                if ($nas && (int)($nas->pop_id ?? 0) !== $popId) {
                    return PolicyResult::reject('Anda tidak diijinkan login diluar POP yang ditentukan paket', self::ruleClass());
                }
            }
        }
        return PolicyResult::pass();
    }

    public static function ruleClass(): string { return self::class; }
}
