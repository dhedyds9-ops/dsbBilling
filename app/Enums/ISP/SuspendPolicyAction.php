<?php

declare(strict_types=1);

namespace App\Enums\ISP;

/**
 * SSOT: Apa yang terjadi saat customer masuk status suspend
 *
 * Dinamis per-policy, bukan hardcoded 128k
 */
enum SuspendPolicyAction: string
{
    case RateLimit = 'rate_limit';
    case RedirectOnly = 'redirect_only';
    case Disconnect = 'disconnect';
    case DisableSecret = 'disable_secret';

    public function label(): string
    {
        return match ($this) {
            self::RateLimit => 'Rate Limit (bandwidth throttle tanpa disconnect)',
            self::RedirectOnly => 'Redirect Hanya (buka portal payment, bandwidth penuh)',
            self::Disconnect => 'Disconnect (PoD Kick user)',
            self::DisableSecret => 'Disable PPP Secret (reject re-auth berikutnya)',
        };
    }
}
