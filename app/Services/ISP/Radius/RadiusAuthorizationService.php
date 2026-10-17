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
                    if (!empty($sp->radius_rate_limit)) {
                        $reply['MikroTik-Rate-Limit'] = $sp->radius_rate_limit;
                    } else {
                        $dl = (int)($sp->download_speed ?? 0);
                        $ul = (int)($sp->upload_speed ?? 0);
                        if ($dl > 0 && $ul > 0) {
                            $rl = "{$ul}M/{$dl}M";
                            $bDl = (int)($sp->burst_limit_download ?? 0);
                            $bUl = (int)($sp->burst_limit_upload ?? 0);
                            if ($bDl > 0 && $bUl > 0) {
                                $bThDl = (int)($sp->burst_threshold_download ?? (int)(($dl + $bDl) / 2));
                                $bThUl = (int)($sp->burst_threshold_upload ?? (int)(($ul + $bUl) / 2));
                                $bTmDl = (int)($sp->burst_time_download ?? 8);
                                $bTmUl = (int)($sp->burst_time_upload ?? 8);
                                $rl .= " {$bUl}M/{$bDl}M {$bThUl}M/{$bThDl}M {$bTmUl}/{$bTmDl}";
                            }
                            $reply['MikroTik-Rate-Limit'] = $rl;
                        } elseif (!empty($sp->rate_download_kbps) && !empty($sp->rate_upload_kbps)) {
                            $dlK = (int)$sp->rate_download_kbps;
                            $ulK = (int)$sp->rate_upload_kbps;
                            $reply['MikroTik-Rate-Limit'] = "{$ulK}k/{$dlK}k";
                        }
                    }
                }

                // Framed-IP Pool assignment
                // CATATAN KRITIS: Untuk Hotspot / Voucher, klien sudah mendapatkan IP dari Hotspot DHCP.
                // Jika RADIUS mengirim Framed-Pool ke Hotspot, MikroTik akan mencoba 1:1 NAT yang menyebabkan
                // "Tersambung tapi tidak ada internet". Framed-Pool HANYA untuk PPPoE.
                $isHotspot = ($ctx->protocol === 'hotspot' || $ctx->voucher !== null || $ctx->hotspotUser !== null);
                if (empty($reply['Framed-Pool']) && !$isHotspot) {
                    if ($sp && !empty($sp->radius_framed_pool) && !str_contains(strtolower($sp->radius_framed_pool), 'unknown')) {
                        $reply['Framed-Pool'] = $sp->radius_framed_pool;
                    } elseif ($sp && !empty($sp->ip_pool_name) && !str_contains(strtolower($sp->ip_pool_name), 'unknown')) {
                        $reply['Framed-Pool'] = $sp->ip_pool_name;
                    } elseif ($ctx->protocol === 'pppoe' || $ctx->pppoeUser !== null) {
                        $reply['Framed-Pool'] = 'pool-pppoe';
                    }
                }

                // Assign MikroTik-Group (User Profile di MikroTik Hotspot) jika ada
                if ($isHotspot && empty($reply['MikroTik-Group'])) {
                    $group = $sp?->hotspot_profile ?: ($sp?->user_profile ?: ($sp?->mikrotik_profile ?: null));
                    if (!empty($group)) {
                        $reply['MikroTik-Group'] = $group;
                    }
                }

                // Address-List default
                if ($sp && !empty($sp->default_address_list) && empty($reply['MikroTik-Address-List'])) {
                    $reply['MikroTik-Address-List'] = $sp->default_address_list;
                }

                // User / Voucher level attributes
                $userModel = $ctx->pppoeUser ?? $ctx->hotspotUser ?? $ctx->voucher;
                if ($userModel) {
                    if (!empty($userModel->session_timeout) && empty($reply['Session-Timeout'])) {
                        $reply['Session-Timeout'] = (int)$userModel->session_timeout;
                    }
                    if (!empty($userModel->idle_timeout) && empty($reply['Idle-Timeout'])) {
                        $reply['Idle-Timeout'] = (int)$userModel->idle_timeout;
                    }
                    if (!empty($userModel->static_ip) && empty($reply['Framed-IP-Address'])) {
                        $reply['Framed-IP-Address'] = (string)$userModel->static_ip;
                    }
                    if (!empty($userModel->framed_pool) && empty($reply['Framed-Pool'])) {
                        $reply['Framed-Pool'] = (string)$userModel->framed_pool;
                    }
                    if (!empty($userModel->address_list) && empty($reply['MikroTik-Address-List'])) {
                        $reply['MikroTik-Address-List'] = (string)$userModel->address_list;
                    }
                }

                // Voucher lifecycle activation & session timeout calculation
                if ($ctx->voucher) {
                    $voucher = $ctx->voucher;
                    $dirty = false;
                    if (!$voucher->activated_at) {
                        $voucher->activated_at = now();
                        $dirty = true;
                    }
                    if ($voucher->status === 'available' || $voucher->status === 'unused') {
                        $voucher->status = 'used';
                        $dirty = true;
                    }
                    if (!$voucher->expires_at) {
                        $unit = strtolower((string)($sp?->validity_unit ?: ($sp?->duration_unit ?: 'hours')));
                        
                        if ($unit === 'hours' || $unit === 'hour' || (!empty($sp?->validity_hours) && empty($sp?->validity_days))) {
                            $dur = (int)($sp?->validity_hours ?: ($sp?->duration_value ?: 1));
                            $voucher->expires_at = now()->addHours(max(1, $dur));
                        } elseif ($unit === 'months' || $unit === 'month') {
                            $days = (int)($sp?->validity_days ?: (($sp?->duration_value ?: 1) * 30));
                            $voucher->expires_at = now()->addDays(max(1, $days));
                        } else {
                            // Default: days (Hari)
                            $dur = (int)($voucher->validity_days ?: ($sp?->validity_days ?: ($sp?->duration_value ?: 1)));
                            $voucher->expires_at = now()->addDays(max(1, $dur));
                        }
                        $dirty = true;
                    }
                    if ($dirty) {
                        try {
                            $voucher->save();
                            if ($voucher->voucher_pool_id) {
                                \App\Models\ISP\VoucherPool::query()->where('id', $voucher->voucher_pool_id)->increment('used_vouchers');
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Voucher activation save failed', ['code' => $voucher->code, 'err' => $e->getMessage()]);
                        }
                    }

                    if ($voucher->expires_at && empty($reply['Session-Timeout'])) {
                        // Carbon 3: now()->diffInSeconds($target, false) bernilai POSITIF jika target di masa depan
                        $diffSeconds = (int)now()->diffInSeconds($voucher->expires_at, false);
                        if ($diffSeconds <= 0) {
                            $accept = false;
                            $httpStatus = 401;
                            $failAction = 'reject';
                            $replyMessage = 'Masa aktif voucher telah berakhir';
                            $reply['Reply-Message'] = $replyMessage;
                        } else {
                            $reply['Session-Timeout'] = $diffSeconds;
                        }
                    }
                }

                if ($ctx->protocol === 'pppoe' || $ctx->pppoeUser !== null) {
                    if (empty($reply['Framed-Protocol'])) {
                        $reply['Framed-Protocol'] = 'PPP';
                    }
                    if (empty($reply['Service-Type'])) {
                        $reply['Service-Type'] = 'Framed-User';
                    }
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
