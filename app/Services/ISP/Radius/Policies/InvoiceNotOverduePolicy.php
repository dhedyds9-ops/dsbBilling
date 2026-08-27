<?php

namespace App\Services\ISP\Radius\Policies;

use App\Models\ISP\SuspendPolicy;
use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;

/**
 * Policy #4: Tidak ada invoice OVERDUE.
 *
 * Configurable:
 *   grace_days: 3           = masih kasih toleransi 3 hari setelah due_date
 *   strict_days_after_overdue: 14  = jika lebih 14 hari = REJECT. dibawah itu = APPLY_SUSPEND_POLICY
 */
class InvoiceNotOverduePolicy implements RadiusAccessPolicy
{
    public function passes(RadiusAccessContext $ctx, array $config = []): PolicyResult
    {
        if ($ctx->voucher !== null) return PolicyResult::pass();
        $cs = $ctx->customerService;
        if (!$cs) return PolicyResult::pass();
        $customerId = (int)($cs->customer_id ?? 0);
        if ($customerId <= 0) return PolicyResult::pass();

        $graceDays = (int)($config['grace_days'] ?? 3);
        $strictDays = (int)($config['strict_days_after_overdue'] ?? 999);
        $failMsg = (string)($config['fail_message'] ?? 'Silakan bayar tagihan Anda di portal pembayaran');

        $overdueQuery = \App\Models\Billing\Invoice::query()
            ->where('customer_id', $customerId)
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'written_off')
            ->where('due_date', '<', now()->subDays($graceDays)->toDateString())
            ->orderByRaw("DATEDIFF(CURDATE(), due_date) DESC")
            ->limit(10);

        $count = (int)$overdueQuery->count();
        if ($count === 0) return PolicyResult::pass();

        $oldestDue = $overdueQuery->clone()->min('due_date');
        $daysOverdue = $oldestDue ? now()->diffInDays($oldestDue, false) : 0;

        // 1. Strict mode: > strict_days overdue = REJECT (kick user, paksa bayar)
        if ($daysOverdue >= $strictDays) {
            return PolicyResult::reject(
                reason: "Tagihan sudah lewat {$daysOverdue} hari melewati batas maksimal. {$failMsg}",
                ruleClass: self::ruleClass(),
            );
        }

        // 2. Grace / warning = lanjut login tapi TERAPKAN SUSPEND POLICY (rate limit redirect)
        $suspendPolicy = $ctx->customerService ? SuspendPolicy::resolveForService($ctx->customerService) : null;
        if ($suspendPolicy) {
            return PolicyResult::applySuspendPolicy(
                reason: "Warning - Tagihan overdue {$daysOverdue} hari. Kecepatan dibatasi sampai pembayaran diterima.",
                ruleClass: self::ruleClass(),
                suspendPolicy: $suspendPolicy,
            );
        }
        return PolicyResult::warn("Tagihan overdue {$daysOverdue} hari. Segera lakukan pembayaran", self::ruleClass());
    }

    public static function ruleClass(): string { return self::class; }
}
