<?php

namespace App\Services\AAA;

use App\Models\AAA\Voucher;
use App\Models\AAA\VoucherPool;
use App\Repositories\AAA\VoucherRepository;
use App\Repositories\AAA\VoucherPoolRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class VoucherService
{
    public function __construct(
        protected VoucherRepository $voucherRepository,
        protected VoucherPoolRepository $voucherPoolRepository,
    ) {}

    public function createVoucherPool(
        string $name,
        int $serviceProfileId,
        int $userId,
        ?string $description = null,
        ?string $prefix = null,
        int $length = 8,
        int $quota = 0,
        ?int $validityDays = null,
    ): VoucherPool {
        return DB::transaction(function () use (
            $name,
            $serviceProfileId,
            $userId,
            $description,
            $prefix,
            $length,
            $quota,
            $validityDays,
        ) {
            $voucherPool = $this->voucherPoolRepository->create([
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'description' => $description,
                'service_profile_id' => $serviceProfileId,
                'prefix' => $prefix,
                'length' => $length,
                'quota' => $quota,
                'validity_days' => $validityDays,
                'total_vouchers' => 0,
                'used_vouchers' => 0,
                'active_vouchers' => 0,
                'status' => 'active',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // TODO: Dispatch domain event

            return $voucherPool;
        });
    }

    public function generateVouchers(int $voucherPoolId, int $count, int $userId): array
    {
        return DB::transaction(function () use ($voucherPoolId, $count, $userId) {
            $voucherPool = $this->voucherPoolRepository->find($voucherPoolId);
            $vouchers = [];

            for ($i = 0; $i < $count; $i++) {
                $code = $this->generateVoucherCode($voucherPool);
                $voucher = $this->voucherRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'code' => $code,
                    'voucher_pool_id' => $voucherPoolId,
                    'status' => 'available',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $vouchers[] = $voucher;
            }

            $voucherPool->increment('total_vouchers', $count);

            return $vouchers;
        });
    }

    protected function generateVoucherCode(VoucherPool $voucherPool): string
    {
        $prefix = $voucherPool->prefix ?? '';
        $length = $voucherPool->length;
        $randomString = strtoupper(Str::random($length));
        return $prefix . $randomString;
    }

    public function redeemVoucher(string $code, ?int $userId): ?Voucher
    {
        return DB::transaction(function () use ($code, $userId) {
            $voucher = $this->voucherRepository->where('code', $code)->first();
            
            if (!$voucher || $voucher->status !== 'available') {
                return null;
            }

            $voucherPool = $voucher->voucherPool;
            
            $expiresAt = $voucherPool->validity_days 
                ? now()->addDays($voucherPool->validity_days)
                : null;

            $voucher->update([
                'status' => 'used',
                'activated_at' => now(),
                'expires_at' => $expiresAt,
                'updated_by' => $userId,
            ]);

            $voucherPool->increment('used_vouchers');

            // TODO: Create hotspot user and dispatch events

            return $voucher;
        });
    }
}
