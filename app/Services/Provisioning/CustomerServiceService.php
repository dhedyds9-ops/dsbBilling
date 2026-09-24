<?php

namespace App\Services\Provisioning;

use App\Models\Customer\CustomerService;
use App\Models\Customer\Contract;
use App\Models\ServiceCatalog\Service;
use App\Models\ISP\PPPoEUser;
use App\Models\Provisioning\ServiceInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Domain\Customer\Events\CustomerServiceCreatedEvent;
use Src\Domain\Customer\Events\ServiceActivatedEvent;
use Src\Domain\Customer\Events\ServiceSuspendedEvent;
use Src\Domain\Customer\Events\ServiceReactivatedEvent;
use Src\Domain\Customer\Events\ServiceTerminatedEvent;

class CustomerServiceService
{
    public function createCustomerService(
        int $customerId,
        int $contractId,
        int $serviceId,
        int $userId,
        array $attributes = []
    ): CustomerService {
        return DB::transaction(function () use ($customerId, $contractId, $serviceId, $userId, $attributes) {
            $customerService = CustomerService::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customerId,
                'contract_id' => $contractId,
                'service_id' => $serviceId,
                'status' => 'pending',
                'attributes' => $attributes,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new CustomerServiceCreatedEvent(
                $customerService->uuid,
                $customerId,
                $serviceId
            ));

            return $customerService;
        });
    }

    public function activateService(int $customerServiceId, int $serviceInstanceId, int $userId): CustomerService
    {
        return DB::transaction(function () use ($customerServiceId, $serviceInstanceId, $userId) {
            $customerService = CustomerService::findOrFail($customerServiceId);
            $customerService->update([
                'status' => 'active',
                'activated_at' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new ServiceActivatedEvent(
                $customerService->uuid,
                $serviceInstanceId
            ));

            $serviceInstance = ServiceInstance::with('serviceProfile')->find($serviceInstanceId);
            $pppoeUser = PPPoEUser::where('customer_service_id', $customerServiceId)->first();
            if (!$pppoeUser && $serviceInstance) {
                $username = 'cust_'.$customerService->customer_id.'_'.uniqid();
                $password = Str::random(12);
                $pppoeUser = PPPoEUser::create([
                    'uuid' => (string) Str::uuid(),
                    'username' => $username,
                    'password' => \Illuminate\Support\Facades\Crypt::encryptString($password),
                    'customer_service_id' => $customerServiceId,
                    'service_profile_id' => $serviceInstance->service_profile_id,
                    'ip_allocation_id' => $serviceInstance->ip_allocation_id,
                    'status' => 'active',
                    'activated_at' => now(),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

            } elseif ($pppoeUser) {
                $pppoeUser->update(['status' => 'active', 'updated_by' => $userId]);
            }

            return $customerService;
        });
    }

    public function suspendService(int $customerServiceId, string $reason, int $userId): CustomerService
    {
        return DB::transaction(function () use ($customerServiceId, $reason, $userId) {
            $customerService = CustomerService::findOrFail($customerServiceId);
            $customerService->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new ServiceSuspendedEvent(
                $customerService->uuid,
                $reason
            ));

            $pppoeUser = PPPoEUser::where('customer_service_id', $customerServiceId)->first();
            if ($pppoeUser) {
                $pppoeUser->update(['status' => 'suspended', 'updated_by' => $userId]);
            }

            return $customerService;
        });
    }

    public function reactivateService(int $customerServiceId, int $serviceInstanceId, int $userId): CustomerService
    {
        return DB::transaction(function () use ($customerServiceId, $serviceInstanceId, $userId) {
            $customerService = CustomerService::findOrFail($customerServiceId);
            $customerService->update([
                'status' => 'active',
                'suspended_at' => null,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new ServiceReactivatedEvent(
                $customerService->uuid,
                $serviceInstanceId
            ));

            $pppoeUser = PPPoEUser::where('customer_service_id', $customerServiceId)->first();
            if ($pppoeUser) {
                $pppoeUser->update(['status' => 'active', 'updated_by' => $userId]);
            }

            return $customerService;
        });
    }

    public function terminateService(int $customerServiceId, int $serviceInstanceId, string $reason, int $userId): CustomerService
    {
        return DB::transaction(function () use ($customerServiceId, $serviceInstanceId, $reason, $userId) {
            $customerService = CustomerService::findOrFail($customerServiceId);
            $customerService->update([
                'status' => 'terminated',
                'terminated_at' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new ServiceTerminatedEvent(
                $customerService->uuid,
                $serviceInstanceId,
                $reason
            ));

            $pppoeUser = PPPoEUser::where('customer_service_id', $customerServiceId)->first();
            if ($pppoeUser) {
                $pppoeUser->update(['status' => 'terminated', 'updated_by' => $userId]);
            }

            return $customerService;
        });
    }
}



