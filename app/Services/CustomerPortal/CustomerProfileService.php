<?php

namespace App\Services\CustomerPortal;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerProfileService
{
    public function getProfile(int $userId): ?User
    {
        return User::find($userId);
    }

    public function updateProfile(int $userId, array $data): array
    {
        $user = User::findOrFail($userId);

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'whatsapp' => $data['phone'] ?? $user->whatsapp,
        ]);

        $customer = \App\Models\CRM\Customer::where('user_id', $user->id)->first();
        if ($customer) {
            $customer->update([
                'name' => $data['name'] ?? $customer->name,
                'email' => $data['email'] ?? $customer->email,
                'phone' => $data['phone'] ?? $customer->phone,
            ]);
        }

        return ['success' => true, 'message' => 'Profil berhasil diperbarui'];
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = User::findOrFail($userId);

        if (!Hash::check($currentPassword, $user->password)) {
            return ['success' => false, 'message' => 'Password saat ini salah'];
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Password berhasil diubah'];
    }
}

