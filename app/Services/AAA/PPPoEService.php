<?php

namespace App\Services\AAA;

use App\Models\AAA\PPPoEUser;
use App\Models\Customer\CustomerService;
use App\Repositories\AAA\PPPoEUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Domain\AAA\Events\PPPoEUserCreatedEvent;
use Src\Domain\AAA\Events\PPPoEUserActivatedEvent;
use Src\Domain\AAA\Events\PPPoEUserSuspendedEvent;
use Src\Domain\AAA\Events\PPPoEUserReactivatedEvent;
use Src\Domain\AAA\Events\PPPoEUserTerminatedEvent;

class PPPoEService
{
    public function __construct(
        protected PPPoEUserRepository $pppoeUserRepository,
    ) {}

    public function createPPPoEUser(
        CustomerService $customerService,
        int $serviceProfileId,
        ?int $ipAllocationId,
        int $userId,
        ?string $username = null,
        ?string $password = null
    ): PPPoEUser {
        return DB::transaction(function () use (
            $customerService,
            $serviceProfileId,
            $ipAllocationId,
            $userId,
            $username,
            $password
        ) {
            $pppoeUser = $this->pppoeUserRepository->create([
                'uuid' => (string) Str::uuid(),
                'username' => $username ?? 'user_' . $customerService->id,
                'password' => $password ?? Str::random(12),
                'customer_service_id' => $customerService->id,
                'service_profile_id' => $serviceProfileId,
                'ip_allocation_id' => $ipAllocationId,
                'status' => 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new PPPoEUserCreatedEvent($pppoeUser->uuid, $customerService->id));

            return $pppoeUser;
        });
    }

    public function activatePPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $pppoeUser->update([
                'status' => 'active',
                'activated_at' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new PPPoEUserActivatedEvent($pppoeUser->uuid, $pppoeUser->customer_service_id));

            return $pppoeUser;
        });
    }

    public function suspendPPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $pppoeUser->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new PPPoEUserSuspendedEvent($pppoeUser->uuid, $pppoeUser->customer_service_id));

            return $pppoeUser;
        });
    }

    public function reactivatePPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $pppoeUser->update([
                'status' => 'active',
                'suspended_at' => null,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new PPPoEUserReactivatedEvent($pppoeUser->uuid, $pppoeUser->customer_service_id));

            return $pppoeUser;
        });
    }

    public function terminatePPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $pppoeUser->update([
                'status' => 'terminated',
                'terminated_at' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new PPPoEUserTerminatedEvent($pppoeUser->uuid, $pppoeUser->customer_service_id));

            return $pppoeUser;
        });
    }
}
