<?php

namespace App\Services\ISP\Radius\Policies;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * Policy #1: Customer harus status active (bukan suspended / terminated).
 */
class CustomerActivePolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        $cs = $ctx->customerService;
        if ($ctx->voucher !== null) return PolicyResult::pass(); // Voucher punya lifecycle sendiri
        if (!$cs) return PolicyResult::reject('Customer service tidak ditemukan', self::ruleClass());
        $status = is_string($cs->status) ? $cs->status : (string)($cs->status?->value ?? $cs->status);
        if ($status !== 'active') {
            return match ($status) {
                'suspended' => PolicyResult::reject('Layanan Anda saat ini sedang suspend. Silakan hubungi CS.', self::ruleClass()),
                'terminated' => PolicyResult::reject('Layanan Anda sudah di-nonaktifkan permanen.', self::ruleClass()),
                'pending' => PolicyResult::reject('Aktivasi layanan sedang dalam proses. Mohon tunggu 5-10 menit.', self::ruleClass()),
                default => PolicyResult::reject("Status akun tidak valid ($status). Hubungi CS ISP.", self::ruleClass()),
            };
        }
        $customer = $cs->customer;
        if ($customer && (string)($customer->status ?? 'active') !== 'active') {
            return PolicyResult::reject('Akun pelanggan Anda saat ini dinonaktifkan.', self::ruleClass());
        }
        return PolicyResult::pass();
    }

    public static function ruleClass(): string { return self::class; }
}
