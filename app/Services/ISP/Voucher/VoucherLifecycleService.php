<?php

namespace App\Services\ISP\Voucher;

use App\Events\ISP\Voucher\VoucherRedeemedEvent;
use App\Models\AuditLog;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherPool;
use App\Models\User;
use App\Repositories\ISP\VoucherRepository;
use App\Repositories\ISP\VoucherPoolRepository;
use App\Services\ISP\HotspotService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class VoucherLifecycleService
{
    public function __construct(
        protected VoucherRepository $voucherRepository,
        protected VoucherPoolRepository $voucherPoolRepository,
        protected HotspotService $hotspotService,
    ) {}

    public function redeemVoucher(string $code, ?int $userId): ?Voucher
    {
        return DB::transaction(function () use ($code, $userId) {
            $voucher = $this->voucherRepository->where('code', $code)->lockForUpdate()->first();

            if (!$voucher || $voucher->status !== 'available') {
                Log::info('Voucher redeem gagal: tidak available', ['code' => $code]);
                return null;
            }

            $pool = $voucher->voucherPool;
            $serviceProfileId = $voucher->service_profile_id ?? $pool?->service_profile_id;
            if (!$serviceProfileId) {
                Log::warning('Voucher redeem gagal: tidak ada service_profile_id', [
                    'voucher_id' => $voucher->id,
                    'code' => $code,
                ]);
                return null;
            }

            $expiresAt = null;
            if ($voucher->validity_days > 0) {
                $expiresAt = now()->addDays($voucher->validity_days);
            } elseif ($pool?->validity_days > 0) {
                $expiresAt = now()->addDays($pool->validity_days);
            }

            $hotspotUser = null;
            try {
                $generatedPassword = Str::random(12);
                $username = $voucher->login_method === 'username_password'
                    ? ($code . '_' . substr(Str::random(4), 0, 4))
                    : $code;

                $hotspotUser = $this->hotspotService->createHotspotUser(
                    customerService: null,
                    serviceProfileId: $serviceProfileId,
                    voucherPoolId: $pool?->id,
                    userId: $userId ?? User::query()->value('id') ?? 1,
                    username: $username,
                    password: $generatedPassword,
                );

                try {
                    $this->hotspotService->activateHotspotUser($hotspotUser->id, $userId ?? User::query()->value('id') ?? 1);
                } catch (Throwable $e) {
                    Log::warning('Voucher redeem: activate hotspot user non-fatal', [
                        'voucher_id' => $voucher->id,
                        'err' => $e->getMessage(),
                    ]);
                }
            } catch (Throwable $e) {
                Log::error('Voucher redeem: create hotspot user gagal', [
                    'voucher_id' => $voucher->id,
                    'code' => $code,
                    'err' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $voucher->update([
                'status' => 'used',
                'activated_at' => now(),
                'expires_at' => $expiresAt,
                'hotspot_user_id' => $hotspotUser?->id,
                'updated_by' => $userId,
            ]);

            if ($pool) {
                $pool->increment('used_vouchers');
                $pool->increment('active_vouchers');
            }

            try {
                event(new VoucherRedeemedEvent($voucher, $hotspotUser, $userId));
            } catch (Throwable $e) {
                Log::warning('VoucherRedeemedEvent dispatch gagal', [
                    'voucher_id' => $voucher->id,
                    'err' => $e->getMessage(),
                ]);
            }

            $this->logAudit($voucher, 'redeemed', [
                'old_status' => 'available',
                'hotspot_user_id' => $hotspotUser?->id,
                'expires_at' => $expiresAt?->toIso8601String(),
            ], $voucher->toArray(), User::find($userId));

            return $voucher;
        });
    }

    public function expireVoucher(Voucher $voucher, ?int $userId = null): Voucher
    {
        return DB::transaction(function () use ($voucher, $userId) {
            $oldStatus = $voucher->status;
            if ($oldStatus === 'expired') {
                return $voucher;
            }

            if ($voucher->hotspot_user_id) {
                try {
                    $this->hotspotService->terminateHotspotUser($voucher->hotspot_user_id, $userId ?? User::query()->value('id') ?? 1);
                } catch (Throwable $e) {
                    Log::warning('Voucher expire: terminateHotspotUser non-fatal', [
                        'voucher_id' => $voucher->id,
                        'err' => $e->getMessage(),
                    ]);
                }
            }

            $voucher->update([
                'status' => 'expired',
                'updated_by' => $userId,
            ]);

            if ($voucher->voucher_pool_id) {
                $pool = $this->voucherPoolRepository->find($voucher->voucher_pool_id);
                if ($pool && (int)$pool->active_vouchers > 0) {
                    $pool->decrement('active_vouchers');
                }
            }

            $this->logAudit($voucher, 'expired', [
                'old_status' => $oldStatus,
            ], $voucher->toArray(), User::find($userId));

            return $voucher;
        });
    }

    protected function logAudit($model, string $event, ?array $oldValues, ?array $newValues, ?User $user): void
    {
        try {
            $audit = [
                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id' => $model?->id,
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => $user?->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            if (function_exists('audit_log_create')) {
                audit_log_create($audit);
            } else {
                AuditLog::create($audit);
            }
        } catch (\Exception $e) {
            Log::error('Voucher audit log failed: ' . $e->getMessage(), [
                'model' => $model ? get_class($model) : null,
                'id' => $model?->id ?? null,
            ]);
        }
    }
}
