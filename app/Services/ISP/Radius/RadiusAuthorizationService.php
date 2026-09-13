<?php

declare(strict_types=1);

namespace App\Services\ISP\Radius;

use App\Services\ISP\Radius\ValueObjects\PolicyResult;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;
use App\Models\ISP\RadiusNas;

/**
 * SSOT: Radius Authorization Service.
 *
 * Tanggung jawab TUNGGAL: menjalankan Policy Engine + membangun response attributes.
 * Tidak melakukan credential verification (itu tugas RadiusAuthService).
 *
 * Output:
 *   - accept: true/false
 *   - reply_attributes: merged policy attributes + MikroTik Vendor Specific
 *   - reject_reason: pesan user-friendly untuk Reply-Message
 *   - policy_trace: debug semua policy yang dijalankan (untuk audit trail)
 */
final class RadiusAuthorizationService
{
    public function __construct(
        private readonly RadiusPolicyEngine $policyEngine,
        private readonly PerformanceMetricsService $metrics,
    ) {}

    /**
     * @return array{
     *     accept: bool,
     *     http_status: int,
     *     reply_attributes: array<string, string|int>,
     *     reply_message: string,
     *     fail_action: string,
     *     reject_reason?: string,
     *     policy_trace: array<string, array{pass: bool, reason: string, fail_action: string}>,
     *     warnings: array<string>,
     * }
     */
    public function authorize(RadiusAccessContext $ctx): array
    {
        $t0 = microtime(true);
        $accept = false;
        try {
            $result = $this->policyEngine->evaluate($ctx);

            $trace = [];
            foreach ($result['policies_executed'] as $class => $r) {
                /** @var PolicyResult $r */
                $trace[$class] = [
                    'pass' => $r->pass,
                    'reason' => $r->reason,
                    'fail_action' => $r->failAction,
                ];
            }

            $warnings = $result['warnings'];
            $reply = $result['mergedReply'];
            $replyMessage = '';
            $failAction = 'accept';
            $httpStatus = 200;

            if (!$result['pass']) {
                $rej = $result['firstRejection'];
                $accept = false;
                $httpStatus = $rej?->httpStatusCode ?? 401;
                $failAction = $rej?->failAction ?? 'reject';
                $replyMessage = $rej?->reason ?? 'Access Denied';
                $reply['Reply-Message'] = $replyMessage;

                if (!empty($rej?->replyAttributes)) {
                    $reply = array_merge($reply, $rej->replyAttributes);
                }
            } else {
                $accept = true;
                $failAction = 'accept';
                if (count($warnings) > 0) {
                    $replyMessage = implode(' | ', $warnings);
                    $reply['Reply-Message'] = $replyMessage;
                }

                // Default Rate-Limit dari ServiceProfile jika tidak ada policy override
                $sp = $ctx->serviceProfile;
                if ($sp && empty($reply['MikroTik-Rate-Limit'])) {
                    $dl = (int)($sp->rate_download_kbps ?? 0);
                    $ul = (int)($sp->rate_upload_kbps ?? 0);
                    if ($dl > 0 && $ul > 0) {
                        $rl = "{$dl}k/{$ul}k";
                        $bDl = (int)($sp->burst_download_kbps ?? 0);
                        $bUl = (int)($sp->burst_upload_kbps ?? 0);
                        if ($bDl > 0 && $bUl > 0) {
                            $bTh = (int)($sp->burst_threshold_kbps ?? (int)(($dl + $bDl) / 2));
                            $bTm = (int)($sp->burst_time_seconds ?? 8);
                            $rl .= " {$bDl}k/{$bUl}k {$bTh}k {$bTm}s";
                        }
                        $reply['MikroTik-Rate-Limit'] = $rl;
                    }
                }

                // Framed-IP Pool assignment
                if ($sp && !empty($sp->ip_pool_name) && empty($reply['Framed-Pool'])) {
                    $reply['Framed-Pool'] = $sp->ip_pool_name;
                }

                // Address-List default
                if ($sp && !empty($sp->default_address_list) && empty($reply['MikroTik-Address-List'])) {
                    $reply['MikroTik-Address-List'] = $sp->default_address_list;
                }
            }

            return [
                'accept' => $accept,
                'http_status' => $httpStatus,
                'reply_attributes' => $reply,
                'reply_message' => $replyMessage,
                'fail_action' => $failAction,
                'reject_reason' => $accept ? null : ($result['firstRejection']?->reason ?? 'Access Denied'),
                'policy_trace' => $trace,
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            report($e);
            return [
                'accept' => false,
                'http_status' => 500,
                'reply_attributes' => ['Reply-Message' => 'Internal authorization error'],
                'reply_message' => 'Internal authorization error: ' . $e->getMessage(),
                'fail_action' => 'reject',
                'reject_reason' => 'Internal server error',
                'policy_trace' => [],
                'warnings' => ['exception: ' . $e->getMessage()],
            ];
        } finally {
            $latMs = (microtime(true) - $t0) * 1000.0;
            $this->metrics->recordLatency(PerformanceMetricsService::OP_AUTHORIZE, $latMs, $accept ?? false);
        }
    }

    /**
     * Combined pipeline: authenticate dulu lalu authorize.
     * Convenience method untuk preAuth endpoint.
     *
     * @return array{
     *     accept: bool,
     *     http_status: int,
     *     reply_attributes: array,
     *     reply_message: string,
     *     auth?: array,
     *     authz?: array,
     * }
     */
    public function preAuthorize(
        RadiusAuthService $authService,
        string $username,
        string $password,
        ?RadiusNas $nas = null,
        ?string $nasIp = null,
        ?string $callingStationId = null,
        ?string $calledStationId = null,
        ?string $framedIp = null,
        string $protocol = 'pppoe',
    ): array {
        $t0 = microtime(true);
        try {
            $authResult = $authService->authenticate(
                username: $username,
                password: $password,
                nas: $nas,
                nasIp: $nasIp,
                callingStationId: $callingStationId,
                calledStationId: $calledStationId,
                framedIp: $framedIp,
                protocol: $protocol,
            );

            if (!$authResult['ok']) {
                return [
                    'accept' => false,
                    'http_status' => 401,
                    'reply_attributes' => ['Reply-Message' => $authResult['reason']],
                    'reply_message' => $authResult['reason'],
                    'auth' => $authResult,
                ];
            }

            $authzResult = $this->authorize($authResult['context']);
            $authzResult['auth'] = $authResult;
            $authzResult['authz'] = $authzResult;
            return $authzResult;
        } finally {
            $latMs = (microtime(true) - $t0) * 1000.0;
            $ok = isset($authzResult) && is_array($authzResult) && ($authzResult['accept'] ?? false);
            $this->metrics->recordLatency(PerformanceMetricsService::OP_PREAUTH, $latMs, $ok);
        }
    }
}
