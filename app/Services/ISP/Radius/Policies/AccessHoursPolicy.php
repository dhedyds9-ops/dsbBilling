<?php

namespace App\Services\ISP\Radius\Policies;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * Policy #7: Jam akses sesuai aturan paket.
 *
 * Config:
 *   timezone default Asia/Jakarta
 *   allowed_hours: [
 *     ["start" => "06:00", "end" => "23:59"],
 *     ["start" => "00:00", "end" => "02:00"]
 *   ]
 *   whitelist_days: [1,2,3,4,5] = Senin-Jumat (Carbon: 0=Minggu s/d 6=Sabtu)
 */
class AccessHoursPolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        if ($ctx->voucher !== null) {
            if (!empty($ctx->voucher->access_hours_restricted)) return PolicyResult::pass();
        }
        $sp = $ctx->serviceProfile;
        if ($sp && empty($sp->access_restricted) && empty($config['allowed_hours'])) return PolicyResult::pass();

        $tz = (string)($config['timezone'] ?? 'Asia/Jakarta');
        $now = now($tz);
        $nowTime = (int)$now->format('Hi'); // 24 jam integer: 1835 = 18:35

        $whitelistDays = array_map('intval', (array)($config['whitelist_days'] ?? []));
        if (count($whitelistDays) > 0) {
            if (!in_array((int)$now->dayOfWeekIso, $whitelistDays, true)) {
                return PolicyResult::reject('Akses internet hanya diijinkan hari kerja', self::ruleClass());
            }
        }
        $allowedHours = (array)($config['allowed_hours'] ?? []);
        if (count($allowedHours) === 0) return PolicyResult::pass();
        foreach ($allowedHours as $range) {
            $s = (int)str_replace(':', '', (string)($range['start'] ?? '0000'));
            $e = (int)str_replace(':', '', (string)($range['end'] ?? '2359'));
            if ($e < $s) {
                // Cross midnight: 22:00 - 06:00
                if ($nowTime >= $s || $nowTime <= $e) return PolicyResult::pass();
            } else {
                if ($nowTime >= $s && $nowTime <= $e) return PolicyResult::pass();
            }
        }
        $msg = (string)($config['fail_message'] ?? 'Akses internet di luar jam operasional. Silakan coba kembali pada jam yang diijinkan.');
        return PolicyResult::reject($msg, self::ruleClass());
    }

    public static function ruleClass(): string { return self::class; }
}
