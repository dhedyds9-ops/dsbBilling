<?php

namespace App\Services\ISP\Radius;

use App\Services\ISP\Radius\Policies\Contracts\RadiusAccessPolicy;
use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * SSOT: Radius Access Policy Engine (Chain of Responsibility Pattern + Pipeline)
 *
 * Urutan evaluasi:
 * 1. Baca semua policy enabled dari radius_auth_policy_rules (configurable lewat DB).
 * 2. Urutkan berdasarkan priority (ASCENDING = paling kecil duluan).
 * 3. Execute satu per satu. First policy yang return pass=false = STOP, return result FAIL.
 * 4. Jika pass=true tapi ada replyAttributes / overrideSuspendPolicy = collect & merge.
 * 5. Semua policy pass = return ACCEPT beserta collected attributes.
 *
 * TAMBAH RULE BARU = 1 file Policy class baru + INSERT 1 row di tabel radius_auth_policy_rules.
 * TIDAK PERLU mengubah FreeRADIUS configuration sama sekali!
 */
final class RadiusPolicyEngine
{
    public const CACHE_TTL_SECONDS = 120; // 2 menit cache enabled policy list

    /** @var array<class-string<RadiusAccessPolicy>, RadiusAccessPolicy> */
    private array $instantiated = [];

    /**
     * @return array{pass: bool, policies_executed: array<string, PolicyResult>, mergedReply: array, overrideSuspendPolicy: mixed, firstRejection: ?PolicyResult, warnings: array<string>}
     */
    public function evaluate(RadiusAccessContext $ctx): array
    {
        $rules = $this->enabledPolicyRules();
        $executed = [];
        $warnings = [];
        $mergedReply = [];
        $overrideSuspendPolicy = null;
        $firstRejection = null;
        $pass = true;

        foreach ($rules as $rule) {
            $class = (string)($rule['rule_class'] ?? '');
            if (!is_a($class, RadiusAccessPolicy::class, true)) {
                $warnings[] = "Rule class {$class} tidak mengimplement RadiusAccessPolicy interface, skip";
                continue;
            }
            $instance = $this->instantiated[$class] ??= new $class();
            $config = $rule['config'] ?? [];
            try {
                $result = $instance->passes($ctx, is_array($config) ? $config : []);
            } catch (Throwable $e) {
                report($e);
                $executed[$class] = new PolicyResult(
                    pass: true,
                    reason: 'policy_exception: ' . $e->getMessage(),
                    policyRuleClass: $class,
                    failAction: 'warn',
                );
                continue;
            }
            $executed[$class] = $result;

            if ($result->failAction === 'warn' && $result->reason !== '') {
                $warnings[] = $result->reason;
            }
            if ($result->failAction === 'apply_suspend_policy' && $result->overrideSuspendPolicy !== null) {
                $overrideSuspendPolicy = $result->overrideSuspendPolicy;
            }
            if (count($result->replyAttributes) > 0) {
                $mergedReply = array_merge($mergedReply, $result->replyAttributes);
            }
            if (!$result->pass) {
                $pass = false;
                $firstRejection = $result;
                break;
            }
        }

        return [
            'pass' => $pass,
            'policies_executed' => $executed,
            'mergedReply' => $mergedReply,
            'overrideSuspendPolicy' => $overrideSuspendPolicy,
            'firstRejection' => $firstRejection,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return array<array{rule_class: class-string, priority: int, config: array, fail_message: ?string, fail_action: string}>
     */
    private function enabledPolicyRules(): array
    {
        return Cache::remember(
            key: 'radius:policy_rules:enabled',
            ttl: self::CACHE_TTL_SECONDS,
            callback: function () {
                try {
                    $rows = DB::table('radius_auth_policy_rules')
                        ->where('enabled', true)
                        ->orderBy('priority', 'asc')
                        ->orderBy('id', 'asc')
                        ->get(['rule_class', 'priority', 'config', 'fail_message', 'fail_action']);
                    $out = [];
                    foreach ($rows as $r) {
                        $out[] = [
                            'rule_class' => (string)$r->rule_class,
                            'priority' => (int)$r->priority,
                            'config' => json_decode((string)($r->config ?? '[]'), true) ?: [],
                            'fail_message' => $r->fail_message,
                            'fail_action' => (string)($r->fail_action ?? 'reject'),
                        ];
                    }
                    return $out;
                } catch (Throwable) {
                    // Fallback jika tabel belum ada (migration belum jalan): list policy default hardcoded urut priority
                    return [
                        ['rule_class' => \App\Services\ISP\Radius\Policies\CustomerActivePolicy::class, 'priority' => 1, 'config' => [], 'fail_action' => 'reject'],
                        ['rule_class' => \App\Services\ISP\Radius\Policies\PackageActivePolicy::class, 'priority' => 2, 'config' => [], 'fail_action' => 'reject'],
                        ['rule_class' => \App\Services\ISP\Radius\Policies\NotExpiredPolicy::class, 'priority' => 3, 'config' => [], 'fail_action' => 'reject'],
                        ['rule_class' => \App\Services\ISP\Radius\Policies\InvoiceNotOverduePolicy::class, 'priority' => 10, 'config' => ['grace_days' => 3, 'strict_days_after_overdue' => 14], 'fail_action' => 'reject'],
                        ['rule_class' => \App\Services\ISP\Radius\Policies\NasAllowedPolicy::class, 'priority' => 15, 'config' => [], 'fail_action' => 'reject'],
                        ['rule_class' => \App\Services\ISP\Radius\Policies\QuotaAvailablePolicy::class, 'priority' => 20, 'config' => [], 'fail_action' => 'reject'],
                        ['rule_class' => \App\Services\ISP\Radius\Policies\AccessHoursPolicy::class, 'priority' => 25, 'config' => ['timezone' => 'Asia/Jakarta'], 'fail_action' => 'reject'],
                        ['rule_class' => \App\Services\ISP\Radius\Policies\IpBlacklistPolicy::class, 'priority' => 30, 'config' => [], 'fail_action' => 'reject'],
                    ];
                }
            }
        );
    }
}
