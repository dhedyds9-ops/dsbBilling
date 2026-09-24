<?php

namespace App\Http\Controllers\Api\Radius;

use App\Http\Controllers\Controller;
use App\Http\Requests\Radius\AccountingIngestRequest;
use App\Http\Requests\Radius\PreAuthRequest;
use App\Models\ISP\RadiusNas;
use App\Services\ISP\Accounting\RadiusAccountingStateMachine;
use App\Services\ISP\Radius\PerformanceMetricsService;
use App\Services\ISP\Radius\RadiusAuthService;
use App\Services\ISP\Radius\RadiusAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AccountingController extends Controller
{
    public function __construct(
        private readonly RadiusAccountingStateMachine $stateMachine,
        private readonly RadiusAuthService $authService,
        private readonly RadiusAuthorizationService $authorizationService,
        private readonly PerformanceMetricsService $metrics,
    ) {}

    public function ingest(AccountingIngestRequest $request): JsonResponse
    {
        /** @var RadiusNas|null $nas */
        $nas = $request->attributes->get('radius_nas');
        $source = $request->input('ingest_source') ?? 'free_radius_rest';

        try {
            $normalized = $this->metrics::measure(PerformanceMetricsService::OP_ACCOUNTING, function () use ($request, $nas) {
                $normalized = $request->normalized();
                if ($nas && empty($normalized['nas_ip_address'])) {
                    $normalized['nas_ip_address'] = $nas->nas_ip_address;
                }
                if ($nas && empty($normalized['nas_name'])) {
                    $normalized['nas_name'] = $nas->nas_name;
                }
                return $normalized;
            });

            $accounting = $this->stateMachine->ingest($normalized, $source);

            return response()->json([
                'ok' => true,
                'id' => $accounting->id,
                'uuid' => $accounting->uuid,
                'status_type' => $accounting->acct_status_type,
                'acct_session_id' => $accounting->acct_session_id,
                'nas' => $nas?->nas_name ?? $normalized['nas_ip_address'] ?? null,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Radius accounting ingest exception', [
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'body' => $request->only(['acct_session_id', 'username', 'nas_ip_address']),
            ]);
            return response()->json([
                'ok' => false,
                'error' => 'Internal ingest error',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * PreAuth: combined auth + authorize endpoint.
     *
     * Digunakan oleh FreeRADIUS rlm_rest / mod_rest pada bagian authorize / authenticate.
     * Flow:
     *   1. Verify username+password (RadiusAuthService)
     *   2. Jalankan semua policy rules (RadiusAuthorizationService -> PolicyEngine)
     *   3. Return: Access-Accept / Access-Reject beserta reply attributes (MikroTik-Rate-Limit, Framed-Pool, dll)
     *
     * Response body mengikuti format FreeRADIUS rlm_rest.
     */
    public function preAuth(PreAuthRequest $request): JsonResponse
    {
        /** @var RadiusNas|null $nas */
        $nas = $request->attributes->get('radius_nas');
        $data = $request->normalized();

        try {
            $result = $this->authorizationService->preAuthorize(
                authService: $this->authService,
                username: (string)$data['username'],
                password: (string)$data['password'],
                nas: $nas,
                nasIp: (string)($data['nas_ip_address'] ?? null),
                callingStationId: (string)($data['calling_station_id'] ?? null),
                calledStationId: (string)($data['called_station_id'] ?? null),
                framedIp: (string)($data['framed_ip_address'] ?? null),
                protocol: (string)($data['protocol'] ?? 'pppoe'),
            );

            $response = [
                'ok' => $result['accept'],
                'accept' => $result['accept'],
                'reply_attributes' => $result['reply_attributes'],
                'reply_message' => $result['reply_message'] ?? '',
                'fail_action' => $result['fail_action'] ?? ($result['accept'] ? 'accept' : 'reject'),
            ];

            if (!empty($result['auth'])) {
                $response['auth'] = [
                    'method' => $result['auth']['auth_method'] ?? null,
                ];
            }
            if (!empty($result['reject_reason'])) {
                $response['reject_reason'] = $result['reject_reason'];
            }
            if (!empty($result['warnings']) && is_array($result['warnings'])) {
                $response['warnings'] = $result['warnings'];
            }
            if ($request->boolean('debug', false) && !empty($result['policy_trace'])) {
                $response['_debug_policy_trace'] = $result['policy_trace'];
            }

            return response()->json($response, (int)($result['http_status'] ?? ($result['accept'] ? 200 : 401)));
        } catch (Throwable $e) {
            Log::error('Radius preAuth exception', [
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'username' => $data['username'] ?? null,
            ]);
            return response()->json([
                'ok' => false,
                'accept' => false,
                'reply_attributes' => ['Reply-Message' => 'Internal authorization error'],
                'reply_message' => 'Internal server error',
                'fail_action' => 'reject',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Authorize-only endpoint (pisah dari authentication).
     * Untuk FreeRADIUS yang sudah handle PAP/CHAP/MS-CHAP via rlm_perl atau modul lain,
     * dan hanya butuh evaluasi policy + reply attributes dari dsBilling.
     *
     * Body requirement: username + (pppoe_user_id OR customer_service_id OR voucher_code)
     */
    public function authorizeOnly(Request $request): JsonResponse
    {
        $username = (string)$request->input('username', '');
        $pppoeUserId = $request->input('pppoe_user_id');
        $customerServiceId = $request->input('customer_service_id');
        $voucherCode = $request->input('voucher_code');

        if ($username === '') {
            return response()->json([
                'ok' => false,
                'accept' => false,
                'reply_attributes' => ['Reply-Message' => 'username required'],
            ], 400);
        }

        try {
            // Hydrate context tanpa verifikasi password — authorize-only endpoint
            // PENTING: gunakan flag $authorizeOnly=true, bukan password dummy string.
            $authResult = $this->authService->authenticate(
                username: $username,
                password: '',
                nas: $request->attributes->get('radius_nas'),
                nasIp: (string)($request->input('nas_ip_address') ?? $request->ip()),
                callingStationId: (string)($request->input('calling_station_id') ?? null),
                calledStationId: (string)($request->input('called_station_id') ?? null),
                framedIp: (string)($request->input('framed_ip_address') ?? null),
                authorizeOnly: true,
            );

            $ctx = $authResult['context'] ?? null;
            if (!$ctx) {
                return response()->json([
                    'ok' => false,
                    'accept' => false,
                    'reply_attributes' => ['Reply-Message' => $authResult['reason'] ?? 'User not found'],
                ], 401);
            }

            $authzResult = $this->authorizationService->authorize($ctx);

            return response()->json([
                'ok' => $authzResult['accept'],
                'accept' => $authzResult['accept'],
                'reply_attributes' => $authzResult['reply_attributes'],
                'reply_message' => $authzResult['reply_message'] ?? '',
                'fail_action' => $authzResult['fail_action'],
                'reject_reason' => $authzResult['reject_reason'],
                'warnings' => $authzResult['warnings'],
                '_debug_policy_trace' => $request->boolean('debug', false) ? $authzResult['policy_trace'] : null,
            ], (int)($authzResult['http_status'] ?? 200));
        } catch (Throwable $e) {
            Log::error('Radius authorizeOnly exception', ['err' => $e->getMessage(), 'username' => $username]);
            return response()->json(['ok' => false, 'accept' => false, 'error' => 'Internal error'], 500);
        }
    }

    public function health(Request $request): JsonResponse
    {
        $count = \App\Models\ISP\RadiusAccounting::query()
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $unresolvedFk = \App\Models\ISP\RadiusAccounting::query()
            ->whereNotNull('username')
            ->whereNull('customer_service_id')
            ->count();

        $deadSessions = \App\Models\ISP\RadiusAccounting::query()
            ->whereNull('acct_stop_time')
            ->where(function ($q) {
                $q->where('acct_start_time', '<=', now()->subHour())
                    ->orWhere('created_at', '<=', now()->subHour());
            })
            ->count();

        $online = [
            'total' => 0,
            'pppoe' => 0,
            'hotspot' => 0,
            'from_radius' => 0,
            'from_router' => 0,
        ];
        try {
            $online['total'] = \App\Models\ISP\OnlineSession::query()->count();
            $online['pppoe'] = \App\Models\ISP\OnlineSession::query()->pppoe()->count();
            $online['hotspot'] = \App\Models\ISP\OnlineSession::query()->hotspot()->count();
            $online['from_radius'] = \App\Models\ISP\OnlineSession::query()->sourceRadius()->count();
            $online['from_router'] = \App\Models\ISP\OnlineSession::query()->sourcePoller()->count();
        } catch (\Throwable) {
        }

        $voucherStats = null;
        try {
            $expiringSoon = \App\Models\ISP\Voucher::query()
                ->whereIn('status', ['used', 'active'])
                ->where('expires_at', '<=', now()->addDay())
                ->count();
            $expiredStale = \App\Models\ISP\Voucher::query()
                ->whereIn('status', ['used', 'active'])
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count();
            $voucherStats = [
                'expired_not_marked_count' => $expiredStale,
                'expiring_within_24h' => $expiringSoon,
            ];
        } catch (\Throwable) {
        }

        $performance = null;
        try {
            $performance = $this->metrics->all();
        } catch (\Throwable) {
        }

        return response()->json([
            'ok' => true,
            'service' => 'radius-accounting-ingest',
            'hourly_records' => $count,
            'unresolved_lifecycle_fk_count' => $unresolvedFk,
            'dead_session_candidates_count' => $deadSessions,
            'nas_resolved' => $request->attributes->has('radius_nas') ? $request->attributes->get('radius_nas')->nas_name : null,
            'online_sessions' => $online,
            'vouchers' => $voucherStats,
            'performance' => $performance,
            'time' => now()->toIso8601String(),
        ], 200);
    }
}
