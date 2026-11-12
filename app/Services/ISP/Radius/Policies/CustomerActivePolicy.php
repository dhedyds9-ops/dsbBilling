<?php

namespace App\Services\ISP\Radius\Policies;

use App\Models\ISP\SuspendPolicy;
use App\Models\Setting;
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

        if ($ctx->hotspotUser !== null) {
            $huStatus = (string)($ctx->hotspotUser->status ?? 'active');
            if ($huStatus !== 'active') {
                if (in_array($huStatus, ['suspended', 'suspend'])) {
                    $suspendMode = 'soft';
                    try {
                        $suspendMode = Setting::getValue('billing.suspend_mode', 'soft');
                    } catch (\Throwable) {}

                    if ($suspendMode === 'soft') {
                        return new PolicyResult(
                            pass: true,
                            reason: 'Akun Hotspot sedang disuspend (mode soft isolir)',
                            policyRuleClass: self::ruleClass(),
                            failAction: 'apply_suspend_policy',
                            replyAttributes: [
                                'MikroTik-Group' => 'ISOLIR',
                                'Framed-Pool' => 'pool-isolir',
                                'MikroTik-Address-List' => 'ISOLIR_LIST',
                            ]
                        );
                    }
                }
                return PolicyResult::reject("Akun Hotspot Anda sedang tidak aktif ({$huStatus}). Silakan hubungi admin.", self::ruleClass());
            }
            if (!$cs) {
                // Hotspot user mandiri / voucher mode tanpa langganan customer service
                return PolicyResult::pass();
            }
        }

        if ($ctx->pppoeUser !== null) {
            $puStatus = (string)($ctx->pppoeUser->status ?? 'active');
            if ($puStatus !== 'active') {
                if (in_array($puStatus, ['suspended', 'suspend'])) {
                    $suspendMode = 'soft';
                    try {
                        $suspendMode = Setting::getValue('billing.suspend_mode', 'soft');
                    } catch (\Throwable) {}

                    if ($suspendMode === 'soft') {
                        return new PolicyResult(
                            pass: true,
                            reason: 'Layanan PPPoE sedang disuspend (mode soft isolir)',
                            policyRuleClass: self::ruleClass(),
                            failAction: 'apply_suspend_policy',
                            replyAttributes: [
                                'MikroTik-Group' => 'ISOLIR',
                                'Framed-Pool' => 'pool-isolir',
                                'MikroTik-Address-List' => 'ISOLIR_LIST',
                            ]
                        );
                    }
                }
                return PolicyResult::reject("Akun PPPoE Anda sedang tidak aktif ({$puStatus}). Silakan hubungi admin.", self::ruleClass());
            }
        }

        if (!$cs) return PolicyResult::reject('Customer service tidak ditemukan', self::ruleClass());
        $status = is_string($cs->status) ? $cs->status : (string)($cs->status?->value ?? $cs->status);
        if ($status !== 'active') {
            if (in_array($status, ['suspended', 'suspend'])) {
                $suspendMode = 'soft';
                try {
                    $suspendMode = Setting::getValue('billing.suspend_mode', 'soft');
                } catch (\Throwable) {}

                if ($suspendMode === 'soft') {
                    return new PolicyResult(
                        pass: true,
                        reason: 'Layanan disuspend (mode soft isolir)',
                        policyRuleClass: self::ruleClass(),
                        failAction: 'apply_suspend_policy',
                        replyAttributes: [
                            'MikroTik-Group' => 'ISOLIR',
                            'Framed-Pool' => 'pool-isolir',
                            'MikroTik-Address-List' => 'ISOLIR_LIST',
                        ]
                    );
                }
                return PolicyResult::reject('Layanan Anda saat ini sedang suspend. Silakan hubungi CS.', self::ruleClass());
            }

            return match ($status) {
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
