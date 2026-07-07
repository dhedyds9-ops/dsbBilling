<?php

namespace App\Services\CustomerPortal;

use App\Models\AAA\PPPoEUser;
use App\Models\Customer\CustomerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Domain\AAA\Events\PPPoEUserReactivatedEvent;

class CustomerSelfServiceService
{
    public function changePppoePassword(int $customerId, string $newPassword): array
    {
        $customerService = CustomerService::where('customer_id', $customerId)
            ->where('status', 'active')
            ->first();

        if (!$customerService) {
            return ['success' => false, 'message' => 'Layanan tidak ditemukan'];
        }

        $pppoeUser = PPPoEUser::where('customer_service_id', $customerService->id)->first();

        if (!$pppoeUser) {
            return ['success' => false, 'message' => 'PPPoE User tidak ditemukan'];
        }

        $pppoeUser->update([
            'password' => Hash::make($newPassword),
        ]);

        // You can dispatch PPPoE Provision Job here to sync with router
        // \App\Jobs\AAA\ProvisionPPPoEUserJob::dispatch($pppoeUser);

        return ['success' => true, 'message' => 'Password PPPoE berhasil diubah'];
    }
}

