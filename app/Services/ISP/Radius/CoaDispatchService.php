<?php

namespace App\Services\ISP\Radius;

use App\Enums\ISP\CoaAuditState;
use App\Enums\ISP\CoaType;
use App\Models\Customer\CustomerService;
use App\Models\ISP\OnlineSession;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\RadiusCoaAudit;
use App\Models\ISP\RadiusNas;
use App\Models\ISP\SuspendPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SSOT: COA Orchestration Service.
 *
 * Bertanggung jawab:
 *   - Build payload COA berdasarkan CoaType
 *   - Create audit record (RadiusCoaAudit) dengan state Created → Queued
 *   - Mencari identifier session aktif untuk identifikasi (Acct-Session-Id, Framed-IP, Calling-Station-Id)
 *   - Dispatch job ke Horizon Redis queue (SingleCoaJob)
 *
 * Semua operasi PENULISAN via method ini (CUD via Service Layer, bukan dari Controller/Livewire langsung).
 */
final class CoaDispatchService
{
    public const MAX_ATTEMPTS_DEFAULT = 5;

    public function __construct(
        private readonly RFC5176DisconnectService $disconnectService,
        private readonly PerformanceMetricsService $metrics,
    ) {}

    /**
     * Single COA dispatch.
     *
     * @param  array<string,mixed>  $extraAttrs
     * @return array{ok: bool, audit_id: ?int, audit_uuid: ?string, queued_at: ?string}
     */
    public function enqueue(
        CoaType $type,
        PPPoEUser $pppoeUser,
        int $operatorId = 1,
        ?SuspendPolicy $overrideSuspendPolicy = null,
        ?int $maxAttempts = null,
        array $extraAttrs = [],
    ): array {
        $t0 = microtime(true);
        try {
            DB::beginTransaction();

            $customerService = $pppoeUser->customerService;
            $serviceProfile = $customerService?->serviceProfile;

            [$identifiers] = $this->resolveSessionIdentifiers($pppoeUser);
            $nas = $this->resolveNas($pppoeUser, $identifiers);

            $changeAttrs = $this->buildChangeAttributes($type, $pppoeUser, $customerService, $serviceProfile, $overrideSuspendPolicy, $extraAttrs);

            $audit = RadiusCoaAudit::create([
                'uuid' => RadiusCoaAudit::newUuid(),
                'coa_type' => $type,
                'state' => CoaAuditState::Created,
                'attempt_count' => 0,
                'max_attempts' => $maxAttempts ?? self::MAX_ATTEMPTS_DEFAULT,
                'identifiers' => $identifiers,
                'attributes_to_change' => $changeAttrs,
                'radius_nas_id' => $nas?->id,
                'router_id' => $nas?->router_id,
                'pppoe_user_id' => $pppoeUser->id,
                'customer_service_id' => $customerService?->id,
                'operator_id' => $operatorId,
            ]);

            $audit->transition(CoaAuditState::Queued);

            DB::commit();

            \App\Jobs\ISP\Radius\SingleCoaJob::dispatch($audit->id)
                ->onQueue('radius-coa')
                ->onConnection(config('queue.default', 'redis'));

            $latMs = (microtime(true) - $t0) * 1000.0;
            $this->metrics->recordLatency(PerformanceMetricsService::OP_COA, $latMs, true);

            return [
                'ok' => true,
                'audit_id' => $audit->id,
                'audit_uuid' => $audit->uuid,
                'queued_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CoaDispatchService enqueue failed', [
                'pppoe_user_id' => $pppoeUser->id,
                'coa_type' => $type->value,
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $latMs = (microtime(true) - $t0) * 1000.0;
            $this->metrics->recordLatency(PerformanceMetricsService::OP_COA, $latMs, false);
            return [
                'ok' => false,
                'audit_id' => null,
                'audit_uuid' => null,
                'queued_at' => null,
            ];
        }
    }

    /**
     * Batch enqueue many PPPoE users with same CoaType. Returns result summary.
     *
     * @param  iterable<PPPoEUser>  $pppoeUsers
     * @return array{total: int, queued: int, failed: int, uuids: list<string>}
     */
    public function enqueueBatch(
        CoaType $type,
        iterable $pppoeUsers,
        int $operatorId = 1,
        ?SuspendPolicy $overrideSuspendPolicy = null,
        ?int $maxAttempts = null,
        array $extraAttrs = [],
    ): array {
        $total = 0;
        $queued = 0;
        $failed = 0;
        $uuids = [];

        foreach ($pppoeUsers as $user) {
            $total++;
            $r = $this->enqueue($type, $user, $operatorId, $overrideSuspendPolicy, $maxAttempts, $extraAttrs);
            if ($r['ok']) {
                $queued++;
                $uuids[] = $r['audit_uuid'];
            } else {
                $failed++;
            }
        }

        Log::info('COA batch enqueue summary', [
            'type' => $type->value,
            'total' => $total,
            'queued' => $queued,
            'failed' => $failed,
        ]);

        return [
            'total' => $total,
            'queued' => $queued,
            'failed' => $failed,
            'uuids' => $uuids,
        ];
    }

    /**
     * Actually execute COA request (dipanggil oleh SingleCoaJob).
     *
     * @return array{success: bool, result: array, transitioned_to: ?CoaAuditState}
     */
    public function executeAudit(int $coaAuditId): array
    {
        $audit = RadiusCoaAudit::find($coaAuditId);
        if (!$audit) {
            return ['success' => false, 'result' => ['error' => 'Audit not found'], 'transitioned_to' => null];
        }
        if ($audit->isFinal()) {
            return ['success' => false, 'result' => ['error' => 'Audit already final'], 'transitioned_to' => $audit->state];
        }

        $nas = $audit->nas;
        if (!$nas) {
            $audit->transition(CoaAuditState::Failed, ['last_error_message' => 'NAS not found for audit']);
            return ['success' => false, 'result' => ['error' => 'NAS not found'], 'transitioned_to' => CoaAuditState::Failed];
        }

        $identifiers = $audit->identifiers ?? [];
        $changeAttrs = $audit->attributes_to_change ?? [];

        if (!$audit->canRetry()) {
            $audit->transition(CoaAuditState::Failed, ['last_error_message' => 'Max attempts reached']);
            return ['success' => false, 'result' => ['error' => 'Max attempts reached'], 'transitioned_to' => CoaAuditState::Failed];
        }

        $audit->transition(CoaAuditState::Sent);

        $port = (int)($nas->coa_port ?? 3799);
        $timeout = (int)($nas->coa_timeout_sec ?? 3);
        $retries = (int)($nas->coa_retries ?? 2);

        if ($audit->coa_type === CoaType::AdminForceDisconnect) {
            $result = $this->disconnectService->sendDisconnect($nas, $identifiers, $port, $timeout, $retries);
        } else {
            $result = $this->disconnectService->sendCoA($nas, $identifiers, $changeAttrs, $port, $timeout, $retries);
        }

        $nextState = CoaAuditState::Success;
        $extra = ['coa_result' => $result];

        if (!($result['success'] ?? false)) {
            $code = (int)($result['code'] ?? 0);
            if ($code === 45 || $code === 42) {
                $nextState = CoaAuditState::Nak;
            } elseif ($audit->canRetry()) {
                $nextState = CoaAuditState::Timeout;
            } else {
                $nextState = CoaAuditState::Failed;
            }
            $extra['last_error_message'] = (string)($result['error'] ?? 'Unknown COA error');
        }

        $audit->transition($nextState, $extra);

        // Jika Timeout → dispatch retry job dengan backoff
        if ($nextState === CoaAuditState::Timeout) {
            $attemptNo = (int)$audit->attempt_count;
            $delaySec = min(60, (2 ** ($attemptNo - 1)) * 5);
            \App\Jobs\ISP\Radius\SingleCoaJob::dispatch($audit->id)
                ->delay(now()->addSeconds($delaySec))
                ->onQueue('radius-coa')
                ->onConnection(config('queue.default', 'redis'));
        }

        return [
            'success' => $nextState === CoaAuditState::Success,
            'result' => $result,
            'transitioned_to' => $nextState,
        ];
    }

    /**
     * @return array{array, ?OnlineSession}
     */
    private function resolveSessionIdentifiers(PPPoEUser $user): array
    {
        $session = OnlineSession::query()
            ->where('pppoe_user_id', $user->id)
            ->whereIn('radius_state', ['online', 'suspend_applied', 'authenticated'])
            ->orderByDesc('last_seen_at')
            ->first();

        $ids = [];

        if ($session) {
            if (!empty($session->acct_session_id)) {
                $ids[RFC5176DisconnectService::ATTR_ACCT_SESSION_ID] = (string)$session->acct_session_id;
            }
            if (!empty($session->framed_ip_address)) {
                $ids[RFC5176DisconnectService::ATTR_FRAMED_IP_ADDRESS] = (string)$session->framed_ip_address;
            }
            if (!empty($session->calling_station_id)) {
                $ids[RFC5176DisconnectService::ATTR_CALLING_STATION_ID] = (string)$session->calling_station_id;
            }
        }

        // Fallback: minimal User-Name
        if (count($ids) === 0 && !empty($user->username)) {
            $ids[RFC5176DisconnectService::ATTR_USER_NAME] = (string)$user->username;
        }

        return [$ids, $session];
    }

    private function resolveNas(PPPoEUser $user, array $identifiers): ?RadiusNas
    {
        $nasId = $user->radius_nas_id ?? null;
        if ($nasId) {
            $n = RadiusNas::find($nasId);
            if ($n) return $n;
        }
        $cs = $user->customerService;
        if ($cs && !empty($cs->radius_nas_id)) {
            $n = RadiusNas::find($cs->radius_nas_id);
            if ($n) return $n;
        }
        if (!empty($identifiers[RFC5176DisconnectService::ATTR_NAS_IP_ADDRESS])) {
            return RadiusNas::query()->where('nas_ip_address', $identifiers[RFC5176DisconnectService::ATTR_NAS_IP_ADDRESS])->first();
        }
        return RadiusNas::query()->where('is_default', true)->first();
    }

    /**
     * @return array<int, string|int>
     */
    private function buildChangeAttributes(
        CoaType $type,
        PPPoEUser $user,
        ?CustomerService $cs,
        mixed $serviceProfile,
        ?SuspendPolicy $overridePolicy,
        array $extraAttrs,
    ): array {
        $attrs = [];

        switch ($type) {
            case CoaType::Suspend:
                $policy = $overridePolicy;
                if (!$policy && $cs) {
                    try {
                        $policy = SuspendPolicy::resolveForService($cs);
                    } catch (\Throwable) {
                        $policy = null;
                    }
                }
                if ($policy) {
                    $rl = $policy->mikrotikRateLimitString();
                    if ($rl) $attrs['MikroTik-Rate-Limit'] = $rl;
                    if (!empty($policy->address_list)) $attrs['MikroTik-Address-List'] = $policy->address_list;
                } else {
                    $attrs['MikroTik-Rate-Limit'] = '128k/128k';
                }
                break;

            case CoaType::Reactivate:
            case CoaType::RebalanceProfile:
            case CoaType::BandwidthChange:
                $dl = (int)($serviceProfile?->rate_download_kbps ?? 0);
                $ul = (int)($serviceProfile?->rate_upload_kbps ?? 0);
                if ($dl > 0 && $ul > 0) {
                    $rl = "{$dl}k/{$ul}k";
                    $bDl = (int)($serviceProfile?->burst_download_kbps ?? 0);
                    $bUl = (int)($serviceProfile?->burst_upload_kbps ?? 0);
                    if ($bDl > 0 && $bUl > 0) {
                        $bTh = (int)($serviceProfile?->burst_threshold_kbps ?? (int)(($dl + $bDl) / 2));
                        $bTm = (int)($serviceProfile?->burst_time_seconds ?? 8);
                        $rl .= " {$bDl}k/{$bUl}k {$bTh}k {$bTm}s";
                    }
                    $attrs['MikroTik-Rate-Limit'] = $rl;
                }
                if (!empty($serviceProfile?->default_address_list)) {
                    $attrs['MikroTik-Address-List'] = (string)$serviceProfile->default_address_list;
                }
                break;

            case CoaType::AdminForceDisconnect:
                break;
        }

        return array_merge($attrs, $extraAttrs);
    }
}
