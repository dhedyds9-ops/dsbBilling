<?php

namespace App\Services\ISP\Radius\Policies\Contracts;

use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * SSOT: Single Responsibility Policy Rule Interface (Open/Closed Principle).
 *
 * Setiap aturan akses = 1 Class Concrete.
 * Tambah aturan baru = buat class baru, TIDAK perlu edit existing code / FreeRADIUS config.
 */
interface RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult;

    /**
     * Unique rule key untuk mapping dari table radius_auth_policy_rules.rule_class
     */
    public static function ruleClass(): string;
}
