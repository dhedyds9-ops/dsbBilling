<?php

namespace App\Services\ISP\Radius\Policies;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;
use Illuminate\Support\Facades\DB;

/**
 * Policy #8: IP / MAC Address tidak masuk blacklist pelanggaran TOS.
 *
 * (contoh: user berbagi koneksi RT RW Net, botnet, dll - Admin blacklist manual)
 */
class IpBlacklistPolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        $caller = strtoupper(preg_replace('/[^A-F0-9]/', '', (string)$ctx->callingStationId ?? ''));
        $framedIp = (string)$ctx->framedIp ?? '';

        if ($caller !== '') {
            $existsMac = \Illuminate\Support\Facades\Cache::remember(
                key: "radius:blacklist:mac:{$caller}",
                ttl: 300,
                callback: function () use ($caller) {
                    try {
                        return DB::table('radius_blacklist_macs')
                            ->where('mac_address', $caller)
                            ->where('expires_at', '>', now())
                            ->orWhereNull('expires_at')
                            ->exists();
                    } catch (\Throwable) {
                        return false;
                    }
                }
            );
            if ($existsMac) {
                return PolicyResult::reject('MAC Anda tercatat di blacklist karena pelanggaran Terms of Service. Hubungi CS.', self::ruleClass());
            }
        }
        if ($framedIp !== '' && filter_var($framedIp, FILTER_VALIDATE_IP)) {
            $existsIp = \Illuminate\Support\Facades\Cache::remember(
                key: "radius:blacklist:ip:{$framedIp}",
                ttl: 300,
                callback: function () use ($framedIp) {
                    try {
                        return DB::table('radius_blacklist_ips')
                            ->where('ip_address', $framedIp)
                            ->where('expires_at', '>', now())
                            ->orWhereNull('expires_at')
                            ->exists();
                    } catch (\Throwable) { return false; }
                }
            );
            if ($existsIp) {
                return PolicyResult::reject('IP Anda masuk blacklist security', self::ruleClass());
            }
        }
        return PolicyResult::pass();
    }

    public static function ruleClass(): string { return self::class; }
}
