<?php

namespace App\Models\ISP;

use App\Enums\ISP\CoaAuditState;
use App\Enums\ISP\CoaType;
use App\Enums\ISP\RadiusSessionState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * SSOT: COA Audit Log + State Machine
 *
 *  Created → Queued → Sent → Success
 *                  ↘ Timeout → Retry (≤max_attempts) → Failed
 *                  ↘ NAK
 */
class RadiusCoaAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'coa_type', 'state',
        'attempt_count', 'max_attempts',
        'identifiers', 'attributes_to_change', 'coa_result',
        'last_error_message',
        'radius_nas_id', 'router_id',
        'pppoe_user_id', 'customer_service_id', 'hotspot_user_id', 'operator_id',
        'queued_at', 'sent_at', 'success_at', 'failed_at',
    ];

    protected $casts = [
        'coa_type' => CoaType::class,
        'state' => CoaAuditState::class,
        'identifiers' => 'array',
        'attributes_to_change' => 'array',
        'coa_result' => 'array',
    ];

    public static function newUuid(): string
    {
        return (string)\Illuminate\Support\Str::uuid();
    }

    public function transition(CoaAuditState $next, ?array $extra = []): bool
    {
        $current = $this->state;
        if (!$current->canTransitionTo($next)) {
            throw new \InvalidArgumentException("Invalid COA transition: {$current->value} → {$next->value} not allowed for coa#{$this->id}");
        }
        try {
            return DB::transaction(function () use ($next, $extra) {
                $payload = ['state' => $next->value];
                $now = now();
                switch ($next) {
                    case CoaAuditState::Queued:
                        $payload['queued_at'] = $now;
                        break;
                    case CoaAuditState::Sent:
                        $payload['sent_at'] = $now;
                        $payload['attempt_count'] = DB::raw('attempt_count + 1');
                        break;
                    case CoaAuditState::Success:
                        $payload['success_at'] = $now;
                        break;
                    case CoaAuditState::Failed:
                    case CoaAuditState::Nak:
                        $payload['failed_at'] = $now;
                        break;
                }
                $this->update(array_merge($payload, $extra));
                $this->refresh();

                // Side-effect: update OnlineSession state juga ikut berubah sesuai COA success
                try {
                    if ($next === CoaAuditState::Success && $this->pppoe_user_id) {
                        $mapState = match ($this->coa_type) {
                            CoaType::Suspend => RadiusSessionState::SuspendApplied,
                            CoaType::Reactivate, CoaType::BandwidthChange => RadiusSessionState::Online,
                            CoaType::AdminForceDisconnect => RadiusSessionState::Disconnecting,
                            default => null,
                        };
                        if ($mapState) {
                            \App\Models\ISP\OnlineSession::query()
                                ->where('pppoe_user_id', $this->pppoe_user_id)
                                ->limit(100)
                                ->update(['radius_state' => $mapState->value, 'state_changed_at' => now()]);
                        }
                    }
                } catch (Throwable) { /* ignore non-fatal */ }

                return true;
            });
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    public function isFinal(): bool
    {
        return $this->state->isFinal();
    }

    public function canRetry(): bool
    {
        return !$this->isFinal() && (int)$this->attempt_count < (int)$this->max_attempts;
    }

    public function pppoeUser(): BelongsTo
    {
        return $this->belongsTo(PPPoEUser::class);
    }

    public function customerService(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer\CustomerService::class);
    }

    public function nas(): BelongsTo
    {
        return $this->belongsTo(RadiusNas::class, 'radius_nas_id');
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'operator_id');
    }
}
