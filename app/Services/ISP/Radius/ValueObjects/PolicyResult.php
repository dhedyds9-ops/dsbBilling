<?php

declare(strict_types=1);

namespace App\Services\ISP\Radius\ValueObjects;

use App\Enums\ISP\SuspendPolicyAction;
use App\Models\ISP\SuspendPolicy;

/**
 * SSOT: Hasil evaluasi satu policy.
 *
 * pass=true => lanjut ke policy berikutnya.
 * pass=false => stop chain, REQUEST access atau redirect sesuai fail_action.
 */
final readonly class PolicyResult
{
    public function __construct(
        public bool   $pass,
        public string $reason = '',
        public string $policyRuleClass = '',
        public string $failAction = 'reject',   // reject | warn | redirect_only | apply_suspend_policy
        public array  $replyAttributes = [],     // override attributes in response
        public ?SuspendPolicy $overrideSuspendPolicy = null,
        public ?SuspendPolicyAction $forcedAction = null,
        public int    $httpStatusCode = 200,
    ) {}

    public static function pass(): self
    {
        return new self(pass: true);
    }

    public static function reject(string $reason, string $ruleClass, array $reply = []): self
    {
        return new self(
            pass: false,
            reason: $reason,
            policyRuleClass: $ruleClass,
            failAction: 'reject',
            replyAttributes: $reply,
            httpStatusCode: 401
        );
    }

    public static function warn(string $reason, string $ruleClass, array $reply = []): self
    {
        return new self(
            pass: true,
            reason: $reason,
            policyRuleClass: $ruleClass,
            failAction: 'warn',
            replyAttributes: $reply,
        );
    }

    public static function applySuspendPolicy(string $reason, string $ruleClass, SuspendPolicy $suspendPolicy): self
    {
        return new self(
            pass: true,
            reason: $reason,
            policyRuleClass: $ruleClass,
            failAction: 'apply_suspend_policy',
            replyAttributes: $suspendPolicy->replyAttributes(),
            overrideSuspendPolicy: $suspendPolicy,
            forcedAction: $suspendPolicy->action_type,
        );
    }
}
