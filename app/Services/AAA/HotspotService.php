<?php

namespace App\Services\AAA;

use App\Models\AAA\HotspotUser;
use App\Models\Customer\CustomerService;
use App\Repositories\AAA\HotspotUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class HotspotService
{
    public function __construct(
        protected HotspotUserRepository $hotspotUserRepository,
    ) {}

    public function createHotspotUser(
        ?CustomerService $customerService,
        int $serviceProfileId,
        ?int $voucherPoolId,
        int $userId,
        ?string $username = null,
        ?string $password = null
    ): HotspotUser {
        return DB::transaction(function () use (
            $customerService,
            $serviceProfileId,
            $voucherPoolId,
            $userId,
            $username,
            $password
        ) {
            $hotspotUser = $this->hotspotUserRepository->create([
                'uuid' => (string) Str::uuid(),
                'username' => $username ?? 'hs_user_' . time(),
                'password' => $password ?? Str::random(12),
                'customer_service_id' => $customerService?->id,
                'service_profile_id' => $serviceProfileId,
                'voucher_pool_id' => $voucherPoolId,
                'status' => 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // TODO: Dispatch domain event

            return $hotspotUser;
        });
    }

    public function activateHotspotUser(int $hotspotUserId, int $userId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUserId, $userId) {
            $hotspotUser = $this->hotspotUserRepository->find($hotspotUserId);
            $hotspotUser->update([
                'status' => 'active',
                'activated_at' => now(),
                'updated_by' => $userId,
            ]);

            // TODO: Dispatch domain event

            return $hotspotUser;
        });
    }

    public function suspendHotspotUser(int $hotspotUserId, int $userId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUserId, $userId) {
            $hotspotUser = $this->hotspotUserRepository->find($hotspotUserId);
            $hotspotUser->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'updated_by' => $userId,
            ]);

            // TODO: Dispatch domain event

            return $hotspotUser;
        });
    }

    public function terminateHotspotUser(int $hotspotUserId, int $userId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUserId, $userId) {
            $hotspotUser = $this->hotspotUserRepository->find($hotspotUserId);
            $hotspotUser->update([
                'status' => 'terminated',
                'terminated_at' => now(),
                'updated_by' => $userId,
            ]);

            // TODO: Dispatch domain event

            return $hotspotUser;
        });
    }
}
