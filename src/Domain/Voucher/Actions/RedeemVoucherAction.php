<?php

namespace Src\Domain\Voucher\Actions;

use App\Events\ISP\Voucher\VoucherRedeemedEvent;
use App\Models\ISP\Voucher;
use App\Models\User;
use App\Services\ISP\HotspotService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Domain\Voucher\Repositories\VoucherRepositoryInterface;
use Src\Domain\Voucher\Support\VoucherAuditLogger;
use Throwable;

class RedeemVoucherAction
{
    public function __construct(
        private readonly VoucherRepositoryInterface $voucherRepository,
        private readonly HotspotService $hotspotService,
        private readonly VoucherAuditLogger $auditLogger,
    ) {}

    public function execute(string $code, ?int $userId): ?Voucher
    {
        return DB::transaction(function () use ($code, $userId) {
            /** @var Voucher|null $voucher */
            $voucher = $this->voucherRepository->where('code', $code)
                ->lockForUpdate()
                ->first();

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

            $resolvedUserId = $userId ?? User::query()->value('id') ?? 1;
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
                    userId: $resolvedUserId,
                    username: $username,
                    password: $generatedPassword,
                );

                try {
                    $this->hotspotService->activateHotspotUser($hotspotUser->id, $resolvedUserId);
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
                'updated_by' => $resolvedUserId,
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

            $this->auditLogger->log($voucher, 'redeemed', [
                'old_status' => 'available',
                'hotspot_user_id' => $hotspotUser?->id,
                'expires_at' => $expiresAt?->toIso8601String(),
            ], $voucher->toArray(), User::find($resolvedUserId));

            return $voucher;
        });
    }
}
