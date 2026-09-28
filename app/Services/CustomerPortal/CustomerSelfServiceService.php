<?php

namespace App\Services\CustomerPortal;

use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;
use App\Models\Customer\CustomerService;
use App\Services\ISP\GenieAcsService;
use App\Services\ISP\HotspotService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Exception;

class CustomerSelfServiceService
{
    public function __construct(
        protected GenieAcsService $genieAcsService,
        protected HotspotService $hotspotService
    ) {}

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
            'password' => $newPassword, // RADIUS requires cleartext, not hashed
        ]);

        // Kick active session so the user is forced to reconnect with the new password
        try {
            $kickService = app(\App\Services\ISP\Session\SessionKickService::class);
            $kickService->kickUsernameGlobally($pppoeUser->username, Auth::user());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to kick PPPoE user after password change: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Password PPPoE berhasil diubah! Anda harus menghubungkan ulang router dengan kredensial baru.'];
    }

    public function getOnuWifiCredentials(int $customerId, int $onuId): array
    {
        return $this->genieAcsService->getWifiCredentials($customerId, $onuId);
    }

    public function getOnuConnectedDevices(int $customerId, int $onuId): array
    {
        return $this->genieAcsService->getConnectedDevices($customerId, $onuId);
    }

    public function changeOnuWifiPassword(int $customerId, int $onuId, string $newPassword): array
    {
        return $this->genieAcsService->updateWifiPassword(
            $customerId,
            $onuId,
            $newPassword
        );
    }

    public function changeOnuWifiCredentials(int $customerId, int $onuId, string $newSsid, ?string $newPassword = null): array
    {
        return $this->genieAcsService->updateWifiSsidAndPassword(
            $customerId,
            $onuId,
            $newSsid,
            $newPassword
        );
    }

    public function changeHotspotCredentials(
        int $customerId, 
        int $hotspotUserId, 
        ?string $newUsername = null, 
        ?string $newPassword = null
    ): array {
        try {
            // Verify ownership of the hotspot user (prevent IDOR)
            $hotspotUser = HotspotUser::where('id', $hotspotUserId)
                ->whereHas('customerService', function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                })
                ->first();

            if (!$hotspotUser) {
                return ['success' => false, 'message' => 'Hotspot User tidak ditemukan atau bukan milik Anda'];
            }

            // Validate username uniqueness if changing
            if ($newUsername && $newUsername !== $hotspotUser->username) {
                $existingUser = HotspotUser::where('username', $newUsername)
                    ->where('id', '!=', $hotspotUserId)
                    ->first();
                if ($existingUser) {
                    return ['success' => false, 'message' => 'Username sudah digunakan'];
                }
            }

            // Update credentials
            $this->hotspotService->updateHotspotUserCredentials(
                $hotspotUserId,
                Auth::id(), // Use customer's user id (Auth::id()) for updated_by
                $newUsername,
                $newPassword
            );

            return [
                'success' => true, 
                'message' => 'Kredensial Hotspot berhasil diubah! Anda harus login ulang dengan kredensial baru.'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengubah kredensial Hotspot'];
        }
    }
}

